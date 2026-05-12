# SessionPilot -Documentation

> **Smart session intelligence for WordPress**  
> Self-hosted · Privacy-first · Open source · No cloud lock-in

---

## Table of Contents

| Document | Description |
|---|---|
| [Vision & Philosophy](./vision.md) | Core philosophy, what SessionPilot is and is not, positioning |
| [Competitors](./competitors.md) | Competitor inventory, feature matrix, gap analysis |
| [Product Modules](./modules.md) | All feature modules in detail |
| [Tech Stack](./tech-stack.md) | Recommended technologies, libraries, and tools |
| [Architecture](./architecture.md) | Database schema (with SQL), system design rules, performance & real-time strategy |
| [Folder Structure](./folder-structure.md) | Recommended plugin directory layout |
| [Security](./security.md) | Security strategy, GDPR compliance, threat model |
| [UI/UX Direction](./ui-ux.md) | Dashboard design philosophy and key views |
| [API Reference](./api.md) | WP-CLI commands and REST API endpoint specification |
| [Roadmap](./roadmap.md) | MVP scope, implementation milestones, Phase 2 & Phase 3 |
| [Contributing](./contributing.md) | Dev setup, coding standards, testing, CI/CD, Git workflow |
| [Sustainability](./sustainability.md) | Open source model and future income strategy |

---

## Quick Summary

**SessionPilot** is a fully self-hosted, privacy-first WordPress plugin for:

- Session tracking & management (force logout, concurrent limits, idle timeout)
- Online users dashboard (who is logged in right now)
- Admin activity logging (audit trail for all key WordPress events)
- Session rules (per-role and per-user policies)
- Device tracking (browser, OS, IP per session)

Phase 2 will add WooCommerce intelligence, extended analytics, and additional notification channels. Phase 3 will add AI/anomaly detection and multi-server aggregation.

It is built on [Roots Acorn](https://roots.io/acorn/) for a Laravel-style architecture while remaining 100% WordPress-native.

---

## Tagline

*"Open-source session & activity monitoring for logged-in WordPress users."*
