# Contributing & Development

## Requirements

| Tool | Version |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 20+ |
| npm | 10+ |
| WordPress | 6.4+ |
| MySQL / MariaDB | 8.0+ / 10.6+ |

---

## Local Setup

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/sessionpilot.git
cd sessionpilot
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JS Dependencies

```bash
npm install
```

### 4. Build Frontend Assets

```bash
# Development (with hot reload)
npm run dev

# Production build
npm run build
```

### 5. Run Migrations

On a local WordPress install with the plugin activated, migrations run automatically on activation. To run manually via WP-CLI:

```bash
wp acorn migrate
```

---

## Project Structure

```
sessionpilot/
├── app/
│   ├── Console/          # WP-CLI command classes
│   ├── Http/
│   │   ├── Controllers/  # REST API controllers
│   │   └── Middleware/
│   ├── Models/           # Eloquent models
│   ├── Services/         # Business logic
│   ├── Repositories/     # Data access layer
│   ├── Actions/          # Single-purpose action classes
│   ├── Events/           # Domain events
│   ├── Listeners/        # Event listeners
│   ├── Jobs/             # Queue jobs
│   ├── Notifications/    # Notification channel handlers
│   └── Support/          # Helpers and utilities
├── bootstrap/
├── config/
├── database/
│   └── migrations/
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # React / Alpine.js source
│   └── css/
├── routes/
│   ├── api.php           # REST routes
│   └── admin.php         # Admin menu routes
├── tests/
│   ├── Unit/
│   └── Feature/
├── composer.json
├── package.json
├── vite.config.js
└── sessionpilot.php
```

**PHP Namespace:** `ProgrammerNomad\SessionPilot\`

---

## Coding Standards

### PHP

- Follow **PSR-12** coding style
- Enforce with PHP_CodeSniffer:

```bash
composer run phpcs
```

- Fix auto-fixable issues:

```bash
composer run phpcbf
```

### JavaScript

- Follow the ESLint config in `.eslintrc.json`
- Alpine.js components live in `resources/js/`
- Run linting:

```bash
npm run lint
```

---

## Testing

### PHP Tests (PHPUnit)

Tests live in `tests/Unit/` and `tests/Feature/`.

```bash
composer run test
```

#### Key Test Cases

| Test | Description |
|---|---|
| Session limit enforcement | Third login attempt kills oldest session |
| Idle expiration | Sessions idle > threshold are expired on next cron |
| Force logout | `ForceLogoutUser` action destroys WP_Session_Tokens entry |
| API auth | `/sp/v1/sessions` returns 403 without valid capability |
| Prepared statements | No raw SQL interpolation in any query |
| IP anonymization | Last octet masked when setting is enabled |

### JavaScript Tests (Vitest)

```bash
npm run test
```

---

## Continuous Integration

CI runs on every push and pull request via **GitHub Actions**.

### Pipeline Steps

```yaml
# .github/workflows/ci.yml (simplified)

on: [push, pull_request]

jobs:
  php:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Install PHP deps
        run: composer install
      - name: PHP CodeSniffer
        run: composer run phpcs
      - name: PHPUnit
        run: composer run test

  js:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Install JS deps
        run: npm ci
      - name: ESLint
        run: npm run lint
      - name: JS Tests
        run: npm run test
      - name: Build
        run: npm run build
```

### On Git Tag (Release)

When a semver tag is pushed (e.g. `v1.0.0`), the pipeline:

1. Runs the full test suite
2. Builds production JS/CSS assets
3. Creates a `.zip` plugin archive
4. Publishes it as a GitHub Release asset

---

## Git Workflow

### Branch Naming

| Branch | Purpose |
|---|---|
| `main` | Stable, always deployable |
| `develop` | Integration branch for ongoing work |
| `feature/<name>` | New features |
| `fix/<name>` | Bug fixes |
| `release/<version>` | Release preparation |

### Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add idle timeout auto-logout
fix: correct session count query for role limits
docs: add WP-CLI command examples to api.md
chore: update GeoLite2 database
```

### Pull Requests

- All changes require a PR to `develop`
- At least 1 approval required before merge
- CI must pass before merge
- PRs must reference a GitHub issue

---

## Versioning

SessionPilot uses [Semantic Versioning](https://semver.org/):

- `MAJOR.MINOR.PATCH`
- `1.0.0` -initial stable release
- `1.1.0` -new features (backward compatible)
- `1.0.1` -bug fixes only
- `2.0.0` -breaking changes

The version is defined in:
- `sessionpilot.php` plugin header (`Version:`)
- `composer.json` (`version`)
- Git tag

---

## Reporting Issues

- Use [GitHub Issues](https://github.com/your-org/sessionpilot/issues)
- For security vulnerabilities, see `SECURITY.md` -do **not** open a public issue

---

## Security Disclosures

See `SECURITY.md` in the repository root for the responsible disclosure process. Do not publicly disclose security issues before they are patched.

---

## License

SessionPilot is licensed under the **GPL-3.0-or-later** license, in compliance with WordPress licensing requirements.

```
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
```
