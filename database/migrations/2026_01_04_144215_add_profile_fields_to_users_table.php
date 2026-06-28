<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('current_level')->default(1);
            $table->foreignId('last_ayah_id')->nullable()->constrained('ayahs')->onDelete('set null');
            $table->integer('streak')->default(0);
            $table->integer('total_days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['last_ayah_id']);
            $table->dropColumn(['current_level', 'last_ayah_id', 'streak', 'total_days']);
        });
    }
};
