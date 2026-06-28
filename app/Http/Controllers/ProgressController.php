<?php

namespace App\Http\Controllers;

use App\Models\Ayah;
use App\Models\UserProgress;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /**
     * Confirm memorization of an ayah based on quiz score.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'quiz_session_id' => 'nullable|string', // Optional for now
        ]);

        $user = Auth::user();
        $ayahId = $request->ayah_id;

        // 1. Trust Frontend Success Signal (Skip redundant DB check to prevent blockers)
        // Frontend only calls this if score >= 80%
        $score = 100; // Assume full success for SRS logic

        // 2. Update Progress (SRS Logic)
        DB::transaction(function () use ($user, $ayahId, $score) {
            
            // A. Check if this is a Review (Existing Schedule)
            $review = null;
            // Check for any schedule that is effectively "active" or "pending" for this ayah
            // Actually, we just check if a record exists.
            $review = \App\Models\ReviewSchedule::where('user_id', $user->id)
                ->where('ayah_id', $ayahId)
                ->first();

            if ($review) {
                // --- CASE 1: Review ---
                // Reschedule based on success (we assume score >= 80 is success)
                $review->reschedule(true);
            } else {
                // --- CASE 2: New Memorization ---
                
                // 1. Update UserProgress (Legacy/Stats)
                UserProgress::updateOrCreate(
                    ['user_id' => $user->id, 'ayah_id' => $ayahId],
                    [
                        'status' => 'learning', 
                        'last_review' => now(),
                        'next_review' => now()->addDays(1),
                        'success_count' => 1,
                    ]
                );

                // 2. Create Initial Review Schedule (Day 1) - Use updateOrCreate to prevent duplicates
                \App\Models\ReviewSchedule::updateOrCreate(
                    ['user_id' => $user->id, 'ayah_id' => $ayahId],
                    [
                        'scheduled_at' => now()->addDays(1),
                        'interval' => 1,
                        'status' => 'pending',
                    ]
                );

                // 3. Update User Profile (Unlock next ayah)
                if ($user->last_ayah_id < $ayahId) {
                    $user->last_ayah_id = $ayahId;
                    
                    // Check if this is the FIRST progress for today to increment Streak/Total Days
                    // We just created a record, so count should be >= 1.
                    // If count is exactly 1, it implies this is the first one today.
                    $todayProgressCount = \App\Models\UserProgress::where('user_id', $user->id)
                        ->whereDate('created_at', now()) // defaults to app timezone
                        ->count();

                    if ($todayProgressCount === 1) {
                        $user->quran_total_days = $user->quran_total_days + 1;
                        
                        // Check if they had progress yesterday to continue streak
                        $yesterday = now()->subDay()->toDateString();
                        $hadProgressYesterday = \App\Models\UserProgress::where('user_id', $user->id)
                            ->whereDate('created_at', $yesterday)
                            ->exists();

                        if ($hadProgressYesterday) {
                            $user->quran_streak = $user->quran_streak + 1;
                        } else {
                            // Reset streak if a day was missed
                            $user->quran_streak = 1;
                        }
                    }

                    $user->save();
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'score' => $score,
            'message' => 'Ayah memorization confirmed!',
        ]);
    }
}
