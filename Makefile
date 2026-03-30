#!/usr/bin/env make -f
SHELL := /bin/zsh
.PHONY: build up down logs test test-all clean

install:
	docker compose build

run:
	docker compose up -d
	docker compose exec -T web sh scripts/wait-for-it.sh database:5432 -- php bin/console doctrine:migrations:migrate --no-interaction

down:
	docker compose down -v

logs:
	docker compose logs -f --tail=10

test:
	docker compose exec -T web php bin/console doctrine:database:create --if-not-exists --env=test
	docker compose exec -T web php bin/console doctrine:migrations:migrate --no-interaction --env=test
	docker compose exec -T web php bin/phpunit tests/ --testdox --colors=always

clean:
	docker compose down -v

cli:
	docker compose exec -T web php bin/console debug:router