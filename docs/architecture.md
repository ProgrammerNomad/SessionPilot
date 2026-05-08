# Architecture

## Database Architecture

### Design Rules

- Do **not** overload `wp_options` with plugin data
- Do **not** store tracking data in `wp_postmeta`
- Use **custom tables** for all SessionPilot data

This ensures:
- Fast indexed queries
- Clean separation from WordPress core data
- Efficient auto-cleanup without affecting WP performance

---

### Custom Database Tables

**v1 uses exactly 5 tables.** Additional tables (visitors, events, WC carts, WC orders) are added in Phase 2 modules only when needed.

> Rationale: every extra table adds migration complexity, cleanup jobs, index maintenance, and query surface area. Start small.

| Table | Purpose | When |
|---|---|---|
| `wp_sp_sessions` | Active and historical session records | v1 |
| `wp_sp_activity_logs` | Admin and user activity audit trail | v1 |
| `wp_sp_rules` | Session policy rules per role or user | v1 |
| `wp_sp_devices` | Known device fingerprints per user | v1 |
| `wp_sp_settings` | Plugin configuration | v1 |
| `wp_sp_visitors` | Anonymous visitor profiles | Phase 2 |
| `wp_sp_events` | Raw page view / interaction events | Phase 2 |
| `wp_sp_wc_carts` | WooCommerce cart snapshots | Phase 2 |
| `wp_sp_wc_orders` | WooCommerce purchase journey data | Phase 2 |

---

### Schema Definitions

#### `wp_sp_sessions`

```sql
CREATE TABLE wp_sp_sessions (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL,
  token         VARCHAR(255)    NOT NULL,
  device_id     BIGINT UNSIGNED,
  ip_address    VARCHAR(45),
  user_agent    VARCHAR(255),
  created_at    DATETIME        NOT NULL,
  last_activity DATETIME,
  expires_at    DATETIME,
  logged_out_at DATETIME,
  INDEX (user_id),
  INDEX (device_id),
  INDEX (ip_address),
  INDEX (created_at)
);
```

#### `wp_sp_devices`

```sql
CREATE TABLE wp_sp_devices (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED NOT NULL,
  device_name VARCHAR(100),
  browser     VARCHAR(100),
  os          VARCHAR(100),
  created_at  DATETIME NOT NULL,
  last_seen   DATETIME,
  last_ip     VARCHAR(45),
  INDEX (user_id)
);
```

#### `wp_sp_activity_logs`

```sql
CREATE TABLE wp_sp_activity_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED,
  action_type VARCHAR(100)  NOT NULL,
  description TEXT,
  ip          VARCHAR(45),
  user_agent  VARCHAR(255),
  timestamp   DATETIME      NOT NULL,
  severity    ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
  INDEX (user_id),
  INDEX (action_type),
  INDEX (timestamp)
);
```

#### `wp_sp_rules`

```sql
CREATE TABLE wp_sp_rules (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_role        VARCHAR(100),
  user_id          BIGINT UNSIGNED,
  max_sessions     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  enforcement_mode ENUM('block_new','logout_oldest','logout_all') NOT NULL DEFAULT 'logout_oldest',
  idle_timeout     INT UNSIGNED COMMENT 'seconds; 0 = disabled',
  created_at       DATETIME NOT NULL,
  updated_at       DATETIME,
  INDEX (user_role),
  INDEX (user_id)
);
```

#### `wp_sp_alerts`

```sql
CREATE TABLE wp_sp_alerts (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    BIGINT UNSIGNED COMMENT 'admin/user to notify',
  event      VARCHAR(100) NOT NULL COMMENT 'e.g. limit_exceeded, suspicious_login',
  payload    JSON,
  status     ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL,
  sent_at    DATETIME,
  INDEX (user_id),
  INDEX (status),
  INDEX (created_at)
);
```

---

### Required Database Indexes

Every table must be indexed on the columns used in WHERE and ORDER BY clauses:

| Column | Tables |
|---|---|
| `user_id` | `wp_sp_sessions`, `wp_sp_activity_logs`, `wp_sp_devices` |
| `ip_address` | `wp_sp_sessions`, `wp_sp_activity_logs` |
| `created_at` | All tables |
| `updated_at` | `wp_sp_sessions` |
| `action_type` | `wp_sp_activity_logs` |

---

## System Design Rules

### 0. Authenticated Users Only

**SessionPilot tracks only logged-in WordPress users.** It does not track:

- Anonymous visitors
- Page views from non-authenticated users
- Frontend click events
- Session replay data
- Any data that requires cookie consent for anonymous tracking

This is a deliberate architectural constraint, not a limitation. It eliminates:
- Cookie consent management systems
- Visitor fingerprinting infrastructure
- Frontend analytics scripts loaded on every page
- Massive event storage tables
- Anonymous session tracking complexity

