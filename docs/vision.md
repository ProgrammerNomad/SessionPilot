# Vision & Philosophy

## Project Identity

**Name:** SessionPilot

### Tagline

> **Open-source session & activity monitoring for logged-in WordPress users**

---

## Primary Target Audience

**WordPress admins and agencies** -because they are the universal layer.

Admins and agencies manage:
- WooCommerce sites
- LMS sites
- Membership sites
- Company blogs and portfolios
- Multisite installs

If SessionPilot serves admins and agencies well, every other user type benefits naturally. Building for "everyone" creates a bloated plugin. Building for admins first creates a focused, excellent product.

### Secondary Users

| User Type | How They Benefit |
|---|---|
| WooCommerce store owners | Session control + activity logs protect customer accounts |
| Membership site owners | Concurrent session limits prevent account sharing |
| LMS platform operators | Per-user session policies for student access control |
| Security-conscious site owners | Audit trail for all admin actions |

---

## Core Philosophy

SessionPilot must be:

- Fully **open source**
- Fully **self-hosted**
- **No SaaS dependency**
- **No cloud lock-in**
- **No telemetry**
- **No feature paywalls**
- **Lightweight and fast** on shared hosting
- **WordPress-native**
- **Focused** -does one thing excellently

---

## Why This Matters

Most competitors are moving in the wrong direction:

| Problem | Description |
|---|---|
| Cloud-dependent | Data leaves the user's server |
| Subscription-focused | Core features locked behind paywalls |
| Bloated | Heavy JS, external requests, slow dashboards |
| Enterprise-only | Too complex for small site owners |
| Privacy-unfriendly | GDPR-hostile, external trackers |

SessionPilot takes the opposite approach -local-first, simple, and completely under the user's control.

---

## What SessionPilot IS

SessionPilot is a **logged-in user session & activity management platform** for WordPress.

It tracks and controls authenticated WordPress users -admins, editors, authors, subscribers, WooCommerce customers, LMS students, membership users.

**It is:**
- A session management and control system
- An admin activity audit log
- An online user dashboard
- A login & session policy engine
- A device tracking view

---

## What SessionPilot IS NOT

**Not competing with analytics tools:**
- ~~Google Analytics~~
- ~~Plausible~~
- ~~Matomo~~
- ~~Clarity~~
- ~~Hotjar~~

**Not a security product:**
- ~~Antivirus~~
- ~~Firewall~~
- ~~Malware scanner~~
- ~~Enterprise SIEM / Security Intelligence platform~~

**Not anonymous visitor tracking.** SessionPilot does not track page views, clicks, or sessions for non-logged-in visitors. That is a completely different product category.

> **This is a deliberate and important constraint.** Removing anonymous tracking eliminates: cookie tracking, frontend analytics scripts, visitor fingerprinting, consent management systems, heatmap pipelines, and massive event storage. This makes SessionPilot vastly simpler, faster, and more maintainable.

> **Naming rule:** Avoid terms like "intelligence", "AI", "behavior engine", "anomaly engine", "security intelligence". Use: *activity monitoring*, *session monitoring*, *access tracking*, *audit logs*, *login tracking*. These are clearer, safer, and create the right user expectations.

---

## Positioning Statement

> **SessionPilot -Open-source session & activity control center for WordPress**

This position is:
- Less crowded than analytics (vs. GA / Plausible / Matomo)
- More achievable than a full security suite
- Easier to support and scale
- Clearer value proposition for the target user

No well-known open-source plugin currently fills this role completely and freely.

---

## Why This Niche Works

| Dimension | Result |
|---|---|
| Technical complexity | Lower -no pageview pipelines, no event streams, no visitor tracking |
| DB write volume | Lower -only authenticated user events |
| Privacy / GDPR | Simpler -users are already authenticated, expected operational tracking |
| WP integration | Deeper -can rely fully on `WP_Session_Tokens` |
| Competition | Less crowded -session managers are fewer and weaker than analytics tools |
| Support burden | Lower -focused scope means fewer edge cases |

---

## Competitive Landscape

SessionPilot is **not** trying to replace these tools:

| Tool | Their Lane |
|---|---|
| Google Analytics | Website analytics (anonymous visitors) |
| Plausible / Matomo | Privacy-first website analytics |
| Hotjar / Clarity | Heatmaps & session replay |
| Metorik | WooCommerce SaaS analytics |

SessionPilot **does** fill the gap left by weaker session-focused plugins:

| Tool | Their Weakness |
|---|---|
| WP Activity Log | Logs only, no session control, premium paywall |
| Inactive Logout | Idle timeout only, no logs, no device view |
| WPForce Logout | Force logout only, no monitoring |
| Defender Pro | Security product, not a session manager |

SessionPilot fills the gap: **session control + access monitoring + activity logging** -all in one plugin, free, self-hosted.
