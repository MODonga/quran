<?php

namespace App\Http\Controllers;

use App\Models\Ayah;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Generate a session quiz based on the target ayah.
     * Creates multiple question types for the SAME ayah to strengthen memorization.
     */
    public function generate(Request $request)
    {
        $user = Auth::user();
        
        // Validate required parameters
        $validated = $request->validate([
            'target_ayah_id' => 'required|integer|exists:ayahs,id',
            'limit' => 'nullable|integer|min:1|max:50'
        ], [
            'target_ayah_id.required' => 'لم يتم تحديد الآية المستهدفة.',
            'target_ayah_id.integer' => 'رقم الآية غير صحيح.',
            'target_ayah_id.exists' => 'الآية المحددة غير موجودة.',
        ]);
        
        $limit = $validated['limit'] ?? 15;
        $targetAyahId = $validated['target_ayah_id'];
        
        // Get the target ayah with context
        $targetAyah = Ayah::with('surah')->find($targetAyahId);
        
        // Build question pool from:
        // 1. Target ayah: MCQ + Next + Prev
        // 2. All previous ayahs in same surah: MCQ + Next only
        
        $questionPool = [];
        $questionIds = [];
        
        // Get all previous ayahs in the same surah
        $previousAyahs = Ayah::where('surah_id', $targetAyah->surah_id)
            ->where('ayah_number', '<', $targetAyah->ayah_number)
            ->orderBy('ayah_number', 'asc')
            ->get();
        
        // Generate questions for PREVIOUS ayahs (Next + Prev only, NO MCQ)
        foreach ($previousAyahs as $prevAyah) {
            $types = ['next_ayah', 'prev_ayah'];
            
            foreach ($types as $type) {
                // For ayah 1, skip prev_ayah (no context)
                if ($prevAyah->ayah_number == 1 && $type === 'prev_ayah') continue;
                
                // For ayah 1, next_ayah is allowed as it asks "What comes after Ayah 1?"
                
                // Try to find or create question
                $question = Question::where('ayah_id', $prevAyah->id)
                    ->where('type', $type)
                    ->first();
                
                if (!$question) {
                    $question = $this->createQuestionForAyah($prevAyah, $type);
                }
                
                if ($question && !in_array($question->id, $questionIds)) {
                    $questionPool[] = $question;
                    $questionIds[] = $question->id;
                }
            }
        }
        
        // Generate questions for TARGET ayah (MCQ + Next + Prev)
        $targetTypes = ['mcq', 'next_ayah', 'prev_ayah'];
        foreach ($targetTypes as $type) {
            // Skip invalid types for ayah 1
            if ($targetAyah->ayah_number == 1 && $type !== 'mcq') continue;
            
            $question = Question::where('ayah_id', $targetAyah->id)
                ->where('type', $type)
                ->first();
            
            if (!$question) {
                $question = $this->createQuestionForAyah($targetAyah, $type);
            }
            
            if ($question && !in_array($question->id, $questionIds)) {
                $questionPool[] = $question;
                $questionIds[] = $question->id;
            }
        }
        
        // SPLIT Pool into Target vs Review
        $targetQuestions = [];
        $reviewQuestions = [];
        
        foreach ($questionPool as $q) {
            if ($q->ayah_id == $targetAyah->id) {
                $targetQuestions[] = $q;
            } else {
                $reviewQuestions[] = $q;
            }
        }
        
        // Ensure Target Questions are included (Try to take all of them, usually 3)
        $selected = $targetQuestions;
        
        // Fill remainder from Review Questions (shuffled)
        shuffle($reviewQuestions);
        $remainder = $limit - count($selected);
        
        if ($remainder > 0) {
            $selected = array_merge($selected, array_slice($reviewQuestions, 0, $remainder));
        } else {
            // If we have more target questions than limit (unlikely with default limit 5, but possible), slice
            $selected = array_slice($selected, 0, $limit);
        }
        
        // Shuffle the FINAL selection so the order is random for the user
        shuffle($selected);
        $questions = $selected;

        // Format for Frontend
        $formatted = collect($questions)->map(function ($q) {
            $options = json_decode($q->options, true);
            if (!is_array($options)) $options = []; 
            
            // If we are reusing the same question object multiple times in the array,
            // we should re-shuffle the options for the frontend display so it doesn't look identical.
            // But here we are sending the JSON options from DB. 
            // To be truly dynamic, options should be shuffled here or in frontend.
            // Let's shuffle here for variety if it's the same ID.
            shuffle($options);

            return [
                'id' => $q->id,
                'ayah_id' => $q->ayah_id,
                'question' => $q->prompt, 
                'type' => $q->type,
                'options' => $options,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Helper to generate a question on the fly and save it to DB.
     * Enforces strict context rules: Same Surah, Neighboring Ayahs.
     * Uses ID-based options to handle Mutashabihat (repeated verses).
     * 
     * Supports types: 'mcq', 'next_ayah', 'prev_ayah'
     */
    private function createQuestionForAyah(Ayah $ayah, string $type = 'mcq')
    {
        $prompt = '';
        $answerAyah = $ayah; // Default: The answer is the target ayah itself
        
        // 1. Determine Prompt & Answer based on Type
        switch ($type) {
            case 'next_ayah':
                // Logic: "What comes after [Prev of Target]?" -> Answer: Target Ayah
                // Requires Target Ayah > 1, so we have a previous one to ask about
                if ($ayah->ayah_number <= 1) return null;
                
                $prevOfTarget = Ayah::where('surah_id', $ayah->surah_id)
                    ->where('ayah_number', $ayah->ayah_number - 1)
                    ->first();
                    
                if (!$prevOfTarget) return null;
                
                $prompt = "ما الآية التي تلي قوله تعالى: {" . $prevOfTarget->text_uthmani . "}؟";
                // Answer is $ayah (Target)
                break;

            case 'prev_ayah':
                // Logic: "What comes before [Target]?" -> Answer: [Prev of Target]
                // Requires Target Ayah > 1
                if ($ayah->ayah_number <= 1) return null;

                $prevOfTarget = Ayah::where('surah_id', $ayah->surah_id)
                    ->where('ayah_number', $ayah->ayah_number - 1)
                    ->first();
                    
                if (!$prevOfTarget) return null;

                $prompt = "ما الآية التي تسبق قوله تعالى: {" . $ayah->text_uthmani . "}؟";
                $answerAyah = $prevOfTarget; // Answer is the previous ayah
                break;

            case 'mcq':
            default:
                $prompt = 'اختر الآية الصحيحة:';
                // Answer is $ayah (Target)
                break;
        }

        // 2. Generate Options (Distractors from SAME Surah)
        // Options should be neighbors of the ANSWER, not necessarily the TARGET
        // (Though usually they are close enough to be the same pool)
        
        $neighbors = Ayah::where('surah_id', $answerAyah->surah_id)
            ->whereBetween('ayah_number', [
                max(1, $answerAyah->ayah_number - 4),
                $answerAyah->ayah_number + 4
            ])
            ->where('id', '!=', $answerAyah->id)
            ->get();

        $distractorAyahs = [];
        
        // Prefer Neighbors
        if ($neighbors->count() >= 3) {
            $distractorAyahs = $neighbors->random(3);
        } else {
            $distractorAyahs = $neighbors->all(); // Take all available neighbors
            
            // Fallback: Same Surah Randoms
            $needed = 3 - count($distractorAyahs);
            if ($needed > 0) {
                 $existingIds = array_merge([$answerAyah->id], collect($distractorAyahs)->pluck('id')->toArray());
                 $randomSameSurah = Ayah::where('surah_id', $answerAyah->surah_id)
                    ->whereNotIn('id', $existingIds)
                    ->inRandomOrder()
                    ->limit($needed)
                    ->get();
                 
                 $distractorAyahs = array_merge($distractorAyahs, $randomSameSurah->toArray());
            }
        }
        
        // Build options as structured objects: [{ayah_id, text}, ...]
        // Include the CORRECT ANSWER ($answerAyah)
        $options = array_merge(
            [['ayah_id' => $answerAyah->id, 'text' => $answerAyah->text_uthmani]],
            array_map(function($distractor) {
                return [
                    'ayah_id' => is_array($distractor) ? $distractor['id'] : $distractor->id,
                    'text' => is_array($distractor) ? $distractor['text_uthmani'] : $distractor->text_uthmani
                ];
            }, is_array($distractorAyahs) ? $distractorAyahs : $distractorAyahs->toArray())
        );
        
        shuffle($options);

        // 3. Optimized Debug Log
        $optionAyahIds = array_column($options, 'ayah_id');
        $surahMap = Ayah::whereIn('id', $optionAyahIds)
            ->pluck('surah_id', 'id');

        \Illuminate\Support\Facades\Log::info('Generated Question Options', [
            'type' => $type,
            'target_ayah_id' => $ayah->id, // The question concept is still "about" this target
            'answer_ayah_id' => $answerAyah->id,
            'option_ayah_ids' => $optionAyahIds,
        ]);

        return Question::create([
            'ayah_id' => $ayah->id, // Linked to Target for tracking
            'prompt' => $prompt,
            'options' => json_encode($options),
            'correct_answer' => (string)$answerAyah->id, // The correct choice ID
            'type' => $type,
        ]);
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required', // Now expects ayah_id (int or string)
        ]);

        $user = Auth::user();
        $question = Question::find($request->question_id);

        $isCorrect = false;
        $submittedAnswer = $request->answer;

        // correct_answer is now an ayah_id (stored as string)
        $correctAyahId = (int)$question->correct_answer;
        
        // Support both ID submission (new) and index submission (legacy fallback)
        if (is_numeric($submittedAnswer)) {
            $submittedId = (int)$submittedAnswer;
            
            // Check if it's a direct ID match
            if ($submittedId === $correctAyahId) {
                $isCorrect = true;
            } else {
                // Fallback: check if it's an index into options array
                $options = json_decode($question->options, true);
                if (isset($options[$submittedId]) && isset($options[$submittedId]['ayah_id'])) {
                    $isCorrect = $options[$submittedId]['ayah_id'] === $correctAyahId;
                }
            }
        }

        \App\Models\UserAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => is_string($submittedAnswer) ? $submittedAnswer : (string)$submittedAnswer,
            'is_correct' => $isCorrect
        ]);
        
        // Get correct Ayah text for feedback
        $correctAyah = Ayah::find($correctAyahId);
        
        return response()->json([
            'status' => 'recorded', 
            'correct' => $isCorrect,
            'feedback' => $isCorrect ? 'أحسنت!' : 'إجابة خاطئة، حاول مرة أخرى.',
            'correct_answer' => $correctAyah ? $correctAyah->text_uthmani : $question->correct_answer
        ]);
    }
}
