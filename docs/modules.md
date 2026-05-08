# Product Modules

SessionPilot uses a two-layer architecture: a **Core System** that is always active, and **Optional Modules** that load only when explicitly enabled.

> **Design rule:** The Core System must stay fast, lean, and fully functional with zero optional modules enabled. Optional modules add capability but never add overhead to sites that don't need them.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│  CORE SYSTEM (always enabled)                       │
│                                                     │
│  Session Engine    Activity Logs    Session Rules   │
│  Online Users      Device Tracking  Cleanup Cron    │
│  REST API          WP-CLI           CSV Export      │
│  Email Alerts      Privacy Controls                 │
└─────────────────────────────────────────────────────┘
         ↓ extends via optional modules ↓
┌──────────────┐ ┌──────────────┐ ┌──────────────────┐
│ WooCommerce  │ │  Analytics   │ │  Notifications   │
│ Module       │ │  Module      │ │  Module          │
│ (Phase 2)    │ │  (Phase 2)   │ │  (Phase 2)       │
└──────────────┘ └──────────────┘ └──────────────────┘
┌──────────────┐ ┌──────────────────────────────────────┐
│ GeoIP Module │ │  Suspicious Activity Module (Phase 2) │
│ (Optional)   │ └──────────────────────────────────────┘
└──────────────┘
```

---

## CORE SYSTEM

Everything below is **always active**. No feature flags, no settings to enable. This is the stable foundation.

> **Scope constraint:** The Core System tracks only **authenticated (logged-in) WordPress users**. It does not track anonymous visitors, page views, or frontend click events. That is a different product category.

---

### Core 1 — Session Engine

The heart of the plugin. Tracks and controls active WordPress sessions.

**What it tracks:**
- Active sessions per user
- Session creation and expiration
- Login events and logout events
- Idle timeout
- IP address, browser, OS per session

**Features:**

| Feature | Description |
|---|---|
| Force logout | Terminate any session immediately |
| Logout all users | Kill every active session site-wide |
| Logout by role | End all sessions for a given role |
| Logout by IP | Force-logout all sessions from a specific IP |
| Concurrent session limits | Restrict simultaneous sessions per user |
| Idle session timeout | Auto-expire sessions after inactivity |
| Browser-close detection | Best-effort logout when tab closes (see roadmap caveat) |
| Kill inactive sessions | Batch-remove stale/orphaned sessions via cron |

---

### Core 2 — Activity Logs

Audit trail for all important WordPress admin and authentication events.

**Tracked events:**
- Login and logout (all users)
- Failed login attempts
- Password resets
- User role changes
- Plugin activation / deactivation / deletion
- Theme switches
- WordPress settings changes

**Features:**

| Feature | Description |
|---|---|
| Searchable | Full-text search across all log entries |
| Filterable | By user, action type, severity, date range |
| Severity levels | Info / Warning / Critical |
| CSV export | Download logs as CSV |
| Per-user timeline | Chronological view per user |

---

### Core 3 — Online Users Dashboard

Shows who is logged in and active right now.

**Displays:**
- Count of online users (active in last 5 minutes)
- Active session count
- Recent activity feed
- Per-user: last seen, browser, device, IP

> Not a website analytics dashboard. Does not show page views, traffic sources, anonymous visitors, or referrer stats. Those are the Analytics Module's territory (Phase 2).

---

### Core 4 — Device Tracking

Stores device fingerprint per session, linked to the user account.

**Tracked per session:**
- Browser name and version
- Operating system
- Device type (desktop / mobile / tablet)
- IP address
- Last activity timestamp
- Country (if GeoIP Module is enabled — optional)

Inspired by the Google Account security panel — a per-user view of all known devices and sessions.

---

### Core 5 — Session Rules

Policy engine for session behavior. Configurable per role and per user.

**Rule types:**
- Maximum concurrent sessions (global default, per-role override, per-user override)
- Enforcement mode: block new / logout oldest / logout all others
- Idle timeout duration
- Browser-close grace period

---

### Core 6 — Infrastructure

Supporting systems that all core features depend on.

| Component | Description |
|---|---|
| Cleanup cron | WP-Cron job to purge expired sessions and old logs |
| REST API | `/sp/v1/sessions`, `/sp/v1/logs`, `/sp/v1/rules`, `/sp/v1/devices` |
| WP-CLI | `sessions list/kill`, `logs list`, `rules set`, `devices list` |
| CSV export | Sessions and activity logs |
| Email alerts | `wp_mail()` for limit exceeded, repeated login failures |
| Privacy controls | IP anonymization, data retention, GDPR export/delete |

---

## OPTIONAL MODULES

These are separate, opt-in additions. They are **not loaded** unless explicitly enabled in Settings. Each module adds its own DB tables, hooks, and admin pages only when active.

---

### Optional Module A — WooCommerce Module

> **Phase 2** — Only activates if WooCommerce is installed and active. Loads zero code if WooCommerce is absent.

**Features:**
- Live cart view (active carts, cart value, checkout status)
- Abandoned cart detection
- Checkout activity logging
- Per-user purchase activity timeline

---

### Optional Module B — Analytics Module

> **Phase 2** — Privacy-first, local-only page analytics. Opt-in only.

**Features:**
- Page view counts (logged-in users only, or optionally all visitors)
- Referrer tracking
- UTM campaign tracking
- Session duration and bounce rate

> Analytics are intentionally kept as a side capability. SessionPilot's brand is **not** an analytics platform. This module exists for site owners who want basic stats without a separate plugin.

---

### Optional Module C — Notifications Module

> **Phase 2** — Extended notification channels beyond the core email alerts.

**Channels:**
- Webhook (HTTP POST to any endpoint)
- Discord (webhook integration)
- Slack (webhook integration)
- ntfy (self-hosted push)
- Gotify (self-hosted push)

---

### Optional Module D — GeoIP Module

> **Optional at any time** — Can be enabled in v1 without waiting for Phase 2.

**Features:**
- Country and region detection from IP
- Country field populated in sessions, activity logs, and device records
- Local `.mmdb` file lookup — no API calls ever

**Requirements:**
- MaxMind GeoLite2 database downloaded and stored in `storage/geoip/`
- Monthly refresh (MaxMind license requirement)
- Plugin works fully without this module — country fields show `—` when absent

---

### Future Modules (Phase 3+)

| Module | Description |
|---|---|
| Suspicious Activity Detection | Rule-based flags: impossible travel, rapid IP change, unusual login time |
| Heatmaps | Click and scroll tracking on page overlays |
| Purchase Journey Funnels | Multi-step WooCommerce funnel visualization |
| Multisite Aggregation | Single dashboard across a WP network |
| Agency Dashboard | Client management view |


