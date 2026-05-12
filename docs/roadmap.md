# Roadmap

## The Core Principle: Scope Discipline

SessionPilot was initially planned with many modules. After careful review, the scope has been deliberately reduced for v1.

**The risk of doing too much too early:**
- Development slows massively
- Admin UI becomes bloated and hard to navigate
- Database complexity explodes
- Performance on shared hosting degrades
- Support burden becomes unsustainable

The correct approach: **ship a small, excellent product** that beats every competitor on its focused scope, then grow responsibly.

---

## v1 Core vs. Optional Modules

| Feature | v1 Core | Future Module |
|---|---|---|
| Session management (force logout, limits, idle, device) | ✅ | -|
| Online users dashboard | ✅ | -|
| Admin activity logs | ✅ | -|
| Session rules (per role/user) | ✅ | -|
| Basic REST API | ✅ | -|
| Basic WP-CLI | ✅ | -|
| CSV export | ✅ | -|
| Email alerts | ✅ | -|
| Cleanup cron | ✅ | -|
| WooCommerce Intelligence | -| Phase 2 |
| Analytics Engine | -| Phase 2 |
| Extended notifications (Discord, Slack, Webhook) | -| Phase 2 |
| Heatmaps | -| Phase 3 |
| Purchase journey / funnel tracking | -| Phase 3 |
| AI / anomaly detection | -| Phase 3 |
| Multi-server aggregation | -| Phase 3 |
| Multisite aggregation | -| Phase 3 |

---

## Competitive Gap Analysis

What SessionPilot v1 delivers free that competitors charge for:

| Gap | Which Competitors Lock It Behind Paywall |
|---|---|
| Force logout (bulk & per-user) | WP Activity Log (premium only) |
| Concurrent session limits per role/user | Loggedin (add-on), SessionQuota (Pro) |
| Idle timeout auto-logout | WPForce Logout (Pro), Inactive Logout (Pro) |
| Browser-close detection | WPForce Logout (Pro), Inactive Logout (Pro) |
| Real-time active user dashboard | WP Activity Log (premium), Inactive Logout (Pro) |
| Log export (CSV) | SimpleHistory (premium), WinterLock (premium) |
| Email alert notifications | WP Activity Log (premium), SessionQuota (Pro) |
| Role-based session policies | SessionQuota (Pro), WP Activity Log (premium) |
| WP-CLI session management | Most plugins (none or premium) |

> **v1 alone already outclasses any single competitor** -without needing analytics, WooCommerce, or AI.

---

## MVP -Version 1.0 Scope

### What IS in v1

**Session Engine**
- Active sessions list with device, IP, browser, last seen
- Force logout (single user, bulk, by role)
- Concurrent session limits (global, per role, per user override)
- Idle timeout (auto-expire after configurable inactivity period)
- Browser-close detection (best-effort -see caveat below)

**Device Tracking**
- Browser name and version
- Operating system
- Device type (desktop / mobile / tablet)
- IP address, last seen timestamp
- Per-user device history view

**Admin Activity Logs**
- Login and logout events
- Role changes
- Plugin and theme activation / deactivation
- Settings changes
- Login failures

**Online Users Dashboard**
- Logged-in users online now
- Active sessions count
- Recent activity feed

**Session Rules**
- Per-role max session limits
- Per-user overrides
- Enforcement modes: block new / logout oldest / logout all

**Infrastructure**
- Basic REST API (`/sp/v1/sessions`, `/sp/v1/logs`, `/sp/v1/rules`, `/sp/v1/devices`)
- WP-CLI commands (`sessions list/kill`, `logs list`, `rules set`, `devices list`)
- CSV export for sessions and activity logs
- Email alerts (admin email on limit exceeded, repeated login failures)
- Cleanup cron (auto-purge expired sessions and old logs)

### What is NOT in v1

| Removed Feature | Why |
|---|---|
| WooCommerce Intelligence | Standalone product-level complexity -own phase |
| Analytics Engine | Dangerously large scope; DB write overhead on shared hosting |
| Full user journey / funnel tracking | Massive write volume; shared hosting risk |
| Heatmaps | Technically expensive, different product entirely |
| AI / anomaly detection | Not now |
| Discord / Slack / ntfy / Gotify notifications | Email only in v1 |
| Advanced export (SQL, JSON, scheduled) | CSV is sufficient for v1 |
| Multi-server / multisite aggregation | Later only |

---

## Browser-Close Logout -Important Caveat

Browser-close detection is included in v1 **as a best-effort convenience feature**, not a guaranteed security control.

**Why it is unreliable:**
- Browser crashes skip unload events entirely
- Mobile browsers background/suspend tabs without firing unload
- Safari blocks `beforeunload` / `unload` in many scenarios
- Users can have JavaScript disabled

**Implementation approach:**
- JS heartbeat pings the server every N seconds while the tab is active
- On `visibilitychange` (tab hidden) or `beforeunload`, the heartbeat stops
- Server-side cron expires sessions that missed heartbeats beyond the grace period
- Grace period is configurable (default: 2 minutes)

