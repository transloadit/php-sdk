# Contributing

Feel free to fork this project. We will happily merge bug fixes or other small
improvements. For bigger changes you should probably get in touch with us
before you start to avoid not seeing them merged.

## Testing

### Basic Tests

```bash
make test
```

### System Tests

System tests require:

1. Valid Transloadit credentials in environment:

```bash
export TRANSLOADIT_KEY='your-auth-key'
export TRANSLOADIT_SECRET='your-auth-secret'
```

Then run:

```bash
make test-all
```

### Node.js Reference Implementation Parity Assertions

The SDK includes assertions that compare Smart CDN URL signatures and regular request signatures with our reference Node.js implementation. To run these tests:

1. Requirements:

   - Node.js 20+ with npm
   - Ability to execute `npx transloadit smart_sig` (the CLI is downloaded on demand)
   - Ability to execute `npx transloadit sig` (the CLI is downloaded on demand)

2. Run the tests:

```bash
export TRANSLOADIT_KEY='your-auth-key'
export TRANSLOADIT_SECRET='your-auth-secret'
TEST_NODE_PARITY=1 make test-all
```

If you want to warm the CLI cache ahead of time you can run:

```bash
npx --yes transloadit smart_sig --help
```

For regular request signatures, you can also prime the CLI by running:

```bash
TRANSLOADIT_KEY=... TRANSLOADIT_SECRET=... \
  npx --yes transloadit sig --algorithm sha1 --help
```

CI opts into `TEST_NODE_PARITY=1`, and you can optionally do this locally as well.

### Run Tests in Docker

Use `scripts/test-in-docker.sh` for a reproducible environment:

```bash
./scripts/test-in-docker.sh
```

This builds the local image, runs `composer install`, and executes `make test-all` (unit + integration tests). Pass a custom command to run something else (composer install still runs first):

```bash
./scripts/test-in-docker.sh vendor/bin/phpunit --filter signedSmartCDNUrl
```

Environment variables such as `TEST_NODE_PARITY` or the credentials in `.env` are forwarded, so you can combine parity checks and integration tests with Docker:

```bash
TEST_NODE_PARITY=1 ./scripts/test-in-docker.sh
```

## Releasing a new version

To release, say `3.3.0` [Packagist](https://packagist.org/packages/transloadit/php-sdk), follow these steps:

1. Make sure `PACKAGIST_TOKEN` is set in your `.env` file
1. Make sure you are in main: `git checkout main`
1. Make sure `CHANGELOG.md` and `composer.json` have been updated
1. Commit: `git add CHANGELOG.md composer.json && git commit -m "Release v3.3.0"`
1. Tag: `git tag v3.3.0`
1. Push: `git push --tags`
1. Notify Packagist (runs via Docker): `VERSION=3.3.0 ./scripts/notify-registry.sh`
1. Publish a GitHub release (include the changelog). This triggers the release workflow. (via the GitHub UI, `gh release creates v3.3.0 --title "v3.3.0" --notes-file <(cat CHANGELOG.md section)`)

The notify script reuses the same Docker image as `./scripts/test-in-docker.sh`, so Docker is the only requirement on your workstation.

This project implements the [Semantic Versioning](http://semver.org/) guidelines.
