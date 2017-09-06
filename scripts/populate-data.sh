#!/usr/bin/env bash

set -ex

# Drop schema
docker exec currencyrates_php_1 php app/console doctrine:database:drop --force --if-exists  && \
docker exec currencyrates_php_1 php app/console doctrine:database:drop --force --if-exists --env=test  && \
docker exec currencyrates_php_1 php app/console doctrine:database:create --if-not-exists && \
docker exec currencyrates_php_1 php app/console doctrine:database:create --if-not-exists --env=test && \
# Perform migrations
docker exec currencyrates_php_1 php app/console doctrine:migrations:migrate -n && \
docker exec currencyrates_php_1 php app/console doctrine:migrations:migrate -n --env=test && \
# Populate some dummy data for dev usage
docker exec currencyrates_php_1 php currency-rates:rate:populate


result=$?
if [ $result -eq 0 ]
then
  echo "Data population succeed!"
else
  echo "Data population failed!" >&2
fi

exit $result