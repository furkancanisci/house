# Image Storage System Report
## Property Management System

---

## Executive Summary

The property management system uses **Laravel Media Library (Spatie)** for handling image uploads and storage. Images are stored locally on the server filesystem and tracked in a PostgreSQL database. The system supports multiple image formats and maintains different collections for property images.

---

## 1. Storage Architecture

### 1.1 Technology Stack
- **Backend Framework**: Laravel 10
- **Media Library**: Spatie Laravel Media Library v10
- **Database**: PostgreSQL (Neon cloud database)
- **Storage Driver**: Local filesystem (public disk)
- **Image Processing**: Intervention Image v2.7

### 1.2 Storage Location

Images are physically stored in the following directory structure:

```
C:\Users\User\Desktop\house\Propenty-management-api-main\
└── storage/
    └── app/
        └── public/
            └── {model_id}/
                └── {image_files}
```

**Note**: The exact path structure is determined by the DefaultPathGenerator class from Spatie Media Library.

### 1.3 Public Access

Images are made publicly accessible through a symbolic link:
- **Source**: `storage/app/public/`
- **Link**: `public/storage/`
- **Access URL**: `http://localhost:8000/storage/{path_to_image}`

---

## 2. Database Structure

### 2.1 Media Table Schema

The `media` table stores metadata for all uploaded images:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| model_type | varchar | Model class (e.g., 'App\Models\Property') |
| model_id | bigint | Related model ID |
| uuid | uuid | Unique identifier for the media |
| collection_name | varchar | Collection type ('main_image' or 'images') |
| name | varchar | Display name of the file |
| file_name | varchar | Actual filename on disk |
| mime_type | varchar | MIME type (e.g., 'image/jpeg') |
| disk | varchar | Storage disk name ('public') |
| conversions_disk | varchar | Disk for image conversions |
| size | bigint | File size in bytes |
| manipulations | json | Image manipulations data |
| custom_properties | json | Custom metadata |
| generated_conversions | json | Generated image variants |
| responsive_images | json | Responsive image data |
| order_column | int | Ordering within collection |
| created_at | timestamp | Upload timestamp |
| updated_at | timestamp | Last update timestamp |

### 2.2 Collections

The system uses two main collections for property images:
1. **main_image**: The primary property image
2. **images**: Gallery images (multiple allowed)

---

## 3. Upload Flow

### 3.1 Frontend to Backend Flow

1. **Frontend (React/TypeScript)**:
   - User selects images through file input
   - Images are added to FormData object
   - FormData sent to API via multipart/form-data POST request

2. **API Endpoint**:
   - Route: `POST /api/properties`
   - Controller: `PropertyController@store`

3. **Backend Processing**:
   ```php
   // Main image upload
   $property->addMedia($mainImageFile)
       ->usingName($mainImageFile->getClientOriginalName())
       ->usingFileName(time() . '_main_' . $mainImageFile->getClientOriginalName())
       ->toMediaCollection('main_image');
   
   // Gallery images upload
   foreach ($images as $index => $image) {
       $property->addMedia($image)
           ->usingName($image->getClientOriginalName())
           ->usingFileName(time() . '_' . $index . '_' . $image->getClientOriginalName())
           ->toMediaCollection('images');
   }
   ```

### 3.2 File Naming Convention

- **Main Image**: `{timestamp}_main_{original_filename}`
- **Gallery Images**: `{timestamp}_{index}_{original_filename}`

Example: `1735398623_main_house.jpg`

---

## 4. Image Retrieval

### 4.1 API Response Structure

When fetching properties, images are returned in this structure:

```json
{
  "images": {
    "main": "http://localhost:8000/storage/123/image.jpg",
    "gallery": [
      "http://localhost:8000/storage/123/gallery1.jpg",
      "http://localhost:8000/storage/123/gallery2.jpg"
    ],
    "count": 2
  },
  "mainImage": "http://localhost:8000/storage/123/image.jpg"
}
```

### 4.2 Frontend Image Processing

The frontend uses `processPropertyImages()` function to:
1. Extract image URLs from API response
2. Fix relative URLs to absolute URLs
3. Handle fallback images if no images exist
4. Prepare images for display in components

---

## 5. Configuration

### 5.1 Media Library Configuration
File: `config/media-library.php`

Key settings:
- **Disk**: `public` (stored in storage/app/public)
- **Max File Size**: 10MB (10 * 1024 * 1024 bytes)
- **Path Generator**: DefaultPathGenerator
- **URL Generator**: DefaultUrlGenerator

### 5.2 Filesystem Configuration
File: `config/filesystems.php`

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
]
```

### 5.3 Environment Variables
File: `.env`

```
APP_URL=http://localhost:8000
```

---

## 6. Supported Formats

### 6.1 Accepted MIME Types
- image/jpeg
- image/jpg
- image/png
- image/webp

### 6.2 File Extensions
- .jpg / .jpeg
- .png
- .webp

**Note**: SVG files are not accepted by default (as seen in seeder errors)

---

## 7. Storage Workflow Diagram

```
User Selects Image
        ↓
Frontend FormData
        ↓
API POST /properties
        ↓
PropertyController@store
        ↓
Spatie Media Library
        ↓
    ┌───┴───┐
    ↓       ↓
Database  Filesystem
(metadata) (actual file)
    ↓       ↓
    └───┬───┘
        ↓
API Response with URLs
        ↓
Frontend Display
```

---

## 8. Current Status

### 8.1 What's Working
✅ Image upload mechanism via FormData
✅ Database structure for media storage
✅ Media Library integration with Property model
✅ Storage symlink for public access
✅ API returns proper image URLs
✅ Frontend can process and display images

### 8.2 Storage Statistics
- **Current Images**: 0 (fresh database)
- **Storage Path**: Configured and ready
- **Database**: Media table with correct schema
- **Collections**: main_image and images configured

---

## 9. Security Considerations

1. **File Size Limit**: 10MB per file
2. **MIME Type Validation**: Only image files accepted
3. **Filename Sanitization**: Timestamps prevent conflicts
4. **Public Access**: Only through storage symlink
5. **Database Tracking**: All uploads tracked in database

---

## 10. Maintenance Notes

### Commands for Management

```bash
# Create storage symlink
php artisan storage:link

# Clear old media (custom command needed)
# php artisan media:clean

# Check media disk usage
# find storage/app/public -type f -name "*.jpg" -o -name "*.png" | wc -l
```

### Database Queries

```sql
-- Check total media count
SELECT COUNT(*) FROM media;

-- Check media by property
SELECT * FROM media WHERE model_type = 'App\Models\Property' AND model_id = ?;

-- Check storage usage
SELECT SUM(size) as total_bytes FROM media;
```

---

## Conclusion

The image storage system is fully configured and operational. Images are stored locally on the server, tracked in PostgreSQL, and served through Laravel's public storage system. The integration with Spatie Media Library provides a robust foundation for handling property images with proper validation, storage, and retrieval mechanisms.

**Key Takeaway**: When you upload an image, it's saved to `storage/app/public/{model_id}/` on the server and tracked in the `media` database table, then served via URLs like `http://localhost:8000/storage/{path}`.