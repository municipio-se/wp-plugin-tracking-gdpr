# Whitespace Tracking & GDPR

[Svensk version](README.sv.md)

Originally developed for
[Municipio LTS](https://github.com/municipio-se/municipio-lts), with a current
Municipio compatibility layer maintained on `main`. Whitespace Tracking & GDPR
adds a cookie consent dialog, Matomo tracking settings, service-based iframe
handling, and Content Security Policy support for Municipio sites.

## Requirements

Whitespace Tracking & GDPR requires its PHP dependencies to be installed through
Composer and Advanced Custom Fields PRO to be installed and active.

The runtime Composer dependency is `imangazaliev/didom`. ACF Pro is supplied by
the host site and is deliberately not replaced by the free ACF package. Frontend
assets are built from TypeScript and CSS sources with pnpm, Vite, TypeScript,
and Vanilla CookieConsent.

## Package and Release Policy

The current Municipio release line is published from `main` as
`municipio/wp-plugin-tracking-gdpr`. A site must declare a scoped Composer `vcs`
repository for `https://github.com/municipio-se/wp-plugin-tracking-gdpr.git` and
require an exact dated `2026.x` release tag. The `v25.x` branch remains the LTS
source line, and LTS constraints on `^2025.12` cannot select a current release.
Version `2026.9.0` is the current release with the canonical repository
identity.

## Migrating from the LTS Package

Replace the old version constraint with an exact current release tag and point
the repository allowlist at the canonical Municipio VCS repository. The shared
package identity and installer directory avoid parallel installations. Keep the
plugin network-active and verify every blog in the network.

No destructive data migration is required. The plugin file, installer path, ACF
option names, service keys, Matomo settings, consent revision, and host-scoped
consent cookie remain compatible. Existing sites keep `youtube.com` as their
default embed host unless an administrator explicitly selects the no-cookie
host. Rollback consists of restoring the previous Composer lock file and
repository configuration; the retained settings can then be read by the LTS
package again.

## Features

- **Cookie consent dialog** – Enqueues Vanilla CookieConsent with service,
  category, text, Matomo, and consent revision settings localized from
  WordPress.
- **Service registry** – Registers services such as YouTube, Vimeo, Mediaflow,
  Visma Recruit, and the plugin's own required service.
- **Embedded content handling** – Replaces supported content iframes with
  consent-aware `wstg-iframe` placeholders before the original iframe is loaded,
  including Municipio oEmbed and Modularity iframe output.
- **Iframe report** – Adds an admin report for published post, page, and
  Municipio iframe module embeds detected on the site.
- **Matomo settings** – Adds settings for Matomo URL, Tag Manager container ID,
  and site ID, with optional constants taking precedence over ACF values.
- **Consent revisions** – Tracks consent-impacting settings and lets
  administrators publish a new consent revision when visitors should be asked
  again, since v2025.12.7.
- **Content Security Policy** – Adds nonces to scripts and styles, registers
  service-driven CSP sources, and provides settings for additional allowed
  iframe hosts.

## Compatibility and Fixes

- **Advanced Custom Fields PRO** – The plugin stops loading its feature files
  and shows an admin notice when ACF PRO functions are missing. The bootstrap
  supports ordinary, network, must-use, and late WP-CLI activation order.
- **Current Municipio assets** – First-party script detection compares URL
  origins against `home_url()` and therefore supports `/wp`, separate
  `wp-content`, ports, and domain-based multisite.
- **Current Municipio iframes** – Component Library's inactive iframe template
  is connected to the global service category without a second consent state.
  Revoking the category unloads an already active iframe.
- **Current Municipio CSP** – The plugin owns the frontend CSP header, uses
  nonces plus narrowly validated hashes for Municipio bootstrap and JSON-LD, and
  prevents WPMU Security from emitting a competing policy. Hashes are collected
  from Municipio's final processed markup so script minification cannot
  invalidate them. The baseline also blocks object embeds, limits base URLs and
  form submissions to the site's own origin, and permits data URLs only for
  images and fonts.
- **Must-use plugins** – Translation loading supports both regular plugin and
  mu-plugin installation paths.
- **Mediaflow embeds** – Mediaflow wrapper replacement is handled through the
  service configuration, so the original preview wrapper can be replaced instead
  of only the nested iframe.
- **CSP settings** – Empty additional `frame-src` settings are treated as an
  empty list instead of causing fatal errors.
- **Consent storage** – Localized consent revision values are coerced to numbers
  before they are passed to the frontend.
- **Matomo startup** – Direct tracking starts in cookieless mode. A configured
  Tag Manager container owns tracker startup when both a container ID and site
  ID exist. The plugin attaches `requireCookieConsent` when each container
  tracker is created, without pre-populating `_paq` and causing Matomo to create
  an extra default tracker. A container with `requireCookieConsent` re-applies
  that requirement after `TrackerSetup`, so container consent is persisted with
  Matomo's `mtm_cookie_consent` cookie. The cookie is created only after the
  configured tracker has applied attributes such as `Secure` and `SameSite`.
  Tracking GDPR only emits Tag Manager consent events for actual changes, so
  navigation does not send a redundant consent ping. Remote containers must not
  use Matomo's stronger `requireConsent` when cookieless baseline measurement is
  required.

## Admin Tools and Migrations

- **Data sharing menu** – Adds a top-level Data sharing admin page with links to
  settings, consent revision publishing, and the iframe report.
- **Settings page** – Adds ACF options for service enablement, custom consent
  dialog strings, Matomo, CSP `frame-src` values, and allowing uncategorized
  embedded content.
- **Consent revisions** – Automatically bumps the revision when tracked consent
  settings change and provides a manual publish action from the admin page.
- **Migrations** – This plugin does not currently include repository migration
  files.

## Embedded Content and Services

The service registry drives consent categories, iframe parsing, optional
replacement targets, cookies, and CSP sources. Supported iframe parsing
currently covers YouTube, Vimeo, Mediaflow, and Visma Recruit, while unknown
valid URLs can be allowed only when the administrator enables unrestricted
embedded content.

Services can declare script matching rules, iframe parsing callbacks, iframe
attributes, cookie metadata, and CSP directives. Necessary services are always
enabled; other services are enabled through the Data sharing settings page.

Services may also declare consent-controlled `fetch` and `sendBeacon` URL
prefixes through their `requests` property. The browser gate matches both the
origin and path, refuses registered calls until that service is accepted, and
uses the current consent state for each new call. This is a declared integration
contract, not a general network firewall: unregistered destinations,
iframe-internal traffic, calls made before the plugin boots, other browser
network APIs, and requests already in flight remain outside its scope. Matomo's
documented cookieless baseline is managed separately and is not registered with
this gate.

Each site can choose whether YouTube embeds use `www.youtube.com` or
`www.youtube-nocookie.com`. Existing installations keep `www.youtube.com` as the
default. Privacy-enhanced mode does not by itself guarantee that YouTube
performs no third-party tracking or data sharing.

## Hook Reference

### Cookie Categories

`apply_filters( 'wstg_get_cookie_categories', array $categories )` Filters the
cookie category definitions used by the consent dialog and service settings.

### Scripts and Services

`apply_filters( 'wstg_script_category', string $category, array $attributes )`
Filters the consent category assigned to an enqueued external script.

`apply_filters( 'wstg_script_service', string $service, array $attributes )`
Filters the service key assigned to an enqueued external script.

`apply_filters( 'wstg_register_service', array $service, string $key )` Filters
a service definition before it is stored in the registry.

`apply_filters( 'wstg_network_request_rules', array $rules )` Filters the
serializable rules used to gate registered `fetch` and `sendBeacon` calls.

`apply_filters( 'wstg_csp_sources', array $sources_by_directive )` Lets other
plugins register CSP sources. Keys must be supported CSP directives and each
value may be one source string or an array of source strings. Sources containing
whitespace, semicolons, or commas are ignored.

Tracking GDPR also imports sources registered through Municipio's existing
`WpSecurity/Csp` filter. Providers can therefore keep their current WPMU
Security integration without depending on Tracking GDPR.

`do_action( 'wstg_register_services' )` Runs when services should register
themselves with `wstg_register_service()`.

`apply_filters( 'wstg_service_settings_sub_fields', array $sub_fields, string $service_key, array $service )`
Filters the ACF settings sub fields generated for a registered service.

### Consent Dialog

`apply_filters( 'wstg_trigger_cookie_dialog_menu_item', string $output, WP_Post $item, int $depth, stdClass $args )`
Filters the menu item output for links that trigger the cookie dialog.

`do_action( 'wstg_consent_revision_bumped', int $revision, int $previous_revision )`
Runs after the published consent revision has been bumped, since v2025.12.7.

### Embedded Content

`apply_filters( 'wstg_content_iframe_replacement', string $replacement_html, array $context )`
Filters the replacement HTML used when content iframes are converted to
consent-aware placeholders.

## Development and Contribution

Read more about how to contribute in the
[CONTRIBUTING.md file](CONTRIBUTING.md).
