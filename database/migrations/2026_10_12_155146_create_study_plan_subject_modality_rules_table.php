<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'study_plan_subject_modality_rules',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('study_plan_subject_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('modality_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->boolean('counts_for_curricular_average')
                    ->default(true);

                $table->boolean('counts_for_institutional_average')
                    ->default(true);

                $table->timestamps();

                $table->unique([
                    'study_plan_subject_id',
                    'modality_id',
                ], 'study_plan_subject_modality_unique');
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'study_plan_subject_modality_rules',
        );
    }
};