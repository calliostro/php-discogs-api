# Integration Test Setup

## Test Strategy

Integration tests are **separated from the CI pipeline** to prevent:

- 🚫 Rate limiting (429 Too Many Requests)
- 🚫 Flaky builds due to network issues
- 🚫 Dependency on external API availability
- 🚫 Slow build times (2+ minutes vs. 0.4 seconds)

## Running Tests

```bash
# Unit tests only (CI default - fast & reliable)
composer test-unit

# Integration tests only (manual - requires API access)
composer test-integration  

# All tests together (local development)
composer test-all
```

## GitHub Secrets Required

To enable authenticated integration tests in CI/CD, add these secrets to your GitHub repository:

### Repository Settings → Secrets and variables → Actions

| Secret Name                     | Description                      | Where to get it                                                           |
|---------------------------------|----------------------------------|---------------------------------------------------------------------------|
| `DISCOGS_CONSUMER_KEY`          | Your Discogs app consumer key    | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_CONSUMER_SECRET`       | Your Discogs app consumer secret | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_PERSONAL_ACCESS_TOKEN` | Your personal access token       | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_OAUTH_TOKEN`           | OAuth access token (optional)    | OAuth flow result                                                         |
| `DISCOGS_OAUTH_TOKEN_SECRET`    | OAuth token secret (optional)    | OAuth flow result                                                         |

## Test Levels

### 1. Public API Tests (Always Run)

- File: `tests/Integration/PublicApiIntegrationTest.php`
- No credentials required
- Tests public endpoints: artists, releases, labels, masters
- Safe for forks and pull requests

### 2. Authentication Levels Test (Conditional)

- File: `tests/Integration/AuthenticationLevelsTest.php`
- Requires all three secrets above
- Tests all four authentication levels:
  - Level 1: No auth (public data)
  - Level 2: Consumer credentials (search)
  - Level 3: Personal token (user data)
  - Level 4: OAuth (interactive flow, tested when tokens are available)

## Local Development

```bash
# Set environment variables
export DISCOGS_CONSUMER_KEY="your-consumer-key"
export DISCOGS_CONSUMER_SECRET="your-consumer-secret" 
export DISCOGS_PERSONAL_ACCESS_TOKEN="your-personal-access-token"

# Run public tests only
vendor/bin/phpunit tests/Integration/PublicApiIntegrationTest.php

# Run authentication tests (requires env vars)
vendor/bin/phpunit tests/Integration/AuthenticationLevelsTest.php

# Run all integration tests
vendor/bin/phpunit tests/Integration/ --testdox
```

## Safety Notes

- Public tests are safe for any environment
- Authentication tests will be skipped if secrets are missing
- No credentials are logged or exposed in the test output
- Tests use read-only operations only (no data modification)
