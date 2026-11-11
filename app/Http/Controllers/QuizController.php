<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizController extends Controller
{
    /**
     * Memulai latihan atau melanjutkan percobaan yang masih aktif.
     */
    public function start(Module $module)
    {
        $user = Auth::user();

        // Pastikan anggota hanya mengakses modul kelompok bahasanya.
        abort_unless(
            $user->language_group_id !== null &&
                (int) $user->language_group_id === (int) $module->language_group_id,
            403,
            'Kamu tidak memiliki akses ke modul ini.'
        );

        $questionIds = $module->questions()
            ->orderBy('id')
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return redirect()
                ->route('modules.read', $module)
                ->with('info', 'Modul ini belum memiliki soal latihan.');
        }

        // Cari percobaan aktif milik pengguna untuk modul ini.
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if (!$attempt) {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'score' => 0,
                'total_questions' => $questionIds->count(),
                'started_at' => now(),
            ]);
        } else {
            // Perbarui jumlah soal jika ada perubahan sebelum latihan selesai.
            $attempt->update([
                'total_questions' => $questionIds->count(),
            ]);
        }

        // Lanjutkan dari soal pertama yang belum dijawab.
        $answeredQuestionIds = $attempt->answers()
            ->whereIn('question_id', $questionIds)
            ->whereNotNull('selected_option_id')
            ->pluck('question_id');

        $nextQuestionId = $questionIds
            ->first(fn($id) => !$answeredQuestionIds->contains($id));

        // Jika semua sudah dijawab, buka soal pertama agar jawaban bisa ditinjau.
        $questionId = $nextQuestionId ?? $questionIds->first();

        return redirect()->route('quiz.show', [
            'attempt' => $attempt->id,
            'question' => $questionId,
        ]);
    }

    /**
     * Menampilkan hasil latihan dan pembahasan setiap soal.
     */
    public function result(QuizAttempt $attempt): View
    {
        $this->authorizeAttempt($attempt);

        abort_if(
            $attempt->completed_at === null,
            403,
            'Latihan ini belum selesai.'
        );

        $module = $attempt->module;

        $questions = $module->questions()
            ->with('options')
            ->orderBy('id')
            ->get();

        $answers = $attempt->answers()
            ->whereIn('question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        $correctCount = (int) $attempt->score;
        $totalQuestions = (int) $attempt->total_questions;

        $scorePercentage = $totalQuestions > 0
            ? (int) round(($correctCount / $totalQuestions) * 100)
            : 0;

        return view('quiz.result', compact(
            'attempt',
            'module',
            'questions',
            'answers',
            'correctCount',
            'totalQuestions',
            'scorePercentage'
        ));
    }

    /**
     * Menampilkan riwayat latihan milik pengguna yang login.
     */
    public function history(): View
    {
        $user = Auth::user();

        $attempts = QuizAttempt::with('module')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->paginate(10);

        return view('quiz.history', compact('attempts'));
    }

    /**
     * Menampilkan soal latihan.
     */
    public function show(Request $request, QuizAttempt $attempt): View
    {
        $this->authorizeAttempt($attempt);

        abort_if($attempt->completed_at !== null, 403, 'Latihan ini sudah selesai.');

        $module = $attempt->module;

        $questions = $module->questions()
            ->with('options')
            ->orderBy('id')
            ->get();

        abort_if($questions->isEmpty(), 404, 'Soal latihan tidak tersedia.');

        $questionId = $request->query('question');

        $currentQuestion = $questionId
            ? $questions->firstWhere('id', (int) $questionId)
            : $questions->first();

        abort_if(
            !$currentQuestion,
            404,
            'Soal tidak ditemukan dalam modul ini.'
        );

        $answers = $attempt->answers()
            ->whereIn('question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        $answeredCount = $answers
            ->filter(fn($answer) => $answer->selected_option_id !== null)
            ->count();

        return view('quiz.show', compact(
            'attempt',
            'module',
            'questions',
            'currentQuestion',
            'answers',
            'answeredCount'
        ));
    }

    /**
     * Menyimpan jawaban satu soal.
     */
    public function saveAnswer(Request $request, QuizAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        abort_if($attempt->completed_at !== null, 403, 'Latihan ini sudah selesai.');

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option_id' => ['required', 'integer'],
        ]);

        $question = Question::where('module_id', $attempt->module_id)
            ->whereKey($validated['question_id'])
            ->firstOrFail();

        // Pastikan opsi benar-benar milik soal yang sedang dijawab.
        $optionExists = $question->options()
            ->whereKey($validated['selected_option_id'])
            ->exists();

        if (!$optionExists) {
            throw ValidationException::withMessages([
                'selected_option_id' => 'Pilihan jawaban tidak valid untuk soal ini.',
            ]);
        }

        $attempt->answers()->updateOrCreate(
            ['question_id' => $question->id],
            [
                'selected_option_id' => $validated['selected_option_id'],
                'answered_at' => now(),
            ]
        );

        return back()->with('answer_saved', true);
    }

    /**
     * Mengakhiri latihan dan menghitung nilai di server.
     */
    public function submit(QuizAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        abort_if($attempt->completed_at !== null, 403, 'Latihan ini sudah selesai.');

        $module = $attempt->module;

        $questionIds = $module->questions()->pluck('id');

        if ($questionIds->isEmpty()) {
            return redirect()
                ->route('modules.read', $module)
                ->with('info', 'Modul ini tidak memiliki soal latihan.');
        }

        $result = DB::transaction(function () use ($attempt, $questionIds) {
            $questions = Question::with('options')
                ->whereIn('id', $questionIds)
                ->get();

            $answers = $attempt->answers()
                ->whereIn('question_id', $questionIds)
                ->get()
                ->keyBy('question_id');

            $correctCount = 0;

            foreach ($questions as $question) {
                $answer = $answers->get($question->id);

                $isCorrect = $answer !== null
                    && $answer->selected_option_id !== null
                    && $question->options->contains(
                        fn($option) =>
                        (int) $option->id === (int) $answer->selected_option_id
                            && (bool) $option->is_correct
                    );

                if ($answer) {
                    $answer->update([
                        'is_correct' => $isCorrect,
                    ]);
                }

                if ($isCorrect) {
                    $correctCount++;
                }
            }

            $attempt->update([
                'score' => $correctCount,
                'total_questions' => $questions->count(),
                'completed_at' => now(),
            ]);

            return [
                'score' => $correctCount,
                'total' => $questions->count(),
            ];
        });

        return redirect()->route('quiz.result', $attempt)
            ->with(
                'success',
                "Latihan selesai! Jawaban benar: {$result['score']} dari {$result['total']} soal."
            );
    }

    /**
     * Pastikan percobaan benar-benar milik pengguna yang login.
     */
    private function authorizeAttempt(QuizAttempt $attempt): void
    {
        abort_unless(
            (int) $attempt->user_id === (int) auth()->id(),
            403,
            'Kamu tidak memiliki akses ke percobaan ini.'
        );

        $user = auth()->user();
        $module = $attempt->module;

        abort_unless(
            $user->language_group_id !== null &&
                (int) $user->language_group_id === (int) $module->language_group_id,
            403,
            'Kamu tidak memiliki akses ke modul ini.'
        );
    }
}
