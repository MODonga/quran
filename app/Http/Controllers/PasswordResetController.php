<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function sendCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ], [
                'email.exists' => 'البريد الإلكتروني غير مسجل لدينا.'
            ]);

            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Log the code to laravel.log for developer access
            // Log the code for developer access
            \Log::info("Password Reset Code generated for {$request->email}: {$code}");
            
            PasswordResetCode::updateOrCreate(
                ['email' => $request->email],
                [
                    'code' => $code,
                    'created_at' => now()
                ]
            );

            // Attempt to send email via MailService
            // MailService::sendOTP($request->email, $code);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال كود التحقق بنجاح.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errors()['email'][0] ?? 'بيانات غير صالحة'
            ], 422);
        } catch (\Throwable $e) {
            \Log::error("CRITICAL ERROR in sendCode: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في المخدم، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $record = PasswordResetCode::where('email', $request->email)->first();

        // 1. Check if record exists
        if (!$record) {
            return response()->json(['message' => 'كود التحقق غير صحيح، يرجى التأكد من الكود المكتوب.'], 422);
        }

        // 2. Check Expiry FIRST (5 Minutes)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) >= 5) {
            return response()->json(['message' => 'انتهت صلاحية الكود (صلاحية الكود 5 دقائق فقط)، يرجى طلب كود جديد.'], 422);
        }

        // 3. Check Code Match
        if ($record->code !== $request->code) {
            return response()->json(['message' => 'كود التحقق غير صحيح، يرجى التأكد من الكود المكتوب.'], 422);
        }

        return response()->json(['message' => 'تم التحقق من الكود بنجاح.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $record = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'الكود غير صحيح.'], 422);
        }

        if (Carbon::parse($record->created_at)->diffInMinutes(now()) >= 5) {
            return response()->json(['message' => 'انتهت صلاحية الكود، حاول مرة أخرى.'], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        $record->delete();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.']);
    }
}
