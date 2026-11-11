<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LanguageGroup;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ModuleController extends Controller
{
    /**
     * Menampilkan daftar modul.
     */
    public function index(Request $request): View
    {
        $modules = Module::with('languageGroup')
            ->withCount('questions')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where('title', 'like', "%{$search}%");
            })
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Menampilkan formulir tambah modul.
     */
    public function create(): View
    {
        $languageGroups = LanguageGroup::orderBy('name')->get();

        return view('admin.modules.create', compact('languageGroups'));
    }

    /**
     * Menyimpan modul baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'language_group_id' => [
                'required',
                'integer',
                'exists:language_groups,id',
            ],
            'pdf_file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
            'cover_mode' => [
                'required',
                'in:automatic,upload',
            ],
            'cover_image' => [
                'required_if:cover_mode,upload',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $pdfPath = $request->file('pdf_file')
            ->store('modules/pdfs', 'public');

        $coverPath = null;

        if ($validated['cover_mode'] === 'upload') {
            $coverPath = $request->file('cover_image')
                ->store('modules/covers', 'public');
        }

        Module::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'language_group_id' => $validated['language_group_id'],
            'pdf_file' => $pdfPath,
            'cover_image' => $coverPath,
            'has_exercise' => false,
            'question_count' => 0,
        ]);

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Modul berhasil disimpan.');
    }

    /**
     * Menampilkan formulir edit modul.
     */
    public function edit(Module $module): View
    {
        $languageGroups = LanguageGroup::orderBy('name')->get();

        return view('admin.modules.edit', compact('module', 'languageGroups'));
    }

    /**
     * Menampilkan PDF melalui route Laravel untuk dibaca PDF.js.
     */
    public function pdf(Module $module): StreamedResponse
    {
        $pdfPath = $module->getAttribute('pdf_file');

        abort_unless(
            $pdfPath &&
            Storage::disk('public')->exists($pdfPath),
            404,
            'File PDF modul tidak ditemukan.'
        );

        $stream = Storage::disk('public')->readStream($pdfPath);

        abort_unless(
            is_resource($stream),
            404,
            'File PDF modul tidak ditemukan.'
        );

        return response()->stream(
            function () use ($stream): void {
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="modul.pdf"',
                'Cache-Control' => 'private, max-age=300',
            ]
        );
    }

    /**
     * Memperbarui modul.
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'language_group_id' => [
                'required',
                'integer',
                'exists:language_groups,id',
            ],
            'pdf_file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
            'cover_mode' => [
                'required',
                'in:automatic,upload',
            ],
            'cover_image' => [
                'nullable',
                'required_if:cover_mode,automatic',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $oldPdfPath = $module->pdf_file;
        $oldCoverPath = $module->cover_image;

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'language_group_id' => $validated['language_group_id'],
        ];

        if ($request->hasFile('pdf_file')) {
            $data['pdf_file'] = $request->file('pdf_file')
                ->store('modules/pdfs', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')
                ->store('modules/covers', 'public');
        }

        $module->update($data);

        if (
            $request->hasFile('pdf_file') &&
            $oldPdfPath &&
            $oldPdfPath !== $module->pdf_file
        ) {
            Storage::disk('public')->delete($oldPdfPath);
        }

        if (
            $request->hasFile('cover_image') &&
            $oldCoverPath &&
            $oldCoverPath !== $module->cover_image
        ) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Modul berhasil diperbarui.');
    }

    /**
     * Menghapus modul dan file terkait.
     */
    public function destroy(Module $module)
    {
        $pdfPath = $module->pdf_file;
        $coverPath = $module->cover_image;

        $module->delete();

        if ($pdfPath) {
            Storage::disk('public')->delete($pdfPath);
        }

        if ($coverPath) {
            Storage::disk('public')->delete($coverPath);
        }

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Modul berhasil dihapus.');
    }
}
