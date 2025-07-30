# Stylers Auth Module

## Notes

==TODO== [Entrust issue](https://github.com/Zizaco/entrust/issues/468)
Switch back to `CACHE_DRIVER=file` in `.env` when Entrust fix is published.

## Dependencies
- https://github.com/tymondesigns/jwt-auth
 - composer.json - `"require": {
        "tymon/jwt-auth": "0.5.*"
    }`
 - providers array in app.php - `'Tymon\JWTAuth\Providers\JWTAuthServiceProvider'`
 - aliases array in app.php
     - `'JWTAuth' => 'Tymon\JWTAuth\Facades\JWTAuth'`
     - `'JWTFactory' => 'Tymon\JWTAuth\Facades\JWTFactory'`
   - Bash
     - `php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"`
     - `php artisan jwt:generate`
- https://github.com/Zizaco/entrust

## Installation
1. php artisan migrate
2. php artisan module:migrate
3. php artisan module:seed StylersAuth
4. add enviromental values to \.env:
    API_URL=http://api.homestead.app
    FRONTEND_URL=http://homestead.app
    ADMIN_URL=http://admin.homestead.app
    TEST_API_URL=http://test-api.homestead.app