# Vercel Deployment for OTS API

This Laravel project has been configured for deployment on Vercel. When you select this repository on vercel.com, Vercel will automatically recognize it as a Laravel project and deploy it correctly.

## Configuration Files

- **vercel.json**: Main Vercel configuration with routing, environment variables, and build settings
- **api/index.php**: Entry point for Laravel on Vercel's serverless platform
- **package.json**: Project metadata and build scripts
- **.vercelignore**: Files and directories to exclude from deployment

## Environment Variables

The following environment variables are pre-configured for production deployment:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `CACHE_DRIVER=array`
- `SESSION_DRIVER=array`
- `LOG_CHANNEL=stderr`

## Additional Setup Required

Before deploying to Vercel, you may need to:

1. Set your `APP_KEY` in Vercel environment variables
2. Configure database connection strings
3. Set any API keys or external service credentials

## Static Assets

The configuration handles static assets from:
- `/public/docs/` - Documentation files
- `/public/storage/` - Storage links
- `/favicon.ico` and `/robots.txt`

## Routes

All API routes are handled through the `/api/index.php` entry point, which bootstraps the full Laravel application.

## Deployment

Simply connect this repository to a new Vercel project. Vercel will:
1. Detect it as a Laravel project
2. Install Composer dependencies
3. Configure the serverless environment
4. Deploy the API endpoints

The main API will be available at your Vercel domain root.