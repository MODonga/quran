<?php

namespace App\Http\Controllers;

use App\Models\Hadith;
use App\Models\HadithBook;
use App\Models\HadithChapter;
use Illuminate\Http\Request;

class HadithController extends Controller
{
    public function indexBooks()
    {
        return response()->json(HadithBook::all());
    }

    public function indexChapters(HadithBook $book)
    {
        return response()->json($book->chapters);
    }

    public function indexHadiths(Request $request, HadithBook $book)
    {
        $query = Hadith::where('book_id', $book->id);
        
        if ($request->has('chapter_id')) {
            $query->where('chapter_id', $request->chapter_id);
        }

        $hadiths = $query->paginate(20);
        return response()->json($hadiths);
    }

    public function show(Hadith $hadith)
    {
        return response()->json($hadith->load(['book', 'chapter']));
    }
}
