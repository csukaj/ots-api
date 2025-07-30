#!/bin/sh
#
# Handling parameters
for i in "$@"
do
    case ${i} in
        -i|--disable-icon-cache)
        ICON_CACHE_DISABLED=--disable-icon-cache
        ;;
        --env=*|--environment=*)
        APP_ENV="${i#*=}"
        ;;
    esac
done

case ${APP_ENV} in
        local) databases="local testing";;
        demo) databases="local testing";;
        staging) databases="staging";;
        production) databases="production";;
        *) databases="local testing staging production";;
esac
#
# Put the application into maintenance mode
echo -e '\nPut the application into maintenance mode...'
php artisan down
#
# composer dump autoload
echo -e '\nDump composer autoload'
composer dump-autoload
#

for db in ${databases}
do
    # Migrate on x database
    printf "\nMigrate on '$db' database..."
    php artisan module:migrate Stylerstaxonomy --database=${db} -vvv --force
    php artisan module:migrate Stylersmedia --database=${db} -vvv --force
    php artisan module:migrate Stylerscontact --database=${db} -vvv --force
    php artisan migrate --database=${db} -vvv --force
    php artisan module:migrate Stylersauth --database=${db} -vvv --force

    # Seed on development
    printf "\nSeed on '$db' database..."
    php artisan module:seed --database=${db} -vvv
    php artisan db:seed --database=${db} -vvv --force
done

# Update cache
printf '\nUpdate cache...'
php artisan command:updatecache -vvv ${ICON_CACHE_DISABLED}
#
# Create app runable
chmod -R 0777 storage/
chmod -R 0777 bootstrap/cache
#
# Empty media storage and create symbolic links
printf '\nEmpty media storage and create symbolic links...'
pwd
rm -r -f storage/public
ln -s ../storage/public public/storage
#
# Bring the application out of maintenance mode
printf '\nBring the application out of maintenance mode...'
php artisan up
#
# Done
printf '\nDone.\n\a'
