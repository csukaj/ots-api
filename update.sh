#!/bin/bash
#
# Handling parameters
for i in "$@"
do
    case $i in
        -i|--disable-icon-cache)
        ICON_CACHE_DISABLED=--disable-icon-cache
        ;;
    esac
done
# Put the application into maintenance mode
echo -e '\nPut the application into maintenance mode...'
php artisan down
#
# Dump composer autoload
#echo -e '\nDump composer autoload...'
#composer dump-autoload ###DONE AT docker restart
#
# Migrate on development
echo -e '\nMigrate on development...'
php artisan module:migrate Stylerstaxonomy --database=local -vvv
php artisan module:migrate Stylersmedia --database=local -vvv
php artisan module:migrate Stylerscontact --database=local -vvv
php artisan migrate --database=local -vvv
php artisan module:migrate Stylersauth --database=local -vvv
#
# Migrate on testing
echo -e '\nMigrate on testing...'
php artisan module:migrate Stylerstaxonomy --database=testing -vvv
php artisan module:migrate Stylersmedia --database=testing -vvv
php artisan module:migrate Stylerscontact --database=testing -vvv
php artisan migrate --database=testing -vvv
php artisan module:migrate Stylersauth --database=testing -vvv
#
# Migrate on staging
echo -e '\nMigrate on staging...'
php artisan module:migrate Stylerstaxonomy --database=staging -vvv
php artisan module:migrate Stylersmedia --database=staging -vvv
php artisan module:migrate Stylerscontact --database=staging -vvv
php artisan migrate --database=staging -vvv
php artisan module:migrate Stylersauth --database=staging -vvv
#
#
# Migrate on production
echo -e '\nMigrate on production...'
php artisan module:migrate Stylerstaxonomy --database=production -vvv
php artisan module:migrate Stylersmedia --database=production -vvv
php artisan module:migrate Stylerscontact --database=production -vvv
php artisan migrate --database=production -vvv
php artisan module:migrate Stylersauth --database=production -vvv
#
# Seed on development
echo -e '\nSeed on development...'
php artisan module:seed --database=local -vvv
php artisan db:seed --database=local -vvv
#
# Seed on testing
echo -e '\nSeed on testing...'
php artisan module:seed --database=testing -vvv
php artisan db:seed --database=testing -vvv --force
#
# Seed on staging
echo -e '\nSeed on staging...'
php artisan module:seed --database=staging -vvv
php artisan db:seed --database=staging -vvv
#
# Seed on production
echo -e '\nSeed on production...'
php artisan module:seed --database=production -vvv
php artisan db:seed --database=production -vvv


# Update cache
echo -e '\nUpdate cache...'
php artisan command:updatecache -vvv ${ICON_CACHE_DISABLED}
chmod 0777 ../frontend/src/assets/cache
chmod 0777 ../frontend/src/assets/cache/*
chmod 0777 ../frontend_new/src/assets/cache
chmod 0777 ../frontend_new/src/assets/cache/*

# Create app runable
chmod -R 0777 storage/
chmod -R 0777 bootstrap/cache

# Update cache
echo -e '\nUpdate translations...'
chmod 0777 ../frontend/src/assets/i18n
chmod 0777 ../frontend/src/assets/i18n/*.json
chmod 0777 ../frontend_new/src/assets/i18n
chmod 0777 ../frontend_new/src/assets/i18n/*.json
php artisan command:updatetranslations -vvv
#
# Bring the application out of maintenance mode
echo -e '\nBring the application out of maintenance mode...'
php artisan up

# Update cache
echo -e '\nFlush redis...'
php artisan command:flushredis

#
# Done
echo -e '\nDone.\n\a'
