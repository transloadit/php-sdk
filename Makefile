SHELL := /bin/bash

test:
	phpunit --colors --verbose test

.PHONY: test
