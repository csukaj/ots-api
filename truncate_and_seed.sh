#!/bin/sh
#
# Truncate database
echo -e '\n\e[1mTruncate database `local`...\e[0m'
php artisan command:truncatedb --database=local -vvv
#
# Seed database
echo -e '\n\e[1mSeed database `local`...\e[0m'
php artisan db:seed --database=local -vvv
php artisan module:seed --database=local -vvv
#
# Update cache
echo -e '\n\e[1mUpdate cache...\e[0m'
php artisan command:updatecache -vvv
#
# Done
echo -e '\n\e[1mDone.\e[0m\n\a'
