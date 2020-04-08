#!/usr/bin/env bash
set -o errexit
set -o errtrace
set -o nounset
set -o pipefail
# set -o xtrace

if ! grep composer.json |grep "${VERSION}"; then
  echo "First update composer.json please"
  exit 1
fi
if ! grep CHANGELOG.md |grep "v${VERSION}"; then
  echo "First update CHANGELOG.md please"
  exit 1
fi

git tag "v${VERSION}"
git push --tags
curl \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"repository":{"url":"https://github.com/transloadit/php-sdk"}}' \
  "https://packagist.org/api/update-package?username=kvz&apiToken=${PACKAGIST_TOKEN}"

