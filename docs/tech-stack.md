# Tech Stack

## Core Architecture

### Backend Framework

**[Roots Acorn](https://roots.io/acorn/)** -Laravel-style framework for WordPress plugins.

Acorn is the recommended foundation for SessionPilot because it provides:

- Laravel-style architecture inside WordPress
- Dependency injection container
- Service providers
- Event / listener system
- **Eloquent ORM** -database models with relationships, scopes, and query builder
- **Database migrations** -reproducible schema installs and upgrades
- **Laravel Queues** -background job processing (WP-Cron or Redis driver)
- Validation
- Artisan-style console commands (mapped to WP-CLI)
- Blade templating

All of this while remaining fully WordPress-native with no external server requirement.

### PHP Version

| Version | Status |
|---|---|
| PHP 8.2 | Minimum supported |
| PHP 8.3 | Preferred |

### Database

| Engine | Status |
|---|---|
| MySQL 5.7+ | Fully supported |
| MariaDB 10.3+ | Fully supported |
| SQLite | Avoid as primary dependency |

### Eloquent ORM

Eloquent is used **only for small, low-frequency admin-side models**:

- `Rule` model (session policies)
- `Device` model (device fingerprints)
- Settings access

**Eloquent is NOT used for:**
- Session tracking writes
- Activity log inserts
- Any high-frequency data writes

**Why this matters:** SessionPilot is data-heavy. Eloquent adds memory overhead, reflection, and query building overhead per call. On shared hosting under load, this becomes a real bottleneck. High-frequency write paths use raw `$wpdb` instead.

### `$wpdb` for High-Frequency Writes

All event recording and session tracking uses `$wpdb` directly with prepared statements:

```php
// Session write -fast, low overhead
global $wpdb;
$wpdb->insert(
    $wpdb->prefix . 'sp_sessions',
    [
        'user_id'       => $user_id,
        'token'         => $token,
        'ip_address'    => $ip,
        'user_agent'    => $ua,
        'created_at'    => current_time( 'mysql' ),
        'last_activity' => current_time( 'mysql' ),
    ],
    [ '%d', '%s', '%s', '%s', '%s', '%s' ]
);
```

This is faster, lower memory, and more predictable than routing every insert through Eloquent's full model layer.

### Laravel Queues

Background processing (GeoIP lookups, email alerts, log purging) is handled via Acorn queues.

| Driver | Use Case |
|---|---|
| `wp-cron` | Default -works on all shared hosting |
| `redis` | Optional -for high-traffic sites needing faster queue processing |

---

## Frontend Strategy

SessionPilot does **not** need a heavy frontend. There is no external SaaS dashboard, no Next.js app, no separate build server.

Everything lives inside `wp-admin`.

### Build Tool

**[Vite](https://vitejs.dev/)** -fast frontend build tool for bundling JS/CSS assets.

```json
// package.json (simplified)
{
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  }
}
```

Outputs compiled assets to `public/js/` and `public/css/`.

### Recommended Admin UI Stack (Primary -All Pages)

| Technology | Role |
|---|---|
| [Blade](https://laravel.com/docs/blade) | Templating (via Acorn) |
| [Alpine.js](https://alpinejs.dev/) | Lightweight reactivity (dropdowns, toggles, heartbeat) |
| [HTMX](https://htmx.org/) | Server-driven partial updates (log search, session list refresh) |

This combination keeps the plugin:
- Lightweight
- Fast to load on shared hosting
- Simple for contributors to work with
- No Node.js runtime requirement for users

> **This is the default for all 6 admin pages.** Dashboard, Sessions, Activity Logs, Rules, Devices, Settings -all Blade + Alpine + HTMX.

### React + TypeScript -Selective Use Only

React is used **only** where the interactivity requirement genuinely cannot be met by Alpine + HTMX:

- The session data table (TanStack Table -sortable, filterable, paginated)
- Charts in the Dashboard (Recharts)

**React is NOT used for:**
- Settings pages
- Rules configuration
- Any form that submits to a PHP endpoint
- Navigation or layout

---

## CSS and Styling Rules

**Do not load Tailwind CSS globally in `wp-admin`.** Tailwind's utility classes conflict with WordPress admin styles and can break other plugins' UI.

| Rule | Reason |
|---|---|
| Scope all CSS to `.sp-admin-wrap` (or equivalent wrapper) | Prevents conflicts with WP core and other plugins |
| Never import Tailwind's `@base` layer globally | Resets body/heading/link styles that WP admin relies on |
| Load styles only on SessionPilot admin pages | Use `$hook` check in `admin_enqueue_scripts` |
| Use WP admin CSS variables (`--wp-admin-theme-color`) | Stays consistent with user's active WP admin color scheme |
| Keep total CSS payload small | Shared hosting users often have slow connections |

If you use a utility CSS approach, scope it entirely inside `.sp-admin-wrap` using CSS Modules, a custom PostCSS prefix, or a scoped Tailwind config with a prefix (`sp-`).

---

| Category | Library | Notes |
|---|---|---|
| Data tables | [TanStack Table](https://tanstack.com/table) | Headless, perfect for logs & sessions |
| Icons | [Lucide Icons](https://lucide.dev/) | Clean, minimal SVG icon set |
| Charts | [Recharts](https://recharts.org/) | React-based, use only in chart/analytics views |

---

## Server-Side Libraries

### Browser / Device Detection

| Library | Purpose |
|---|---|
| [WhichBrowser/Parser](https://github.com/WhichBrowser/Parser-PHP) | Parses User-Agent into browser, engine, OS, device model (MIT) |
| [Mobile Detect](https://github.com/serbanghita/Mobile-Detect) | Lightweight `isMobile()` / `isTablet()` detection via UA regex |

> **Cache device parsing results.** Parsing a User-Agent string is not free. Cache the result per `user_agent` string using WordPress transients or object cache. Do not re-parse the same UA on every request.

### GeoIP (Location Detection)

| Library | Notes |
|---|---|
| [MaxMind GeoLite2](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data) | **Local `.mmdb` database only** -no API calls |

> The GeoLite2 `.mmdb` database file is downloaded and stored locally on the WordPress server. SessionPilot never calls the MaxMind API for lookups. The database must be refreshed monthly (GeoLite2 license requirement).

---

## WordPress Native APIs Used

Where possible, SessionPilot uses WordPress built-in APIs to stay compatible and maintainable.

| API | Usage |
|---|---|
| `WP_Session_Tokens` | Core session management -get, destroy, destroy all |
| `register_rest_route()` | REST API endpoint registration |
| `wp_schedule_event()` | Background cron tasks (cleanup, queue processing) |
| Settings API | Plugin configuration pages |
| `wp_mail()` | Email notifications |
| Nonces API | AJAX/form CSRF protection |
| Capabilities API | Permission checks on every action |
| `$wpdb` | Raw DB queries where Eloquent is not used (prepared statements only) |
