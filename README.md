# Rest api for employees

## Docker env
docker-compose up -d

## App install and data preload
composer install

php bin/console make:migration

php bin/console doctrine:migrations:migrate

php bin/console doctrine:fixtures:load

## App test

php vendor/bin/behat

__You don't need to add categories separately, just add a new employee with a category name, it will be added (if needed) automatically__ 


![GitHub Logo](/sample.png)




