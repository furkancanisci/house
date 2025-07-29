# API Connection Setup Guide

This guide will help you connect the React frontend to the Laravel API.

## Prerequisites

1. **PHP 8.1+** installed
2. **Composer** installed
3. **Node.js 18+** and **pnpm** installed
4. **PostgreSQL** database (or use the provided Neon database)

## Setup Steps

### 1. Laravel API Setup

```bash
# Navigate to the Laravel API directory
cd "c:\Users\mehme\OneDrive\Desktop\house\Propenty-management-api-main"

# Install PHP dependencies
composer install

# Copy environment file (already exists)
# cp .env.example .env

# Generate application key (if not already set)
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Start the Laravel development server
php artisan serve
```

The Laravel API will be available at: `http://localhost:8000`

### 2. React Frontend Setup

```bash
# Navigate to the React frontend directory
cd "c:\Users\mehme\OneDrive\Desktop\house\afrin-houses-main"

# Install dependencies
pnpm install

# Start the development server
pnpm dev
```

The React app will be available at: `http://localhost:5173`

### 3. Test the Connection

1. Open the React app in your browser
2. Add the ApiTestComponent to your app to test the connection
3. Or check the browser console for API calls

### 4. API Endpoints Available

- **Health Check**: `GET /api/health`
- **Properties**: `GET /api/v1/properties`
- **Featured Properties**: `GET /api/v1/properties/featured`
- **Authentication**: `POST /api/v1/auth/login`, `POST /api/v1/auth/register`
- **Dashboard**: `GET /api/v1/dashboard/overview` (requires auth)

### 5. Environment Configuration

The React app is configured to use:
- **API Base URL**: `http://localhost:8000/api/v1`
- **CORS**: Enabled for localhost:5173, localhost:3000, etc.
- **Authentication**: Bearer token with localStorage

### 6. Troubleshooting

If you encounter CORS issues:
1. Make sure Laravel is running on port 8000
2. Check that CORS is properly configured in `config/cors.php`
3. Verify the React app is running on an allowed origin

If authentication doesn't work:
1. Check that Sanctum is properly configured
2. Verify the token is being sent in requests
3. Make sure the user is authenticated

### 7. Database Configuration

The Laravel app is configured to use a Neon PostgreSQL database. If you want to use a local database:

1. Update the `.env` file in the Laravel project
2. Set up a local PostgreSQL database
3. Update the database connection settings

## Files Modified/Created

### React Frontend:
- `src/services/api.ts` - Updated API base URL
- `src/services/authService.ts` - Authentication service
- `src/services/propertyService.ts` - Updated property service
- `src/services/dashboardService.ts` - Dashboard service
- `src/services/searchService.ts` - Search service
- `src/services/apiTest.ts` - API connection test utility
- `src/components/ApiTestComponent.tsx` - Test component
- `.env` - Environment configuration

### Laravel API:
- `config/cors.php` - Updated CORS configuration

## Next Steps

1. Start both servers (Laravel and React)
2. Test the API connection using the test component
3. Implement authentication in your React components
4. Use the property services to load and display data
5. Customize the UI components to work with the Laravel API data structure

## Support

If you encounter any issues:
1. Check the browser console for errors
2. Check the Laravel logs in `storage/logs/laravel.log`
3. Verify both servers are running
4. Test API endpoints directly using a tool like Postman