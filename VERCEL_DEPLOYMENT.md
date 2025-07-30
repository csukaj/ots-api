# Vercel Deployment for OTS API

This Laravel project has been configured for deployment on Vercel. When you select this repository on vercel.com, Vercel will automatically recognize it as a Laravel project and deploy it correctly.

## 🚀 Quick Start

1. Go to [vercel.com](https://vercel.com)
2. Click "New Project"
3. Select this repository (`csukaj/ots-api`)
4. Vercel will automatically detect the Laravel configuration and deploy

## 📁 Configuration Files

- **vercel.json**: Main Vercel configuration with routing, environment variables, and build settings
- **api/index.php**: Entry point for Laravel on Vercel's serverless platform
- **api/vercel.json**: Serverless function configuration
- **package.json**: Project metadata and build scripts for Vercel recognition
- **.vercelignore**: Files and directories to exclude from deployment

## 🔧 Features Configured

✅ **Automatic Laravel Recognition**: Package.json + composer.json structure  
✅ **Serverless PHP Runtime**: vercel-php@0.7.2 with 30s timeout  
✅ **Smart Routing**: API routes through Laravel, static assets cached  
✅ **CORS Support**: Proper handling of cross-origin requests  
✅ **Production Environment**: Optimized for serverless deployment  
✅ **Static Asset Handling**: Documentation, storage, favicon, robots.txt  
✅ **Build Optimization**: Composer autoloader optimization  

## 🌍 Environment Variables

The following environment variables are pre-configured for production deployment:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `CACHE_DRIVER=array` (serverless-friendly)
- `SESSION_DRIVER=array` (serverless-friendly)
- `LOG_CHANNEL=stderr` (Vercel logging)
- `VIEW_COMPILED_PATH=/tmp/storage/framework/views`
- `CACHE_PATH=/tmp/storage/framework/cache`

## ⚙️ Additional Setup Required

Before deploying to Vercel, you may need to set these environment variables in your Vercel project:

1. **APP_KEY**: Generate with `php artisan key:generate --show`
2. **Database credentials**: If using external database
3. **API keys**: For external services used by the application

## 📍 Routes Handled

- `/` → Laravel application root
- `/api/*` → All API endpoints  
- `/docs/*` → Documentation (cached for 24h)
- `/storage/*` → File storage access
- `/favicon.ico` → Cached for 1 year
- `/robots.txt` → SEO file

## 🏗️ Build Process

When deployed, Vercel will:

1. **Detect** the project as Laravel (via package.json structure)
2. **Install** Composer dependencies with optimization
3. **Build** serverless functions from api/index.php
4. **Configure** routing and environment variables
5. **Deploy** the complete API

## 📝 API Endpoints

After deployment, your API will be available at your Vercel domain. Key endpoints include:

- `GET /` → Laravel version info
- `POST /accommodation-search` → Accommodation search
- `POST /charter-search` → Charter search  
- `POST /cruise-search` → Cruise search
- `GET /content/posts` → Content posts
- `POST /order` → Order processing
- `POST /contact` → Contact form
- Plus admin and extranet endpoints with authentication

## 🔍 Testing the Configuration

All configuration files have been validated:
- ✅ JSON syntax is correct
- ✅ PHP syntax is valid  
- ✅ Required files present
- ✅ Laravel structure intact

The project is ready for immediate deployment on Vercel.