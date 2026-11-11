<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinalGradeRecord;
use App\Models\LanguageGroup;
use App\Models\User;
use App\Services\FinalGradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FinalGradeController extends Controller
{
    /**
     * Menampilkan daftar penilaian anggota.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            in_array($user->role, ['admin', 'pengajar']),
            403
        );

        $groups = $user->role === 'admin'
            ? LanguageGroup::orderBy('name')->get()
            : LanguageGroup::where('id', $user->language_group_id)->get();

        $selectedGroup = null;
        $members = collect();

        if ($user->role === 'admin' && $request->filled('language_group_id')) {
            $request->validate([
                'language_group_id' => [
                    'required',
                    'exists:language_groups,id',
                ],
            ]);

            $selectedGroup = LanguageGroup::findOrFail(
                $request->language_group_id
            );
        } elseif ($user->role === 'pengajar') {
            abort_unless($user->language_group_id, 403);

            $selectedGroup = LanguageGroup::findOrFail(
                $user->language_group_id
            );
        }

        if ($selectedGroup) {
            $members = User::where('role', 'anggota')
                ->where('language_group_id', $selectedGroup->id)
                ->orderBy('name')
                ->get();

            $records = FinalGradeRecord::where(
                'language_group_id',
                $selectedGroup->id
            )
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('user_id');

            foreach ($members as $member) {
                $memberRecords = $records->get($member->id, collect());

                $member->latestPendingRecord = $memberRecords
                    ->firstWhere('status', 'pending');

                $member->latestApprovedRecord = $memberRecords
                    ->firstWhere('status', 'approved');
            }
        }

        return view('admin.final-grades.index', compact(
            'groups',
            'selectedGroup',
            'members'
        ));
    }

    /**
     * Menghitung ulang nilai seluruh anggota dalam kelompok bahasa.
     */
    public function calculate(
        Request $request,
        FinalGradeService $service
    ) {
        $group = $this->resolveGroup($request);

        $records = $service->calculateForGroup(
            $group,
            Auth::user()
        );

        return redirect()
            ->route('admin.final-grades.index', [
                'language_group_id' => $group->id,
            ])
            ->with(
                'success',
                'Perhitungan nilai berhasil. '
                . $records->count()
                . ' anggota mendapatkan hasil penilaian baru.'
            );
    }

    /**
     * Menyetujui hasil penilaian seorang anggota.
     */
    public function approve(FinalGradeRecord $record)
    {
        $user = Auth::user();

        abort_unless(
            in_array($user->role, ['admin', 'pengajar']),
            403
        );

        $this->authorizeGroup($record->language_group_id);

        abort_unless(
            $record->status === 'pending',
            403,
            'Penilaian ini sudah diproses.'
        );

        // Pastikan tidak ada hasil perhitungan yang lebih baru.
        $latestPending = FinalGradeRecord::where('user_id', $record->user_id)
            ->where('language_group_id', $record->language_group_id)
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();

        abort_unless(
            $latestPending && $latestPending->id === $record->id,
            403,
            'Ada hasil perhitungan yang lebih baru. Silakan periksa hasil terbaru.'
        );

        $record->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.final-grades.index', [
                'language_group_id' => $record->language_group_id,
            ])
            ->with('success', 'Nilai anggota berhasil disetujui.');
    }

    /**
     * Menentukan kelompok bahasa yang boleh dikelola pengguna.
     */
    private function resolveGroup(Request $request): LanguageGroup
    {
        $user = Auth::user();

        abort_unless(
            in_array($user->role, ['admin', 'pengajar']),
            403
        );

        if ($user->role === 'admin') {
            $validated = $request->validate([
                'language_group_id' => [
                    'required',
                    'exists:language_groups,id',
                ],
            ]);

            return LanguageGroup::findOrFail(
                $validated['language_group_id']
            );
        }

        abort_unless($user->language_group_id, 403);

        return LanguageGroup::findOrFail($user->language_group_id);
    }

    /**
     * Memastikan pengguna hanya mengakses kelompok bahasanya.
     */
    private function authorizeGroup(int $languageGroupId): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        abort_unless(
            $user->role === 'pengajar'
            && (int) $user->language_group_id === $languageGroupId,
            403
        );
    }
}
