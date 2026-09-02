<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('person_id')
                ->nullable()
                ->after('id')
                ->constrained('people')
                ->nullOnDelete();

            $table->boolean('is_active')
                ->default(true)
                ->after('password');

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropIndex(['is_active']);

            $table->dropColumn([
                'person_id',
                'is_active',
            ]);
        });
    }
};