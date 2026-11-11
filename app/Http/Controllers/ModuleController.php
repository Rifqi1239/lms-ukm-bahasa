<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $modules = Module::where(
            'language_group_id',
            $user->language_group_id
        )
            ->withCount('questions');

        if ($request->filled('search')) {
            $search = $request->search;

            $modules->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filter === 'exercise') {
            $modules->has('questions');
        } elseif ($request->filter === 'no-exercise') {
            $modules->doesntHave('questions');
        }

        if ($request->sort === 'oldest') {
            $modules->orderBy('created_at', 'asc');
        } else {
            $modules->orderBy('created_at', 'desc');
        }

        $modules = $modules->get();

        return view('modules.index', compact('modules'));
    }

    /**
     * Menampilkan halaman deskripsi modul.
     */
    public function show(Module $module): View
    {
        $user = Auth::user();

        $isAdmin = $user->role === 'admin';

        if (
            !$isAdmin &&
            (
                $user->language_group_id === null ||
                (int) $user->language_group_id !== (int) $module->language_group_id
            )
        ) {
            abort(403, 'Kamu tidak memiliki akses ke modul ini.');
        }

        $module->load('languageGroup');
        $module->loadCount('questions');

        return view('modules.show', compact('module'));
    }

    /**
     * Menampilkan PDF reader beserta progres membaca pengguna.
     */
    public function read(Module $module): View
    {
        $user = Auth::user();

        $isAdmin = $user->role === 'admin';

        if (
            !$isAdmin &&
            (
                $user->language_group_id === null ||
                (int) $user->language_group_id !== (int) $module->language_group_id
            )
        ) {
            abort(403, 'Kamu tidak memiliki akses ke modul ini.');
        }

        if (!$module->pdf_file) {
            abort(404, 'File PDF modul belum tersedia.');
        }

        $path = $module->pdf_file;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        $readingProgress = ReadingProgress::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        return view('modules.read', compact(
            'module',
            'readingProgress'
        ));
    }

    /**
     * Menyimpan progres membaca pengguna.
     */
    public function saveProgress(
        Request $request,
        Module $module
    ): JsonResponse {
        $user = Auth::user();

        $isAdmin = $user->role === 'admin';

        if (
            !$isAdmin &&
            (
                $user->language_group_id === null ||
                (int) $user->language_group_id !== (int) $module->language_group_id
            )
        ) {
            abort(403, 'Kamu tidak memiliki akses ke modul ini.');
        }

        $validated = $request->validate([
            'current_page' => ['required', 'integer', 'min:1'],
            'total_pages' => ['required', 'integer', 'min:1'],
        ]);

        $currentPage = (int) $validated['current_page'];
        $totalPages = (int) $validated['total_pages'];

        if ($currentPage > $totalPages) {
            return response()->json([
                'message' => 'Halaman yang dikirim tidak valid.',
            ], 422);
        }

        $percentage = (int) round(
            ($currentPage / $totalPages) * 100
        );

        ReadingProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_id' => $module->id,
            ],
            [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'progress_percentage' => $percentage,
                'last_read_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Progres membaca berhasil disimpan.',
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'progress_percentage' => $percentage,
        ]);
    }

    /**
     * Menampilkan file PDF.
     */
    public function streamPdf(Module $module)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        if (
            !$isAdmin &&
            (
                $user->language_group_id === null ||
                (int) $user->language_group_id !== (int) $module->language_group_id
            )
        ) {
            abort(403, 'Kamu tidak memiliki akses ke modul ini.');
        }

        abort_unless($module->pdf_file, 404, 'File PDF belum tersedia.');

        $disk = Storage::disk('public');
        $path = $module->pdf_file;

        abort_unless($disk->exists($path), 404, 'File PDF tidak ditemukan.');

        return response()->file($disk->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="modul.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
