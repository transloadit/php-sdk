## Versions

### [main](https://github.com/transloadit/php-sdk/tree/main)

diff: https://github.com/transloadit/php-sdk/compare/v3.0.5...main

### [3.0.5](https://github.com/transloadit/php-sdk/tree/v3.0.5)

- Pass down `curlOptions` when `TransloaditRequest` reinstantiates itself for `waitForCompletion`

diff: https://github.com/transloadit/php-sdk/compare/v3.0.4...v3.0.5

### [3.0.4](https://github.com/transloadit/php-sdk/tree/v3.0.4)

- Ditch `v` prefix in versions as that's more idiomatic
- Bring back the getAssembly() function
- Implement Transloadit client header. Closes #25. (#28)
- Fix waitForCompletion
- Travis php & ubuntu version changes
- fix: remove deprecation warning
- Rename tl->deleteAssembly to cancelAssembly and add it to the Readme

diff: https://github.com/transloadit/php-sdk/compare/v2.0.0...3.0.4

### [v2.1.0](https://github.com/transloadit/php-sdk/tree/v2.1.0)

- Fix for CURL deprecated functions (thanks @ABerkhout)
- CI improvements (phpunit, travis, composer)
- Add example for fetching the assembly status
- Add ability to set additional curl_setopt (thanks @michaelkasper)

diff: https://github.com/transloadit/php-sdk/compare/v2.0.0...v2.1.0

### [v2.0.0](https://github.com/transloadit/php-sdk/tree/v2.0.0)

- Retire host + protocol in favor of one endpoint property,
  allow passing that on to the Request object.
- Improve readme (getting started)
- Don't rely on globally installed phpunit when we can ship it via Composer

diff: https://github.com/transloadit/php-sdk/compare/v1.0.1...v2.0.0

### [v1.0.1](https://github.com/transloadit/php-sdk/tree/v1.0.1)

- Fix broken examples
- Improve documentation (version changelogs)

diff: https://github.com/transloadit/php-sdk/compare/v1.0.0...v1.0.1

### [v1.0.0](https://github.com/transloadit/php-sdk/tree/v1.0.0)

A big thanks to [@nervetattoo](https://github.com/nervetattoo) for making this version happen!

- Add support for Composer
- Make phpunit run through Composer
- Change to namespaced PHP

diff: https://github.com/transloadit/php-sdk/compare/v0.10.0...v1.0.0

### [v0.10.0](https://github.com/transloadit/php-sdk/tree/v0.10.0)

- Add support for Strict mode
- Add support for more auth params
- Improve documentation
- Bug fixes
- Refactoring

diff: https://github.com/transloadit/php-sdk/compare/v0.9.1...v0.10.0

### [v0.9.1](https://github.com/transloadit/php-sdk/tree/v0.9.1)

- Improve documentation
- Better handling of errors & non-json responses
- Change directory layout

diff: https://github.com/transloadit/php-sdk/compare/v0.9...v0.9.1

### [v0.9](https://github.com/transloadit/php-sdk/tree/v0.9)

- Use markdown for docs
- Add support for signed GET requests
- Add support for HTTPS
- Simplified API
- Improve handling of magic quotes

diff: https://github.com/transloadit/php-sdk/compare/v0.2...v0.9

### [v0.2](https://github.com/transloadit/php-sdk/tree/v0.2)

- Add error handling

diff: https://github.com/transloadit/php-sdk/compare/v0.1...v0.2

### [v0.1](https://github.com/transloadit/php-sdk/tree/v0.1)

The very first version
