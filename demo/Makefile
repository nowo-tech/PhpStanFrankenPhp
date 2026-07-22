# Demo aggregator — PhpStanFrankenPhp

DEMOS := symfony8

.PHONY: help up-symfony8 down-symfony8 verify-symfony8 phpstan-symfony8 clean
.PHONY: up down verify release-check release-verify test-symfony8

help:
	@echo "PhpStanFrankenPhp demos"
	@echo "  make up-symfony8          Start Symfony 8 FrankenPHP demo"
	@echo "  make verify-symfony8      HTTP 200 smoke"
	@echo "  make phpstan-symfony8     Run classic+worker+hardening PHPStan in the demo"
	@echo "  make release-check        For root release-check: test + HTTP 200 + tear down"
	@echo ""
	@echo "Fixture-only demos (no HTTP): see ../demo/README.md (classic/worker/hardening folders)"

up-symfony8:
	@$(MAKE) -C symfony8 up

down-symfony8:
	@$(MAKE) -C symfony8 down

verify-symfony8:
	@$(MAKE) -C symfony8 verify

phpstan-symfony8:
	@$(MAKE) -C symfony8 phpstan-classic || true
	@$(MAKE) -C symfony8 phpstan-worker || true
	@$(MAKE) -C symfony8 phpstan-hardening || true

up:
	@[ -n "$(DEMO)" ] || { echo "Usage: make up DEMO=symfony8"; exit 1; }
	@$(MAKE) up-$(DEMO)

down:
	@[ -n "$(DEMO)" ] || { echo "Usage: make down DEMO=symfony8"; exit 1; }
	@$(MAKE) down-$(DEMO)

verify:
	@[ -n "$(DEMO)" ] || { echo "Usage: make verify DEMO=symfony8"; exit 1; }
	@$(MAKE) verify-$(DEMO)

test-symfony8:
	@$(MAKE) -C symfony8 test

release-verify:
	@for demo in $(DEMOS); do \
		echo "=== release-verify $$demo ==="; \
		$(MAKE) -C $$demo verify; \
		$(MAKE) -C $$demo down; \
	done

release-check: test-symfony8 release-verify
	@echo "All demos passed release-check."

clean:
	@rm -rf $(addsuffix /vendor,$(DEMOS)) $(addsuffix /var,$(DEMOS)) $(addsuffix /.phpunit.cache,$(DEMOS))
	@echo "Cleaned demos."

# REQ-MAKE-008
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
include $(BUNDLE_ROOT)/../.scripts/Makefile.demo-aggregate-update-deps.mk
