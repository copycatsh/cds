.PHONY: up down build migrate test lint analyse install install-frontend dev-frontend build-frontend test-frontend

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

install-frontend:
	docker compose exec frontend npm install

dev-frontend:
	docker compose exec frontend npm run dev

build-frontend:
	docker compose exec frontend npm run build

test-frontend:
	docker compose exec frontend npm run test
