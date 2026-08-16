# Variables
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= latest
PHP ?= /usr/bin/env php
NB_THREADS ?= 4
APP_ENV ?= prod
IGNORE_DEPRECATIONS ?= 0
PHP_LINT_DIRS := ./src ./domain ./infrastructures ./tests ./config ./extensions

MAKEFLAGS += --no-print-directory

.DEFAULT_GOAL := help

.PHONY: help
.PHONY: verify
.PHONY: install dev-install
.PHONY: update dev-update
.PHONY: config config-dry-run create-admin extension-list extension-enable extension-disable
.PHONY: build start stop restart db-backup db-restore
.PHONY: qa qa-offline lint phpstan phpcs audit
.PHONY: test test-mono-thread test-without-coverage
.PHONY: units-tests units-tests-without-coverage behavior-test behavior-test-mono-thread
.PHONY: all
.PHONY: clean warmup
.PHONY: setup-behat setup-phpunit

## Teknoo Space — CI/CD/Deployment PaaS Build & Deployment Tools
##
## Usage: make <target>
##

help: ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | grep -v '^[[:space:]]' | awk '{gsub(/^[[:space:]]+/, ""); gsub(/[[:space:]]+$$/, ""); if ($$0 == "") {printf "\n"; next} if ($$0 ~ /^#/) {gsub(/^#+[[:space:]]*/, ""); printf "\n%s\n", $$0; next} if (match($$0, /^[a-zA-Z_-]+:/)) {target = substr($$0, 1, index($$0, ":") - 1); pos = index($$0, "##"); if (pos > 0) {desc = substr($$0, pos + 2)} else {desc = substr($$0, index($$0, ":") + 1)} gsub(/^[[:space:]]+/, "", target); gsub(/[[:space:]]+$$/, "", target); gsub(/^[[:space:]]+/, "", desc); printf "  %-35s %s\n", target, desc; next} printf "%s\n", $$0}'

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

config-dry-run: 	## Preview the config (print the files that would be written, change nothing)
	@bin/config.sh --dry-run ${PHP} ${APP_ENV}

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
db-backup: 		## Backup MongoDB database to an archive file
	@bin/db-backup.sh

db-restore: 		## Restore MongoDB database from an archive file
	@bin/db-restore.sh

##
### QA:
qa: 		## Run a set of quality tests, to detect bugs, securities or qualities issues
qa: lint phpstan phpcs audit
	@echo "QA successful !"

qa-offline:	## Run a set of quality tests, without audit, in offline, to detect bugs, securities or qualities issues
qa-offline: lint phpstan phpcs
	@echo "QA successful !"

lint: 		## To detect error in PHP file causing compilation errors
	@echo "Start lint analyze"
	@find $(PHP_LINT_DIRS) -name "*.php" -exec ${PHP} -l {} \; 2>/dev/null \
		| grep "Parse error" > /tmp/php_lint_errors || true
	@if [ -s /tmp/php_lint_errors ]; then \
		cat /tmp/php_lint_errors; exit 1; \
	fi
	@rm -f /tmp/php_lint_errors
	@echo "Lint analyze ended"

phpstan: 	## To run code analyze with PHPStan to prevent bugs
	@[ -f phpstan.neon ] || cp phpstan.dist.neon phpstan.neon
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse

phpcs: 		## To check if the code follow the PSR 12
	${PHP} vendor/bin/phpcs --standard=PSR12 --extensions=php --ignore=config/secrets,config/reference.php,domain/Object/DTO/SpaceAccount.php,domain/Object/Persisted/AccountData.php,domain/Object/Persisted/AccountCluster.php domain/ src/ infrastructures/ tests/ config/ extensions/

audit: 		## Run an audit on vendors to detect CVE and deprecated libraries
	${COMPOSER} audit

##
### Testing:
test: 		## Run tests in $NB_THREADS threads (units tests and behavior tests, with a code coverage) to check if the installation can work properly
test: units-tests behavior-test

test-mono-thread: 	  ## Run tests in a mono thread (units tests and behavior tests, with a code coverage) to check if the installation can work properly
test-mono-thread: units-tests behavior-test-mono-thread

test-without-coverage: ## Run tests (units tests and behavior tests without a code coverage)
test-without-coverage: units-tests-without-coverage behavior-test

setup-phpunit:
	@[ -f phpunit.xml ] || cp phpunit.dist.xml phpunit.xml

units-tests: setup-phpunit 		## Run only units tests with a code coverage
	@XDEBUG_MODE=coverage ${PHP} -dmax_execution_time=0 -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit -c phpunit.xml --colors --coverage-text

units-tests-without-coverage: setup-phpunit 		## Run only units tests without code coverage
	@${PHP} -dmax_execution_time=0 vendor/bin/phpunit -c phpunit.xml

setup-behat:
	@[ -f behat.yml ] || cp behat.dist.yml behat.yml
	@rm -rf var/cache/test
	@APP_ENV=test ${PHP} -dmemory_limit=-1 bin/console cache:warmup
	@APP_ENV=test ${PHP} bin/console lexik:jwt:generate-keypair --skip-if-exists

behavior-test: setup-behat 		## Run behavior tests in $NB_THREADS to check if the installation can work properly
	@rm -rf var/cache/test
	@APP_ENV=test ${PHP} -dxdebug.mode=off vendor/bin/behat --parallel-feature ${NB_THREADS} --colors
	@rm -rf var/cache/test
	@echo "Tests successful !"

behavior-test-mono-thread: setup-behat 	  ## Run behavior tests in a mono thread to check if the installation can work properly
	@rm -rf var/cache/test
	@APP_ENV=test ${PHP} -dxdebug.mode=off vendor/bin/behat --colors
	@rm -rf var/cache/test
	@echo "Tests successful !"


##
### Miscellaneous:
all: ## Run QA and all tests
all: qa test

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
