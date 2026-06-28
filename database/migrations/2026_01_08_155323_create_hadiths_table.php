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
        Schema::create('hadiths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('hadith_books')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('hadith_chapters')->onDelete('cascade');
            $table->integer('number_in_book');
            $table->text('text');
            $table->text('text_en')->nullable();
            $table->string('narrator_en')->nullable();
            $table->string('grade')->nullable(); // sahih, hasan, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hadiths');
    }
};
