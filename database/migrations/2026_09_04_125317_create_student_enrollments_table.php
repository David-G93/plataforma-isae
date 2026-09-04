<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('division_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('modality_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('status', 50)
                ->default('active');

            $table->date('enrolled_at')
                ->nullable();

            $table->date('ended_at')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'student_profile_id',
                'academic_year_id',
            ]);

            $table->index([
                'academic_year_id',
                'division_id',
            ]);

            $table->index([
                'academic_year_id',
                'modality_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};