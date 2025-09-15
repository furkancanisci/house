<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'تم قبول إعلان العقار الخاص بك' : 'Your Property Has Been Approved' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
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
            color: #067977;
        }

        .message {
            margin-bottom: 25px;
            font-size: 16px;
        }

        .property-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: #f9fafb;
        }

        .property-title {
            font-size: 18px;
            font-weight: 600;
            color: #067977;
            margin-bottom: 10px;
        }

        .property-details {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .property-price {
            font-size: 20px;
            font-weight: 700;
            color: #059669;
            margin-top: 15px;
        }

        .button {
            display: inline-block;
            background: linear-gradient(135deg, #067977, #059669);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        .button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .footer {
            background: #f3f4f6;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            background: #dcfdf7;
            color: #065f46;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🎉</div>
            <h1>
                {{ app()->getLocale() === 'ar' ? 'تهانينا! تم قبول إعلانك' : 'Congratulations! Your Property is Approved' }}
            </h1>
            <p>
                {{ app()->getLocale() === 'ar' ? 'إعلان عقارك الآن منشور ومتاح للعرض' : 'Your property listing is now live and visible to potential buyers/renters' }}
            </p>
        </div>

        <div class="content">
            <div class="greeting">
                {{ app()->getLocale() === 'ar' ? 'مرحباً ' . $user->first_name : 'Hello ' . $user->first_name }}!
            </div>

            <div class="message">
                {{ app()->getLocale() === 'ar' ?
                    'نحن سعداء لإعلامك بأن إعلان العقار الخاص بك قد تم قبوله ونشره بنجاح على منصتنا. يمكن للمستخدمين الآن مشاهدة عقارك والتواصل معك مباشرة.' :
                    'We\'re excited to inform you that your property listing has been approved and successfully published on our platform. Users can now view your property and contact you directly.'
                }}
            </div>

            <div class="property-card">
                <div class="property-title">{{ $property->title }}</div>
                <div class="status-badge">
                    {{ app()->getLocale() === 'ar' ? '✅ تم النشر' : '✅ Published' }}
                </div>

                <div class="property-details">
                    <strong>{{ app()->getLocale() === 'ar' ? 'النوع:' : 'Type:' }}</strong>
                    {{ $property->property_type }} - {{ $property->listing_type === 'sale' ? (app()->getLocale() === 'ar' ? 'للبيع' : 'For Sale') : (app()->getLocale() === 'ar' ? 'للإيجار' : 'For Rent') }}
                </div>

                <div class="property-details">
                    <strong>{{ app()->getLocale() === 'ar' ? 'الموقع:' : 'Location:' }}</strong>
                    {{ $property->city }}, {{ $property->state }}
                </div>

                @if($property->bedrooms)
                <div class="property-details">
                    <strong>{{ app()->getLocale() === 'ar' ? 'غرف النوم:' : 'Bedrooms:' }}</strong>
                    {{ $property->bedrooms }}
                </div>
                @endif

                @if($property->bathrooms)
                <div class="property-details">
                    <strong>{{ app()->getLocale() === 'ar' ? 'الحمامات:' : 'Bathrooms:' }}</strong>
                    {{ $property->bathrooms }}
                </div>
                @endif

                <div class="property-price">
                    ${{ number_format($property->price) }}
                    @if($property->listing_type === 'rent')
                        {{ $property->price_type === 'monthly' ? (app()->getLocale() === 'ar' ? '/شهرياً' : '/month') : (app()->getLocale() === 'ar' ? '/سنوياً' : '/year') }}
                    @endif
                </div>
            </div>

            <div style="text-align: center;">
                @if(config('app.frontend_url'))
                <a href="{{ config('app.frontend_url') }}/property/{{ $property->slug }}" class="button">
                    {{ app()->getLocale() === 'ar' ? 'عرض إعلانك' : 'View Your Listing' }}
                </a>
                @endif
            </div>

            <div class="message">
                <strong>{{ app()->getLocale() === 'ar' ? 'ماذا يحدث الآن؟' : 'What happens next?' }}</strong>
                <ul>
                    <li>{{ app()->getLocale() === 'ar' ? 'عقارك مرئي الآن لجميع المستخدمين على المنصة' : 'Your property is now visible to all users on the platform' }}</li>
                    <li>{{ app()->getLocale() === 'ar' ? 'ستتلقى إشعارات عند تواصل المهتمين معك' : 'You\'ll receive notifications when interested users contact you' }}</li>
                    <li>{{ app()->getLocale() === 'ar' ? 'يمكنك تحديث تفاصيل العقار في أي وقت من حسابك' : 'You can update your property details anytime from your account' }}</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>
                {{ app()->getLocale() === 'ar' ? 'شكراً لاختيارك منصتنا لبيع أو تأجير عقارك!' : 'Thank you for choosing our platform to sell or rent your property!' }}
            </p>
            <p>
                {{ app()->getLocale() === 'ar' ? 'فريق العقارات' : 'Real Estate Team' }}
            </p>
        </div>
    </div>
</body>
</html>