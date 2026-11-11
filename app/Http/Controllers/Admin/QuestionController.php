<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    /**
     * Menampilkan daftar soal.
     */

    /**
     * Menampilkan daftar soal dan menyiapkan form tambah/edit.
     */
    public function index(Request $request)
    {
        $modules = Module::orderBy('title')->get();

        $selectedModuleId = $request->query('module_id');

        $questions = Question::with(['module', 'options'])
            ->when($selectedModuleId, function ($query, $moduleId) {
                $query->where('module_id', $moduleId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Soal yang dipilih untuk diedit pada panel sebelah kanan.
        $selectedQuestion = null;

        if ($request->filled('edit')) {
            $selectedQuestion = Question::with('options')
                ->when($selectedModuleId, function ($query, $moduleId) {
                    $query->where('module_id', $moduleId);
                })
                ->findOrFail($request->query('edit'));
        }

        return view('admin.questions.index', compact(
            'modules',
            'questions',
            'selectedModuleId',
            'selectedQuestion'
        ));
    }


    /**
     * Menampilkan form tambah soal.
     */
    public function create()
    {
        $modules = Module::orderBy('title')->get();

        return view('admin.questions.create', [
            'modules' => $modules,
        ]);
    }

    /**
     * Menyimpan soal dan lima pilihan jawaban.
     */
    public function store(Request $request)
    {
        $validated = $this->validateQuestion($request);

        DB::transaction(function () use ($validated) {
            $question = Question::create([
                'module_id' => $validated['module_id'],
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $label) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'option_text' => $validated['options'][$label],
                    'is_correct' =>
                    $label === $validated['correct_option'],
                ]);
            }
        });

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit soal.
     */
    public function edit(Question $question)
    {
        $question->load('options');

        $modules = Module::orderBy('title')->get();

        return view('admin.questions.edit', compact(
            'question',
            'modules'
        ));
    }

    /**
     * Memperbarui soal dan pilihan jawaban.
     */
    public function update(Request $request, Question $question)
    {
        $validated = $this->validateQuestion($request);

        DB::transaction(function () use ($validated, $question) {
            $question->update([
                'module_id' => $validated['module_id'],
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $label) {
                QuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'label' => $label,
                    ],
                    [
                        'option_text' => $validated['options'][$label],
                        'is_correct' =>
                        $label === $validated['correct_option'],
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Menghapus soal.
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Validasi data soal untuk tambah dan edit.
     */
    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'module_id' => [
                'required',
                'integer',
                'exists:modules,id',
            ],
            'question_text' => [
                'required',
                'string',
            ],
            'explanation' => [
                'nullable',
                'string',
            ],
            'options' => [
                'required',
                'array',
                'size:5',
            ],
            'options.A' => ['required', 'string', 'max:1000'],
            'options.B' => ['required', 'string', 'max:1000'],
            'options.C' => ['required', 'string', 'max:1000'],
            'options.D' => ['required', 'string', 'max:1000'],
            'options.E' => ['required', 'string', 'max:1000'],
            'correct_option' => [
                'required',
                Rule::in(['A', 'B', 'C', 'D', 'E']),
            ],
        ]);
    }
}
