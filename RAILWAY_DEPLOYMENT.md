# Railway Deployment Configuration

This project includes configuration for deployment on Railway.com.

## Files

- `nixpacks.toml` - Configures the build process for Railway's Nixpacks buildpack
- `composer.json` - Updated to remove Laravel commands that don't exist in this version

## Deployment

Railway.com will automatically detect the `nixpacks.toml` file and use it to configure the build process. The configuration:

1. Installs PHP 8.3 with required extensions (mbstring, curl, soap, pdo, pdo_mysql)
2. Copies `.env.example` to `.env` before running composer install
3. Runs composer install without dev dependencies
4. Installs npm packages
5. Starts the application using PHP's built-in server

## Troubleshooting

If you encounter build errors on Railway:

1. Check that all required environment variables are set in Railway's dashboard
2. Ensure your database connection variables are configured correctly
3. Verify that the APP_KEY is set (you can generate one with `php artisan key:generate`)

The build process has been tested to work with the current Laravel 12 setup.