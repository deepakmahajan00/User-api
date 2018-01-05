#!/bin/bash

set -e
set -x

composer install --no-dev --prefer-dist -n

bin/phpunit -c app
