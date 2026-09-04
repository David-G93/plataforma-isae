<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('grade_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 100);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'academic_year_id',
                'grade_id',
                'name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};