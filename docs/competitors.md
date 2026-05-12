# Competitor Analysis

## Overview

This document inventories the main WordPress session management and activity logging plugins, compares their free vs. paid features, and identifies the gaps that SessionPilot fills -entirely for free.

---

## Competitor Inventory

| Plugin | Author | Active Installs | Last Updated |
|---|---|---|---|
| [WP Security Audit Log](https://wordpress.org/plugins/wp-security-audit-log/) | Melapress | 300,000+ | Active |
| [Simple History](https://wordpress.org/plugins/simple-history/) | Pär Thernström | 300,000+ | Active |
| [WPForce Logout](https://wordpress.org/plugins/wp-force-logout/) | MiniPlugins | 8,000+ | Active |
| [Loggedin - Limit Concurrent Sessions](https://wordpress.org/plugins/loggedin/) | Duck Dev | 8,000+ | Active |
| [Sessions](https://wordpress.org/plugins/sessions/) | PerfOps | 900+ | Active |
| [SessionQuota](https://wordpress.org/plugins/sessionquota/) | HandyPlugins | <10 | Active |
| [Activity Log for WordPress (WinterLock)](https://wordpress.org/plugins/winterlock/) | activity-log.com | 60+ | Active |
| Inactive Logout | -| -| Active |

---

## Feature Matrix

| Feature | WP Security Audit Log | Loggedin | WPForce Logout | Sessions | SessionQuota | WinterLock | Simple History | Inactive Logout | **SessionPilot** |
|---|---|---|---|---|---|---|---|---|---|
| **Force Logout (admin)** | ❌ free / ✅ paid | ✅ free | ✅ free | ✅ free | ✅ free | ✅ free | ❌ | ✅ free | ✅ **free** |
| **Concurrent Login Limits** | ✅ paid | ✅ free (global) + paid add-ons | ✅ free (modes) | ✅ free (per role/IP) | ✅ free (global) + ✅ paid (per role) | ✅ free (basic) | ❌ | ✅ free (per role) | ✅ **free** |
| **Idle Timeout / Auto Logout** | ❌ | ❌ | ❌ free / ✅ Pro | ✅ free | ❌ free / ✅ Pro | ❌ | ❌ | ✅ free | ✅ **free** |
| **Browser-Close Logout** | ❌ | ❌ | ❌ free / ✅ Pro | ❌ | ❌ | ❌ | ❌ | ✅ Pro | ✅ **free** |
| **Device / IP Info on Sessions** | ✅ paid | ❌ | ✅ free | ✅ free | ✅ paid | ✅ free | ❌ | ✅ free | ✅ **free** |
| **Login / Logout History** | ✅ free (basic) / ✅ paid (export) | ❌ | ✅ free | ✅ free | ✅ paid | ✅ free / ✅ paid | ✅ free / ✅ paid | ✅ free | ✅ **free** |
| **Real-Time Active Users Dashboard** | ✅ paid | ❌ | ✅ free | ✅ free | ✅ paid | ✅ free | ❌ | ✅ Pro | ✅ **free** |
| **Export / Reports (CSV, JSON)** | ✅ paid | ❌ | ❌ | via WP-CLI | ✅ paid | ✅ paid | ✅ paid | ❌ | ✅ **free** |
| **Notifications / Alerts** | ✅ paid (email/SMS/Slack) | ❌ | ❌ | ❌ | ✅ paid (email) | ✅ paid (email/SMS) | ✅ paid (email) | ✅ free (email on fail) | ✅ **free** |
| **Role-Based Policies** | ✅ paid | ❌ free / paid add-ons | ❌ | ✅ free | ✅ paid | ✅ paid | ❌ | ✅ free (per role) | ✅ **free** |
| **WP-CLI Support** | ✅ paid | ❌ | ❌ | ✅ free | ✅ paid | ❌ | ✅ free | ❌ | ✅ **free** |
| **Multisite Support** | ✅ paid | ❌ | ❌ | ❌ | ✅ paid | ❌ | ❌ | ✅ free | Phase 3 |
| **Per-User Session Overrides** | ❌ | paid add-on | ❌ | ❌ | ✅ paid | ❌ | ❌ | ❌ | ✅ **free** |
| **REST API** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ **free** |

---

## Pricing Comparison

| Plugin | Free Tier | Paid Tier |
|---|---|---|
| WP Security Audit Log | Basic logging only | ~$149+/year |
| Simple History | Basic logging | $49-$99/year |
| WPForce Logout | Core force-logout | ~$20 (one-time) |
| Loggedin | Global limit only | Add-ons $29-$49 each |
| Sessions | Full (no paid version) | Free |
| SessionQuota | Global limit only | $49/$99 |
| WinterLock | Basic logging | Contact for quote |
| Inactive Logout | Basic timeout | $39/$99/$159/year |
| **SessionPilot** | **Everything** | **Free forever** |

---

## Key Competitive Gaps SessionPilot Fills

### Gap 1: Idle + Browser-Close Logout Together

No free plugin combines **idle timeout** and **browser-close logout** in the same free tier. WPForce Logout and Inactive Logout both lock these behind paid plans. SessionPilot includes both.

### Gap 2: Per-User and Per-Role Session Limits in One Place

Loggedin requires paid add-ons for per-user overrides. SessionQuota restricts per-role limits to Pro. SessionPilot provides global, per-role, and per-user limits all in the free core.

### Gap 3: Real-Time Dashboard + Session Control + Audit Log in One Plugin

Currently, a site owner needs to install at least 2-3 plugins to cover:
- Activity logging (WP Activity Log or Simple History)
- Session control (WPForce Logout or Loggedin)
- Live user monitoring (Sessions plugin)

SessionPilot replaces all three with one unified plugin.

### Gap 4: REST API

Not a single competitor exposes a REST API for session management. SessionPilot provides a full `/sp/v1/` endpoint set, enabling automation and third-party integrations.

### Gap 5: WP-CLI

Only the Sessions plugin provides CLI commands in its free version. SessionPilot includes comprehensive WP-CLI commands for all session operations.

---

## Positioning

> "SessionPilot does in one free plugin what competitors charge $50-$150/year for across multiple paid products."
