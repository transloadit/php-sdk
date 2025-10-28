#!/usr/bin/env bash
set -o errexit
set -o errtrace
set -o nounset
set -o pipefail
# set -o xtrace

if [[ -z "${PACKAGIST_TOKEN:-}" ]]; then
  # shellcheck disable=SC1091
  source .env || {
    echo "Failed to source .env"
    exit 1
  }
  if [[ -z "${PACKAGIST_TOKEN:-}" ]]; then
    echo "PACKAGIST_TOKEN is not set"
    exit 1
  fi
fi

if ! grep "${VERSION}" composer.json > /dev/null 2>&1; then
  echo "First add '${VERSION}' to composer.json please"
  exit 1
fi
if ! grep "${VERSION}" CHANGELOG.md > /dev/null 2>&1; then
  echo "First add '${VERSION}' to CHANGELOG.md please"
  exit 1
fi
if [ -n "$(git status --porcelain)" ]; then
  echo "Git working tree not clean. First commit all your work please."
  exit 1
fi

curl \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"repository":{"url":"https://github.com/transloadit/php-sdk"}}' \
  "https://packagist.org/api/update-package?username=kvz&apiToken=${PACKAGIST_TOKEN}"
