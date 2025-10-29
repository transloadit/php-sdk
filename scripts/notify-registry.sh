#!/usr/bin/env bash
set -euo pipefail
set -o errtrace
# set -o xtrace

IMAGE_NAME=${IMAGE_NAME:-transloadit-php-sdk-dev}
CACHE_DIR=.docker-cache

ensure_docker() {
  if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is required to run this script." >&2
    exit 1
  fi

  if ! docker info >/dev/null 2>&1; then
    if [[ -z "${DOCKER_HOST:-}" && -S "$HOME/.colima/default/docker.sock" ]]; then
      export DOCKER_HOST="unix://$HOME/.colima/default/docker.sock"
    fi
  fi

  if ! docker info >/dev/null 2>&1; then
    echo "Docker daemon is not reachable. Start Docker (or Colima) and retry." >&2
    exit 1
  fi
}

configure_platform() {
  if [[ -z "${DOCKER_PLATFORM:-}" ]]; then
    local arch
    arch=$(uname -m)
    if [[ "$arch" == "arm64" || "$arch" == "aarch64" ]]; then
      DOCKER_PLATFORM=linux/amd64
    fi
  fi
}

if [[ "${1:-}" != "--inside-container" ]]; then
  ensure_docker
  configure_platform

  mkdir -p "$CACHE_DIR/composer-cache" "$CACHE_DIR/npm-cache" "$CACHE_DIR/composer-home"

  BUILD_ARGS=()
  if [[ -n "${DOCKER_PLATFORM:-}" ]]; then
    BUILD_ARGS+=(--platform "$DOCKER_PLATFORM")
  fi
  BUILD_ARGS+=(-t "$IMAGE_NAME" -f Dockerfile .)

  docker build "${BUILD_ARGS[@]}"

  DOCKER_ARGS=(
    --rm
    --user "$(id -u):$(id -g)"
    -e HOME=/workspace
    -e COMPOSER_HOME=/workspace/$CACHE_DIR/composer-home
    -e COMPOSER_CACHE_DIR=/workspace/$CACHE_DIR/composer-cache
    -e npm_config_cache=/workspace/$CACHE_DIR/npm-cache
    -v "$PWD":/workspace
    -w /workspace
  )

  if [[ -n "${DOCKER_PLATFORM:-}" ]]; then
    DOCKER_ARGS+=(--platform "$DOCKER_PLATFORM")
  fi

  if [[ -f .env ]]; then
    DOCKER_ARGS+=(--env-file "$PWD/.env")
  fi

  if [[ -n "${PACKAGIST_TOKEN:-}" ]]; then
    DOCKER_ARGS+=(-e "PACKAGIST_TOKEN=${PACKAGIST_TOKEN}")
  fi

  if [[ -n "${VERSION:-}" ]]; then
    DOCKER_ARGS+=(-e "VERSION=${VERSION}")
  fi

  exec docker run "${DOCKER_ARGS[@]}" "$IMAGE_NAME" bash -lc "./scripts/notify-registry.sh --inside-container"
fi

shift

if [[ -z "${PACKAGIST_TOKEN:-}" ]]; then
  if [[ -f .env ]]; then
    # shellcheck disable=SC1091
    source .env || {
      echo "Failed to source .env"
      exit 1
    }
  fi
  if [[ -z "${PACKAGIST_TOKEN:-}" ]]; then
    echo "PACKAGIST_TOKEN is not set"
    exit 1
  fi
fi

if [[ -z "${VERSION:-}" ]]; then
  echo "VERSION is not set"
  exit 1
fi

if ! grep "${VERSION}" composer.json > /dev/null 2>&1; then
  echo "First add '${VERSION}' to composer.json please"
  exit 1
fi
if ! grep "${VERSION}" CHANGELOG.md > /dev/null 2>&1; then
  echo "First add '${VERSION}' to CHANGELOG.md please"
  exit 1
fi
if [[ -n "$(git status --porcelain)" ]]; then
  echo "Git working tree not clean. First commit all your work please."
  exit 1
fi

curl \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"repository":{"url":"https://github.com/transloadit/php-sdk"}}' \
  "https://packagist.org/api/update-package?username=kvz&apiToken=${PACKAGIST_TOKEN}"
