<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModulePractice;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    private function currentUser(): User
    {
        $user = Auth::user();

        abort_unless($user, 401, 'Sesi pengguna tidak valid.');

        return $user;
    }

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
     * Memastikan soal pada latihan penilaian tidak diubah
     * selama periode penilaian sedang berlangsung.
     */
    private function ensureQuestionCanBeModified(Question $question): void
    {
        $question->loadMissing('practice');

        $practice = $question->practice;

        if (
            $practice &&
            $practice->type === 'assessment' &&
            $practice->isAvailable()
        ) {
            throw ValidationException::withMessages([
                'practice_type' => 'Soal tidak dapat diubah selama latihan penilaian sedang berlangsung.',
            ]);
        }
    }

    /**
     * Mengambil atau membuat latihan berdasarkan modul dan jenisnya.
     */
    private function getOrCreatePractice(array $data): ModulePractice
    {
        $practice = ModulePractice::firstOrNew([
            'module_id' => $data['module_id'],
            'type' => $data['practice_type'],
        ]);

        $hasAttempts = $practice->exists
            && $practice->quizAttempts()->exists();

        if ($practice->exists && $hasAttempts) {
            if ($practice->type === 'assessment') {
                $newStart = $data['start_at'] ?? null;
                $newDeadline = $data['deadline_at'] ?? null;

                $oldStart = $practice->start_at?->format('Y-m-d H:i:s');
                $oldDeadline = $practice->deadline_at?->format('Y-m-d H:i:s');

                if (
                    $oldStart !== $newStart ||
                    $oldDeadline !== $newDeadline
                ) {
                    throw ValidationException::withMessages([
                        'deadline_at' => 'Jadwal latihan penilaian tidak dapat diubah karena sudah memiliki riwayat pengerjaan.',
                    ]);
                }
            }

            return $practice;
        }

        $practice->is_active = true;

        if ($practice->type === 'independent') {
            $practice->start_at = null;
            $practice->deadline_at = null;
        } else {
            $practice->start_at = $data['start_at'];
            $practice->deadline_at = $data['deadline_at'];
        }

        $practice->save();

        return $practice;
    }

    /**
     * Menampilkan daftar soal.
     */
    public function index(Request $request)
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

        $selectedModuleId = $request->query('module_id');

        $questions = Question::with([
            'module',
            'options',
            'practice',
        ])
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

        $selectedQuestion = null;

        if ($request->filled('edit')) {
            $selectedQuestion = Question::with([
                'options',
                'practice',
            ])
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
            $practice = $this->getOrCreatePractice($validated);

            $question = Question::create([
                'module_id' => $validated['module_id'],
                'module_practice_id' => $practice->id,
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

        $returnModuleId = $request->input('return_module_id')
            ?: $validated['module_id'];

        return redirect()
            ->route('admin.questions.index', [
                'module_id' => $returnModuleId,
            ])
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit soal.
     */
    public function edit(Question $question)
    {
        $user = $this->currentUser();

        $this->authorizeQuestionAccess($question);

        $question->load([
            'options',
            'practice',
        ]);

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

        $this->ensureQuestionCanBeModified($question);

        $validated = $this->validateQuestion($request);

        DB::transaction(function () use ($validated, $question) {
            $currentPractice = ModulePractice::findOrFail(
                $question->module_practice_id
            );

            $newPractice = $this->getOrCreatePractice($validated);

            if (
                $currentPractice->id !== $newPractice->id &&
                $currentPractice->quizAttempts()->exists()
            ) {
                throw ValidationException::withMessages([
                    'practice_type' => 'Jenis latihan soal tidak dapat dipindahkan karena sudah memiliki riwayat pengerjaan.',
                ]);
            }

            $question->update([
                'module_id' => $validated['module_id'],
                'module_practice_id' => $newPractice->id,
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

        $returnModuleId = $request->input('return_module_id')
            ?: $validated['module_id'];

        return redirect()
            ->route('admin.questions.index', [
                'module_id' => $returnModuleId,
            ])
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Menghapus soal.
     */
    public function destroy(Request $request, Question $question)
    {
        $this->authorizeQuestionAccess($question);

        $this->ensureQuestionCanBeModified($question);

        // Tentukan modul yang sedang difilter.
        $returnModuleId = $request->input('return_module_id');

        if (
            $question->quizAnswers()->exists() ||
            $question->practice?->quizAttempts()->exists()
        ) {
            return redirect()
                ->route(
                    'admin.questions.index',
                    $request->filled('return_module_id')
                        ? ['module_id' => $returnModuleId]
                        : []
                )
                ->with(
                    'error',
                    'Soal tidak dapat dihapus karena sudah memiliki riwayat pengerjaan.'
                );
        }

        $question->delete();

        return redirect()
            ->route(
                'admin.questions.index',
                $request->filled('return_module_id')
                    ? ['module_id' => $returnModuleId]
                    : []
            )
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

            'practice_type' => [
                'required',
                Rule::in(['independent', 'assessment']),
            ],

            'start_at' => [
                'nullable',
                'required_if:practice_type,assessment',
                'date',
            ],

            'deadline_at' => [
                'nullable',
                'required_if:practice_type,assessment',
                'date',
                'after:start_at',
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
