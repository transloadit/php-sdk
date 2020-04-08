#!/usr/bin/env bash
set -o errexit
set -o errtrace
set -o nounset
set -o pipefail
# set -o xtrace

git tag ${VERSION}
git push --tags
curl \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"repository":{"url":"https://github.com/transloadit/php-sdk"}}' \
  "https://packagist.org/api/update-package?username=kvz&apiToken=${PACKAGIST_TOKEN}"

