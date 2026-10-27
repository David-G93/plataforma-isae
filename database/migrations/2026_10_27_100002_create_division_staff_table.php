<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('assignment_role', 50)->default('personal');
            $table->timestamps();
            $table->unique(['division_id', 'person_id']);
            $table->index(['division_id', 'assignment_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_staff');
    }
};
