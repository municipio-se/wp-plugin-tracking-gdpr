# Whitespace Tracking & GDPR

[Svensk version](README.sv.md)

Part of [Municipio LTS](https://github.com/municipio-se/municipio-lts).
Whitespace Tracking & GDPR adds a cookie consent dialog, Matomo tracking
settings, service-based iframe handling, and Content Security Policy support for
Municipio sites.

## Requirements

Whitespace Tracking & GDPR is a WordPress plugin for Municipio LTS
installations. The plugin requires PHP dependencies installed through Composer
and Advanced Custom Fields PRO to be installed and active.

The runtime Composer dependencies are `imangazaliev/didom` and
`wpackagist-plugin/advanced-custom-fields`. Frontend assets are built from
TypeScript and CSS sources with pnpm, Vite, TypeScript, and Vanilla
CookieConsent.

## Features

- **Cookie consent dialog** – Enqueues Vanilla CookieConsent with service,
  category, text, Matomo, and consent revision settings localized from
  WordPress.
- **Service registry** – Registers services such as YouTube, Vimeo, Mediaflow,
  Visma Recruit, and the plugin's own required service.
- **Embedded content handling** – Replaces supported content iframes with
  consent-aware `wstg-iframe` placeholders before the original iframe is loaded.
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
  and shows an admin notice when ACF PRO functions are missing.
- **Must-use plugins** – Translation loading supports both regular plugin and
  mu-plugin installation paths.
- **Mediaflow embeds** – Mediaflow wrapper replacement is handled through the
  service configuration, so the original preview wrapper can be replaced instead
  of only the nested iframe.
- **CSP settings** – Empty additional `frame-src` settings are treated as an
  empty list instead of causing fatal errors.
- **Consent storage** – Localized consent revision values are coerced to numbers
  before they are passed to the frontend.

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
