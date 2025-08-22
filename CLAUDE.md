# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a full-stack property management platform consisting of:
- **Laravel API** (`Propenty-management-api-main/`) - Backend REST API with property management, user authentication, and media handling
- **React Frontend** (`afrin-houses-main/`) - Modern React TypeScript application with property browsing, search, and user dashboard

## Development Commands

### Laravel API (Propenty-management-api-main/)
```bash
# Install dependencies
composer install

# Database operations
php artisan migrate
php artisan db:seed

# Development server
php artisan serve          # Starts on http://localhost:8000

# Testing
php artisan test           # Run PHPUnit tests
vendor/bin/phpunit         # Alternative test command

# Artisan commands
php artisan make:controller Api/ExampleController
php artisan make:model Example -mfsr
php artisan route:list     # View all API routes
```

### React Frontend (afrin-houses-main/)
```bash
# Install dependencies and start development server
pnpm dev                   # Runs install then vite dev server

# Build and linting
pnpm build                 # TypeScript compile + Vite build
pnpm lint                  # ESLint check
pnpm preview              # Preview production build

# Individual commands
vite                      # Development server only
vite build               # Production build only
```

## Architecture Overview

### Backend (Laravel)
- **API Endpoints**: RESTful API with `/api/v1` prefix
- **Authentication**: Laravel Sanctum with Bearer tokens
- **Database**: PostgreSQL (Neon cloud database only)
- **Key Packages**: Spatie Media Library (file handling), Spatie Permissions, Spatie Query Builder
- **Image Processing**: Custom ImageProcessingService for property photos
- **Testing**: PHPUnit with Feature and Unit test suites

### Frontend (React)
- **Framework**: React 18 with TypeScript and Vite
- **UI Components**: Radix UI primitives with Tailwind CSS styling  
- **State Management**: React Context API (AppContext)
- **Routing**: React Router v6
- **API Communication**: Axios with interceptors for auth token management
- **Internationalization**: i18next with English/Arabic support
- **Image Handling**: Custom image utilities with fallback support

## Key API Endpoints

- `GET /api/health` - Health check
- `POST /api/v1/auth/register|login` - Authentication
- `GET /api/v1/properties` - Property listings with filtering
- `GET /api/v1/properties/featured` - Featured properties
- `GET /api/v1/dashboard/overview` - User dashboard data
- `GET /api/v1/cities` - Location data for filters
- `POST /api/v1/properties` - Create property (with image upload)

## Database Schema Highlights

- **Users**: Authentication and profile management
- **Properties**: Core property data with type relationships
- **Cities**: Location hierarchy for filtering
- **Property Views/Favorites**: User interaction tracking
- **Media**: File attachments via Spatie Media Library

## Authentication Flow

1. Frontend stores JWT tokens in localStorage
2. API uses Laravel Sanctum for stateless authentication
3. Axios interceptors handle token refresh and 401 responses
4. Protected routes redirect to login on authentication failure

## Development Notes

- API runs on port 8000, frontend on port 5173 (Vite default)
- CORS configured for local development between the two servers
- Image uploads handled with validation middleware
- Database uses PostgreSQL with Neon cloud provider
- Both applications have proper error handling and logging

## File Upload Handling

- Properties support multiple images via Spatie Media Library
- Custom `ValidateImageUpload` middleware on API routes
- Frontend has `FixedImage` component for display consistency
- Image processing service handles resize/optimization

## Testing Approach

- **Backend**: PHPUnit tests in `tests/Feature` and `tests/Unit`
- **Frontend**: No testing framework currently configured
- API includes test routes for debugging connection issues