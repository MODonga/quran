namespace App\Http\Controllers;

use App\Models\Hadith;
use App\Models\HadithReviewSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HadithReviewController extends Controller
{
    /**
     * Get hadiths due for review.
     */
    public function getDue()
    {
        $user = Auth::user();
        
        $due = HadithReviewSchedule::where('user_id', $user->id)
            ->where('scheduled_at', '<=', now())
            ->with(['hadith.book', 'hadith.chapter'])
            ->get();

        return response()->json($due);
    }

    /**
     * Confirm memorization or review of a hadith.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'hadith_id' => 'required|exists:hadiths,id',
            'success' => 'required|boolean',
        ]);

        $user = Auth::user();
        $hadithId = $request->hadith_id;
        $success = $request->success;

        DB::transaction(function () use ($user, $hadithId, $success) {
            $review = HadithReviewSchedule::where('user_id', $user->id)
                ->where('hadith_id', $hadithId)
                ->first();

            if ($review) {
                // CASE 1: Review existing schedule
                $review->reschedule($success);
            } else {
                // CASE 2: New Memorization
                HadithReviewSchedule::create([
                    'user_id' => $user->id,
                    'hadith_id' => $hadithId,
                    'scheduled_at' => now()->addDays(1),
                    'interval' => 1,
                    'status' => 'pending',
                ]);
                
                // Note: We are not tracking stats like streak for Hadiths separately for now 
                // to keep it simple, but we could add HadithUserProgress if needed later.
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Hadith review/memorization recorded.',
        ]);
    }
}
