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
        Schema::create('ayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surah_id')->constrained('surahs')->onDelete('cascade');
            $table->unsignedInteger('ayah_number'); // Ayah number within the surah
            $table->text('text_uthmani'); // Uthmani text (with diacritics)
            $table->text('text_simple')->nullable(); // Simple text (without diacritics)
            $table->unsignedInteger('juz')->nullable(); // Juz number (1-30)
            $table->unsignedInteger('hizb')->nullable(); // Hizb number
            $table->unsignedInteger('page')->nullable(); // Mushaf page number
            $table->timestamps();
            
            // Composite index for faster queries
            $table->index(['surah_id', 'ayah_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayahs');
    }
};
