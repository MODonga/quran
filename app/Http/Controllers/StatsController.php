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

        // 4. Most Failed Ayahs
        $mostFailedAyahs = DB::table('user_answers')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->join('ayahs', 'questions.ayah_id', '=', 'ayahs.id')
            ->join('surahs', 'ayahs.surah_id', '=', 'surahs.id')
            ->where('user_answers.user_id', $user->id)
            ->where('user_answers.is_correct', false)
            ->select('surahs.name_ar as surah', 'ayahs.ayah_number', DB::raw('count(user_answers.id) as fails'))
            ->groupBy('surahs.name_ar', 'ayahs.ayah_number')
            ->orderByDesc('fails')
            ->take(5)
            ->get();

        return response()->json([
            'memorized_ayahs'     => $memorizedCount,
            'streak'              => $user->quran_streak,
            'total_days'          => $user->quran_total_days,
            'accuracy'            => $accuracy,
            'has_pending_reviews' => $hasPendingReviews,
            'most_failed_ayahs'   => $mostFailedAyahs,
        ]);
    }
}
