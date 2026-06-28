<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('quran_streak')->default(0)->after('streak');
            $table->unsignedInteger('quran_total_days')->default(0)->after('total_days');
            $table->unsignedInteger('hadith_streak')->default(0)->after('quran_streak');
            $table->unsignedInteger('hadith_total_days')->default(0)->after('quran_total_days');
        });

        // Migrate existing data
        DB::table('users')->update([
            'quran_streak' => DB::raw('streak'),
            'quran_total_days' => DB::raw('total_days'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['quran_streak', 'quran_total_days', 'hadith_streak', 'hadith_total_days']);
        });
    }
};
