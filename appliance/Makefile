# Variables
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= latest
PHP ?= /usr/bin/env php
APP_ENV ?= prod
IGNORE_DEPRECATIONS ?= 0

MAKEFLAGS += --no-print-directory

.PHONY: help
.PHONY: verify
.PHONY: install dev-install
.PHONY: update dev-update
.PHONY: config create-admin extension-list extension-enable extension-disable
.PHONY: build start stop restart
.PHONY: qa qa-offline lint phpstan phpcs audit
.PHONY: test test-without-coverage
.PHONY: clean warmup

##Teknoo Space
##Tools to manage Space's vendors, configuration, tests and deployment
##
### Generics:
help: 		## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

verify: 	## Download dependencies via Composer and verify space installation
verify: clean dev-install test-without-coverage qa-offline clean install

##
### Installations:
install: 	## To install all PHP vendors for Space, thanks to Composer, without dev libraries, build Symfony app and warmup caches
install: clean
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction --no-dev --optimize-autoloader --classmap-authoritative;
	${COMPOSER} thanks
	@$(MAKE) warmup

dev-install: 	## To install all PHP vendors for Space, thanks to Composer, including dev libraries
dev-install: clean
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction --optimize-autoloader --classmap-authoritative;
	${COMPOSER} thanks
	@$(MAKE) warmup

update: 	## Install and update all dependencies according to composer configuration, without dev libraries, build Symfony app and warmup caches
        	## Set the env var DEPENDENCIES to lowest to download lowest vendors versions instead of lasts versions
ifeq ($(DEPENDENCIES), lowest)
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} update --prefer-lowest --no-dev --prefer-dist --no-interaction;
else
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-dev --no-interaction;
endif
	@$(MAKE) warmup

dev-update: 	## Install and update all dependencies according to composer configuration,
             	## Set the env var DEPENDENCIES to lowest to download lowest vendors versions instead of lasts versions
ifeq ($(DEPENDENCIES), lowest)
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} update --prefer-lowest --prefer-dist --no-interaction;
else
	COMPOSER_MEMORY_LIMIT=-1 ${COMPOSER} install --prefer-dist --no-interaction;
endif
	@$(MAKE) warmup

##
### Configuration:
config: 		## To set values in env file to configure Space
	@bin/config.sh ${PHP} ${APP_ENV}

create-admin: 		## To create an administrator in users, requires "email" and "password" parameter
	@bin/console teknoo:common:create-admin ${email} ${password}

extension-list: 	## To list available extension
	@bin/console teknoo:space:extension:list

extension-enable: 	## To enable an extension into Space, requires "name" parameter
	@bin/console teknoo:space:extension:enable ${name}

extension-disable: 	## To disable an extension into Space, requires "name" parameter
	@bin/console teknoo:space:extension:disable ${name}

##
### Docker:
build: 		## To build docker images to run locally Space on Docker
	@docker-compose build

start: 		## To start or refresh the docker stack and use Space locally on localhost
	@docker-compose up -d

stop: 		## To stop the docker stack
	@docker-compose stop

restart: 	## To restart the docker stack
restart: stop start

##
### QA:
qa: 		## Run a set of quality tests, to detect bugs, securities or qualities issues
qa: lint phpstan phpcs audit

qa-offline:	## Run a set of quality tests, without audit, in offline, to detect bugs, securities or qualities issues
qa-offline: lint phpstan phpcs

lint: 		## To detect error in PHP file causing compilation errors
	find ./src -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./domain -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./infrastructures -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./tests -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./config -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./extensions -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

phpstan: 	## To run code analyze with PHPStan to prevent bugs
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse domain src infrastructures config extensions --level max

phpcs: 		## To check if the code follow the PSR 12
	${PHP} vendor/bin/phpcs --standard=PSR12 --extensions=php --ignore=config/secrets domain/ src/ infrastructures/ tests/ config/ extensions/

audit: 		## Run an audit on vendors to detect CVE and deprecated libraries
	${COMPOSER} audit

##
### Testing:
test: 		## Run tests (units tests and behavior tests, with a code coverage) to check if the installation can work properly
	rm -rf var/cache/test
	APP_ENV=test ${PHP} -dmemory_limit=-1 bin/console cache:warmup
	XDEBUG_MODE=coverage ${PHP} -dmax_execution_time=0 -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit -c phpunit.xml --colors --coverage-text
	APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists
	APP_ENV=test ${PHP} -dxdebug.mode=off vendor/bin/behat
	rm -rf var/cache/test

test-without-coverage: ## Run tests (units tests and behavior tests without a code coverage)
	rm -rf var/cache/test
	APP_ENV=test ${PHP} -dmemory_limit=-1 bin/console cache:warmup
	${PHP} -dmax_execution_time=0 vendor/bin/phpunit -c phpunit.xml
	APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists
	IGNORE_DEPRECATIONS=${IGNORE_DEPRECATIONS} APP_ENV=test ${PHP} -dxdebug.mode=off vendor/bin/behat
	rm -rf var/cache/test

##
### Cleaning:
clean: 		## Remove all PHP vendors, composer generated map, clean all Symfony builds, caches and logs
	rm -rf vendor
	rm -rf var/cache/*
	rm -rf var/log/*

warmup: 	## Clear cache and warming , dump autoloader
	rm -rf var/cache/*
	APP_ENV=${APP_ENV} ${COMPOSER} dump-autoload
	${PHP} -dmemory_limit=-1 bin/console cache:warmup
