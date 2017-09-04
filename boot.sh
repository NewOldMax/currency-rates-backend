#!/usr/bin/env bash

# Disable xdebug in production environment
xdebug_config=/usr/local/etc/php/conf.d/xdebug.ini
if [ -f $xdebug_config ] && [ "$SYMFONY_ENV" == "prod" ]
    then
        rm $xdebug_config
fi

# Wait for postgres to start

host=${SYMFONY__DATABASE__HOST}
port=5432

echo -n "waiting for TCP connection to database:..."

while ! nc -z -w 1 $host $port 2>/dev/null
do
  echo -n "."
  sleep 1
done

echo 'ok'

# Prepare application
app/console cache:clear --env=dev
app/console cache:clear --env=prod
app/console doctrine:migrations:migrate -n

php-fpm --allow-to-run-as-root
