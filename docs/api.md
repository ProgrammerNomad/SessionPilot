# API Reference

SessionPilot exposes two interfaces for programmatic access: **WP-CLI commands** and a **REST API**.

Both require appropriate authentication and capability checks. All session-modifying operations require `manage_options` or a custom `sessionpilot_manage` capability.

---

## WP-CLI Commands

All commands are under the `wp sessionpilot` namespace.

---

### Sessions

#### List Sessions

```bash
wp sessionpilot sessions list [--user=<id>] [--active] [--format=<table|json|csv>]
```

| Option | Description |
|---|---|
| `--user=<id>` | Filter by WordPress user ID |
| `--active` | Show only sessions not yet logged out or expired |
| `--format` | Output format: `table` (default), `json`, `csv` |

**Example output:**

```
+----+---------+-------------------+----------------+-----------+---------------------+
| id | user_id | browser           | ip_address     | country   | last_activity       |
+----+---------+-------------------+----------------+-----------+---------------------+
| 12 | 3       | Chrome 124 / Win  | 192.168.1.x    | UK        | 2026-05-08 10:22:01 |
| 13 | 7       | Safari 17 / macOS | 203.0.113.x    | US        | 2026-05-08 10:18:44 |
+----+---------+-------------------+----------------+-----------+---------------------+
```

---

#### Kill Session(s)

```bash
wp sessionpilot sessions kill <session_id|all|oldest> [--user=<id>]
```

| Argument / Option | Description |
|---|---|
| `<session_id>` | Kill a specific session by ID |
| `all` | Kill all active sessions (site-wide or scoped to `--user`) |
| `oldest` | Kill the oldest active session for `--user` |
| `--user=<id>` | Scope to a specific user |

**Examples:**

```bash
# Kill a single session
wp sessionpilot sessions kill 12

# Kill all sessions for user 3
wp sessionpilot sessions kill all --user=3

# Kill the oldest session for user 7
wp sessionpilot sessions kill oldest --user=7
```

---

### Logs

#### List Activity Logs

```bash
wp sessionpilot logs list [--user=<id>] [--since=<date>] [--action=<type>] [--format=<table|json|csv>]
```

| Option | Description |
|---|---|
| `--user=<id>` | Filter by user ID |
| `--since=<date>` | Show logs after this date (e.g. `2026-01-01`) |
| `--action=<type>` | Filter by action type (e.g. `login`, `logout`, `role_change`) |
| `--format` | Output format |

---

### Rules

#### Set Session Rules

```bash
wp sessionpilot rules set [--role=<role>] [--user=<id>] [--max=<n>] [--mode=<block_new|logout_oldest|logout_all>] [--idle=<seconds>]
```

| Option | Description |
|---|---|
| `--role=<role>` | Apply rule to a WordPress role (e.g. `editor`) |
| `--user=<id>` | Apply rule to a specific user (overrides role rule) |
| `--max=<n>` | Maximum concurrent sessions allowed |
| `--mode` | What to do when limit is exceeded |
| `--idle=<seconds>` | Auto-logout after this many seconds of inactivity (`0` = disabled) |

**Examples:**

```bash
# Limit editors to 1 session; logout oldest when exceeded
wp sessionpilot rules set --role=editor --max=1 --mode=logout_oldest

# Allow user 5 up to 3 sessions
wp sessionpilot rules set --user=5 --max=3

# Set idle timeout to 30 minutes for subscribers
wp sessionpilot rules set --role=subscriber --idle=1800
```

---

### Alerts

#### Manually Trigger Pending Alerts

```bash
wp sessionpilot alerts send
```

Processes the alert queue immediately without waiting for the next cron run.

---

## REST API

**Base URL:** `/wp-json/sp/v1/`

All endpoints require:
- A valid WordPress nonce (`X-WP-Nonce` header) **or** Application Password authentication
- The requesting user must have `manage_options` (or `sessionpilot_manage`) capability for admin endpoints
- Users may read their own session data without admin capability

