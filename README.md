# SessionPilot

> **Open-source session & activity monitoring for logged-in WordPress users**  
> Self-hosted · Privacy-first · No SaaS · No telemetry · Free forever

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress: 6.4+](https://img.shields.io/badge/WordPress-6.4%2B-21759B)](https://wordpress.org)
[![PHP: 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777BB4)](https://php.net)

---

## What is SessionPilot?

SessionPilot is a WordPress plugin that gives site admins complete visibility and control over logged-in user sessions. It replaces a combination of 2–3 paid plugins with one unified, fully free tool.

**It is NOT** an analytics platform, visitor tracker, or security scanner. It tracks only authenticated (logged-in) WordPress users.

---

## Features (v1)

| Feature | Description |
|---|---|
| **Session Management** | View all active sessions with device, browser, OS, IP, and last-seen |
| **Force Logout** | Terminate any session instantly -single user, by role, or all |
| **Concurrent Limits** | Restrict simultaneous sessions globally, per role, or per user |
| **Idle Timeout** | Auto-expire sessions after configurable inactivity period |
| **Browser-Close Detection** | Best-effort logout when tab closes (heartbeat-based) |
| **Online Users Dashboard** | See who is logged in right now, active session counts, recent activity |
| **Activity Logs** | Audit trail for logins, logouts, role changes, plugin/theme events, settings changes |
| **Device Tracking** | Browser name, OS, device type, IP history per user |
| **Session Rules** | Per-role and per-user session policies with configurable enforcement modes |
| **Email Alerts** | Notify admin on limit exceeded or repeated login failures |
| **CSV Export** | Download sessions and activity logs as CSV |
| **REST API** | Full `/sp/v1/` endpoint set for automation and integrations |
| **WP-CLI** | Manage sessions, logs, rules, and devices from the command line |
| **Cleanup Cron** | Auto-purge expired sessions and old logs on a schedule |

---

## Why SessionPilot?

Most competitors split these features across multiple paid plans. SessionPilot ships everything free:

| What You'd Normally Pay For | SessionPilot |
|---|---|
| Force logout -WP Activity Log Pro (~$149/yr) | ✅ Free |
| Concurrent session limits -SessionQuota Pro ($49) | ✅ Free |
| Idle timeout -Inactive Logout Pro ($39/yr) | ✅ Free |
| Real-time active users -WP Activity Log Premium | ✅ Free |
| CSV export -Simple History Premium ($49/yr) | ✅ Free |
| Per-user session overrides -Loggedin add-ons ($29–$49) | ✅ Free |
| REST API -no competitor offers this | ✅ Free |

---

## Requirements

| Requirement | Minimum | Recommended |
|---|---|---|
| WordPress | 6.4 | Latest |
| PHP | 8.1 | 8.3 |
| MySQL | 8.0 | Latest |
| MariaDB | 10.6 | Latest |

---

## Installation

### From Source (Development)

1. **Clone the repository** into your WordPress plugins directory:

```bash
git clone https://github.com/your-org/sessionpilot.git wp-content/plugins/sessionpilot
cd wp-content/plugins/sessionpilot
```

2. **Install PHP dependencies:**

```bash
composer install
```

3. **Install and build JS assets:**

```bash
npm install
npm run build
```

4. **Activate the plugin** in WordPress admin (`Plugins → SessionPilot → Activate`).  
   Database tables are created automatically on activation.

5. **Run migrations manually** (optional, via WP-CLI):

```bash
wp acorn migrate
```

---

## Quick Start

After activation, navigate to **SessionPilot → Dashboard** in your WordPress admin.

### Force-logout a user via WP-CLI

```bash
wp sessionpilot sessions kill all --user=3
```

### Set a concurrent session limit for editors

```bash
wp sessionpilot rules set --role=editor --max=2 --mode=logout_oldest
```

### View active sessions

```bash
wp sessionpilot sessions list --active --format=table
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend framework | [Roots Acorn](https://roots.io/acorn/) (Laravel-style for WordPress) |
| ORM | Eloquent (admin models only) + raw `$wpdb` (high-frequency writes) |
| Frontend build | [Vite](https://vitejs.dev/) |
| Frontend JS | Alpine.js + HTMX |
| Templating | Blade |
| Background jobs | WP-Cron (default) / Redis (optional) |
| Device detection | WhichBrowser + Mobile_Detect |

---

## Documentation

Full documentation lives in the [`docs/`](docs/) directory.

| Document | Description |
|---|---|
| [Vision & Philosophy](docs/vision.md) | What SessionPilot is and is not, core principles |
| [Roadmap](docs/roadmap.md) | v1 MVP scope, Phase 2 & 3 plans, build order |
| [Architecture](docs/architecture.md) | Database schema, design rules, performance strategy |
| [Product Modules](docs/modules.md) | All feature modules in detail |
| [Tech Stack](docs/tech-stack.md) | All libraries, tools, and rationale |
| [Folder Structure](docs/folder-structure.md) | Plugin directory layout |
| [API Reference](docs/api.md) | WP-CLI commands and REST API endpoints |
| [Security](docs/security.md) | Security strategy, GDPR compliance, threat model |
| [UI/UX Direction](docs/ui-ux.md) | Admin dashboard design principles |
| [Competitors](docs/competitors.md) | Feature matrix and competitive gap analysis |
| [Contributing](docs/contributing.md) | Dev setup, coding standards, testing, CI/CD |
| [Sustainability](docs/sustainability.md) | Open-source model and future funding strategy |

---

## Roadmap

**v1 (current focus):** Session engine, activity logs, online users dashboard, session rules, device tracking, REST API, WP-CLI, CSV export, email alerts.

**Phase 2:** WooCommerce intelligence module, extended analytics, GeoIP, Discord/Slack/Webhook notifications, suspicious activity detection.

**Phase 3:** Heatmaps, purchase journey tracking, AI/anomaly detection, multisite aggregation.

> See [docs/roadmap.md](docs/roadmap.md) for full details and implementation build order.

---

## Security

SessionPilot follows WordPress security best practices throughout:

- Capability checks on every admin action
- Nonce verification on all AJAX and form requests
- Prepared statements for all database queries (`$wpdb->prepare()`)
- Full output escaping before HTML rendering
- No raw SQL interpolation anywhere

To report a security vulnerability, please use responsible disclosure. Do not open a public issue.

> See [docs/security.md](docs/security.md) for the full security strategy.

---

## Contributing

Contributions are welcome. Please read [docs/contributing.md](docs/contributing.md) for:

- Local dev setup instructions
- Coding standards (PSR-12, ESLint)
- Testing requirements (PHPUnit + Vitest)
- Git workflow and PR process

---

## License

SessionPilot is open source software released under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0).

No Pro version. No license keys. No cloud dependency. Everything is free, forever.

---

## Philosophy

> "SessionPilot does in one free plugin what competitors charge $50–$150/year for across multiple paid products."

Built for WordPress admins and agencies who want full control over their users' sessions -locally, privately, and without vendor lock-in.