**Label it clearly in the UI:**
> *"Best-effort browser-close detection -not a guaranteed security mechanism"*

---

## Implementation Build Order

> **Build backend first. Not UI first.** A plugin with a working engine and no pretty UI is better than a pretty UI with a fragile engine.

| Phase | Focus | Key Deliverables |
|---|---|---|
| **Phase 1 -Foundation** | Core backend | DB tables, migrations, WP hooks registered, plugin scaffold, service providers |
| **Phase 2 -Session Engine** | Session tracking | `WP_Session_Tokens` integration, `$wpdb` session writes, force logout, concurrent limits, idle timeout, browser-close heartbeat, cron cleanup |
| **Phase 3 -Activity Logs** | Event logging | `$wpdb` writes on login/logout/role/plugin hooks, device fingerprinting (WhichBrowser + Mobile Detect) |
| **Phase 4 -Admin UI** | Dashboard + 6 pages | Blade + Alpine + HTMX: Dashboard, Sessions, Activity Logs, Rules, Devices, Settings |
| **Phase 5 -Infrastructure** | REST API + WP-CLI | All `/sp/v1/` endpoints, WP-CLI commands, nonces, capability checks, CSV export |
| **Phase 6 -Hardening** | Polish + performance | PHPUnit tests, security audit, shared-hosting performance profiling, UI polish |

**Total estimated:** ~22 weeks solo, faster with parallel contributors

---

## Phase 2 -Optional Modules (Post-Stable v1)

Shipped as **separate optional modules**, not bundled into core. Core stays lean regardless of which modules are active.

| Module | Description |
|---|---|
| WooCommerce Intelligence | Live carts, abandoned cart detection, product interest, purchase journey |
| Analytics Engine | Page views, sessions, bounce rate, referrers -privacy-first, local only |
| Extended Notifications | Webhook, Discord, Slack, ntfy, Gotify channels |
| Advanced Export | JSON, SQL backup, scheduled exports, filtered downloads |
| Suspicious Activity Detection | Rule-based flagging: impossible travel, rapid IP change, unusual login times |

---

## Phase 3 -Long-Term Vision

| Feature | Description |
|---|---|
| Heatmaps | Click and scroll tracking with page overlay visualization |
| Multisite Aggregation | Single dashboard across an entire WP network |
| Agency Dashboard | Client management view for multi-site agencies |
| Local AI Anomaly Detection | On-server model (no cloud) for behavioral anomaly detection |
| Server-Sent Events (SSE) | True live push updates instead of polling |

---

## Versioning System

SessionPilot uses **Semantic Versioning** (`MAJOR.MINOR.PATCH`).

### Version Number Rules

| Segment | When to increment | Example |
|---|---|---|
| `MAJOR` | Breaking changes to DB schema, hooks, or REST API that require migration | `1.0.0` → `2.0.0` |
| `MINOR` | New features added in a backwards-compatible way | `1.0.0` → `1.1.0` |
| `PATCH` | Bug fixes, security fixes, performance improvements | `1.0.0` → `1.0.1` |

### Pre-release Versioning

| Stage | Version Format | Meaning |
|---|---|---|
| Early development | `0.1.0`, `0.2.0`, ... | Unstable. DB schema may change. No migration guarantees. |
| Feature-complete beta | `0.9.0`, `0.9.1`, ... | Feature-locked. Bug fixes and polish only. |
| Stable release | `1.0.0` | Production-ready. Full backward-compat guarantees start here. |
| Patch release | `1.0.1`, `1.0.2`, ... | Bug and security fixes only. No new features. |
| Feature release | `1.1.0`, `1.2.0`, ... | New features. Existing features unchanged. |

### WordPress Plugin Header

The `Version:` field in the plugin header must always match the tag.

```php
/**
 * Plugin Name: SessionPilot
 * Version:     0.1.0
 */
```

### DB Schema Migrations

- `0.x.x` releases: no migration guarantees. Deactivate and reactivate to reset tables.
- `1.0.0+` releases: all schema changes must ship with Acorn migration files and a `UPGRADE.md` entry.
- Breaking schema changes increment `MAJOR`.

### Release Checklist

Before tagging any release:
- [ ] `CHANGELOG.md` entry written
- [ ] All tests pass
- [ ] `readme.txt` (WP plugin repo format) updated
- [ ] Plugin header `Version:` bumped
- [ ] Git tag created matching the version (`v1.0.0`)
- [ ] GitHub release published with changelog

---

## Version Philosophy

- Releases are **stable and tested** before shipping
- No half-baked features merged to meet a deadline
- Each phase is independently valuable -users don't need Phase 2/3
- Optional modules are opt-in -core stays lean regardless of what is installed
- Breaking changes are documented with migration paths
- **The goal is to be the best WordPress session manager, not the biggest.**
