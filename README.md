# Rest api for employees

## Docker env
docker-compose -f stack.yml up

## App install and data preload
composer install

php bin/console make:migration

php bin/console doctrine:migrations:migrate

php bin/console doctrine:fixtures:load

## App start and test
php -q -S localhost:9200 -t public &

php vendor/bin/behat

__You don't need to add categories separately, just add a new employee with a category name, it will be added (if needed) automatically__ 

## Also you can try the [App on AWS](http://ec2-13-59-194-113.us-east-2.compute.amazonaws.com/index.php/api/doc) 

![GitHub Logo](/sample.png)




