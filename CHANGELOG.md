# Changelog

[Svensk version](CHANGELOG.sv.md)

## v2026.8.2 – 2026-09-01

- **Content Security Policy** – Added the documented `wstg_csp_sources` filter
  so other plugins can register narrowly scoped sources without taking over the
  frontend header, and imports existing `WpSecurity/Csp` registrations without
  requiring providers to depend on Tracking GDPR.

## v2026.8.1 – 2026-09-01

- **Composer identity** – Restored `municipio/wp-plugin-tracking-gdpr` as the
  package name after the repository moved to the Municipio organization. The
  current line remains isolated by its exact `2026.x` release tag, while LTS
  constraints on `^2025.12` continue to resolve only LTS releases.

## v2026.8.0 – 2026-08-30

- **Current Municipio support** – Ported the plugin to Municipio Deployment 5
  and Municipio theme 6 while retaining the existing settings and consent data.
- **Multisite and assets** – Added multisite-safe bootstrapping, ACF Pro checks,
  translation loading, and first-party asset detection for current Municipio.
- **Content Security Policy** – Let the plugin own one frontend policy and added
  narrowly scoped support for current Municipio bootstrap scripts.
- **Embedded content** – Added inert, consent-aware iframe placeholders,
  revocation unloading, and a per-site YouTube host choice.
- **Analytics** – Preserved cookieless Matomo measurement before consent and
  made direct tracker and Tag Manager startup deterministic. Container consent
  is attached to each configured tracker without creating an extra default
  tracker that can rewrite cookies with weaker attributes during unload. The
  container consent is persisted only after the configured tracker has applied
  its cookie security attributes. Consent events are not replayed during
  navigation, so analytics cookies survive page views without redundant pings.
- **Network requests** – Added an explicit, service-owned request gate for
  declared fetch and beacon destinations.
- **Accessibility** – Coordinated the Municipio menu drawer with the consent
  dialog and restored focus when the dialog closes.
- **Release line** – Established the first `2026.x` release for current
  Municipio; the `v25.x` line remains the Municipio LTS source branch.

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
