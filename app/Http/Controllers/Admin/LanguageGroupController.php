<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LanguageGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageGroupController extends Controller
{
    /**
     * Menampilkan daftar kelompok bahasa.
     */
    public function index(Request $request)
    {
        $query = LanguageGroup::query()
            ->withCount(['users', 'modules']);

        // Pencarian nama atau kode bahasa
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $languageGroups = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Statistik
        $totalKelompok = LanguageGroup::count();

        $kelompokAktif = LanguageGroup::where(
            'is_active',
            true
        )->count();

        $kelompokNonaktif = LanguageGroup::where(
            'is_active',
            false
        )->count();

        $totalPengguna = LanguageGroup::withCount('users')
            ->get()
            ->sum('users_count');

        return view('admin.language-groups.index', compact(
            'languageGroups',
            'totalKelompok',
            'kelompokAktif',
            'kelompokNonaktif',
            'totalPengguna'
        ));
    }

    /**
     * Menampilkan form tambah kelompok bahasa.
     */
    public function create()
    {
        return view('admin.language-groups.create');
    }

    /**
     * Menyimpan kelompok bahasa baru.
     */
    public function store(Request $request)
    {
        $request->merge([
            'code' => strtoupper(trim($request->code ?? '')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                'unique:language_groups,code',
            ],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama kelompok bahasa wajib diisi.',
            'code.required' => 'Kode bahasa wajib diisi.',
            'code.unique' => 'Kode bahasa tersebut sudah digunakan.',
            'code.alpha_dash' => 'Kode hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
        ]);

        $validated['is_active'] = true;

        LanguageGroup::create($validated);

        return redirect()
            ->route('admin.language-groups.index')
            ->with('success', 'Kelompok bahasa berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kelompok bahasa.
     */
    public function edit(LanguageGroup $languageGroup)
    {
        return view('admin.language-groups.edit', compact(
            'languageGroup'
        ));
    }

    /**
     * Memperbarui kelompok bahasa.
     */
    public function update(
        Request $request,
        LanguageGroup $languageGroup
    ) {
        $request->merge([
            'code' => strtoupper(trim($request->code ?? '')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('language_groups', 'code')
                    ->ignore($languageGroup->id),
            ],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama kelompok bahasa wajib diisi.',
            'code.required' => 'Kode bahasa wajib diisi.',
            'code.unique' => 'Kode bahasa tersebut sudah digunakan.',
            'code.alpha_dash' => 'Kode hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
        ]);

        $languageGroup->update($validated);

        return redirect()
            ->route('admin.language-groups.index')
            ->with('success', 'Kelompok bahasa berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan kelompok bahasa.
     */
    public function toggleStatus(LanguageGroup $languageGroup)
    {
        $languageGroup->update([
            'is_active' => !$languageGroup->is_active,
        ]);

        $message = $languageGroup->is_active
            ? 'Kelompok bahasa berhasil diaktifkan.'
            : 'Kelompok bahasa berhasil dinonaktifkan.';

        return redirect()
            ->route('admin.language-groups.index')
            ->with('success', $message);
    }
}
