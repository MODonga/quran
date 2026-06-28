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
        Schema::create('recitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reciter_id')->constrained('reciters')->onDelete('cascade');
            $table->foreignId('surah_id')->constrained('surahs')->onDelete('cascade');
            $table->integer('ayah_number');
            $table->string('audio_url');
            $table->timestamps();

            $table->unique(['reciter_id', 'surah_id', 'ayah_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recitations');
    }
};
