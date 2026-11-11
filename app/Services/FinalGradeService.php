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
     */
    public function calculateForGroup(
        LanguageGroup $languageGroup,
        User $actor
    ): Collection {
        // Ambil seluruh modul dalam kelompok bahasa yang memiliki soal.
        $modules = Module::where('language_group_id', $languageGroup->id)
            ->whereHas('questions')
            ->orderBy('title')
            ->get(['id', 'title']);

        if ($modules->isEmpty()) {
            throw ValidationException::withMessages([
                'final_grade' => 'Belum ada modul yang memiliki soal latihan pada kelompok bahasa ini.',
            ]);
        }

        // Ambil pengaturan batas kelulusan.
        $setting = FinalGradeSetting::firstOrCreate(
            ['language_group_id' => $languageGroup->id],
            ['passing_score' => 75]
        );

        // Ambil seluruh anggota dalam kelompok bahasa.
        $members = User::where('role', 'anggota')
            ->where('language_group_id', $languageGroup->id)
            ->orderBy('name')
            ->get();

        if ($members->isEmpty()) {
            return collect();
        }

        // Ambil nilai persentase tertinggi setiap anggota pada setiap modul.
        $bestScores = DB::table('quiz_attempts')
            ->select(
                'user_id',
                'module_id'
            )
            ->selectRaw(
                'MAX(score * 100.0 / NULLIF(total_questions, 0)) AS best_score'
            )
            ->whereIn('user_id', $members->pluck('id'))
            ->whereIn('module_id', $modules->pluck('id'))
            ->whereNotNull('completed_at')
            ->where('total_questions', '>', 0)
            ->groupBy('user_id', 'module_id')
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

                foreach ($modules as $module) {
                    $key = $member->id . '-' . $module->id;

                    $attempt = $bestScores->get($key);

                    // Jika belum pernah menyelesaikan latihan, nilai 0.
                    $score = $attempt
                        ? round((float) $attempt->best_score, 2)
                        : 0;

                    $moduleScores[] = [
                        'module_id' => $module->id,
                        'module_title' => $module->title,
                        'score' => $score,
                        'has_attempt' => $attempt !== null,
                    ];
                }

                // Hitung rata-rata seluruh modul yang memiliki soal.
                $finalScore = round(
                    collect($moduleScores)->avg('score'),
                    2
                );

                // Buat catatan baru, tidak menimpa hasil sebelumnya.
                $record = FinalGradeRecord::create([
                    'user_id' => $member->id,
                    'language_group_id' => $languageGroup->id,
                    'final_score' => $finalScore,
                    'passing_score' => $setting->passing_score,
                    'total_modules' => $modules->count(),
                    'module_scores' => $moduleScores,
                    'status' => 'pending',
                    'calculated_by' => $actor->id,
                    'calculated_at' => now(),
                ]);

                $records->push($record);
            }
        });

        return $records;
    }
}
