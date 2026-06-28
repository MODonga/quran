<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ReviewSchedule;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test reviews block new memorization.
     */
    public function test_pending_reviews_block_new_session()
    {
        // Setup data
        Surah::create(['id' => 1, 'name_ar' => 'Test', 'ayah_count' => 10]);
        Ayah::create(['id' => 1, 'surah_id' => 1, 'ayah_number' => 1, 'text_uthmani' => 'Test']);
        
        $user = User::factory()->create();
        
        // Add a pending review
        ReviewSchedule::create([
            'user_id' => $user->id,
            'ayah_id' => 1,
            'scheduled_at' => now()->subDay(),
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)
                         ->postJson('/api/session/start');

        $response->assertStatus(200); // We return a 200 with 'review_pending' status code in JSON
        $response->assertJson(['status' => 'review_pending']);
    }

    /**
     * Test confirmation requires a quiz attempt.
     */
    public function test_confirm_requires_quiz_attempt()
    {
        Surah::firstOrCreate(['id' => 1], ['name_ar' => 'Test', 'ayah_count' => 10]);
        Ayah::firstOrCreate(['id' => 1], ['surah_id' => 1, 'ayah_number' => 1, 'text_uthmani' => 'Test']);
        
        $user = User::factory()->create(['last_ayah_id' => null]);
        
        $response = $this->actingAs($user)
                         ->postJson('/api/ayah/confirm', [
                             'ayah_id' => 1,
                             'quiz_session_id' => 'test_id'
                         ]);

        // Should fail because no answers were submitted for this session/ayah
        $response->assertStatus(400); // Assuming 400 is based on my earlier logic or I might need to adjust it
        $response->assertJson(['message' => 'No quiz attempts found for this ayah.']);
    }
}
