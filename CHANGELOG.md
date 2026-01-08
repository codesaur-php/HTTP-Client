# Changelog

This file contains all changes for all versions of the `codesaur/http-client` package.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.1] - 2026-01-08
[2.0.1]: https://github.com/codesaur-php/HTTP-Client/compare/v2.0.0...v2.0.1

### Changed
- Refactored main README.md to properly reflect HTTP-Client project structure and features
- Simplified test section in docs/mn/README.md with clearer composer command documentation

### Added
- Complete English translation of all documentation files in docs/en/:
  - docs/en/README.md - Full English documentation
  - docs/en/api.md - Complete API documentation in English
  - docs/en/review.md - Package review and code quality assessment in English
- Bilingual code comments (Mongolian/English) in README.md examples

### Documentation
- Improved documentation structure and consistency across all language versions
- Added comprehensive test command documentation with examples
- Enhanced code examples with bilingual comments for better understanding

---

## [2.0.0] - 2025-12-18
[2.0.0]: https://github.com/codesaur-php/HTTP-Client/compare/v1.0...v2.0.0

### Breaking Changes
- **PHP Version Requirement**: Minimum PHP version increased from `>=7.2.0` to `^8.2.1`
- **Client class renamed**: `Client` class renamed to `CurlClient` for better clarity
- **JSONClient architecture**: Changed from inheritance (`extends Client`) to composition (uses `CurlClient` internally)
- **Mail class API**: Complete rewrite with fluent interface pattern
  - `send()` method replaced with `sendMail()`
  - `sendSMTP()` method removed (PHPMailer dependency removed)
  - New fluent methods: `targetTo()`, `addRecipient()`, `addCCRecipient()`, `addBCCRecipient()`, `setFrom()`, `setSubject()`, `setMessage()`, etc.

### Added
- **CurlClient improvements**:
  - Comprehensive PHPDoc documentation in Mongolian
  - Default parameter values for `request()` method (`$method = 'GET'`, `$data = ''`, `$options = []`)
  - Return type hints (`: string`)
  - Improved HTTP header array initialization handling
- **JSONClient enhancements**:
  - New `put()` method for PUT requests
  - New `delete()` method for DELETE requests
  - Smart GET request handling: payload automatically converted to query string
  - Environment-based SSL verification: respects `CODESAUR_APP_ENV` variable
    - `development` environment: SSL verify disabled
    - `production` or other: SSL verify enabled
  - Improved JSON decode error handling with proper validation
  - Comprehensive PHPDoc documentation
- **Mail class features**:
  - Fluent interface for method chaining
  - Multiple recipients support (To, Cc, Bcc)
  - Multiple attachment support:
    - File path attachments via `addFileAttachment()`
    - URL attachments via `addUrlAttachment()`
    - Raw content attachments via `addContentAttachment()`
  - MIME multipart email generation for attachments
  - Enhanced UTF-8 encoding support for headers and filenames
  - Email validation for all addresses
  - Comprehensive PHPDoc documentation
- **Testing infrastructure**:
  - Complete PHPUnit test suite
  - Unit tests for all classes
  - Integration tests for real-world scenarios
  - End-to-end tests
  - Test scripts in composer.json (`test`, `test:unit`, `test:integration`, `test:coverage`, `test:all`)
- **CI/CD pipeline**:
  - GitHub Actions workflow for automated testing
  - Multi-PHP version testing (8.2, 8.3)
  - Multi-OS testing (Ubuntu, Windows)
  - Code coverage reporting
  - Security audit via Composer
- **Documentation**:
  - Comprehensive README.md with examples
  - API.md documentation (auto-generated from PHPDoc)
  - REVIEW.md code quality assessment
  - Improved code examples and usage instructions

### Changed
- **Code quality improvements**:
  - Modern PHP array syntax (`[]` instead of `array()`)
  - Proper namespace prefixing for global functions (`\curl_init()` instead of `curl_init()`)
  - Type hints added throughout codebase
  - Return type declarations added
  - Better error handling and validation
- **Composer configuration**:
  - Removed `phpmailer/phpmailer` dependency (Mail class now uses native PHP `mail()` function)
  - Added `phpunit/phpunit` as dev dependency
  - Added `autoload-dev` section for test classes
  - Added test scripts for easier testing
  - Updated package description and keywords
  - Updated author information and homepage URLs

### Removed
- PHPMailer dependency (SMTP functionality removed from Mail class)
- `sendSMTP()` method from Mail class

### Fixed
- Improved error handling in CurlClient for edge cases
- Better JSON decode validation in JSONClient
- Fixed HTTP header array handling in CurlClient
- Enhanced email address validation in Mail class

---

## [1.0] - 2021-10-10
[1.0]: https://github.com/codesaur-php/HTTP-Client/releases/tag/v1.0

### Added
- **Client class** - Basic cURL-based HTTP client
  - `request()` method for sending HTTP requests with custom methods
  - Support for GET, POST, PUT, DELETE and other HTTP methods
  - Custom cURL options configuration
  - Basic error handling with Exception throwing
- **JSONClient class** - JSON API client extending Client
  - `get()` method for GET requests with JSON payload
  - `post()` method for POST requests with JSON payload
  - Automatic JSON encoding/decoding
  - SSL verification disabled by default
  - Error handling returns error array structure instead of throwing exceptions
- **Mail class** - Email sending functionality
  - `send()` method for basic email sending using PHP's native `mail()` function
    - Support for UTF-8 encoded subject and content
    - Automatic content-type detection (text/plain or text/html)
    - Base64 encoding for message content
    - Support for array-based sender and recipient with name encoding
  - `sendSMTP()` method for SMTP email sending using PHPMailer
    - Full SMTP configuration support (host, port, username, password)
    - SMTP authentication support
    - SSL/TLS secure connection support
    - Custom SMTP options configuration
    - HTML message support via `MsgHTML()`
- **Composer package configuration**
  - PHP 7.2+ requirement
  - Required extensions: `ext-curl`, `ext-json`
  - PHPMailer dependency (`phpmailer/phpmailer >= 6.5`)
  - PSR-4 autoloading support
- **Basic example files**
  - Example usage for HTTP client
  - Example usage for Mail functionality

### Features
- Object-oriented HTTP client implementation
- JSON-based API communication support
- Dual email sending methods (native PHP mail and SMTP via PHPMailer)
- UTF-8 support for international characters
- Simple and lightweight design
- Framework-agnostic (can be used standalone or with any PHP framework)
