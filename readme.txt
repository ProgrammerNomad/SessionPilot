=== SessionPilot ===
Contributors: programmernomad
Tags: session, security, activity-log, user-management, login
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Open-source session and activity monitoring for logged-in WordPress users. Self-hosted, privacy-first, no cloud dependency.

== Description ==

SessionPilot gives WordPress administrators full visibility into who is logged in, from where, and on which device - with the ability to force-logout any session instantly.

**Core Features:**

* Real-time session dashboard - see all active sessions with browser, OS, device type, and IP
* Force-logout any session or all sessions with one click
* Activity log - tracks login, logout, failed logins, role changes, plugin/theme changes, password resets
* Session rules - set per-role or per-user max session limits with enforcement modes (logout oldest, block new, logout all)
* Idle timeout and heartbeat-based browser-close detection
* Device fingerprinting using browser and OS detection
* CSV export for activity logs
* Email alerts on repeated login failures and session limit violations
* IP anonymization (GDPR-friendly)
* WP-CLI commands for all major operations
* Full REST API under `/wp-json/sp/v1/`
* Automatic cleanup cron for expired sessions and old logs
* Self-hosted - no external services, no cloud, no tracking

**Technical Stack:**

* PHP 8.2+ with strict types
* Roots Acorn 4 (Laravel-style service container for WordPress)
* Eloquent ORM for admin models
* Alpine.js + HTMX for reactive admin UI
* Vite-compiled assets (no jQuery dependency)
* WhichBrowser + Mobile Detect for user-agent parsing

== Installation ==

= From ZIP (recommended for self-hosted) =

1. Download `sessionpilot-1.0.0.zip` from the [GitHub Releases page](https://github.com/ProgrammerNomad/SessionPilot/releases)
2. In wp-admin, go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Click **Activate Plugin**
5. Navigate to **SessionPilot** in the left menu

= Requirements =

* WordPress 6.4 or higher
* PHP 8.2 or higher
* MySQL 5.7+ or MariaDB 10.3+

= What gets created =

On activation, SessionPilot creates 5 tables in your database:

* `wp_sp_sessions` - active and historical sessions
* `wp_sp_activity_logs` - event audit trail
* `wp_sp_rules` - per-role/per-user session rules
* `wp_sp_devices` - device fingerprint records
* `wp_sp_settings` - plugin configuration

All tables are removed cleanly when the plugin is deleted.

== Frequently Asked Questions ==

= Does this plugin send data anywhere? =

No. All data stays in your own database. There are no external API calls, no analytics pings, no cloud services of any kind.

= Is browser-close logout reliable? =

No, and the UI says so clearly. Browser-close detection is best-effort via heartbeat. Browser crashes, mobile app backgrounding, and JavaScript-disabled environments will not trigger immediate logout. Sessions are expired by the cleanup cron based on your configured heartbeat grace period (default: 2 minutes after last heartbeat).

= Can I use WP-CLI? =

Yes. The plugin registers commands under `wp sessionpilot`:

`wp sessionpilot sessions list`
`wp sessionpilot sessions kill --user=5`
`wp sessionpilot logs list --since=24h`
`wp sessionpilot rules set --role=subscriber --max=2`
`wp sessionpilot devices list`

= Does it work with multisite? =

Single-site only in v1. Multisite aggregation is on the roadmap.

= Does it work with WooCommerce? =

Yes, but there are no WooCommerce-specific features in v1. WooCommerce Intelligence (cart tracking, abandoned cart detection) is planned as an optional module.

= What PHP version is required? =

PHP 8.2 minimum. Tested on PHP 8.2 and 8.3.

== Screenshots ==

1. Dashboard - online users, active sessions, failed login count, recent activity feed
2. Sessions - full session table with force-logout per row and bulk logout
3. Activity Logs - filterable audit trail with CSV export
4. Rules - per-role and per-user session limit configuration
5. Devices - device fingerprint tracking per user
6. Settings - retention, idle timeout, IP anonymization, email alerts

== Changelog ==

= 1.0.0 =
* Initial release
* Session tracking and force-logout
* Activity logging (login, logout, failed login, role change, plugin/theme events, password reset)
* Session rules with enforcement modes
* Device fingerprinting
* CSV export for logs
* Email alerts for failed logins and session limit violations
* IP anonymization
* WP-CLI commands
* Full REST API
* Automatic cleanup cron
* PHPUnit test suite

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade path needed.
