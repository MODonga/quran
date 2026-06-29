<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function sendOTP(string $email, string $code): bool
    {
        try {
            Mail::html(self::buildOtpHtml($code), function ($message) use ($email) {
                $message
                    ->to($email)
                    ->subject('كود التحقق الخاص بك - منصة حفظ القرآن')
                    ->from(
                        config('mail.from.address', 'noreply@example.com'),
                        config('mail.from.name', 'منصة حفظ القرآن')
                    );
            });

            return true;
        } catch (\Throwable $e) {
            \Log::error('Mail Error: ' . $e->getMessage());
            return false;
        }
    }

    private static function buildOtpHtml(string $code): string
    {
        return <<<HTML
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
    .container { max-width: 480px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #059669, #047857); padding: 32px; text-align: center; }
    .header h1 { color: white; margin: 0; font-size: 22px; }
    .body { padding: 32px; text-align: center; }
    .body p { color: #555; line-height: 1.8; margin-bottom: 24px; }
    .code { font-size: 42px; font-weight: bold; letter-spacing: 12px; color: #059669; background: #f0fdf4; border: 2px dashed #6ee7b7; border-radius: 12px; padding: 20px 32px; display: inline-block; margin: 12px 0 28px; }
    .note { font-size: 13px; color: #999; margin-top: 24px; }
    .footer { background: #f9fafb; padding: 16px; text-align: center; font-size: 12px; color: #aaa; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>📖 منصة حفظ القرآن الكريم</h1>
    </div>
    <div class="body">
      <p>لقد طلبت إعادة تعيين كلمة المرور.<br>أدخل الكود التالي لاستكمال العملية:</p>
      <div class="code">{$code}</div>
      <p class="note">⏳ ينتهي هذا الكود خلال <strong>5 دقائق</strong>.<br>إذا لم تطلب إعادة التعيين، تجاهل هذا البريد.</p>
    </div>
    <div class="footer">منصة حفظ القرآن الكريم &copy; 2025</div>
  </div>
</body>
</html>
HTML;
    }
}
