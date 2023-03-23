SHELL := /usr/bin/env bash

export PATH := $(PATH):bin

phpUnit = vendor/phpunit/phpunit/phpunit --colors --verbose --stderr --configuration phpunit.xml $(2) $(1)

.PHONY: install
install:
	which composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer
	composer install --no-interaction --prefer-source

.PHONY: test
test: test-simple

.PHONY: test-all-coverage
test-all-coverage:
	$(call phpUnit,test,--coverage-clover build/logs/clover.xml)

.PHONY: test-all
test-all: test-simple test-system

.PHONY: test-simple
test-simple:
	$(call phpUnit,test/simple)

.PHONY: test-system
test-system:
	$(call phpUnit,test/system)

.PHONY: docs
docs:
	php tool/generate-example-docs.php

.PHONY: lint
lint:
  vendor/bin/phpcs --standard=./phpcs.xml src/ tests/ plugins/ config/

.PHONY: fix
fix:
  vendor/bin/phpcbf --standard=./phpcs.xml src/ tests/ plugins/ config/


.PHONY: docs-html
docs-html: docs
	Markdown.pl --html4tags Readme.md > Readme.html
