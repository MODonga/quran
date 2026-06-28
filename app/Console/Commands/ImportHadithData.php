<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportHadithData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-hadith-data';
    protected $description = 'Import Hadith data from JSON files';

    public function handle()
    {
        $basePath = 'c:\\Users\\compu market\\Desktop\\Quran\\hadith-json-main\\db\\by_book\\';
        $files = [
            'forties/nawawi40.json',
            'forties/qudsi40.json',
            'other_books/mishkat_almasabih.json',
            'other_books/bulugh_almaram.json',
            'other_books/shamail_muhammadiyah.json',
            'other_books/riyad_assalihin.json',
        ];

        foreach ($files as $file) {
            $filePath = $basePath . $file;
            if (!file_exists($filePath)) {
                $this->error("File not found: $filePath");
                continue;
            }

            $this->info("Importing: $file");
            $data = json_decode(file_get_contents($filePath), true);

            \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                // 1. Import Book
                $book = \App\Models\HadithBook::updateOrCreate(
                    ['id' => $data['metadata']['id']],
                    [
                        'name' => $data['metadata']['arabic']['title'],
                        'author' => $data['metadata']['arabic']['author'],
                        'description' => $data['metadata']['arabic']['introduction'] ?? null,
                    ]
                );

                // 2. Import Chapters
                $chaptersMap = [];
                foreach ($data['chapters'] as $chapterData) {
                    $chapter = \App\Models\HadithChapter::updateOrCreate(
                        ['id' => $chapterData['id'], 'book_id' => $book->id],
                        ['name' => $chapterData['arabic']]
                    );
                    $chaptersMap[$chapterData['id']] = $chapter->id;
                }

                // 3. Import Hadiths
                foreach ($data['hadiths'] as $hadithData) {
                    \App\Models\Hadith::updateOrCreate(
                        ['id' => $hadithData['id']],
                        [
                            'book_id' => $book->id,
                            'chapter_id' => $chaptersMap[$hadithData['chapterId']] ?? null,
                            'number_in_book' => $hadithData['idInBook'],
                            'text' => $hadithData['arabic'],
                            'text_en' => $hadithData['english']['text'] ?? null,
                            'narrator_en' => $hadithData['english']['narrator'] ?? null,
                            'grade' => $hadithData['grade'] ?? 'sahih', // Default to sahih for these primary books if not specified
                        ]
                    );
                }
            });
            $this->info("Successfully imported: $file");
        }

        return Command::SUCCESS;
    }
}
