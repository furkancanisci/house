# Environment Configuration Guide

## Problem Fixed: Admin Login 419 Error

The 419 error was caused by session domain mismatch. The application was configured for production domain (besttrend-sy.com) but you were accessing it from localhost (127.0.0.1:8000).

## Current Configuration (Local Development)

The `.env` file has been updated for local development:

```env
# Local Development URLs
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173

# CORS Configuration for local development
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,127.0.0.1:8000,localhost:5173
SESSION_DOMAIN=null
```

## Switching Between Environments

### For Production Deployment:

```env
# Production URLs
APP_URL=https://api.besttrend-sy.com
FRONTEND_URL=https://besttrend-sy.com

# CORS Configuration for production
SANCTUM_STATEFUL_DOMAINS=besttrend-sy.com
SESSION_DOMAIN=besttrend-sy.com
```

### For Local Development:

```env
# Local Development URLs
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173

# CORS Configuration for local development
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,127.0.0.1:8000,localhost:5173
SESSION_DOMAIN=null
```

## After Changing .env

Always run these commands after modifying .env:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Testing Admin Login

1. Open your browser
2. Go to: http://127.0.0.1:8000/admin/login
3. Enter your credentials
4. The 419 error should be resolved!

## Note

- `SESSION_DOMAIN=null` allows session cookies to work on any domain (good for development)
- For production, set `SESSION_DOMAIN` to your actual domain for security
- CSRF protection is still active, but now works correctly with local development

## Troubleshooting

If you still get 419 error:
1. Clear browser cookies for 127.0.0.1
2. Try in incognito/private mode
3. Make sure you ran the cache clear commands
4. Restart php artisan serve if using built-in server
