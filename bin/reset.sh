#!/bin/bash

#https://github.com/composer/composer/issues/1453
if [ "$1" = "--force" ]
then
    export SYMFONY_ENV="dev"
else
    export SYMFONY_ENV=${1:-dev}
fi

# Install the vendor
rm -rf app/cache/*
composer install

# Clear and update the database
if [ "${!#}" = "--force" ]
then
    php app/console doctrine:database:drop --force || true
    php app/console doctrine:database:create
else
    php app/console doctrine:schema:drop --force --full-database
fi

php app/console doctrine:migrations:migrate -n
php app/console doctrine:fixtures:load -n

php app/console assets:install

mkdir web/upload || true
chmod -R 777 web/upload
