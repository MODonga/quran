<?php

namespace App\Http\Controllers;

use App\Models\HadithReviewSchedule;
use App\Models\HadithUserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HadithStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Memorized Hadiths Count
        $memorizedCount = HadithReviewSchedule::where('user_id', $user->id)->count();

        // 2. Accuracy Rate
        $correct = HadithUserAnswer::where('user_id', $user->id)
            ->where('is_correct', true)
            ->count();
        $total = HadithUserAnswer::where('user_id', $user->id)->count();
        $accuracy = $total ? round(($correct / $total) * 100, 2) : 0;

        // 3. Pending Reviews
        $hasPendingReviews = HadithReviewSchedule::where('user_id', $user->id)
            ->where('scheduled_at', '<=', now())
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'memorized_hadiths' => $memorizedCount,
            'streak' => $user->hadith_streak,
            'accuracy' => $accuracy,
            'has_pending_reviews' => $hasPendingReviews,
        ]);
    }
}
