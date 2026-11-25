<?php

namespace App\Services;

use App\Models\FinalGradeRecord;
use App\Models\FinalGradeSetting;
use App\Models\LanguageGroup;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalGradeService
{
    /**
     * Menghitung nilai akhir seluruh anggota dalam satu kelompok bahasa.
     *
     * Hanya latihan penilaian yang dihitung.
     * Latihan mandiri tidak masuk perhitungan.
     */
    public function calculateForGroup(
        LanguageGroup $languageGroup,
        User $actor
    ): Collection {

        // Ambil modul yang memiliki latihan penilaian dengan jadwal.
        // Tidak menggunakan is_active agar riwayat percobaan valid
        // tetap dapat dihitung meskipun latihan dinonaktifkan.
        $modules = Module::where('language_group_id', $languageGroup->id)
            ->whereHas('practices', function ($query) {
                $query->where('type', 'assessment')
                    ->whereNotNull('start_at')
                    ->whereNotNull('deadline_at');
            })
            ->with(['practices' => function ($query) {
                $query->where('type', 'assessment')
                    ->whereNotNull('start_at')
                    ->whereNotNull('deadline_at');
            }])
            ->orderBy('title')
            ->get(['id', 'title']);

        if ($modules->isEmpty()) {
            throw ValidationException::withMessages([
                'final_grade' => 'Belum ada modul dengan latihan penilaian yang memiliki jadwal pada kelompok bahasa ini.',
            ]);
        }

        // Ambil pengaturan batas kelulusan.
        $setting = FinalGradeSetting::firstOrCreate(
            ['language_group_id' => $languageGroup->id],
            ['passing_score' => 75]
        );

        // Ambil seluruh anggota kelompok bahasa.
        $members = User::where('role', 'anggota')
            ->where('language_group_id', $languageGroup->id)
            ->orderBy('name')
            ->get();

        if ($members->isEmpty()) {
            return collect();
        }

        // Ambil ID latihan penilaian dari seluruh modul.
        $practiceIds = $modules
            ->flatMap(function ($module) {
                return $module->practices->pluck('id');
            })
            ->values();

        // Ambil nilai tertinggi percobaan penilaian yang valid.
        $bestScores = DB::table('quiz_attempts')
            ->join(
                'module_practices',
                'quiz_attempts.module_practice_id',
                '=',
                'module_practices.id'
            )
            ->select(
                'quiz_attempts.user_id',
                'quiz_attempts.module_id'
            )
            ->selectRaw(
                'MAX(
                    quiz_attempts.score * 100.0 /
                    NULLIF(quiz_attempts.total_questions, 0)
                ) AS best_score'
            )
            ->whereIn('quiz_attempts.user_id', $members->pluck('id'))
            ->whereIn('quiz_attempts.module_practice_id', $practiceIds)
            ->where('module_practices.type', 'assessment')
            ->where('quiz_attempts.status', 'completed')
            ->whereNotNull('quiz_attempts.completed_at')
            ->whereColumn(
                'quiz_attempts.completed_at',
                '>=',
                'module_practices.start_at'
            )
            ->whereColumn(
                'quiz_attempts.completed_at',
                '<',
                'module_practices.deadline_at'
            )
            ->where('quiz_attempts.total_questions', '>', 0)
            ->groupBy(
                'quiz_attempts.user_id',
                'quiz_attempts.module_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->user_id . '-' . $item->module_id;
            });

        $records = collect();

        DB::transaction(function () use (
            $members,
            $modules,
            $bestScores,
            $setting,
            $languageGroup,
            $actor,
            &$records
        ) {

            foreach ($members as $member) {

                $moduleScores = [];
                $validScores = [];

                foreach ($modules as $module) {

                    $key = $member->id . '-' . $module->id;

                    $attempt = $bestScores->get($key);

                    $hasAttempt = $attempt !== null;

                    $score = $hasAttempt
                        ? round((float) $attempt->best_score, 2)
                        : null;

                    $moduleScores[] = [
                        'module_id' => $module->id,
                        'module_title' => $module->title,
                        'score' => $score,
                        'has_attempt' => $hasAttempt,
                    ];

                    if ($hasAttempt) {
                        $validScores[] = $score;
                    }
                }

                // Jika belum ada penilaian valid, nilai sementara 0.
                $finalScore = count($validScores) > 0
                    ? round(
                        array_sum($validScores) / count($validScores),
                        2
                    )
                    : 0;

                $recordData = [
                    'user_id' => $member->id,
                    'language_group_id' => $languageGroup->id,
                    'final_score' => $finalScore,
                    'passing_score' => $setting->passing_score,
                    'total_modules' => count($validScores),
                    'module_scores' => $moduleScores,
                    'calculated_by' => $actor->id,
                    'calculated_at' => now(),
                ];

                // Cari catatan nilai terbaru milik anggota.
                $latestRecord = FinalGradeRecord::where(
                    'user_id',
                    $member->id
                )
                    ->where(
                        'language_group_id',
                        $languageGroup->id
                    )
                    ->orderByDesc('calculated_at')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($latestRecord && $latestRecord->status === 'pending') {

                    // Perbarui catatan pending terbaru.
                    $latestRecord->update($recordData);

                    $record = $latestRecord->fresh();

                } else {

                    // Jika belum ada catatan atau catatan terakhir
                    // sudah disetujui, buat catatan pending baru.
                    $record = FinalGradeRecord::create(
                        array_merge($recordData, [
                            'status' => 'pending',
                        ])
                    );
                }

                $records->push($record);
            }
        });

        return $records;
    }
}
