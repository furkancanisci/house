<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $locale === 'ar' ? 'تأكيد البريد الإلكتروني' : 'Email Verification' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #067977, #045a5c);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .intro {
            color: #666;
            margin-bottom: 25px;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            border-{{ $locale === 'ar' ? 'right' : 'left' }}: 3px solid #067977;
        }
        
        .user-info h3 {
            color: #067977;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .cta {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #067977, #045a5c);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
        }
        
        .instructions {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .instructions h4 {
            color: #1976d2;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        
        .instructions ol {
            margin: 0;
            padding-{{ $locale === 'ar' ? 'right' : 'left' }}: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
            color: #555;
        }
        
        .security {
            background: #fff3e0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            border-{{ $locale === 'ar' ? 'right' : 'left' }}: 3px solid #ff9800;
        }
        
        .security h4 {
            color: #e65100;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        
        .security ul {
            margin: 0;
            padding-{{ $locale === 'ar' ? 'right' : 'left' }}: 20px;
        }
        
        .security li {
            margin-bottom: 5px;
            color: #bf360c;
            font-size: 14px;
        }
        
        .expiry {
            background: #ff9800;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }
        
        .alt-link {
            background: #f5f5f5;
            border-radius: 5px;
            padding: 15px;
            margin: 25px 0;
        }
        
        .alt-link p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .link {
            word-break: break-all;
            color: #067977;
            font-size: 12px;
            background: white;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: monospace;
        }
        
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 13px;
        }
        
        .footer h3 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .footer a {
            color: #067977;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            body { padding: 10px; }
            .container { margin: 0; }
            .header { padding: 20px; }
            .content { padding: 20px; }
            .header h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $locale === 'ar' ? 'مرحباً بك في بيست ترند' : 'Welcome to Best Trend' }}</h1>
            <p>{{ $locale === 'ar' ? 'تأكيد البريد الإلكتروني' : 'Email Verification Required' }}</p>
        </div>

        <div class="content">
            <div class="greeting">{{ $locale === 'ar' ? 'مرحباً ' . $user->name . '!' : 'Hello ' . $user->name . '!' }}</div>
            
            <p class="intro">{{ $locale === 'ar' ? 'شكراً لك على انضمامك إلى بيست ترند. لضمان أمان حسابك، نحتاج إلى تأكيد عنوان بريدك الإلكتروني.' : 'Thank you for joining our Best Trend. To ensure the security of your account, we need to verify your email address.' }}</p>

            <div class="user-info">
                <h3>{{ $locale === 'ar' ? 'تفاصيل الحساب' : 'Account Details' }}</h3>
                <div class="info-row">
                    <span class="info-label">{{ $locale === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">{{ $locale === 'ar' ? 'البريد الإلكتروني:' : 'Email:' }}</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div class="info-row">
                    <span class="info-label">{{ $locale === 'ar' ? 'الهاتف:' : 'Phone:' }}</span>
                    <span class="info-value">{{ $user->phone }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">{{ $locale === 'ar' ? 'نوع الحساب:' : 'Account Type:' }}</span>
                    <span class="info-value">
                        @if($user->user_type === 'owner')
                            {{ $locale === 'ar' ? 'مالك عقار' : 'Property Owner' }}
                        @elseif($user->user_type === 'tenant')
                            {{ $locale === 'ar' ? 'مستأجر' : 'Tenant' }}
                        @elseif($user->user_type === 'agent')
                            {{ $locale === 'ar' ? 'وسيط عقاري' : 'Real Estate Agent' }}
                        @else
                            {{ $locale === 'ar' ? 'مستخدم عام' : 'General User' }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="cta">
                <a href="{{ $verificationUrl }}" class="btn">
                    {{ $locale === 'ar' ? '✅ تأكيد البريد الإلكتروني' : '✅ Verify Email' }}
                </a>
            </div>

            <div class="instructions">
                <h4>{{ $locale === 'ar' ? 'خطوات التأكيد' : 'Verification Steps' }}</h4>
                <ol>
                    <li>{{ $locale === 'ar' ? 'انقر على زر التأكيد أعلاه' : 'Click the verification button above' }}</li>
                    <li>{{ $locale === 'ar' ? 'ستتم إعادة توجيهك إلى صفحة آمنة' : 'You will be redirected to a secure page' }}</li>
                    <li>{{ $locale === 'ar' ? 'سيتم تفعيل حسابك بالكامل' : 'Your account will be fully activated' }}</li>
                    <li>{{ $locale === 'ar' ? 'يمكنك تسجيل الدخول واستخدام المنصة' : 'You can log in and use the platform' }}</li>
                </ol>
            </div>

            <div class="security">
                <h4>{{ $locale === 'ar' ? 'معلومات الأمان' : 'Security Information' }}</h4>
                <ul>
                    <li>{{ $locale === 'ar' ? 'الرابط آمن ومشفر' : 'This link is secure and encrypted' }}</li>
                    <li>{{ $locale === 'ar' ? 'صالح لمدة 24 ساعة فقط' : 'Valid for 24 hours only' }}</li>
                    <li>{{ $locale === 'ar' ? 'يستخدم مرة واحدة فقط' : 'Can only be used once' }}</li>
                    <li>{{ $locale === 'ar' ? 'لا تشارك هذا الرابط مع أحد' : 'Do not share this link with anyone' }}</li>
                </ul>
                <div class="expiry">
                    {{ $locale === 'ar' ? 'ينتهي في: ' : 'Expires at: ' }}{{ $expiresAt }}
                </div>
            </div>

            <div class="alt-link">
                <p>{{ $locale === 'ar' ? 'إذا كنت تواجه مشكلة، انسخ الرابط التالي:' : 'If you have trouble, copy this link:' }}</p>
                <div class="link">{{ $verificationUrl }}</div>
            </div>
        </div>

        <div class="footer">
            <h3>{{ $locale === 'ar' ? 'منصة إدارة العقارات' : 'Property Management Platform' }}</h3>
            <p>{{ $locale === 'ar' ? 'هذا بريد تلقائي، لا ترد عليه.' : 'This is an automated email, do not reply.' }}</p>
            <p>{{ $locale === 'ar' ? 'للدعم:' : 'Support:' }} <a href="mailto:support@propertymanagement.com">support@propertymanagement.com</a></p>
        </div>
    </div>
</body>
</html>