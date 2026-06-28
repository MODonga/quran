<?php

namespace App\Http\Controllers;

use App\Models\Hadith;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HadithQuizController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'hadith_id' => 'required|exists:hadiths,id',
        ]);

        $hadithId = $request->hadith_id;
        $hadith = Hadith::with(['book', 'chapter'])->findOrFail($hadithId);

        $questions = [];

        // Question 1: Which book is this hadith from?
        $wrongBooks = Hadith::where('id', '!=', $hadith->id)
            ->whereHas('book')
            ->with('book')
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->pluck('book.name')
            ->unique()
            ->filter()
            ->take(3)
            ->values()
            ->toArray();

        if (count($wrongBooks) >= 3) {
            // Ensure no duplicates by removing the correct answer from wrong options
            $wrongBooks = array_diff($wrongBooks, [$hadith->book->name]);
            $wrongBooks = array_values(array_slice($wrongBooks, 0, 3));
            $bookOptions = array_merge([$hadith->book->name], $wrongBooks);
            shuffle($bookOptions);

            $questions[] = [
                'type' => 'mcq',
                'prompt' => 'من أي كتاب هذا الحديث؟',
                'options' => array_map(fn($name) => ['text' => $name, 'correct' => $name === $hadith->book->name], $bookOptions),
            ];
        }

        // Question 2: Which chapter is this hadith from? (if chapter exists)
        if ($hadith->chapter) {
            $wrongChapters = Hadith::where('id', '!=', $hadith->id)
                ->where('book_id', $hadith->book_id)
                ->whereHas('chapter')
                ->with('chapter')
                ->inRandomOrder()
                ->limit(10)
                ->get()
                ->pluck('chapter.name')
                ->unique()
                ->filter()
                ->take(3)
                ->values()
                ->toArray();

            if (count($wrongChapters) >= 3) {
                // Ensure no duplicates
                $wrongChapters = array_diff($wrongChapters, [$hadith->chapter->name]);
                $wrongChapters = array_values(array_slice($wrongChapters, 0, 3));
                $chapterOptions = array_merge([$hadith->chapter->name], $wrongChapters);
                shuffle($chapterOptions);

                $questions[] = [
                    'type' => 'mcq',
                    'prompt' => 'من أي باب هذا الحديث؟',
                    'options' => array_map(fn($name) => ['text' => $name, 'correct' => $name === $hadith->chapter->name], $chapterOptions),
                ];
            }
        }

        // Question 3: What is the hadith number in the book?
        $wrongNumbers = [];
        $range = range(max(1, $hadith->number_in_book - 20), $hadith->number_in_book + 20);
        $range = array_diff($range, [$hadith->number_in_book]);
        shuffle($range);
        $wrongNumbers = array_slice($range, 0, 3);

        if (count($wrongNumbers) >= 3) {
            $numberOptions = array_merge([$hadith->number_in_book], $wrongNumbers);
            shuffle($numberOptions);

            $questions[] = [
                'type' => 'mcq',
                'prompt' => 'ما هو رقم هذا الحديث في الكتاب؟',
                'options' => array_map(fn($num) => ['text' => (string)$num, 'correct' => $num === $hadith->number_in_book], $numberOptions),
            ];
        }

        // Question 4: What is the grade of this hadith? (if grade exists)
        if ($hadith->grade) {
            $allGrades = ['صحيح', 'حسن', 'ضعيف'];
            $correctGrade = $this->translateGrade($hadith->grade);
            $wrongGrades = array_diff($allGrades, [$correctGrade]);
            
            if (count($wrongGrades) >= 2) {
                $gradeOptions = array_merge([$correctGrade], array_values($wrongGrades));
                shuffle($gradeOptions);

                $questions[] = [
                    'type' => 'mcq',
                    'prompt' => 'ما هي درجة هذا الحديث؟',
                    'options' => array_map(fn($grade) => ['text' => $grade, 'correct' => $grade === $correctGrade], $gradeOptions),
                ];
            }
        }

        // Question 5: Who is the author of the book?
        if ($hadith->book->author) {
            $wrongAuthors = Hadith::where('id', '!=', $hadith->id)
                ->whereHas('book', function($q) {
                    $q->whereNotNull('author');
                })
                ->with('book')
                ->inRandomOrder()
                ->limit(10)
                ->get()
                ->pluck('book.author')
                ->unique()
                ->filter()
                ->take(3)
                ->values()
                ->toArray();

            if (count($wrongAuthors) >= 3) {
                // Ensure no duplicates
                $wrongAuthors = array_diff($wrongAuthors, [$hadith->book->author]);
                $wrongAuthors = array_values(array_slice($wrongAuthors, 0, 3));
                $authorOptions = array_merge([$hadith->book->author], $wrongAuthors);
                shuffle($authorOptions);

                $questions[] = [
                    'type' => 'mcq',
                    'prompt' => 'من هو مؤلف الكتاب؟',
                    'options' => array_map(fn($author) => ['text' => $author, 'correct' => $author === $hadith->book->author], $authorOptions),
                ];
            }
        }

        // Ensure we have at least 3 questions, shuffle and return up to 5
        if (count($questions) < 3) {
            // Fallback: add generic questions if we don't have enough
            $questions[] = [
                'type' => 'mcq',
                'prompt' => 'كم عدد كلمات هذا الحديث تقريباً؟',
                'options' => [
                    ['text' => 'أقل من 10 كلمات', 'correct' => str_word_count($hadith->text, 0, 'ابتثجحخدذرزسشصضطظعغفقكلمنهويءآأإؤئة') < 10],
                    ['text' => 'بين 10-30 كلمة', 'correct' => str_word_count($hadith->text, 0, 'ابتثجحخدذرزسشصضطظعغفقكلمنهويءآأإؤئة') >= 10 && str_word_count($hadith->text, 0, 'ابتثجحخدذرزسشصضطظعغفقكلمنهويءآأإؤئة') <= 30],
                    ['text' => 'بين 30-50 كلمة', 'correct' => str_word_count($hadith->text, 0, 'ابتثجحخدذرزسشصضطظعغفقكلمنهويءآأإؤئة') > 30 && str_word_count($hadith->text, 0, 'ابتثجحخدذرزسشصضطظعغفقكلمنهويءآأإؤئة') <= 50],
                    ['text' => 'أكثر من 50 كلمة', 'correct' => str_word_count($hadith->text, 0, 'ابتثجحخدذرزسشصضطظعغفقكلمنهويءآأإؤئة') > 50],
                ],
            ];
        }

        shuffle($questions);
        return response()->json(array_slice($questions, 0, 5));
    }

    private function translateGrade($grade)
    {
        if (!$grade) return null;
        $gradeLower = strtolower($grade);
        if (str_contains($gradeLower, 'sahih')) return 'صحيح';
        if (str_contains($gradeLower, 'hasan')) return 'حسن';
        if (str_contains($gradeLower, 'daif') || str_contains($gradeLower, 'weak')) return 'ضعيف';
        return $grade;
    }
}
