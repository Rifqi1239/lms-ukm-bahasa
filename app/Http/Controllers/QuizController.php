<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModulePractice;
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
     * Memulai latihan mandiri atau latihan penilaian.
     *
     * Jenis latihan ditentukan melalui query parameter:
     * ?type=independent
     * ?type=assessment
     */
    public function start(Request $request, Module $module)
    {
        $user = Auth::user();

        $this->authorizeModule($module);

        $validated = $request->validate([
            'type' => ['nullable', 'in:independent,assessment'],
        ]);

        // Jika tidak ada parameter type, gunakan latihan mandiri.
        $type = $validated['type'] ?? 'independent';

        $practice = ModulePractice::where('module_id', $module->id)
            ->where('type', $type)
            ->first();

        if (!$practice || !$practice->is_active) {
            return redirect()
                ->route('modules.read', $module)
                ->with('info', 'Jenis latihan ini belum tersedia.');
        }

        // Periksa jadwal khusus untuk latihan penilaian.
        if ($type === 'assessment') {
            if (!$practice->start_at || !$practice->deadline_at) {
                return redirect()
                    ->route('modules.read', $module)
                    ->with('info', 'Jadwal latihan penilaian belum diatur.');
            }

            if (now()->lt($practice->start_at)) {
                return redirect()
                    ->route('modules.read', $module)
                    ->with('info', 'Latihan penilaian belum dimulai.');
            }

            if (now()->greaterThanOrEqualTo($practice->deadline_at)) {
                // Tandai percobaan yang belum selesai sebagai kedaluwarsa.
                QuizAttempt::where('user_id', $user->id)
                    ->where('module_practice_id', $practice->id)
                    ->where('status', 'in_progress')
                    ->update(['status' => 'expired']);

                return redirect()
                    ->route('modules.read', $module)
                    ->with('info', 'Waktu latihan penilaian sudah berakhir.');
            }
        }

        $questionIds = $practice->questions()
            ->orderBy('id')
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return redirect()
                ->route('modules.read', $module)
                ->with('info', 'Latihan ini belum memiliki soal.');
        }

        // Cari percobaan yang masih berlangsung untuk jenis latihan ini.
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->where('module_practice_id', $practice->id)
            ->where('status', 'in_progress')
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if (!$attempt) {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'module_practice_id' => $practice->id,
                'score' => 0,
                'total_questions' => $questionIds->count(),
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        } else {
            $attempt->update([
                'total_questions' => $questionIds->count(),
            ]);
        }

        // Cari soal pertama yang belum dijawab.
        $answeredQuestionIds = $attempt->answers()
            ->whereIn('question_id', $questionIds)
            ->whereNotNull('selected_option_id')
            ->pluck('question_id');

        $nextQuestionId = $questionIds->first(
            fn ($id) => !$answeredQuestionIds->contains($id)
        );

        // Jika semua sudah dijawab, buka soal pertama.
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
            $attempt->status !== 'completed' || $attempt->completed_at === null,
            403,
            'Hasil latihan ini belum tersedia.'
        );

        $module = $attempt->module;
        $practice = $attempt->practice;

        abort_unless($practice, 404, 'Jenis latihan tidak ditemukan.');

        $questions = $practice->questions()
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
            'practice',
            'questions',
            'answers',
            'correctCount',
            'totalQuestions',
            'scorePercentage'
        ));
    }

    /**
     * Menampilkan riwayat latihan pengguna.
     */
    public function history(): View
    {
        $user = Auth::user();

        $attempts = QuizAttempt::with(['module', 'practice'])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
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
        $this->ensureAttemptCanContinue($attempt);

        $module = $attempt->module;
        $practice = $attempt->practice;

        abort_unless($practice, 404, 'Jenis latihan tidak ditemukan.');

        $questions = $practice->questions()
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
            'Soal tidak ditemukan dalam latihan ini.'
        );

        $answers = $attempt->answers()
            ->whereIn('question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        $answeredCount = $answers
            ->filter(fn ($answer) => $answer->selected_option_id !== null)
            ->count();

        return view('quiz.show', compact(
            'attempt',
            'module',
            'practice',
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
        $this->ensureAttemptCanContinue($attempt);

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option_id' => ['required', 'integer'],
        ]);

        $practice = $attempt->practice;

        abort_unless($practice, 404, 'Jenis latihan tidak ditemukan.');

        // Soal harus berasal dari jenis latihan yang sedang dikerjakan.
        $question = $practice->questions()
            ->whereKey($validated['question_id'])
            ->firstOrFail();

        // Opsi harus benar-benar milik soal tersebut.
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
        $this->ensureAttemptCanContinue($attempt);

        $result = DB::transaction(function () use ($attempt) {
            $lockedAttempt = QuizAttempt::whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedAttempt->status !== 'in_progress' ||
                $lockedAttempt->completed_at !== null
            ) {
                return ['status' => 'already_finished'];
            }

            $practice = $lockedAttempt->practice;

            if (!$practice) {
                return ['status' => 'practice_missing'];
            }

            // Periksa ulang deadline tepat sebelum menyimpan nilai.
            if ($practice->type === 'assessment') {
                if (
                    !$practice->is_active ||
                    !$practice->start_at ||
                    !$practice->deadline_at ||
                    now()->lt($practice->start_at)
                ) {
                    return ['status' => 'unavailable'];
                }

                if (now()->greaterThanOrEqualTo($practice->deadline_at)) {
                    $lockedAttempt->update([
                        'status' => 'expired',
                    ]);

                    return ['status' => 'expired'];
                }
            }

            $questions = $practice->questions()
                ->with('options')
                ->orderBy('id')
                ->get();

            if ($questions->isEmpty()) {
                return ['status' => 'no_questions'];
            }

            $answers = $lockedAttempt->answers()
                ->whereIn('question_id', $questions->pluck('id'))
                ->get()
                ->keyBy('question_id');

            $correctCount = 0;

            foreach ($questions as $question) {
                $answer = $answers->get($question->id);

                $isCorrect = $answer !== null
                    && $answer->selected_option_id !== null
                    && $question->options->contains(
                        fn ($option) =>
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

            $lockedAttempt->update([
                'score' => $correctCount,
                'total_questions' => $questions->count(),
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            return [
                'status' => 'completed',
                'score' => $correctCount,
                'total' => $questions->count(),
            ];
        });

        if ($result['status'] === 'expired') {
            return redirect()
                ->route('modules.read', $attempt->module)
                ->with('info', 'Waktu latihan penilaian sudah berakhir. Percobaan ini tidak dihitung.');
        }

        if ($result['status'] === 'already_finished') {
            return redirect()->route('quiz.result', $attempt);
        }

        if ($result['status'] === 'no_questions') {
            return redirect()
                ->route('modules.read', $attempt->module)
                ->with('info', 'Latihan ini tidak memiliki soal.');
        }

        if ($result['status'] !== 'completed') {
            abort(403, 'Latihan tidak dapat diselesaikan.');
        }

        return redirect()->route('quiz.result', $attempt)
            ->with(
                'success',
                "Latihan selesai! Jawaban benar: {$result['score']} dari {$result['total']} soal."
            );
    }

    /**
     * Memastikan anggota hanya mengakses modul kelompok bahasanya.
     */
    private function authorizeModule(Module $module): void
    {
        $user = Auth::user();

        abort_unless(
            $user->language_group_id !== null &&
                (int) $user->language_group_id === (int) $module->language_group_id,
            403,
            'Kamu tidak memiliki akses ke modul ini.'
        );
    }

    /**
     * Memastikan percobaan benar-benar milik pengguna yang login.
     */
    private function authorizeAttempt(QuizAttempt $attempt): void
    {
        abort_unless(
            (int) $attempt->user_id === (int) Auth::id(),
            403,
            'Kamu tidak memiliki akses ke percobaan ini.'
        );

        $this->authorizeModule($attempt->module);
    }

    /**
     * Memastikan percobaan masih boleh dilanjutkan.
     * Jika deadline penilaian sudah lewat, tandai sebagai expired.
     */
    private function ensureAttemptCanContinue(QuizAttempt $attempt): void
    {
        abort_if(
            $attempt->status !== 'in_progress' || $attempt->completed_at !== null,
            403,
            'Latihan ini sudah selesai atau tidak dapat dilanjutkan.'
        );

        $practice = $attempt->practice;

        abort_unless($practice, 404, 'Jenis latihan tidak ditemukan.');

        if ($practice->type === 'assessment') {
            if (
                !$practice->is_active ||
                !$practice->start_at ||
                !$practice->deadline_at
            ) {
                abort(403, 'Latihan penilaian sedang tidak tersedia.');
            }

            if (now()->lt($practice->start_at)) {
                abort(403, 'Latihan penilaian belum dimulai.');
            }

            if (now()->greaterThanOrEqualTo($practice->deadline_at)) {
                $attempt->update([
                    'status' => 'expired',
                ]);

                abort(403, 'Waktu latihan penilaian sudah berakhir.');
            }
        }
    }
}