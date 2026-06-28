<?php

namespace Tests\Unit;

use App\Models\ReviewSchedule;
use App\Models\Surah;
use App\Models\Ayah;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class SpacedRepetitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SR success progression.
     */
    public function test_sr_success_progression()
    {
        // Setup dependencies
        Surah::create(['id' => 1, 'name_ar' => 'Test', 'ayah_count' => 10]);
        Ayah::create(['id' => 1, 'surah_id' => 1, 'ayah_number' => 1, 'text_uthmani' => 'Test']);
        $user = User::factory()->create(['id' => 1]);

        $review = ReviewSchedule::create([
            'user_id' => $user->id,
            'ayah_id' => 1,
            'scheduled_at' => now(),
            'interval' => 1,
            'status' => 'pending'
        ]);

        $review->reschedule(true); // Success

        $this->assertEquals(3, $review->interval);
        $this->assertEquals('done', $review->status);
        $this->assertTrue($review->scheduled_at->isFuture());
        
        $review->update(['status' => 'pending']);
        $review->reschedule(true); // Success again (3 -> 7)
        $this->assertEquals(7, $review->interval);
    }

    /**
     * Test SR failure reset.
     */
    public function test_sr_failure_reset()
    {
        Surah::firstOrCreate(['id' => 1], ['name_ar' => 'Test', 'ayah_count' => 10]);
        Ayah::firstOrCreate(['id' => 1], ['surah_id' => 1, 'ayah_number' => 1, 'text_uthmani' => 'Test']);
        $user = User::firstOrCreate(['id' => 1], [
            'name' => 'Test', 
            'email' => 'test@example.com', 
            'password' => 'pass'
        ]);

        $review = ReviewSchedule::create([
            'user_id' => $user->id,
            'ayah_id' => 1,
            'scheduled_at' => now(),
            'interval' => 14,
            'status' => 'pending'
        ]);

        $review->reschedule(false); // Failure

        // Interval should NOT advance, scheduled_at should be tomorrow
        $this->assertEquals(14, $review->interval);
        $this->assertEquals('missed', $review->status);
        $this->assertTrue($review->scheduled_at->isTomorrow());
    }
}
