<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    /**
     * Mengambil pengguna yang sedang login.
     */
    private function currentUser(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user, 401, 'Sesi pengguna tidak valid.');

        return $user;
    }

    /**
     * Memastikan pengajar sudah terdaftar dalam kelompok bahasa.
     */
    private function authorizeTeacherGroup(): void
    {
        $user = $this->currentUser();

        if ($user->role === 'pengajar') {
            abort_unless(
                $user->language_group_id,
                403,
                'Akun pengajar belum terdaftar pada kelompok bahasa.'
            );
        }
    }

    /**
     * Memastikan soal berasal dari kelompok bahasa pengajar.
     */
    private function authorizeQuestionAccess(Question $question): void
    {
        $user = $this->currentUser();

        if ($user->role === 'pengajar') {
            $this->authorizeTeacherGroup();

            abort_unless(
                $question->module &&
                $question->module->language_group_id == $user->language_group_id,
                403,
                'Anda tidak memiliki akses ke soal ini.'
            );
        }
    }

    /**
     * Menampilkan daftar soal.
     */
    public function index(Request $request)
    {
        $user = $this->currentUser();

        $this->authorizeTeacherGroup();

        // Modul yang dapat dipilih.
        $modules = Module::when(
            $user->role === 'pengajar',
            function ($query) use ($user) {
                $query->where(
                    'language_group_id',
                    $user->language_group_id
                );
            }
        )
            ->orderBy('title')
            ->get();

        $selectedModuleId = $request->query('module_id');

        // Daftar soal dibatasi berdasarkan kelompok bahasa.
        $questions = Question::with(['module', 'options'])
            ->when(
                $user->role === 'pengajar',
                function ($query) use ($user) {
                    $query->whereHas('module', function ($moduleQuery) use ($user) {
                        $moduleQuery->where(
                            'language_group_id',
                            $user->language_group_id
                        );
                    });
                }
            )
            ->when($selectedModuleId, function ($query, $moduleId) {
                $query->where('module_id', $moduleId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Soal yang dipilih untuk diedit.
        $selectedQuestion = null;

        if ($request->filled('edit')) {
            $selectedQuestion = Question::with('options')
                ->when(
                    $user->role === 'pengajar',
                    function ($query) use ($user) {
                        $query->whereHas('module', function ($moduleQuery) use ($user) {
                            $moduleQuery->where(
                                'language_group_id',
                                $user->language_group_id
                            );
                        });
                    }
                )
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
        $user = $this->currentUser();

        $this->authorizeTeacherGroup();

        $modules = Module::when(
            $user->role === 'pengajar',
            function ($query) use ($user) {
                $query->where(
                    'language_group_id',
                    $user->language_group_id
                );
            }
        )
            ->orderBy('title')
            ->get();

        return view('admin.questions.create', compact('modules'));
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
                    'is_correct' => $label === $validated['correct_option'],
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
        $user = $this->currentUser();

        $this->authorizeQuestionAccess($question);

        $question->load('options');

        $modules = Module::when(
            $user->role === 'pengajar',
            function ($query) use ($user) {
                $query->where(
                    'language_group_id',
                    $user->language_group_id
                );
            }
        )
            ->orderBy('title')
            ->get();

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
        $this->authorizeQuestionAccess($question);

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
                        'is_correct' => $label === $validated['correct_option'],
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
        $this->authorizeQuestionAccess($question);

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
        $user = $this->currentUser();

        $this->authorizeTeacherGroup();

        $moduleRule = Rule::exists('modules', 'id');

        if ($user->role === 'pengajar') {
            $moduleRule->where(function ($query) use ($user) {
                $query->where(
                    'language_group_id',
                    $user->language_group_id
                );
            });
        }

        return $request->validate([
            'module_id' => [
                'required',
                'integer',
                $moduleRule,
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
