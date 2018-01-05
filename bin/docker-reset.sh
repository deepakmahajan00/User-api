#!/bin/bash

set -e
set -x

composer install --prefer-dist -n

rm -rf app/cache/*
rm -rf app/logs/*

app/console doctrine:database:drop --force || true
app/console doctrine:database:create || true
app/console doctrine:schema:drop --force
app/console doctrine:query:sql 'DROP TABLE IF EXISTS "migration_versions";';
php app/console doctrine:query:sql 'CREATE extension IF NOT EXISTS "uuid-ossp";'

app/console doctrine:migrations:migrate -n
app/console doctrine:fixtures:load -n

mkdir web/upload || true
rm -rf app/cache/*
rm -rf app/logs/*

chmod a+w web/upload/ -R
chmod a+w app/cache/ -R
chmod a+w app/logs/ -R
