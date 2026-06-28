<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }
        .header {
            background: linear-gradient(135deg, #1E6F5C 0%, #2D917A 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
            color: #333333;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .otp-code {
            display: inline-block;
            background-color: #f0f7f4;
            color: #1E6F5C;
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 8px;
            padding: 20px 40px;
            border-radius: 12px;
            border: 2px dashed #1E6F5C;
            margin-bottom: 30px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .warning {
            color: #e11d48;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>منصة حفظ القرآن</h1>
        </div>
        <div class="content">
            <p>مرحباً، لقد طلبت كود تحقق لإعادة تعيين كلمة المرور الخاصة بك.</p>
            <div class="otp-code">
                {{ $code }}
            </div>
            <p>يرجى إدخال هذا الكود في خانة التحقق لإتمام العملية.</p>
            <p class="warning">هذا الكود صالح لمدة 5 دقائق فقط.</p>
        </div>
        <div class="footer">
            إذا لم تطلب هذا الكود، يرجى تجاهل هذا الإيميل.<br>
            &copy; {{ date('Y') }} منصة حفظ القرآن. جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>
