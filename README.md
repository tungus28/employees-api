Rest API for Employees

Run docker stack deploy -c stack.yml postgres (or docker-compose -f stack.yml up)

composer install

symfony console make:migration

symfony console doctrine:migrations:migrate

symfony console doctrine:fixtures:load


php -q -S localhost:9200 -t public &

php vendor/bin/behat

You don't need to add categories separately, just add a new employee with a category name, it will be added (if needed) automatically 



