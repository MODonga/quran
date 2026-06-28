<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ayah;
use App\Models\Surah;
use App\Models\UserProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAlgorithmTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the 60/40 split ratio.
     */
    public function test_quiz_generation_ratio()
    {
        // 1. Setup Data - Create Surah and Ayahs FIRST
        Surah::create(['id' => 1, 'name_ar' => 'Test', 'ayah_count' => 20]);
        for ($i = 1; $i <= 20; $i++) {
            Ayah::create([
                'id' => $i,
                'surah_id' => 1,
                'ayah_number' => $i,
                'text_uthmani' => "Text $i"
            ]);
        }

        $user = User::factory()->create(['last_ayah_id' => 10]);
        
        // Make ayahs 1-10 "due for review"
        for ($i = 1; $i <= 10; $i++) {
            UserProgress::create([
                'user_id' => $user->id,
                'ayah_id' => $i,
                'status' => 'learning',
                'next_review' => now()->subDay()
            ]);
        }

        // 2. Request Quiz
        $response = $this->actingAs($user)
                         ->getJson('/api/quiz/generate?limit=10');

        $response->assertStatus(200);
        $questions = $response->json('questions');

        // 3. Verify Ratio (40% new [11,12,13,14], 60% old [1-10])
        $newCount = 0;
        $oldCount = 0;
        foreach ($questions as $q) {
            $id = $q['ayah_id'] ?? $q['id']; // Depends on how resource/array is returned
            if ($id > 10) $newCount++;
            else $oldCount++;
        }

        $this->assertEquals(4, $newCount, "Should have 4 new ayahs (40% of 10)");
        $this->assertEquals(6, $oldCount, "Should have 6 old ayahs (60% of 10)");
    }
}
