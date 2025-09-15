# Environment Configuration Guide

This project uses environment variables to configure different settings for different environments (local development, production, etc.).

## 🔧 Environment Variables

All environment variables for the frontend must be prefixed with `VITE_` to be accessible in the browser.

### Required Variables

- `VITE_API_BASE_URL` - The base URL for the backend API

### Optional Variables

- `VITE_APP_NAME` - Application name (defaults to "Afrin Houses")
- `VITE_APP_VERSION` - Application version (defaults to "1.0.0")
- `VITE_DEV_TOOLS` - Enable development tools (true/false)

## 📁 Environment Files

The project includes several environment files:

- `.env` - Current active environment configuration
- `.env.example` - Template with example values
- `.env.local` - Local development configuration
- `.env.production` - Production configuration

## 🚀 Quick Start

### For Local Development

```bash
# Switch to local environment
npm run env:local

# Start development server with local environment
npm run dev:local
```

### For Production

```bash
# Switch to production environment
npm run env:production

# Build for production
npm run build:production
```

## 📜 Available Scripts

### Development Scripts

- `npm run dev` - Start development server with current .env
- `npm run dev:local` - Start development server with local backend
- `npm run dev:production` - Start development server with production backend

### Build Scripts

- `npm run build` - Build with current .env
- `npm run build:local` - Build with local environment
- `npm run build:production` - Build with production environment

### Environment Management Scripts

- `npm run env:local` - Switch to local environment (.env.local → .env)
- `npm run env:production` - Switch to production environment (.env.production → .env)
- `npm run env:status` - Show current API URL configuration

## 🔄 Switching Environments

### Method 1: Using npm scripts (Recommended)

```bash
# Switch to local development
npm run env:local
npm run dev

# Switch to production
npm run env:production
npm run dev
```

### Method 2: Manual file copying

```bash
# For local development
cp .env.local .env

# For production
cp .env.production .env
```

### Method 3: Direct environment-specific commands

```bash
# Development with local backend
npm run dev:local

# Development with production backend
npm run dev:production
```

## 🏠 Local Development Setup

1. **Start your Laravel backend:**
   ```bash
   cd /path/to/your/laravel/project
   php artisan serve
   ```

2. **Switch to local environment:**
   ```bash
   npm run env:local
   ```

3. **Start the frontend:**
   ```bash
   npm run dev
   ```

The frontend will now connect to your local Laravel backend at `http://127.0.0.1:8000/api/v1`.

## 🌐 Production Setup

1. **Switch to production environment:**
   ```bash
   npm run env:production
   ```

2. **Build for production:**
   ```bash
   npm run build
   ```

The frontend will connect to the production API at `https://api.besttrend-sy.com/api/v1`.

## 🔍 Troubleshooting

### Check Current Configuration

```bash
npm run env:status
```

### Environment Variable Not Found Error

If you see an error like "VITE_API_BASE_URL is not defined", make sure:

1. You have a `.env` file in the project root
2. The `.env` file contains the required variables
3. All frontend environment variables are prefixed with `VITE_`
4. You've restarted the development server after changing environment variables

### CORS Issues

If you're getting CORS errors:

1. Make sure your Laravel backend CORS configuration includes your frontend URL
2. Check that your backend is running on the expected port
3. Verify the API URL in your `.env` file matches your backend server

## 📋 Environment Variable Reference

### Local Development (.env.local)
```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
VITE_APP_NAME=Best trend (Local)
VITE_DEV_TOOLS=true
```

### Production (.env.production)
```env
VITE_API_BASE_URL=https://api.besttrend-sy.com/api/v1
VITE_APP_NAME=Best trend
VITE_DEV_TOOLS=false
```

## 🔐 Security Notes

- Never commit sensitive data to `.env` files
- Use `.env.example` for documenting required variables
- Keep production credentials secure and separate
- The `.env` file is gitignored for security