SHELL := /usr/bin/env bash

export PATH := $(PATH):bin

phpUnit = vendor/bin/phpunit --colors --verbose --stderr --configuration phpunit.xml $(2) $(1)

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
test-all: lint test-simple test-system

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
	@vendor/bin/phpcs --warning-severity=0 --standard=./phpcs.xml lib/ examples/ test/ tool/

.PHONY: fix
fix:
	@vendor/bin/phpcbf --standard=./phpcs.xml lib/ examples/ test/ tool/

.PHONY: docs-html
docs-html: docs
	Markdown.pl --html4tags Readme.md > Readme.html
