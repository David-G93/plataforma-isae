<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_period_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_period_id')->constrained()->cascadeOnDelete();
            $table->decimal('absences', 6, 2)->default(0);
            $table->decimal('justified_absences', 6, 2)->default(0);
            $table->text('observations')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_enrollment_id', 'school_period_id'],
                'official_period_attendance_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_period_attendances');
    }
};
