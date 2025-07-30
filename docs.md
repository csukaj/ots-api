# OTS004 Documentations description

## Test data
Located at: `<project root>/docs`
Used to: define test data for development & demo environments
How to use: run `./testdataseeder.sh` on a fresh development/demo system, right after installation

## API documentation
Located at: `<project root>/public/docs`
Used to: have an overview of all API endpoints and methods
How to use: open in a browser at <http://project_domain/docs/index.html>
How to regenerate:
    1. Do a fresh install & test data seed on a development system
    2. Run `php artisan api:generate --routePrefix="*" --actAsUserId=1 --output=public/docs/api`
    3. Commit and push changes to repository

## PHP class documentation
Located at: `<project root>/public/docs/phpdoc`
Used to: have an overview of app's PHP classes
How to use: open in a browser at <http://project_domain/docs/phpdoc/index.html>
How to regenerate:
    1. Run `<phpdocumentor binary> -d ./app/ -t ./public/docs/php/ -i "*/vendor/*,*/baum/*" --validate`
    2. Commit and push changes to repository (without cache files)
