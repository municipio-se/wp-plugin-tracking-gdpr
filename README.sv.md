# Whitespace Tracking & GDPR

[English version](README.md)

Del av [Municipio LTS](https://github.com/municipio-se/municipio-lts).
Whitespace Tracking & GDPR lägger till en cookie-samtyckesdialog,
Matomo-inställningar, servicebaserad iframe-hantering och stöd för Content
Security Policy på Municipio-webbplatser.

## Krav

Whitespace Tracking & GDPR är ett WordPress-plugin för Municipio
LTS-installationer. Pluginet kräver PHP-beroenden installerade via Composer och
att Advanced Custom Fields PRO är installerat och aktivt.

Runtime-beroenden i Composer är `imangazaliev/didom` och
`wpackagist-plugin/advanced-custom-fields`. Frontend-assets byggs från
TypeScript- och CSS-källor med pnpm, Vite, TypeScript och Vanilla CookieConsent.

## Funktioner

- **Cookie-samtyckesdialog** – Köar Vanilla CookieConsent med service-,
  kategori-, text-, Matomo- och samtyckesrevisionsinställningar från WordPress.
- **Serviceregister** – Registrerar tjänster som YouTube, Vimeo, Mediaflow,
  Visma Recruit och pluginets egen nödvändiga service.
- **Hantering av inbäddat innehåll** – Ersätter stödda innehålls-iframes med
  samtyckesstyrda `wstg-iframe`-platshållare innan original-iframe laddas.
- **Iframe-rapport** – Lägger till en adminrapport för publicerade inlägg, sidor
  och Municipio iframe-moduler som hittas på webbplatsen.
- **Matomo-inställningar** – Lägger till inställningar för Matomo-URL, Tag
  Manager container-ID och site-ID, där valfria konstanter har företräde över
  ACF-värden.
- **Samtyckesrevisioner** – Spårar inställningar som påverkar samtycke och låter
  administratörer publicera en ny samtyckesrevision när besökare ska tillfrågas
  igen, since v2025.12.7.
- **Content Security Policy** – Lägger till nonces på script och stilmallar,
  registrerar servicestyrda CSP-källor och tillhandahåller inställningar för
  extra tillåtna iframe-värdar.

## Kompatibilitet och fixar

- **Advanced Custom Fields PRO** – Pluginet slutar läsa in sina funktionsfiler
  och visar ett adminmeddelande när ACF PRO-funktioner saknas.
- **Must-use plugins** – Inläsning av översättningar stödjer både vanlig
  plugininstallation och mu-plugin-installation.
- **Mediaflow-inbäddningar** – Ersättning av Mediaflow-wrappers hanteras via
  servicekonfigurationen, så originalets förhandsvisningswrapper kan ersättas i
  stället för bara den inre iframe:en.
- **CSP-inställningar** – Tomma extra `frame-src`-inställningar behandlas som en
  tom lista i stället för att orsaka fatala fel.
- **Samtyckeslagring** – Lokaliserade värden för samtyckesrevision omvandlas
  till nummer innan de skickas till frontend.

## Adminverktyg och migrationer

- **Meny för datadelning** – Lägger till en toppnivåmeny för datadelning med
  länkar till inställningar, publicering av samtyckesrevision och
  iframe-rapport.
- **Inställningssida** – Lägger till ACF-alternativ för aktivering av tjänster,
  anpassade texter i samtyckesdialogen, Matomo, CSP `frame-src`-värden och
  tillåtelse för okategoriserat inbäddat innehåll.
- **Samtyckesrevisioner** – Ökar revisionen automatiskt när spårade
  samtyckesinställningar ändras och tillhandahåller en manuell
  publiceringsåtgärd från adminsidan.
- **Migrationer** – Pluginet innehåller för närvarande inga migrationsfiler i
  repot.

## Inbäddat innehåll och tjänster

Serviceregistret styr samtyckeskategorier, iframe-tolkning, valfria
ersättningsmål, cookies och CSP-källor. Stödd iframe-tolkning täcker för
närvarande YouTube, Vimeo, Mediaflow och Visma Recruit, medan okända giltiga
URL:er bara kan tillåtas när administratören aktiverar obegränsat inbäddat
innehåll.

Tjänster kan deklarera regler för scriptmatchning, callbacks för
iframe-tolkning, iframe-attribut, cookie-metadata och CSP-direktiv. Nödvändiga
tjänster är alltid aktiverade; andra tjänster aktiveras via inställningssidan
för datadelning.

## Hook-referens

### Cookie-kategorier

`apply_filters( 'wstg_get_cookie_categories', array $categories )` Filtrerar
cookie-kategorierna som används av samtyckesdialogen och serviceinställningarna.

### Script och tjänster

`apply_filters( 'wstg_script_category', string $category, array $attributes )`
Filtrerar samtyckeskategorin som tilldelas ett externt script.

`apply_filters( 'wstg_script_service', string $service, array $attributes )`
Filtrerar service-nyckeln som tilldelas ett externt script.

`apply_filters( 'wstg_register_service', array $service, string $key )`
Filtrerar en servicedefinition innan den sparas i registret.

`do_action( 'wstg_register_services' )` Körs när tjänster ska registrera sig med
`wstg_register_service()`.

`apply_filters( 'wstg_service_settings_sub_fields', array $sub_fields, string $service_key, array $service )`
Filtrerar ACF-underfälten som genereras för en registrerad tjänst.

### Samtyckesdialog

`apply_filters( 'wstg_trigger_cookie_dialog_menu_item', string $output, WP_Post $item, int $depth, stdClass $args )`
Filtrerar menyobjektets HTML för länkar som öppnar cookie-dialogen.

`do_action( 'wstg_consent_revision_bumped', int $revision, int $previous_revision )`
Körs efter att den publicerade samtyckesrevisionen har ökats, since v2025.12.7.

### Inbäddat innehåll

`apply_filters( 'wstg_content_iframe_replacement', string $replacement_html, array $context )`
Filtrerar ersättnings-HTML som används när innehålls-iframes konverteras till
samtyckesstyrda platshållare.

## Utveckling och bidrag

Läs mer om hur du kan bidra i [CONTRIBUTING.md-filen](CONTRIBUTING.md).
