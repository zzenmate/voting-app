voting-app
==========

## Information

ApiDoc URL: http://your_url/api/doc

## Installation

1. Using composer
  ```
  $ composer install
  ```
  This command requires you to have Composer installed globally, as explained
  in the [Composer documentation](https://getcomposer.org/doc/00-intro.md).

## Configuring

1. Make sure that your local system is properly configured for Symfony2. To do this, execute the following:
  ```
  $ php app/check.php
  ```
  If you got any warnings or recommendations, fix them before moving on.

2. Setting up permissions for directories app/cache/ and app/logs
  ```
  $ HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
  $ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
  $ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
  ```

3. Change DBAL settings, create DB, update it and load fixtures
  
  Change DBAL setting if your need in `app/config/config.yml`, `app/config/config_dev.yml` or `app/config/config_test.yml`. After that execute the following:
  ```
  $ php bin/console doctrine:database:create
  $ php bin/console doctrine:migrations:migrate
  ```
  You can set test environment for command if you add --env=test to it.

## Usage

1. Запустити консольнку команду у корні проекту:
  ```
php bin/console app:tools:parse-voting
  ```

2. Налаштувати хости для проекта або запустити вбудований сервер задопомогою команды:
  ```
php bin/console server:run
  ```
3. Перейти по url: http://127.0.0.1:8000/api/doc і ознайомитись з документацією.