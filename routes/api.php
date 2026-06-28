<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\RecitationController;
use App\Http\Controllers\HadithController;
use App\Http\Controllers\HadithReviewController;
use App\Http\Controllers\HadithQuizController;
use App\Http\Controllers\HadithSessionController;
use App\Http\Controllers\HadithStatsController; // Added this import based on usage below
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (Rate Limited: 60 requests per minute)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile Logic
    Route::get('/profile', function (Request $request) {
        return response()->json($request->user());
    });
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

    // Memorization Logic
    Route::post('/session/start', [SessionController::class, 'start']);
    Route::post('/ayah/confirm', [ProgressController::class, 'confirm']);

    // Quiz Logic
    Route::get('/quiz/generate', [QuizController::class, 'generate']);
    Route::post('/quiz/submit', [QuizController::class, 'submitAnswer']);

    // Review Logic
    Route::post('/review/submit', [ReviewController::class, 'submit']);

    // Stats Logic
    Route::get('/stats', [StatsController::class, 'index']);

    // Recitation Logic
    Route::get('/reciters', [RecitationController::class, 'index']);
    Route::get('/recitation', [RecitationController::class, 'show']);

    // Hadith Logic
    Route::get('/hadith/stats', [\App\Http\Controllers\HadithStatsController::class, 'index']);
    Route::post('/hadith/session/start', [HadithSessionController::class, 'start']);
    Route::post('/hadith/session/confirm', [HadithSessionController::class, 'confirm']);
    
    Route::get('/hadith-quiz/generate', [HadithQuizController::class, 'generate']);
    
    Route::get('/hadith/books', [HadithController::class, 'indexBooks']);
    Route::get('/hadith/books/{book}/chapters', [HadithController::class, 'indexChapters']);
    Route::get('/hadith/books/{book}/hadiths', [HadithController::class, 'indexHadiths']);
    Route::get('/hadith/{hadith}', [HadithController::class, 'show']);
});

// Password Reset (Public)
Route::post('/password/send-code', [PasswordResetController::class, 'sendCode']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

