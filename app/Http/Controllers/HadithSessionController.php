<?php

namespace App\Http\Controllers;

use App\Models\Hadith;
use App\Models\HadithReviewSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HadithSessionController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();

        // 1. Check for due reviews
        $dueReview = HadithReviewSchedule::where('user_id', $user->id)
            ->where('scheduled_at', '<=', now())
            ->where('status', 'pending')
            ->orderBy('scheduled_at', 'asc')
            ->first();

        if ($dueReview) {
            return response()->json([
                'status' => 'success',
                'mode' => 'review',
                'next_hadith' => $dueReview->hadith->load(['book', 'chapter']),
            ]);
        }

        // 2. Otherwise, get next hadith based on last_hadith_id
        $lastId = $user->last_hadith_id ?? 0;
        $nextHadith = Hadith::where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->first();

        if (!$nextHadith) {
            // If the sequence is broken, find the first hadith the user hasn't memorized
            $memorizedIds = HadithReviewSchedule::where('user_id', $user->id)->pluck('hadith_id');
            $nextHadith = Hadith::whereNotIn('id', $memorizedIds)
                ->orderBy('id', 'asc')
                ->first();
        }

        if (!$nextHadith) {
            return response()->json([
                'status' => 'completed',
                'message' => 'لقد أتممت جميع الأحاديث المتوفرة حالياً!',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'mode' => 'new',
            'next_hadith' => $nextHadith->load(['book', 'chapter']),
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'hadith_id' => 'required|exists:hadiths,id',
        ]);

        $user = Auth::user();
        $hadithId = $request->hadith_id;

        // Update last_hadith_id if it's a new one (or just keep moving forward)
        if (!$user->last_hadith_id || $hadithId > $user->last_hadith_id) {
            $user->last_hadith_id = $hadithId;
            
            // Track Hadith Streak independently
            $todayHadithCount = HadithReviewSchedule::where('user_id', $user->id)
                ->whereDate('created_at', now())
                ->count();

            if ($todayHadithCount === 0) { // This is the first one for today
                $user->hadith_total_days = $user->hadith_total_days + 1;

                $yesterday = now()->subDay()->toDateString();
                $hadHadithYesterday = HadithReviewSchedule::where('user_id', $user->id)
                    ->whereDate('created_at', $yesterday)
                    ->exists();

                if ($hadHadithYesterday) {
                    $user->hadith_streak = $user->hadith_streak + 1;
                } else {
                    $user->hadith_streak = 1;
                }
            }

            $user->save();
        }

        // Create or update review schedule
        HadithReviewSchedule::updateOrCreate(
            ['user_id' => $user->id, 'hadith_id' => $hadithId],
            [
                'scheduled_at' => now()->addDays(1),
                'status' => 'pending',
                'interval' => 1,
                'ease_factor' => 2.5,
            ]
        );

        return response()->json(['message' => 'تم تأكيد الحفظ']);
    }
}
