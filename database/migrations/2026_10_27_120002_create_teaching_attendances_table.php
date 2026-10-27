<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status', 20)->default('present');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['teaching_id', 'student_enrollment_id', 'attendance_date'],
                'teaching_attendance_unique'
            );
            $table->index(['teaching_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_attendances');
    }
};
