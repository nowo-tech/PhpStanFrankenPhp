# PhpStanFrankenPhp — Symfony 8 FrankenPHP demo

.PHONY: help up down build install shell logs test phpstan-classic phpstan-worker phpstan-hardening \
	cache-clear ensure-up verify update-package

SERVICE_PHP ?= php
DC = docker compose

help:
	@echo "PhpStanFrankenPhp — Symfony 8 demo"
	@echo "  make up                 Start FrankenPHP demo"
	@echo "  make phpstan-classic    Analyse anti-patterns (level 1)"
	@echo "  make phpstan-worker     Analyse anti-patterns (level 1+2)"
	@echo "  make phpstan-hardening  Analyse all levels"
	@echo "  make test               PHPUnit"
	@echo "  make verify             HTTP 200 smoke"

up:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	$(DC) up -d --build
	@echo "Waiting for container..."
	@sleep 8
	@$(DC) exec -T -e COMPOSER_MEMORY_LIMIT=-1 $(SERVICE_PHP) composer install --no-interaction --prefer-dist
	@PORT=$$(grep "^PORT=" .env 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
	[ -z "$$PORT" ] && PORT=8090; \
	echo "Demo started at: http://localhost:$$PORT"

down:
	$(DC) down

build:
	$(DC) build --no-cache

ensure-up:
	@if ! $(DC) exec -T $(SERVICE_PHP) true 2>/dev/null; then \
		$(MAKE) up; \
	fi

install: ensure-up
	$(DC) exec -T -e COMPOSER_MEMORY_LIMIT=-1 $(SERVICE_PHP) composer install --no-interaction --prefer-dist

shell: ensure-up
	$(DC) exec $(SERVICE_PHP) sh

logs:
	$(DC) logs -f

test: ensure-up
	$(DC) exec -T -e APP_ENV=test $(SERVICE_PHP) composer test

phpstan-classic: ensure-up
	-$(DC) exec -T $(SERVICE_PHP) composer phpstan-classic
	@echo "(findings expected on src/AntiPattern)"

phpstan-worker: ensure-up
	-$(DC) exec -T $(SERVICE_PHP) composer phpstan-worker
	@echo "(findings expected on src/AntiPattern)"

phpstan-hardening: ensure-up
	-$(DC) exec -T $(SERVICE_PHP) composer phpstan-hardening
	@echo "(findings expected on src/AntiPattern)"

cache-clear: ensure-up
	$(DC) exec -T $(SERVICE_PHP) php bin/console cache:clear

update-package: ensure-up
	$(DC) exec -T $(SERVICE_PHP) composer update nowo-tech/phpstan-frankenphp --no-interaction
	$(DC) exec -T $(SERVICE_PHP) php bin/console cache:clear

verify:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@port=$$(grep '^PORT=' .env 2>/dev/null | cut -d= -f2 | tr -d '\r'); [ -z "$$port" ] && port=8090; \
	$(DC) up -d --build 2>/dev/null; sleep 12; \
	$(DC) exec -T -e COMPOSER_MEMORY_LIMIT=-1 $(SERVICE_PHP) composer install --no-interaction --prefer-dist; \
	code=$$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:$$port 2>/dev/null || echo "000"); \
	if [ "$$code" = "200" ]; then echo "OK (HTTP $$code)"; else echo "Failed (HTTP $$code)"; exit 1; fi

# REQ-MAKE-008
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/../..)
include $(BUNDLE_ROOT)/../.scripts/Makefile.demo-update-deps.mk
