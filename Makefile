.PHONY: up down build migrate test lint analyse install

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

install:
	docker compose exec php composer install

migrate:
	docker compose exec php php migrations/runner.php

test:
	docker compose exec php vendor/bin/phpunit

lint:
	docker compose exec php vendor/bin/php-cs-fixer fix --diff

analyse:
	docker compose exec php vendor/bin/phpstan analyse
