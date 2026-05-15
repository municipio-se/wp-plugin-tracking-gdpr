# Contributing to Whitespace Tracking & GDPR

Whitespace Tracking & GDPR is part of the Municipio LTS stack. Contributions should preserve compatibility with Municipio, Advanced Custom Fields PRO, the consent dialog frontend, and the service-based iframe and CSP integrations documented in the README.

## Local Setup

- **PHP dependencies** – Run `composer install`.
- **Frontend dependencies** – Run `pnpm install`.
- **Asset development** – Run `pnpm dev` to build and watch frontend assets.
- **Asset builds** – Run `pnpm build` when TypeScript or CSS source changes need updated distributed assets.
- **Formatting** – Run `pnpm format` when you need to format supported source and documentation files.
- **Tests** – No dedicated automated test scripts are documented in this repository.

## Repository Structure

- **`whitespace-tracking-gdpr.php`** – Plugin bootstrap, textdomain loading, ACF PRO guard, and autoload entrypoint.
- **`autoload/`** – Automatically loaded WordPress hooks, admin pages, settings, consent revision handling, services, CSP, and iframe processing.
- **`psr-4/`** – Composer-autoloaded PHP classes under the `WhitespaceTrackingGdpr` namespace.
- **`src/`** – TypeScript and CSS sources for the consent dialog, Matomo integration, and `wstg-iframe` custom element.
- **`dist/`** – Generated and distributed frontend assets.
- **`languages/`** – Translation template and Swedish translation files.

## Development Guidelines

- **Existing patterns first** – Follow WordPress, Municipio, ACF, and local plugin patterns before introducing new abstractions.
- **Small scope** – Keep changes focused on the relevant feature, integration, fix, or documentation update.
- **Rationale near code** – Document important business or technical rationale close to the code when behavior is not obvious.
- **Public hooks** – Add WordPress-style PHPDoc for public plugin-owned hooks and keep the README hook reference in sync.
- **Consent behavior** – Treat category, service, Matomo, iframe, and revision changes as consent-impacting unless the code clearly proves otherwise.
- **Bilingual docs** – Keep README and changelog language pairs structurally aligned when editing documentation.

## Assets and Generated Files

- **Source first** – Edit source files in `src/`; do not hand-edit generated assets in `dist/`.
- **Build follow-up** – If frontend source changes require updated distributed assets, run the appropriate build workflow and include the generated files only when the repository expects them.
- **Translations** – Do not edit `.pot`, `.po`, or `.mo` files manually. Refresh translation files with the project i18n workflow when source strings change.

## Migrations

This repository does not currently contain migration files. If a future change requires a one-time data migration, document where it runs, make it repeat-safe, and explain data transformation risks in the pull request.

## Hooks and Public Extension Points

Public filters and actions owned by this plugin must be documented both near the hook call and in the README hook reference when relevant.

Use WordPress-style signatures in documentation:

```md
`apply_filters( 'hook_name', type $value, type $arg )`
Short description.

`do_action( 'hook_name', type $arg )`
Short description.
```

## Verification Before PR

- **Whitespace** – Run `git diff --check`.
- **PHP syntax** – Run `php -l` on changed PHP files.
- **Frontend assets** – Run `pnpm build` when `src/` changes should update `dist/`.
- **Status** – Check `git status --short` for unintended generated files or language file changes.
- **Docs** – Review README and CHANGELOG language pairs when changing documented behavior.

## Pull Request Expectations

- Explain what changed and why.
- Mention affected integrations, consent behavior, public hooks, generated assets, and manual verification.
- Link related issues and pull requests when available.
- Credit external contributors where applicable.
- Use an English Conventional Commit-style summary for commits and PR titles when possible.
