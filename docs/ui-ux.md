# UI/UX Direction

## Design Philosophy

This is SessionPilot's biggest opportunity to stand out.

Most competing plugins look:
- Outdated and cluttered
- Enterprise-ugly
- Like a WordPress plugin from 2014

SessionPilot should feel like a **modern SaaS product** inside `wp-admin`.

**Design inspirations:**

| Product | What to borrow |
|---|---|
| [Cloudflare](https://cloudflare.com) | Clean data density, status indicators |
| [Plausible Analytics](https://plausible.io) | Minimal, readable, fast |
| [Linear](https://linear.app) | Crisp typography, subtle color use |
| [Vercel](https://vercel.com) | Dark-mode excellence, data visualization |

---

## Design Principles

1. **Fast** -Pages load instantly. No spinner on every action.
2. **Minimal** -Show only what matters. No clutter.
3. **Readable** -Numbers are legible. Status is obvious at a glance.
4. **Consistent** -Same patterns across all views.
5. **Responsive** -Usable on tablet-sized screens within wp-admin.

---

## CSS and Styling Rules

SessionPilot's CSS must **not conflict with WordPress admin or other plugins**.

- **No global Tailwind CSS.** Do not load Tailwind utility classes globally in `wp-admin`. Tailwind conflicts with WP admin styles and can break other plugin UI.
- **Scope all styles.** Every CSS rule must be scoped to a SessionPilot-specific container class (e.g., `.sp-admin-wrap`).
- **Minimal CSS footprint.** Load styles only on SessionPilot pages. Use `$hook` checks in `admin_enqueue_scripts`.
- **Use WordPress admin color variables.** Rely on WP admin's native CSS variables (e.g., `--wp-admin-theme-color`) for consistency.

---

## Admin Menu Structure

```
SessionPilot
├── Dashboard
├── Sessions
├── Activity Logs
├── Rules
├── Devices
└── Settings
```

**Six pages. That is enough for v1.** Do not create submenu pages, analytics labyrinths, or enterprise navigation trees.

Optional modules (WooCommerce, Analytics) add their own top-level or submenu entries **only when the module is enabled**.

---

## Page 1 -Dashboard

### Widgets

| Widget | Data Shown |
|---|---|
| **Online Users** | Count of logged-in users active in the last 5 minutes |
| **Active Sessions** | Total active session count |
| **Recent Activity** | Last 5 admin log entries |
| **Failed Logins** | Count in last 24 hours with severity indicator |

> **Not in v1 dashboard:** WooCommerce activity, top pages, traffic sources, analytics charts, heatmaps. Those are Phase 2 module widgets.

---

## Page 2 -Sessions

A full-featured session management table. The most important page in the plugin.

### Columns

| Column | Description |
|---|---|
| User | Avatar + display name |
| Browser | Icon + browser name |
| OS | Operating system name |
| IP Address | Anonymized or full (based on settings) |
| Country | Flag + country name (if GeoIP available) |
| Last Seen | Relative time (e.g., "2 minutes ago") |
| Duration | How long the session has been active |

### Row Actions

- **Logout** -Force-terminate the session immediately
- **Inspect** -Open full session detail

### Table Features

- Auto-refresh (configurable interval)
- Search by user, IP, browser
- Filter by role, status (active / idle / expired)
- Bulk logout selected
- CSV export

---

## Page 3 -Activity Logs

Per-user chronological activity view. Inspired by GitHub's event timeline and Stripe's event log.

### Log Entry Anatomy

```
[Icon]  [Action Description]                     [Timestamp]
        Sub-detail line (e.g., "from IP 192.168.x.x")
```

### Severity Colour Coding

| Level | Colour | Examples |
|---|---|---|
| Info | Blue | Logins, logouts |
| Warning | Amber | Role changes, password resets |
| Critical | Red | Failed logins, plugin deletions, suspicious access |

### Filters

- By user
- By action type
- By severity level
- By date range
- Full-text search

---

## Page 4 -Rules

Session policy configuration. Simple, structured, no clutter.

### Layout

- Global defaults (max sessions per user, enforcement mode, idle timeout, browser-close timeout)
- Per-role overrides table
- Per-user overrides table (optional)

### Enforcement Mode Selector

Radio or select for each rule row:

- Block new sessions (reject login when limit reached)
- Logout oldest session (auto-terminate oldest when new login arrives)
- Logout all other sessions (one session per user strictly)

---

## Page 5 -Devices

Per-user device history view. Inspired by the Google Account security panel.

### Layout

Grouped by user. Each user row expands to show all known sessions and devices:

```
John Doe (admin)
└── Chrome 124 · Windows 11 · London, UK  ·  Active 3 min ago    [Logout]
└── Safari 17  · iPhone 15  · New York, US  ·  Last seen 2d ago  [Logout]

Jane Smith (editor)
└── Firefox 126 · macOS 14  · Paris, FR  ·  Active 1 hr ago      [Logout]
```

### Features

- Search by user, browser, OS, country
- Filter by device type (desktop / mobile / tablet)
- Logout individual session from this view
- Shows country only if GeoIP Module is enabled

---

## Page 6 -Settings

Plugin configuration. All options in one page, grouped by section.

### Sections

| Section | Settings |
|---|---|
| **Session** | Idle timeout, browser-close timeout, cleanup frequency |
| **Privacy** | IP anonymization, cookie-free mode, data retention period |
| **GeoIP** | Upload `.mmdb` file, last updated timestamp, enable/disable |
| **Alerts** | Admin email for alerts, which events trigger email |
| **Export** | Export sessions CSV, export activity logs CSV |
| **Danger Zone** | Purge all session data, purge all logs |

---

## Empty States

Every view needs a clear empty state -no blank tables.

Example for Sessions:

```
No active sessions found.
─────────────────────────────────
Sessions appear here as users log in.
You can adjust the session timeout in Settings.
```

---

## Notifications

In-admin banner for critical events (no browser push notifications in v1):

```
⚠  Multiple failed logins detected for admin@example.com.
   [View Logs]  [Dismiss]
```

---

## Scope Discipline Reminder

> v1 has **exactly 6 admin pages**: Dashboard, Sessions, Activity Logs, Devices, Rules, Settings.
>
> Do not create 15 submenu pages, an analytics labyrinth, or enterprise-style navigation. Keep it simple.
>
> Optional modules (WooCommerce, Analytics) add their own pages **only when that module is installed and enabled**. Core navigation never changes based on optional module state.
