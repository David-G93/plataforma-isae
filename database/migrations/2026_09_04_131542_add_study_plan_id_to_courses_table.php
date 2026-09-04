<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('study_plan_id')
                ->nullable()
                ->after('grade_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index([
                'academic_year_id',
                'study_plan_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign([
                'study_plan_id',
            ]);

            $table->dropIndex([
                'academic_year_id',
                'study_plan_id',
            ]);

            $table->dropColumn('study_plan_id');
        });
    }
};