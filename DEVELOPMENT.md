# Development Guide

This guide is for contributors and developers working on the php-discogs-api library itself.

## 🧪 Testing

### Quick Commands

```bash
# Unit tests (fast, CI-compatible, no external dependencies)
composer test

# Integration tests (requires Discogs API credentials)
composer test-integration

# All tests together (unit + integration)
composer test-all

# Code coverage (HTML + XML reports)
composer test-coverage
```

### Static Analysis & Code Quality

```bash
# Static analysis (PHPStan Level 8)
composer analyse

# Code style check (PSR-12)
composer cs

# Auto-fix code style
composer cs-fix
```

## 🔗 Integration Tests

Integration tests are **separated from the CI pipeline** to prevent:

- 🚫 Rate limiting (429 Too Many Requests)
- 🚫 Flaky builds due to network issues
- 🚫 Dependency on external API availability
- 🚫 Slow build times (2+ minutes vs. 0.4 seconds)

### Test Strategy

- **Unit Tests**: Fast, reliable, no external dependencies → **CI default**
- **Integration Tests**: Real API calls, rate-limited → **Manual execution**
- **Total Coverage**: 100% lines, methods, and classes covered

### GitHub Secrets Required

To enable authenticated integration tests in CI/CD, add these secrets to your GitHub repository:

#### Repository Settings → Secrets and variables → Actions

| Secret Name                     | Description                      | Where to get it                                                           |
|---------------------------------|----------------------------------|---------------------------------------------------------------------------|
| `DISCOGS_CONSUMER_KEY`          | Your Discogs app consumer key    | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_CONSUMER_SECRET`       | Your Discogs app consumer secret | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_PERSONAL_ACCESS_TOKEN` | Your personal access token       | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_OAUTH_TOKEN`           | OAuth access token (optional)    | OAuth flow result                                                         |
| `DISCOGS_OAUTH_TOKEN_SECRET`    | OAuth token secret (optional)    | OAuth flow result                                                         |

### Test Levels

#### 1. Public API Tests (Always Run)

- File: `tests/Integration/PublicApiIntegrationTest.php`
- No credentials required
- Tests public endpoints: artists, releases, labels, masters
- Safe for forks and pull requests

#### 2. Authentication Levels Test (Conditional)

- File: `tests/Integration/AuthenticationLevelsTest.php`
- Requires all three secrets above
- Tests all four authentication levels:
  - Level 1: No auth (public data)
  - Level 2: Consumer credentials (search)
  - Level 3: Personal token (user data)
  - Level 4: OAuth (interactive flow, tested when tokens are available)

### Local Development

```bash
# Set environment variables
export DISCOGS_CONSUMER_KEY="your-consumer-key"
export DISCOGS_CONSUMER_SECRET="your-consumer-secret" 
export DISCOGS_PERSONAL_ACCESS_TOKEN="your-personal-access-token"

# Run integration tests (public tests run without credentials, auth tests skip if no credentials)
composer test-integration

# Run all tests (unit + integration) with detailed output
composer test-all -- --testdox
```

### Safety Notes

- Public tests are safe for any environment
- Authentication tests will be skipped if secrets are missing
- No credentials are logged or exposed in the test output
- Tests use read-only operations only (no data modification)

## 🛠️ Development Workflow

1. Fork the repository
2. Create feature branch (`git checkout -b feature/name`)
3. Make changes with tests
4. Run test suite (`composer test-all`)
5. Check code quality (`composer analyse && composer cs`)
6. Commit changes (`git commit -m 'Add feature'`)
7. Push to branch (`git push origin feature/name`)
8. Open Pull Request

## 📋 Code Standards

- **PHP Version**: ^8.1
- **Code Style**: PSR-12 (enforced by PHP-CS-Fixer)
- **Static Analysis**: PHPStan Level 8
- **Test Coverage**: 100% lines, methods, and classes
- **Dependencies**: Minimal (only Guzzle required)

## 🔍 Architecture

The library consists of only four main classes:

1. **`DiscogsClient`** - Main API client with magic method calls
2. **`DiscogsClientFactory`** - Factory for creating authenticated clients
3. **`OAuthHelper`** - OAuth 1.0a flow helper
4. **`ConfigCache`** - Service configuration cache

Simple, focused architecture with minimal dependencies.