---

### Sessions

#### `GET /sp/v1/sessions`

List active sessions.

**Query Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `user_id` | int | Filter by user |
| `active` | bool | Only non-expired, non-logged-out sessions |
| `per_page` | int | Results per page (default: 20) |
| `page` | int | Page number |

**Response:**

```json
{
  "total": 42,
  "sessions": [
    {
      "id": 12,
      "user_id": 3,
      "user_display_name": "John Doe",
      "browser": "Chrome 124",
      "os": "Windows 11",
      "ip_address": "192.168.1.x",
      "country": "GB",
      "last_activity": "2026-05-08T10:22:01Z",
      "created_at": "2026-05-08T09:00:00Z",
      "expires_at": "2026-05-09T09:00:00Z"
    }
  ]
}
```

---

#### `DELETE /sp/v1/sessions/<session_id>`

Terminate a specific session.

**Response:**

```json
{ "success": true, "message": "Session 12 terminated." }
```

---

#### `POST /sp/v1/sessions/kill`

Bulk-terminate sessions.

**Request Body:**

```json
{
  "user_id": 3,
  "all_except_current": true
}
```

| Field | Type | Description |
|---|---|---|
| `user_id` | int | Target user (required) |
| `all_except_current` | bool | If `true`, preserve the current session |
| `session_ids` | array | Specific session IDs to kill (alternative to bulk) |

---

#### `GET /sp/v1/users/<user_id>/sessions`

List sessions for a specific user. Accessible by the user themselves or an admin.

---

### Activity Logs

#### `GET /sp/v1/logs`

Query the activity log.

**Query Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `user_id` | int | Filter by user |
| `action` | string | Filter by action type |
| `severity` | string | `info`, `warning`, or `critical` |
| `since` | string | ISO 8601 date — return logs after this time |
| `per_page` | int | Results per page |
| `page` | int | Page number |

---

### Rules

#### `GET /sp/v1/rules`

List all configured session rules.

#### `POST /sp/v1/rules`

Create or update a rule.

**Request Body:**

```json
{
  "user_role": "editor",
  "max_sessions": 2,
  "enforcement_mode": "logout_oldest",
  "idle_timeout": 3600
}
```

#### `DELETE /sp/v1/rules/<rule_id>`

Delete a rule.

---

### Alerts

#### `GET /sp/v1/alerts`

List alert queue entries and notification settings (admin only).

---

### Export

#### `POST /sp/v1/export`

Trigger a data export.

**Request Body:**

```json
{
  "type": "sessions",
  "format": "csv",
  "filters": {
    "since": "2026-01-01",
    "user_id": 3
  }
}
```

**Response:**

```json
{
  "success": true,
  "download_url": "https://example.com/wp-content/uploads/sessionpilot-export-xyz.csv",
  "expires_at": "2026-05-08T12:00:00Z"
}
```

Export files are temporary and auto-deleted after 1 hour.

---

## Authentication

### Nonce (for admin-facing requests from wp-admin)

```js
// In React / JS enqueued in admin
const response = await fetch('/wp-json/sp/v1/sessions', {
  headers: {
    'X-WP-Nonce': window.spData.nonce
  }
});
```

The nonce is passed to JS via `wp_localize_script()`.

### Application Passwords (for external integrations)

Use WordPress [Application Passwords](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/#application-passwords) for server-to-server or headless access:

```bash
curl -u "admin:xxxx xxxx xxxx xxxx" \
  https://example.com/wp-json/sp/v1/sessions
```

---

## Error Responses

All errors follow standard WP REST format:

```json
{
  "code": "rest_forbidden",
  "message": "You are not allowed to manage sessions.",
  "data": { "status": 403 }
}
```

| HTTP Status | Meaning |
|---|---|
| 200 | Success |
| 400 | Bad request (invalid parameters) |
| 401 | Not authenticated |
| 403 | Authenticated but not authorized |
| 404 | Resource not found |
| 429 | Rate limit exceeded |
| 500 | Server error |
