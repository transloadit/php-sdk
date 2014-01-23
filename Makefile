SHELL := /bin/bash

phpunit = vendor/phpunit/phpunit/phpunit.php --colors --verbose $(1)

test: test-simple
test-all: test-simple test-system
test-simple:
	$(call phpunit,test/simple)
test-system:
	$(call phpunit,test/system)
docs:
	php tool/generate-example-docs.php
docs-html: docs
	Markdown.pl --html4tags Readme.md > Readme.html