All `WP_Session_Tokens` integration works naturally because sessions only exist for authenticated users. This makes SessionPilot truly WordPress-native.

### 1. Local First

Everything must work without external services. The plugin functions fully offline on a self-hosted server.

### 2. Shared Hosting Friendly

Most WordPress users run on low-cost shared hosting. SessionPilot must:

- Work without Redis, Memcached, or Node.js
- Stay within typical PHP memory limits
- Not require CLI access or special server config

### 3. Graceful Degradation

If optional dependencies are unavailable, the plugin degrades gracefully:

| Missing Dependency | Behavior |
|---|---|
| Redis / object cache | Fall back to transients or direct DB |
| WP-Cron delayed | Queue processes on next cron trigger |
| GeoIP database absent | **Plugin works fully without it** — country field left null; no error shown |
| WooCommerce not active | WooCommerce module silently disabled |

### GeoIP Is Optional

The MaxMind GeoLite2 database is **optional**. SessionPilot must function perfectly without it.

- On shared hosting, the `.mmdb` file can be large and slow to update
- Users should not be required to obtain a MaxMind license key just to use the plugin
- Country detection is a nice-to-have, not a required feature

**Behavior without GeoIP:**
- `country` column stored as `NULL`
- Admin UI shows `—` or `Unknown` for country fields
- No errors, no warnings in logs

### 4. Event-Based Tracking — Authenticated Users Only

**v1 tracks ONLY authenticated user events:**
- Login events
- Logout events
- Admin activity (plugin/theme changes, role changes, settings changes)
- Session activity (last seen, current session state)

**v1 does NOT track:**
- Every page view (even for logged-in users — too much write volume)
- Full user journeys or navigation paths
- Frontend click or scroll events
- Anonymous visitor behavior of any kind

> **Hard rule:** Never insert a DB row on every page load. Track only meaningful events (login, logout, admin actions, session state changes). This is what keeps SessionPilot safe on shared hosting.

Tracking every pageview is where many plugins kill shared hosting sites. Full event tracking is deferred to the Phase 2 Analytics Module.

Attach to WordPress action hooks:

| Hook | Purpose |
|---|---|
| `wp_login` | Create session record, check concurrent limit |
| `wp_logout` | Mark session as logged out |
| `authenticate` | Enforce session limits before login completes |
| `set_current_user` | Detect role changes |
| `delete_user` / `wp_delete_user` | Clean up all sessions for that user |
| `update_option` | Log settings changes |
| `activated_plugin` / `deactivated_plugin` | Log plugin events |

### 5. Session Token Integration

SessionPilot wraps WordPress's native `WP_Session_Tokens` API:

```php
// Retrieve all sessions for a user
$manager = WP_Session_Tokens::get_instance( $user_id );
$sessions = $manager->get_all();

// Destroy a specific session by token
$manager->destroy( $token );

// Destroy all sessions for a user
$manager->destroy_all();
```

This ensures SessionPilot stays compatible with the WP core session layer and does not duplicate auth logic.

### 6. Minimal JavaScript

Keep the frontend lean. Do not ship megabytes of JS to the admin panel. Alpine.js + HTMX covers 90% of interactivity needs. React is used only for complex tables and charts.

---

## Performance Strategy

Tracking plugins can become database bottlenecks. SessionPilot must be engineered to stay fast.

### Batch Writes

Do not execute a database `INSERT` on every page load synchronously.

Instead:
1. Buffer events in memory (PHP transients or Redis) during the request
2. Process the buffer via `wp_schedule_single_event()` or a cron batch job
3. Bulk-insert buffered rows periodically

### Auto Cleanup

Old data is purged automatically based on configurable retention settings.

Default retention periods:

| Data Type | Default Retention |
|---|---|
| Sessions (closed) | 90 days |
| Activity logs | 180 days |

Keeping defaults conservative reduces cleanup job complexity and storage burden on shared hosting.

### Cached Browser Parsing

Browser string parsing (WhichBrowser, Mobile Detect) is expensive.

- Cache parsed results keyed by User-Agent hash
- Use WordPress transients or object cache
- Avoid re-parsing the same UA string twice

---

## Real-Time Architecture

SessionPilot does **not** require:
- External WebSocket servers
- Node.js
- Redis pub/sub

Real-time features are implemented via:

| Method | Use Case |
|---|---|
| REST API polling (AJAX) | Live visitor count, active sessions widget |
| WordPress REST API (`/wp-json/sessionpilot/v1/`) | All data endpoints |
| Server-Sent Events (SSE) | Optional future enhancement for live feed |

This keeps deployment simple — works on any shared host with PHP and MySQL.
