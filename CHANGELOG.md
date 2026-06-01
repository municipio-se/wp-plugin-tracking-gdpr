# Changelog

[Svensk version](CHANGELOG.sv.md)

## v2025.12.9 – 2026-06-01

- **Admin scripts** – Kept consent script rewriting out of WordPress admin so
  external editor dependencies can execute normally.
- **Iframe indexing** – Handled legacy or malformed iframe markup without a
  parsed replacement target during indexing.

## v2025.12.8 – 2026-04-29

- **Consent revisions** – Coerced localized consent revision values to numbers
  before passing them to frontend settings.

## v2025.12.7 – 2026-04-29

- **Consent revisions** – Added revisioned consent publishing so administrators
  can force renewed consent after settings change.

## v2025.12.6 – 2026-02-17

- **Mediaflow embeds** – Moved Mediaflow wrapper replacement into the service
  configuration to keep iframe replacement behavior service-driven.

## v2025.12.5 – 2026-02-17

- **Frontend styling** – Updated descriptions to inherit text color from their
  surrounding UI.

## v2025.12.4 – 2026-02-17

- **Content iframes** – Improved URL matching when parsing content iframes.

## v2025.12.3 – 2026-01-30

- **Content Security Policy** – Prevented a fatal error when the allowed
  `frame-src` setting was empty.

## v2025.12.2 – 2026-01-29

- **Content Security Policy** – Fixed additional allowed `frame-src` settings so
  every configured source is used.
- **Maintenance** – Removed debug code from the CSP handling.

## v2025.12.1 – 2026-01-16

- **Embedded content** – Fixed iframe updates on first consent.

## v2025.12.0 – 2025-12-30

- **Licensing** – Updated license files to comply with GPL-2.0-or-later.
- **Documentation** – Updated the README for the initial `2025.12` release line.
