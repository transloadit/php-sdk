#!/usr/bin/env bash
set -o errexit
set -o errtrace
set -o nounset
set -o pipefail
# set -o xtrace

if ! grep "${VERSION}" composer.json; then
  echo "First add '${VERSION}' to composer.json please"
  exit 1
fi
if ! grep "v${VERSION}" CHANGELOG.md; then
  echo "First add 'v${VERSION}' to CHANGELOG.md please"
  exit 1
fi

git tag "v${VERSION}"
git push --tags
curl \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"repository":{"url":"https://github.com/transloadit/php-sdk"}}' \
  "https://packagist.org/api/update-package?username=kvz&apiToken=${PACKAGIST_TOKEN}"
