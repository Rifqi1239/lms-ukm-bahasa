<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LanguageGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    /**
     * Menampilkan daftar anggota.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');

        $users = User::with('languageGroup')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $totalAnggota = User::where('role', 'anggota')->count();

        $anggotaAktif = User::where('role', 'anggota')
            ->where('is_active', true)
            ->count();

        $anggotaPerKelompok = User::where('role', 'anggota')
            ->whereNotNull('language_group_id')
            ->distinct('language_group_id')
            ->count('language_group_id');

        $totalAdmin = User::where('role', 'admin')->count();

        $languageGroups = LanguageGroup::orderBy('name')->get();

        return view('admin.members.index', compact(
            'users',
            'totalAnggota',
            'anggotaAktif',
            'anggotaPerKelompok',
            'totalAdmin',
            'languageGroups'
        ));
    }

    /**
     * Menampilkan halaman tambah anggota.
     */
    public function create()
    {
        $languageGroups = LanguageGroup::orderBy('name')->get();

        return view('admin.members.create', compact('languageGroups'));
    }

    /**
     * Menyimpan anggota baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'pengajar', 'anggota'])],
            'language_group_id' => ['nullable', 'exists:language_groups,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $member = new User();

        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->password = Hash::make($validated['password']);
        $member->role = $validated['role'];
        $member->language_group_id = $validated['language_group_id'] ?? null;
        $member->is_active = $validated['is_active'];

        $member->save();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Menampilkan halaman edit anggota.
     */
    public function edit(User $member)
    {
        $languageGroups = LanguageGroup::orderBy('name')->get();

        return view('admin.members.edit', compact(
            'member',
            'languageGroups'
        ));
    }

    /**
     * Memperbarui data anggota.
     */
    public function update(Request $request, User $member)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($member->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'pengajar', 'anggota'])],
            'language_group_id' => ['nullable', 'exists:language_groups,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->role = $validated['role'];
        $member->language_group_id = $validated['language_group_id'] ?? null;
        $member->is_active = $validated['is_active'];

        if (!empty($validated['password'])) {
            $member->password = Hash::make($validated['password']);
        }

        $member->save();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Menghapus anggota.
     */
    public function destroy(User $member)
    {
        if ($member->id === auth()->id()) {
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
