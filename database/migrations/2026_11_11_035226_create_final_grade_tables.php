
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pengaturan Nilai Akhir
        |--------------------------------------------------------------------------
        */

        Schema::create('final_grade_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('language_group_id')
                ->unique()
                ->constrained('language_groups')
                ->cascadeOnDelete();

            $table->decimal('passing_score', 5, 2)->default(75);

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Catatan Penilaian Akhir
        |--------------------------------------------------------------------------
        */

        Schema::create('final_grade_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('language_group_id')
                ->constrained('language_groups')
                ->restrictOnDelete();

            $table->decimal('final_score', 5, 2)->default(0);

            $table->decimal('passing_score', 5, 2)->default(75);

            $table->unsignedInteger('total_modules')->default(0);

            // Snapshot rincian nilai setiap modul saat perhitungan dilakukan.
            $table->json('module_scores');

            // pending atau approved.
            $table->string('status', 20)->default('pending');

            $table->foreignId('calculated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['language_group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_grade_records');
        Schema::dropIfExists('final_grade_settings');
    }
};
