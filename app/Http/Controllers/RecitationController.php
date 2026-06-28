<?php

namespace App\Http\Controllers;

use App\Models\Reciter;
use Illuminate\Http\Request;

class RecitationController extends Controller
{
    /**
     * Get the list of all available reciters.
     */
    public function index()
    {
        $reciters = Reciter::all()->map(function ($reciter) {
            return [
                'id' => $reciter->id,
                'name' => $reciter->name,
                'style' => $reciter->rewaya,
            ];
        });

        return response()->json($reciters);
    }

    /**
     * Get the recitation MP3 URL for a specific surah and ayah.
     */
    public function show(Request $request)
    {
        $request->validate([
            'surah_id' => 'required|integer|min:1|max:114',
            'ayah_number' => 'nullable|integer|min:1|max:286', // Max ayahs in a surah
            'reciter_id' => 'nullable|exists:reciters,id',
        ]);

        // 1. Determine Reciter
        $reciterId = $request->reciter_id;
        if (!$reciterId) {
            // Default to the first reciter if none provided
            $reciter = Reciter::first();
        } else {
            $reciter = Reciter::find($reciterId);
        }

        if (!$reciter) {
            return response()->json(['message' => 'No reciters found in the database.'], 404);
        }

        // 2. Fetch URL from Database
        $recitation = \App\Models\Recitation::where('reciter_id', $reciter->id)
            ->where('surah_id', $request->surah_id)
            ->where('ayah_number', $request->ayah_number ?? 0) // Handle missing ayah logic if needed, but validation handles it?
            ->first();

        // If specific ayah not found, maybe handle full surah logic if that's still a thing, 
        // but for now we focus on the ayah requested.
        // The validation allows ayah_number to be nullable, but our seeder populates it.
        // If ayah_number is null, it might be requesting full surah which we might not have seeded yet in this format.

        if ($request->ayah_number) {
             $recitation = \App\Models\Recitation::where('reciter_id', $reciter->id)
                ->where('surah_id', $request->surah_id)
                ->where('ayah_number', $request->ayah_number)
                ->first();
             
             if ($recitation) {
                 $mp3Url = $recitation->audio_url;
             } else {
                 // Fallback or 404
                 // For now, let's keep the dynamic generation as fallback just in case seeder misses something
                 // or return 404 if strict.
                 // Let's return 404 to ensure we are using DB.
                 return response()->json(['message' => 'Recitation not found.'], 404);
             }
        } else {
             // Request for full surah? 
             // Our current system focuses on ayahs. 
             // Let's fallback to dynamic for full surah if needed, or just fail.
             // As per user request "update full database", likely implies ayahs.
             // Let's assume ayah_number is REQUIRED for this new logic to work well, 
             // but original code allowed nullable.
             // Let's simple check:
             return response()->json(['message' => 'Ayah number is required for audio playback system.'], 400);
        }

        return response()->json([
            'reciter_id' => $reciter->id,
            'reciter_name' => $reciter->name,
            'surah_id' => (int) $request->surah_id,
            'ayah_number' => $request->ayah_number ? (int) $request->ayah_number : null,
            'mp3_url' => $mp3Url,
            'url' => $mp3Url,
        ]);
    }
}
