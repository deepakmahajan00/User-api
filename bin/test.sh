#!/bin/bash

#https://github.com/composer/composer/issues/1453
if [ "$1" = "--force" ]
then
    export SYMFONY_ENV="test"
else
    export SYMFONY_ENV=${1:-test}
fi

if [ "${!#}" = "--force" ]
then
    bin/reset.sh $SYMFONY_ENV --force
else
    bin/reset.sh $SYMFONY_ENV
fi

bin/phpunit -c app/
