### Variables

# Applications
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= latest
PHP ?= /usr/bin/env php

### Helpers
default: clean dev
.PHONY: default

verify: clean dev test qa-offline
.PHONY: verify

### Dependencies
update:
ifeq ($(DEPENDENCIES), lowest)
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} update --prefer-lowest --prefer-dist --no-interaction;
else
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction;
endif

.PHONY: update

install: clean
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction --no-dev --optimize-autoloader --classmap-authoritative;
	APP_ENV=prod ${PHP} bin/console cache:warmup

dev: clean
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction --optimize-autoloader --classmap-authoritative;
	APP_ENV=prod ${PHP} bin/console cache:warmup

.PHONY: install

### QA
qa: lint phpstan phpcs audit
qa-offline: lint phpstan phpcs

lint:
	find ./src -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./domain -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./infrastructures -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./tests -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./config -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

phpstan:
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse domain src infrastructures config --level max

phpcs:
	${PHP} vendor/bin/phpcs --standard=PSR12 --extensions=php domain/ src/ infrastructures/ tests/ config/

composerunsed:
	${PHP} vendor/bin/composer-unused

audit:
	${COMPOSER} audit

.PHONY: qa qa-offline lint phpstan phpcs composerunsed audit

### Testing
test:
	XDEBUG_MODE=coverage ${PHP} -dmax_execution_time=0 -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit -c phpunit.xml --colors --coverage-text
	rm -rf var/cache/test
	APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists
	${PHP} vendor/bin/behat
	rm -rf var/cache/test

test-without-coverage:
	${PHP} -dmax_execution_time=0 vendor/bin/phpunit -c phpunit.xml
	rm -rf var/cache/test
	APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists
	${PHP} vendor/bin/behat
	rm -rf var/cache/test

.PHONY: test

### Cleaning
clean:
	rm -rf vendor
	rm -rf var/cache/*
	rm -rf var/log/*

.PHONY: clean
