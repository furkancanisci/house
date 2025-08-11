# نظام معالجة الصور المحسن للعقارات

## نظرة عامة

تم تطوير نظام معالجة صور محسن خصيصاً لصور العقارات والبيوت، يوفر جودة عالية مع أحجام ملفات محسنة.

## المميزات الرئيسية

### 🖼️ أحجام الصور المتعددة
- **Full Size**: 1200x800 بكسل (جودة 90%) - للعرض الكامل
- **Large**: 800x533 بكسل (جودة 85%) - للعرض الكبير
- **Medium**: 600x400 بكسل (جودة 80%) - للعرض المتوسط
- **Thumbnail**: 400x300 بكسل (جودة 75%) - للصور المصغرة
- **Small**: 300x200 بكسل (جودة 70%) - للمعاينة السريعة

### 📐 نسب العرض إلى الارتفاع
- **3:2** للأحجام الكبيرة والمتوسطة (مثالي لصور العقارات)
- **4:3** للصور المصغرة (أفضل للعرض المصغر)

### 🔧 التحسينات التقنية
- تحويل تلقائي إلى تنسيق **WebP** لتوفير مساحة التخزين
- ضغط ذكي يحافظ على الجودة
- معالجة متوازية لجميع الأحجام
- تنظيف تلقائي للملفات المؤقتة

## إعدادات الجودة

```php
// config/images.php
'quality_settings' => [
    'full' => [
        'width' => 1200,
        'height' => 800,
        'quality' => 90,
        'aspect_ratio' => '3:2',
        'format' => 'webp'
    ],
    // ... باقي الإعدادات
]
```

## قواعد التحقق من الصور

### أنواع الملفات المدعومة
- JPEG (.jpeg, .jpg)
- PNG (.png)
- WebP (.webp)

### حدود الحجم
- **الحد الأقصى**: 5 ميجابايت لكل صورة
- **الأبعاد الدنيا**: 400x300 بكسل
- **العدد الأقصى**: 20 صورة لكل عقار

### التحقق التلقائي
- فحص نوع الملف وصحته
- التأكد من الأبعاد الدنيا
- فحص حجم الملف
- التحقق من صحة البيانات

## استخدام النظام

### 1. رفع الصور العادية
```php
// في PropertyController
$validationErrors = $this->imageService->validateImage($file);
if (empty($validationErrors)) {
    $property->addMedia($file)
        ->usingName('Property Image')
        ->usingFileName(time() . '_' . $property->slug . '.webp')
        ->withResponsiveImages()
        ->toMediaCollection('images');
}
```

### 2. رفع صور Base64
```php
$processedImages = $this->imageService->processBase64Image($base64Image, $property->slug);
```

### 3. حذف الصور
```php
$this->imageService->deleteImageVariants($media);
$media->delete();
```

## هيكل الملفات

```
app/
├── Services/
│   └── ImageProcessingService.php    # خدمة معالجة الصور الرئيسية
├── Http/
│   ├── Controllers/Api/
│   │   └── PropertyController.php    # تحديث للاستخدام الخدمة الجديدة
│   ├── Requests/
│   │   ├── StorePropertyRequest.php  # قواعد التحقق المحسنة
│   │   └── UpdatePropertyRequest.php # قواعد التحقق المحسنة
│   └── Middleware/
│       └── ValidateImageUpload.php   # التحقق من عدد الصور
├── Models/
│   └── Property.php                  # تحديث إعدادات Media Library
config/
└── images.php                        # إعدادات معالجة الصور
```

## الاستجابة من API

### بيانات الصورة المُعادة
```json
{
  "id": 1,
  "url": "http://example.com/storage/properties/image.webp",
  "original": "http://example.com/storage/properties/original.webp",
  "full": "http://example.com/storage/properties/full.webp",
  "large": "http://example.com/storage/properties/large.webp",
  "medium": "http://example.com/storage/properties/medium.webp",
  "thumbnail": "http://example.com/storage/properties/thumbnail.webp",
  "small": "http://example.com/storage/properties/small.webp",
  "alt_text": "Property Image",
  "file_size": 245760,
  "mime_type": "image/webp"
}
```

## الأمان والأداء

### الحماية
- فحص نوع MIME للملفات
- التحقق من صحة البيانات
- حدود حجم الملفات
- تنظيف البيانات الوصفية

### الأداء
- ضغط ذكي للصور
- تحويل إلى WebP لتوفير المساحة
- معالجة متوازية
- تخزين محسن

## رسائل الخطأ

### أخطاء التحقق الشائعة
- `File size exceeds maximum allowed size of 5MB`
- `Image dimensions too small. Minimum size is 400x300 pixels`
- `File type not supported. Allowed types: jpeg, jpg, png, webp`
- `Maximum 20 images allowed per property`

## التكامل مع Frontend

### رفع الصور
```javascript
const formData = new FormData();
formData.append('main_image', mainImageFile);
formData.append('images[]', imageFile1);
formData.append('images[]', imageFile2);

// أو باستخدام Base64
const base64Images = [base64String1, base64String2];
formData.append('base64_images', JSON.stringify(base64Images));
```

### عرض الصور
```javascript
// استخدام الحجم المناسب حسب السياق
const thumbnailUrl = property.gallery_urls[0].thumbnail;
const fullSizeUrl = property.gallery_urls[0].full;
```

## الصيانة والمراقبة

### تنظيف الملفات
- حذف تلقائي للملفات المؤقتة
- تنظيف الصور عند حذف العقار
- إزالة جميع أحجام الصورة عند الحذف

### المراقبة
- تتبع أحجام الملفات
- مراقبة استخدام التخزين
- تسجيل أخطاء المعالجة

## التحديثات المستقبلية

### مميزات مخططة
- دعم تنسيقات إضافية (AVIF)
- ضغط أكثر ذكاءً
- معالجة الصور بالذكاء الاصطناعي
- تحسين تلقائي للجودة

---

**ملاحظة**: هذا النظام مُحسن خصيصاً لصور العقارات ويوفر أفضل توازن بين الجودة وحجم الملف.