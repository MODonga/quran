<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function sendOTP($email, $code)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.mailtrap.io');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port       = env('MAIL_PORT', 587);
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'noreply@quran-memo.com'), env('MAIL_FROM_NAME', 'Quran Platform'));
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'كود التحقق الخاص بك - منصة حفظ القرآن';
            
            // We will use a Blade template later, for now keeping a clean HTML structure here
            // that is easily replaceable or can be called from Controller with view()->render()
            $mail->Body    = view('emails.otp', ['code' => $code])->render();

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            \Log::error("Mail Error: " . $e->getMessage());
            return false;
        }
    }
}
