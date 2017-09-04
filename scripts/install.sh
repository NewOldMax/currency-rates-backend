#!/usr/bin/env bash

set -ex

# Composer
docker run --rm -v $(pwd):/app composer/composer --ignore-platform-reqs install && \
docker run --rm -v $(pwd):/app composer/composer dump-autoload --optimize && \
# Create Database
docker exec currencyrates_php_1 php app/console doctrine:database:create --if-not-exists && \
# Perform migrations
docker exec currencyrates_php_1 php app/console doctrine:migrations:migrate -n && \
# Clear cache
docker exec currencyrates_php_1 php app/console cache:clear --env=prod
docker exec currencyrates_php_1 php app/console cache:clear --env=dev

result=$?
if [ $result -eq 0 ]
then
  echo "Install succeed!"
else
  echo "Install failed!" >&2
fi

exit $result