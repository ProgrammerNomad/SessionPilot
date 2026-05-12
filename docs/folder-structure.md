# Folder Structure

Recommended directory layout for the `sessionpilot/` plugin directory.

```
sessionpilot/
│
├── app/                            # Application layer (Acorn / Laravel-style)
│   ├── Console/                    # Artisan-style CLI commands
│   │   └── Commands/
│   ├── Http/                       # HTTP layer
│   │   ├── Controllers/            # REST API controllers
│   │   └── Middleware/             # Request middleware (auth, rate limiting)
│   ├── Models/                     # Eloquent-style models for custom tables
│   │   ├── Session.php             # v1
│   │   ├── ActivityLog.php         # v1
│   │   ├── Device.php              # v1
│   │   ├── Rule.php                # v1
│   │   ├── Visitor.php             # Phase 2
│   │   ├── WcCart.php              # Phase 2
│   │   └── Notification.php        # Phase 2
│   ├── Services/                   # Business logic services
│   │   ├── SessionService.php      # v1
│   │   ├── DeviceService.php       # v1
│   │   ├── ActivityLogService.php  # v1
│   │   ├── NotificationService.php # v1 (email only)
│   │   ├── VisitorService.php      # Phase 2
│   │   ├── GeoIPService.php        # Phase 2
│   │   └── WooCommerceService.php  # Phase 2
│   ├── Repositories/               # Data access layer (wraps DB queries)
│   ├── Actions/                    # Single-responsibility action classes
│   │   ├── ForceLogoutUser.php
│   │   ├── LogAdminActivity.php
│   │   └── CleanupExpiredSessions.php
│   ├── Events/                     # Domain events
│   │   ├── SessionStarted.php      # v1
│   │   ├── SessionExpired.php      # v1
│   │   ├── SuspiciousLoginDetected.php  # Phase 2
│   │   └── CartAbandoned.php            # Phase 2
│   ├── Listeners/                  # Event listeners
│   ├── Jobs/                       # Background queue jobs
│   │   ├── PurgeExpiredData.php    # v1
│   │   ├── SendNotification.php    # v1 (email only)
│   │   └── ProcessEventBuffer.php  # Phase 2
│   ├── Policies/                   # Authorization policies
│   ├── Notifications/              # Notification channel handlers
│   │   ├── EmailNotification.php   # v1
│   │   ├── WebhookNotification.php # Phase 2
│   │   ├── DiscordNotification.php # Phase 2
│   │   └── SlackNotification.php   # Phase 2
│   └── Support/                    # Helpers, traits, utilities
│       ├── Helpers.php
│       └── IpAnonymizer.php
│
├── bootstrap/                      # Acorn bootstrap files
│   └── app.php
│
├── config/                         # Configuration files
│   ├── sessionpilot.php            # Main plugin config
│   ├── privacy.php
│   ├── tracking.php                # Phase 2
│   └── notifications.php           # Phase 2 (email config lives in sessionpilot.php)
│
├── database/
│   └── migrations/                 # Database migration files
│       ├── 0001_create_sp_sessions_table.php       # v1
│       ├── 0002_create_sp_activity_logs_table.php  # v1
│       ├── 0003_create_sp_rules_table.php          # v1
│       ├── 0004_create_sp_devices_table.php        # v1
│       ├── 0005_create_sp_settings_table.php       # v1
│       ├── 0006_create_sp_visitors_table.php       # Phase 2
│       ├── 0007_create_sp_events_table.php         # Phase 2
│       ├── 0008_create_sp_wc_carts_table.php       # Phase 2
│       └── 0009_create_sp_wc_orders_table.php      # Phase 2
│
├── public/                         # Compiled frontend assets (dist output)
│   ├── js/
│   └── css/
│
├── resources/                      # Source frontend files
│   ├── views/                      # Blade templates
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── sessions/
│   │   ├── logs/
│   │   ├── rules/
│   │   ├── devices/
│   │   ├── settings/
│   │   ├── visitors/               # Phase 2
│   │   └── woocommerce/            # Phase 2
│   ├── js/                         # Alpine.js + HTMX source
│   │   ├── dashboard.js
│   │   └── components/
│   ├── css/                        # Scoped CSS (no global Tailwind)
│   └── icons/                      # Local Lucide SVG icons
│
├── routes/                         # Route definitions
│   ├── api.php                     # REST API routes
│   └── admin.php                   # Admin menu routes
│
├── storage/                        # Runtime storage
│   ├── logs/                       # Plugin-level error logs
│   ├── cache/                      # Cached browser parse results
│   └── geoip/                      # MaxMind GeoLite2 .mmdb file
│
├── tests/                          # Automated tests
│   ├── Unit/
│   └── Feature/
│
├── vendor/                         # Composer dependencies
│
├── composer.json                   # PHP dependency manifest
├── package.json                    # JS dependency manifest
├── vite.config.js                  # Frontend build config
└── sessionpilot.php                # Plugin entry point
```

---

## Key Design Notes

### `app/` Structure

Follows the Acorn / Laravel convention. Each subdirectory has a single responsibility. Avoid putting logic directly in controllers -delegate to Services and Actions.

### `database/migrations/`

Each migration is numbered and timestamped. Run via Acorn's migration command or automatically on plugin activation.

### `storage/geoip/`

The MaxMind GeoLite2 `.mmdb` file lives here. It is updated on a scheduled cron job. Never bundled in the plugin ZIP -downloaded post-install.

### `resources/views/`

Blade templates only. No PHP mixed with HTML elsewhere. All data is passed from controllers to views -no business logic in templates.

### Plugin Entry Point

`sessionpilot.php` is minimal -it bootstraps Acorn and registers the service providers. All real logic lives in `app/`.
