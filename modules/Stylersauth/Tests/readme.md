## Installation
1. php artisan migrate --database=testing
2. php artisan module:migrate --database=testing
3. php artisan module:seed StylersAuth --database=testing
4. add enviromental values to \.env:
    API_URL=http://api.homestead.app
    FRONTEND_URL=http://homestead.app
    ADMIN_URL=http://admin.homestead.app