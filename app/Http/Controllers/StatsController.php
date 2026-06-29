<?php

namespace App\Http\Controllers;

use App\Models\UserAnswer;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Get user statistics.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Memorized Ayahs Count
        $memorizedCount = UserProgress::where('user_id', $user->id)
            ->where('status', 'learning') // In this context, 'learning' implies memorization is confirmed
            ->count();

        // 2. Accuracy Rate
        $correct = UserAnswer::where('user_id', $user->id)
            ->where('is_correct', true)
            ->count();
        $total = UserAnswer::where('user_id', $user->id)->count();
        $accuracy = $total ? round(($correct / $total) * 100, 2) : 0;

        // 3. Pending Reviews Check
        $hasPendingReviews = \App\Models\ReviewSchedule::where('user_id', $user->id)
            ->where('scheduled_at', '<=', now())
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'memorized_ayahs'     => $memorizedCount,
            'streak'              => $user->quran_streak,
            'total_days'          => $user->quran_total_days,
            'accuracy'            => $accuracy,
            'has_pending_reviews' => $hasPendingReviews,
        ]);
    }
}
