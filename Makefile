DC=docker compose

up:
	$(DC) up -d --build

down:
	$(DC) down

php:
	$(DC) exec php sh

composer-install:
	$(DC) exec php composer install

migrate:
	$(DC) exec php php bin/console doctrine:migrations:migrate --no-interaction

fixtures:
	$(DC) exec php php bin/console doctrine:fixtures:load --no-interaction

notify-ending:
	$(DC) exec php php bin/console payment:ending:notification

payment-report:
	$(DC) exec php php bin/console payment:report

test:
	$(DC) exec php php bin/phpunit

lint:
	$(DC) exec php php bin/console lint:container
