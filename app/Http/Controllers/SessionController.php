<?php

namespace App\Http\Controllers;

use App\Models\Ayah;
use App\Models\MemorizationSession;
use App\Models\ReviewSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Start or resume a daily memorization session.
     */
    public function start(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // 1. Check for pending reviews (SRS)
        $reviewAyah = ReviewSchedule::with(['ayah.surah'])
            ->where('user_id', $user->id)
            ->where('scheduled_at', '<=', now())
            ->where('status', 'pending')
            ->orderBy('scheduled_at', 'asc') // Prioritize oldest due
            ->orderBy('ayah_id', 'asc')
            ->first();

        if ($reviewAyah) {
            // Manage session record for stats
             $session = MemorizationSession::firstOrCreate(
                ['user_id' => $user->id, 'session_date' => $today],
                ['review_completed' => false, 'memorization_unlocked' => false]
            );

            return response()->json([
                'session_id' => $session->id,
                // 'review_pending' triggers the redirect-to-review flow in the frontend
                'status'     => 'review_pending',
                'mode'       => 'review',
                'next_ayah'  => [
                    'ayah_id'     => $reviewAyah->ayah->id,
                    'surah_id'    => $reviewAyah->ayah->surah_id,
                    'text'        => $reviewAyah->ayah->text_uthmani,
                    'surah_name'  => $reviewAyah->ayah->surah->name_ar,
                    'ayah_number' => $reviewAyah->ayah->ayah_number,
                ],
                'message' => 'Time for review!',
            ]);
        }

        // 2. Manage daily session record
        $session = MemorizationSession::firstOrCreate(
            ['user_id' => $user->id, 'session_date' => $today],
            ['review_completed' => true, 'memorization_unlocked' => true]
        );

        // 3. Unlock next ayah
        $nextAyahId = ($user->last_ayah_id ?? 0) + 1;
        $maxAyahs = 6236; 

        if ($nextAyahId > $maxAyahs) {
            return response()->json([
                'session_id' => $session->id,
                'status' => 'completed',
                'message' => 'Congratulations! You have completed the entire Quran.',
            ]);
        }

        $nextAyah = Ayah::with('surah')->find($nextAyahId);

        if (!$nextAyah) {
             return response()->json([
                'session_id' => $session->id,
                'status' => 'error',
                'message' => 'Next Ayah not found.',
            ], 404);
        }

        return response()->json([
            'session_id' => $session->id,
            'status' => 'new_ayah_unlocked', // Or 'viewing'
            'mode' => 'new',
            'next_ayah' => [
                'ayah_id' => $nextAyah->id,
                'surah_id' => $nextAyah->surah_id,
                'text' => $nextAyah->text_uthmani,
                'surah_name' => $nextAyah->surah->name_ar,
                'ayah_number' => $nextAyah->ayah_number,
            ],
        ]);
    }
}
