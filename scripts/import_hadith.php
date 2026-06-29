<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Set high memory limit and time execution limit for importing large amount of data
ini_set('memory_limit', '2048M');
set_time_limit(600); // 10 minutes

// Pre-declare variables to avoid closure scope warnings/errors
$hadith_books = [];
$hadith_chapters = [];
$hadiths = [];

echo "Starting Hadith import script...\n";

// 1. Locate quran_memorization.php using Laravel's base_path helper
$filePath = base_path('quran_memorization.php');
if (!file_exists($filePath)) {
    $filePath = base_path('../quran_memorization.php');
}

if (!file_exists($filePath)) {
    echo "Error: quran_memorization.php not found in project root (" . base_path() . ") or parent directory!\n";
    exit(1);
}

echo "Found data file at: $filePath\n";
echo "Loading data into memory (this might take a few seconds due to 61MB file size)...\n";

require $filePath;

echo "Loaded. Starting database transaction...\n";

try {
    DB::transaction(function () use ($hadith_books, $hadith_chapters, $hadiths) {
        
        // 2. Import Hadith Books
        if (isset($hadith_books) && is_array($hadith_books)) {
            echo "Importing Hadith Books (" . count($hadith_books) . ")...\n";
            $booksBatch = [];
            foreach ($hadith_books as $book) {
                $booksBatch[] = [
                    'id'          => $book['id'],
                    'name'        => $book['name'],
                    'author'      => $book['author'] ?? null,
                    'description' => $book['description'] ?? null,
                    'created_at'  => $book['created_at'] ?? now(),
                    'updated_at'  => $book['updated_at'] ?? now(),
                ];
            }
            if (!empty($booksBatch)) {
                DB::table('hadith_books')->upsert(
                    $booksBatch,
                    ['id'],
                    ['name', 'author', 'description', 'updated_at']
                );
            }
            echo "Hadith Books imported successfully.\n";
        } else {
            echo "Warning: \$hadith_books array not found or empty!\n";
        }

        // 3. Import Hadith Chapters
        if (isset($hadith_chapters) && is_array($hadith_chapters)) {
            echo "Importing Hadith Chapters (" . count($hadith_chapters) . ")...\n";
            
            // To prevent memory exhaustion and respect SQL placeholders limit, chunk chapters
            $chaptersChunks = array_chunk($hadith_chapters, 1000);
            $chapterCount = 0;
            
            foreach ($chaptersChunks as $chunk) {
                $chaptersBatch = [];
                foreach ($chunk as $chapter) {
                    $chaptersBatch[] = [
                        'id'         => $chapter['id'],
                        'book_id'    => $chapter['book_id'],
                        'name'       => $chapter['name'],
                        'created_at' => $chapter['created_at'] ?? now(),
                        'updated_at' => $chapter['updated_at'] ?? now(),
                    ];
                }
                
                DB::table('hadith_chapters')->upsert(
                    $chaptersBatch,
                    ['id'],
                    ['book_id', 'name', 'updated_at']
                );
                $chapterCount += count($chaptersBatch);
                echo "  - Processed $chapterCount chapters...\r";
            }
            echo "\nHadith Chapters imported successfully.\n";
        } else {
            echo "Warning: \$hadith_chapters array not found or empty!\n";
        }

        // 4. Import Hadiths
        if (isset($hadiths) && is_array($hadiths)) {
            echo "Importing Hadiths (" . count($hadiths) . ")...\n";
            
            // We use 1000 chunk size for large text payloads to optimize memory
            $hadithsChunks = array_chunk($hadiths, 1000);
            $hadithCount = 0;

            foreach ($hadithsChunks as $chunk) {
                $hadithsBatch = [];
                foreach ($chunk as $hadith) {
                    $hadithsBatch[] = [
                        'id'             => $hadith['id'],
                        'book_id'        => $hadith['book_id'],
                        'chapter_id'     => $hadith['chapter_id'],
                        'number_in_book' => $hadith['number_in_book'],
                        'text'           => $hadith['text'],
                        'text_en'        => $hadith['text_en'] ?? null,
                        'narrator_en'    => $hadith['narrator_en'] ?? null,
                        'grade'          => $hadith['grade'] ?? null,
                        'created_at'     => $hadith['created_at'] ?? now(),
                        'updated_at'     => $hadith['updated_at'] ?? now(),
                    ];
                }

                DB::table('hadiths')->upsert(
                    $hadithsBatch,
                    ['id'],
                    ['book_id', 'chapter_id', 'number_in_book', 'text', 'text_en', 'narrator_en', 'grade', 'updated_at']
                );
                $hadithCount += count($hadithsBatch);
                echo "  - Processed $hadithCount hadiths...\r";
            }
            echo "\nHadiths imported successfully.\n";
        } else {
            echo "Warning: \$hadiths array not found or empty!\n";
        }
    });

    echo "\nAll Hadith data imported successfully!\n";

} catch (\Exception $e) {
    echo "\nError during import: " . $e->getMessage() . "\n";
    exit(1);
}
