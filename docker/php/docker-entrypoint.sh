#!/bin/bash

set -euo pipefail

mkdir -p var/cache var/log

set -e

cd /var/www/logs-service

composer install --prefer-dist --no-progress --no-interaction

chmod +x bin/console; sync;

php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --no-interaction

exec docker-php-entrypoint "$@"
