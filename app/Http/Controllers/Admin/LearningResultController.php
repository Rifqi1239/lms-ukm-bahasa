<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LanguageGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LearningResultController extends Controller
{
    /**
     * Menampilkan ringkasan hasil belajar anggota.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            in_array($user->role, ['admin', 'pengajar']),
            403
        );

        // Admin dapat memilih kelompok bahasa.
        // Pengajar otomatis menggunakan kelompoknya sendiri.
        $groups = $user->role === 'admin'
            ? LanguageGroup::orderBy('name')->get()
            : LanguageGroup::where('id', $user->language_group_id)->get();

        $selectedGroup = null;
        $members = collect();

        $totalMembers = 0;
        $membersWithProgress = 0;
        $averageProgress = 0;
        $totalCompletedAttempts = 0;

        if ($user->role === 'admin' && $request->filled('language_group_id')) {
            $validated = $request->validate([
                'language_group_id' => [
                    'required',
                    'exists:language_groups,id',
                ],
            ]);

            $selectedGroup = LanguageGroup::findOrFail(
                $validated['language_group_id']
            );
        } elseif ($user->role === 'pengajar') {
            abort_unless($user->language_group_id, 403);

            $selectedGroup = LanguageGroup::findOrFail(
                $user->language_group_id
            );
        }

        if ($selectedGroup) {
            $groupId = $selectedGroup->id;

            $memberQuery = User::where('role', 'anggota')
                ->where('language_group_id', $groupId);

            $totalMembers = (clone $memberQuery)->count();

            $membersWithProgress = (clone $memberQuery)
                ->whereHas('readingProgress')
                ->count();

            $averageProgress = DB::table('reading_progress')
                ->join('users', 'users.id', '=', 'reading_progress.user_id')
                ->where('users.role', 'anggota')
                ->where('users.language_group_id', $groupId)
                ->avg('reading_progress.progress_percentage') ?? 0;

            $totalCompletedAttempts = DB::table('quiz_attempts')
                ->join('users', 'users.id', '=', 'quiz_attempts.user_id')
                ->where('users.role', 'anggota')
                ->where('users.language_group_id', $groupId)
                ->whereNotNull('quiz_attempts.completed_at')
                ->count();

            $members = (clone $memberQuery)
                ->with('languageGroup')
                ->withCount([
                    'readingProgress as total_reading_modules',
                    'quizAttempts as completed_attempts' => function ($query) {
                        $query->whereNotNull('completed_at');
                    },
                ])
                ->withAvg(
                    'readingProgress as average_progress',
                    'progress_percentage'
                )
                ->addSelect([
                    'best_quiz_score' => DB::table('quiz_attempts')
                        ->selectRaw(
                            'MAX(score * 100.0 / NULLIF(total_questions, 0))'
                        )
                        ->whereColumn('user_id', 'users.id')
                        ->whereNotNull('completed_at')
                        ->where('total_questions', '>', 0),
                ])
                ->orderBy('name')
                ->get();
        }

        return view('admin.learning-results.index', compact(
            'groups',
            'selectedGroup',
            'members',
            'totalMembers',
            'membersWithProgress',
            'averageProgress',
            'totalCompletedAttempts'
        ));
    }
}
