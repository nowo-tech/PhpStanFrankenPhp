# PhpStanFrankenPhp — root Makefile (Docker workflow)

COMPOSE ?= docker compose
SERVICE_PHP ?= php

.PHONY: help ensure-up up down build shell install assets test test-coverage \
	coverage-check test-coverage-100 \
	demo-symfony8 demo-symfony8-verify demo-symfony8-phpstan \
	check-no-cursor-coauthor strip-cursor-coauthor-from-history \
	cs-check cs-fix rector rector-dry phpstan qa release-check release-check-demos \
	composer-sync clean update validate setup-hooks \
	demo-classic demo-worker demo-hardening demo-all \
	demo-classic-good demo-worker-good demo-hardening-good demo-worker-strict

help:
	@echo "PhpStanFrankenPhp — make targets"
	@echo ""
	@echo "Container: up, down, build, shell"
	@echo "Dependencies: install, update, validate"
	@echo "Tests: test, test-coverage, coverage-check, test-coverage-100"
	@echo "Quality: cs-check, cs-fix, rector, rector-dry, phpstan, qa"
	@echo "Demos: demo-classic, demo-worker, demo-hardening, demo-all"
	@echo "Demo (clean): demo-classic-good, demo-worker-good, demo-hardening-good"
	@echo "Release: release-check, release-check-demos, composer-sync"
	@echo "Other: setup-hooks, ensure-up, clean"

ensure-up:
	@echo "Ensuring Docker environment is up..."
	@$(COMPOSE) up -d --build
	@sleep 8
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction

up:
	@$(COMPOSE) up -d --build

down:
	@$(COMPOSE) down

build:
	@$(COMPOSE) build --no-cache

shell: ensure-up
	@$(COMPOSE) exec $(SERVICE_PHP) sh

install: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction

assets:
	@echo "No frontend assets in this package."

test: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer test

test-coverage: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer test-coverage

coverage-check: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer coverage-check

test-coverage-100: coverage-check

cs-check: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer cs-check

cs-fix: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer cs-fix

rector: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer rector

rector-dry: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer rector-dry

phpstan: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer phpstan

qa: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer qa

demo-classic: ensure-up
	@echo "=== Classic demos (expect findings on bad/) ==="
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-classic || true

demo-worker: ensure-up
	@echo "=== Worker demos (expect findings on bad/) ==="
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-worker || true

demo-hardening: ensure-up
	@echo "=== Hardening demos (expect findings on bad/) ==="
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-hardening || true

demo-all: demo-classic demo-worker demo-hardening

demo-classic-good: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-classic-good

demo-worker-good: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-worker-good

demo-worker-strict: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-worker-strict || true

demo-hardening-good: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-hardening-good

composer-sync: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer validate --strict
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer install --no-interaction

release-check: check-no-cursor-coauthor ensure-up composer-sync cs-fix cs-check rector-dry phpstan coverage-check release-check-demos

release-check-demos:
	@echo "=== Fixture demos: bad samples must produce findings ==="
	@if $(COMPOSE) exec -T $(SERVICE_PHP) composer demo-classic; then echo "Expected findings on demo/classic/bad" >&2; exit 1; fi
	@if $(COMPOSE) exec -T $(SERVICE_PHP) composer demo-worker; then echo "Expected findings on demo/worker/bad" >&2; exit 1; fi
	@if $(COMPOSE) exec -T $(SERVICE_PHP) composer demo-hardening; then echo "Expected findings on demo/hardening/bad" >&2; exit 1; fi
	@echo "=== Fixture demos: good samples must be clean ==="
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-classic-good
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-worker-good
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer demo-hardening-good
	@echo "=== Symfony 8 FrankenPHP smoke ==="
	@$(MAKE) -C demo release-check

clean:
	rm -rf vendor
	rm -rf .phpunit.cache
	rm -rf coverage
	rm -f coverage.xml
	rm -f coverage-php.txt
	rm -f coverage-output.txt
	rm -f .php-cs-fixer.cache

update: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer update

validate: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer validate --strict

check-no-cursor-coauthor:
	@chmod +x .scripts/check-no-cursor-coauthor.sh
	@./.scripts/check-no-cursor-coauthor.sh HEAD

setup-hooks:
	@chmod +x .githooks/pre-commit 2>/dev/null || true
	@chmod +x .githooks/commit-msg 2>/dev/null || true
	@git config core.hooksPath .githooks
	@echo "✅ Git hooks installed (.githooks — includes commit-msg for REQ-GIT-001)."

# REQ-MAKE-008: update-deps
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))
include $(BUNDLE_ROOT)/../.scripts/Makefile.update-deps.mk

strip-cursor-coauthor-from-history:
	@chmod +x .scripts/strip-cursor-coauthor-from-history.sh
	@./.scripts/strip-cursor-coauthor-from-history.sh main


demo-symfony8:
	@$(MAKE) -C demo/symfony8 up

demo-symfony8-verify:
	@$(MAKE) -C demo/symfony8 verify

demo-symfony8-phpstan:
	@$(MAKE) -C demo phpstan-symfony8
