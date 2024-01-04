# Variables
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= latest
PHP ?= /usr/bin/env php

##Teknoo Space
##Tools to manage Space's vendors, tests and deployment
##
### Generics:
help: 		## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

.PHONY: verify
verify: 	## Download dependencies via Composer and verify space installation
verify: clean dev test qa-offline

##
### Dependencies:
.PHONY: update
update: 	## Install and update all dependencies according to composer configuration
        	## Set the env var DEPENDENCIES to lowest to download lowest vendors versions instead of lasts versions
ifeq ($(DEPENDENCIES), lowest)
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} update --prefer-lowest --prefer-dist --no-interaction;
else
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction;
endif

.PHONY: install
install: 	## To install all PHP vendors for Space, thanks to Composer, without dev libriraies, build Symfony app and warmup caches
install: clean
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction --no-dev --optimize-autoloader --classmap-authoritative;
	APP_ENV=prod ${PHP} bin/console cache:warmup

.PHONY: clean
dev: 		## To install all PHP vendors for Space, thanks to Composer, including dev libriraies
dev: clean
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction --optimize-autoloader --classmap-authoritative;
	APP_ENV=prod ${PHP} bin/console cache:warmup

##
### QA:
.PHONY: qa
qa: 		## Run a set of quality tests, to detect bugs, securities or qualities issues
qa: lint phpstan phpcs audit

.PHONY: qa-offline
qa-offline:	## Run a set of quality tests, without audit, in offline, to detect bugs, securities or qualities issues
qa-offline: lint phpstan phpcs

.PHONY: lint
lint: 		## To detect error in PHP file causing compilation errors
	find ./src -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./domain -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./infrastructures -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./tests -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./config -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

.PHONY: phpstan
phpstan: 	## To run code analyze with PHPStan to prevent bugs
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse domain src infrastructures config --level max

.PHONY: phpcs
phpcs: 		## To check if the code follow the PSR 12
	${PHP} vendor/bin/phpcs --standard=PSR12 --extensions=php domain/ src/ infrastructures/ tests/ config/

.PHONY: composerunsed
composerunsed:	## To detect unused vendor in the code
	${PHP} vendor/bin/composer-unused

.PHONY: audit
audit: 		## Run an audit on vendors to detect CVE and deprecated libraries
	${COMPOSER} audit

##
### Testing:
.PHONY: test
test: 		## Run tests (units tests and behavior tests, with a code coverage) to check if the installation can work properly
	XDEBUG_MODE=coverage ${PHP} -dmax_execution_time=0 -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit -c phpunit.xml --colors --coverage-text
	rm -rf var/cache/test
	APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists
	${PHP} vendor/bin/behat
	rm -rf var/cache/test

.PHONY: test-without-coverage
test-without-coverage: ## Run tests (units tests and behavior tests without a code coverage)
	${PHP} -dmax_execution_time=0 vendor/bin/phpunit -c phpunit.xml
	rm -rf var/cache/test
	APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists
	${PHP} vendor/bin/behat
	rm -rf var/cache/test

##
### Cleaning:
.PHONY: clean
clean: 		## Remove all PHP vendors, composer generated map, clean all symfony builds, caches and logs
	rm -rf vendor
	rm -rf var/cache/*
	rm -rf var/log/*

