# Currency Rates test project (backend)

### Installation

 - make sure that you have installed Docker and Docker Compose.
 - clone this repository
 - navigate to project folder
 - rename `.env.dist` file to `.env`
 - you *must* change following variables in `.env` file:
    - SYMFONY__APP__SECRET
    - SYMFONY__JWT__SECRET
    - SYMFONY__GOOGLE__CLIENT__ID
    - SYMFONY__GOOGLE__CLIENT__SECRET
    - SYMFONY__HOSTNAME
    - SYMFONY__PROTOCOL

 (visit [console.developers.google.com](https://console.developers.google.com) and create a project, to get google client id and google client secret)
 - run `docker-compose -p currency_rates build` to build containers
 - run `docker-compose -p currency_rates up -d` to start containers
 - run `sh scripts/install.sh` to perform vendors installation
 - run `docker exec currencyrates_php_1 php app/console currency-rates:rate:populate` to populate currency rates for last 25 weeks (if you see any errors in console, you can wait few minutes and try again)
 - (optional) run `sh scripts/populate-data.sh` to recreate database and populate currency rates
 - (optional) add `docker exec currencyrates_php_1 php app/console currency-rates:rate:populate` command to your cron or any schedule serivce to make currency rates up to date

After that your backend will be available at localhost:8098, and you can start with [frontend](https://github.com/NewOldMax/currency-rates-frontend)

### Dev utils
Run `alias run=devops/aliases` command inside project folder and you will be able to use useful shortcats:
 - `run psql` - connects to porject database
 - `run project` - launches project
 - `run php` - opens php console in project container
 - `run behat` - runs tests
 - `run console` - runs `app/console` command
 - `run composer` - runs composer container