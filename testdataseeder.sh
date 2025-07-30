#!/bin/sh

# Handle parameters
for i in "$@"
do
    case $i in
        -i|--disable-icon-cache)
        ICON_CACHE_DISABLED=--disable-icon-cache
        ;;
    esac
done

databases="local testing"

for db in ${databases}
do
    # Development database
    echo -e "\n$db database"

    echo -e '\nSeeding hotel chains...'
    php artisan command:testhotelchainseeder --database=$db -vvv

    echo -e '\nSeeding accommodations...'
    php artisan command:testaccommodationseeder --database=$db -vvv

    echo -e '\nSeeding ship companies...'
    php artisan command:testshipcompanyseeder --database=$db -vvv

    echo -e '\nSeeding ship groups...'
    php artisan command:testshipgroupseeder --database=$db -vvv

    echo -e '\nSeeding ships...'
    php artisan command:testshipseeder --database=$db -vvv

    echo -e '\nSeeding managers...'
    php artisan command:testmanagerseeder --database=$db -vvv

    echo -e '\nSeeding contents...'
    php artisan command:testcontentseeder --database=$db -vvv

    echo -e '\nSeeding programs...'
    php artisan command:testprogramseeder --database=$db -vvv

    echo -e '\nSeeding program relations...'
    php artisan command:testprogramrelationseeder --database=$db -vvv

    echo -e '\nSeeding cruises...'
    php artisan command:testcruiseseeder --database=$db -vvv

    echo -e '\nSeeding contacts...'
    php artisan command:testcontactseeder --database=$db -vvv

    echo -e '\nSeeding persons...'
    php artisan command:testpeopleseeder --database=$db -vvv

    echo -e '\nSeeding emails...'
    php artisan command:testemailseeder --database=$db -vvv

done

# Set permissions
echo -e '\nSet permissions...'
pwd
chmod -R 0777 storage/public/modules/stylersmedia/images/

# Update cache
echo -e '\nUpdate cache...'
php artisan command:updatecache -vvv ${ICON_CACHE_DISABLED}

# Update cache
echo -e '\nFlush redis...'
php artisan command:flushredis

# Done
echo -e '\nDone.\n\a'