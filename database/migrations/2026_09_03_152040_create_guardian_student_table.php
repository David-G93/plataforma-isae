<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guardian_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('relationship', 50)->nullable();

            $table->boolean('is_primary')
                ->default(false);

            $table->boolean('authorized_pickup')
                ->default(false);

            $table->boolean('receives_communications')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'guardian_profile_id',
                'student_profile_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};