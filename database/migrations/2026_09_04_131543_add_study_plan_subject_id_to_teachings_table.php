<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachings', function (Blueprint $table) {
            $table->foreignId('study_plan_subject_id')
                ->nullable()
                ->after('subject_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->unique([
                'division_id',
                'study_plan_subject_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('teachings', function (Blueprint $table) {
            $table->dropForeign([
                'study_plan_subject_id',
            ]);

            $table->dropUnique([
                'division_id',
                'study_plan_subject_id',
            ]);

            $table->dropColumn('study_plan_subject_id');
        });
    }
};