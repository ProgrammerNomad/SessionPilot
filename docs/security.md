# Security Strategy

> Tracking plugins are high-risk targets. SessionPilot must be secure by default.

---

## Required Protections

### 1. Capability Checks

Every admin-facing action must verify that the current user has the required WordPress capability before executing.

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Unauthorized', 'sessionpilot' ) );
}
```

Never assume a user is authorized based on being logged in alone.

### 2. Nonces

Every AJAX request and every form submission must include and verify a WordPress nonce.

```php
// Generating
wp_nonce_field( 'sessionpilot_action', 'sp_nonce' );

// Verifying
check_ajax_referer( 'sessionpilot_action', 'sp_nonce' );
```

REST API endpoints must use nonce or application password authentication.

### 3. Prepared Statements

**No raw SQL.** All database queries must use `$wpdb->prepare()` or the Acorn/Eloquent query builder with bound parameters.

```php
// Correct
$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}sp_sessions WHERE user_id = %d",
        $user_id
    )
);

// Never do this
$wpdb->get_results( "SELECT * FROM wp_sp_sessions WHERE user_id = $user_id" );
```

### 4. Output Escaping

**Escape everything** before outputting to HTML.

| Context | Function |
|---|---|
| HTML text | `esc_html()` |
| HTML attributes | `esc_attr()` |
| URLs | `esc_url()` |
| JavaScript | `esc_js()` |
| Textarea content | `esc_textarea()` |

Never output user-controlled data directly into HTML.

### 5. Rate Limiting

Protect high-frequency endpoints from abuse:

| Endpoint / Action | Limit |
|---|---|
| Login tracking | Max 10 requests/minute per IP |
| Export downloads | Max 5 exports/hour per user |
| REST API polling | Max 60 requests/minute per IP |
| Force logout API | Capability check + nonce required |

Implement using WordPress transients as a lightweight counter store.

---

## Session Security

### Concurrent Session Control

- Enforce per-role limits on simultaneous active sessions
- Terminate oldest session when limit is exceeded (configurable: oldest vs. newest)

### Session Token Integrity

- Use `WP_Session_Tokens` to track and invalidate sessions
- Never store session tokens in custom tables — reference them by ID only
- Implement session fixation protection by regenerating tokens on privilege changes

### Idle Timeout

- Track `last_activity` timestamp per session
- Expire sessions that have been idle longer than the configured threshold
- Process expiry via WP-Cron to avoid blocking page requests

### Browser-Close Detection

Browser-close logout is a **best-effort convenience feature**, not a security control. It is unreliable in the following conditions:

- Browser crashes (no unload event fired)
- Mobile browsers backgrounding tabs
- Safari's handling of `beforeunload` / `unload`
- JavaScript disabled

**Implementation:** JS heartbeat stops when `visibilitychange` fires or `beforeunload` triggers. Server-side cron expires sessions that miss heartbeats beyond the grace period (default: 2 minutes). The UI labels this feature clearly as best-effort.

**Security implication:** Do not rely on browser-close detection as a security enforcement mechanism. Use idle timeout for session security guarantees.

---

## Threat Model

| Threat | Mitigation |
|---|---|
| Unauthorized access to logs | Capability checks on every view |
| SQL injection via tracking data | Prepared statements everywhere |
| CSRF on force-logout actions | Nonce verification on all state-changing requests |
| IP spoofing via `X-Forwarded-For` | Configurable trusted proxy list; fallback to `REMOTE_ADDR` |
| Brute-force on export endpoint | Rate limiting per user |
| Stored XSS in log entries | Escape all output; sanitize on input |
| Clickjacking on admin pages | WordPress core handles `X-Frame-Options` |
| Session hijacking | Token invalidation on force-logout; regenerate tokens on privilege changes |
| Unauthorized REST API access | All endpoints require valid nonce or application password + capability check |

---

## Session Hijacking Prevention

- **Token invalidation:** When a session is force-logged-out, the corresponding `WP_Session_Tokens` entry is destroyed immediately — not just marked in the SessionPilot table.
- **Privilege escalation:** If a user's role is elevated, regenerate their session token to prevent fixation attacks.
- **Secure cookies:** WordPress's own `SECURE_AUTH_COOKIE` / `AUTH_COOKIE` with `HttpOnly` and `SameSite=Lax` attributes are respected. Do not bypass or duplicate WP's cookie handling.

---

## GDPR Compliance

| Requirement | Implementation |
|---|---|
| Right to access (Art. 20) | Export all data for a specific user via the Export module |
| Right to erasure (Art. 17) | Delete all sessions, logs, and device records for a user on request |
| Data minimization | Configurable: disable IP logging, disable UA logging individually |
| IP anonymization | Mask last octet by default (e.g. `192.168.1.x`) |
| Retention limits | Auto-purge after configurable number of days per data type |
| Consent | Cookie-free tracking mode available; no data sent externally |

### MaxMind GeoLite2 Compliance

- The `.mmdb` database file must be updated monthly (MaxMind license requirement)
- Old database files must be deleted within 30 days of receiving a new version
- Never send IPs to the MaxMind API — use the local file lookup only

---

## Data Security

- **No data leaves the server.** All SessionPilot data is stored in the local WordPress database.
- **IP anonymization** available out of the box (last octet masked by default).
- **Auto-purge** removes old data automatically — sensitive data does not accumulate indefinitely.
- Sensitive fields (full IPs, user agents) can be individually disabled in settings.
- **No telemetry.** SessionPilot never phones home, checks for updates via external servers, or sends usage statistics.

