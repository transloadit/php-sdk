SHELL := /bin/bash

phpunit = phpunit --colors --verbose $(1)

test: test-simple
test-all: test-simple test-system
test-simple:
	$(call phpunit,test/simple)
test-system:
	$(call phpunit,test/system)
