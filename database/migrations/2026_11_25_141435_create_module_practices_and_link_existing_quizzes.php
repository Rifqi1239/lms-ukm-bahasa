
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Tabel jenis latihan per modul
        |--------------------------------------------------------------------------
        */

        Schema::create('module_practices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->enum('type', ['independent', 'assessment']);

            $table->timestamp('start_at')->nullable();
            $table->timestamp('deadline_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['module_id', 'type']);
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Tambahkan relasi latihan ke pertanyaan
        |--------------------------------------------------------------------------
        */

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('module_practice_id')
                ->nullable()
                ->after('module_id')
                ->constrained('module_practices')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Tambahkan relasi latihan dan status ke percobaan kuis
        |--------------------------------------------------------------------------
        */

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreignId('module_practice_id')
                ->nullable()
                ->after('module_id')
                ->constrained('module_practices')
                ->restrictOnDelete();

            $table->string('status')
                ->default('in_progress')
                ->after('completed_at');
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Migrasikan pertanyaan dan percobaan lama
        |--------------------------------------------------------------------------
        |
        | Semua pertanyaan dan percobaan lama dimasukkan ke latihan mandiri.
        | Riwayat jawaban dan penilaian akhir tidak diubah.
        |
        */

        $moduleIds = DB::table('questions')
            ->select('module_id')
            ->union(
                DB::table('quiz_attempts')->select('module_id')
            )
            ->pluck('module_id');

        foreach ($moduleIds as $moduleId) {
            $now = now();

            $practiceId = DB::table('module_practices')
                ->insertGetId([
                    'module_id' => $moduleId,
                    'type' => 'independent',
                    'start_at' => null,
                    'deadline_at' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('questions')
                ->where('module_id', $moduleId)
                ->update([
                    'module_practice_id' => $practiceId,
                ]);

            DB::table('quiz_attempts')
                ->where('module_id', $moduleId)
                ->update([
                    'module_practice_id' => $practiceId,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Tandai percobaan lama yang sudah selesai
        |--------------------------------------------------------------------------
        */

        DB::table('quiz_attempts')
            ->whereNotNull('completed_at')
            ->update([
                'status' => 'completed',
            ]);

        DB::table('quiz_attempts')
            ->whereNull('completed_at')
            ->update([
                'status' => 'in_progress',
            ]);
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['module_practice_id']);
            $table->dropColumn(['module_practice_id', 'status']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['module_practice_id']);
            $table->dropColumn('module_practice_id');
        });

        Schema::dropIfExists('module_practices');
    }
};
