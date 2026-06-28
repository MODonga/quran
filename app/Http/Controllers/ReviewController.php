<?php

namespace App\Http\Controllers;

use App\Models\ReviewSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Submit the result of a review.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'success' => 'required|boolean',
        ]);

        $user = Auth::user();
        
        $review = ReviewSchedule::where('user_id', $user->id)
            ->where('ayah_id', $request->ayah_id)
            ->where('status', 'pending')
            ->first();

        if (!$review) {
            return response()->json(['message' => 'No pending review found for this ayah.'], 404);
        }

        $review->reschedule($request->success);

        return response()->json([
            'status' => 'success',
            'next_review' => $review->scheduled_at,
            'interval' => $review->interval,
            'message' => 'Review recorded successfully.',
        ]);
    }
}
