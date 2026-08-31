# Whitespace Tracking & GDPR

[English version](README.md)

Ursprungligen utvecklat för
[Municipio LTS](https://github.com/municipio-se/municipio-lts), med ett
kompatibilitetslager för aktuell Municipio på `main`. Whitespace Tracking & GDPR
lägger till en cookie-samtyckesdialog, Matomo-inställningar, servicebaserad
iframe-hantering och stöd för Content Security Policy på Municipio-webbplatser.

## Krav

Whitespace Tracking & GDPR kräver att PHP-beroendena installeras via Composer
och att Advanced Custom Fields PRO är installerat och aktivt.

Runtime-beroendet i Composer är `imangazaliev/didom`. ACF Pro tillhandahålls av
sajten och ersätts avsiktligt inte med det fria ACF-paketet. Frontend-assets
byggs från TypeScript- och CSS-källor med pnpm, Vite, TypeScript och Vanilla
CookieConsent.

## Paket- och releasepolicy

Den aktuella Municipio-linjen ägs av Whitespace och publiceras från `main` som
`whitespace-se/wp-plugin-tracking-gdpr`. Paketet publiceras inte på Packagist.
En sajt måste deklarera ett avgränsat Composer-repo av typen `vcs` för
`https://github.com/whitespace-se/wp-plugin-tracking-gdpr.git` och kräva en
daterad releasetagg. Version `2026.8.0` är den första releasen i linjen.

`v25.x`-linjen och Composer-paketet `municipio/wp-plugin-tracking-gdpr` förblir
distributionen för Municipio LTS. LTS-paketet får inte ersättas eller avvecklas
förrän varje LTS-sajt som stöds har en likvärdig ersättare och en separat
verifierad migrering.

## Migrering från LTS-paketet

Ersätt det gamla paketkravet och dess repo-tillåtelselista med Whitespace-repot
och ett krav på den valda aktuella releasetaggen. Uppdatera båda paketnamnen i
en Composer-operation så att bara den gemensamma installationskatalogen
`wp-content/plugins/whitespace-tracking-gdpr` återstår. Behåll pluginet
nätverksaktiverat och verifiera varje blogg i nätverket.

Ingen destruktiv datamigrering behövs. Pluginfilen, installationssökvägen,
ACF-inställningsnamnen, tjänstenycklarna, Matomo-inställningarna,
samtyckesrevisionen och den värdavgränsade samtyckeskakan är kompatibla.
Befintliga sajter behåller `youtube.com` som standardvärd för inbäddningar om en
administratör inte uttryckligen väljer no-cookie-värden. Återställning innebär
att den tidigare Composer-lockfilen och repokonfigurationen återställs. Dom
sparade inställningarna kan därefter läsas av LTS-paketet igen.

## Funktioner

- **Cookie-samtyckesdialog** – Köar Vanilla CookieConsent med service-,
  kategori-, text-, Matomo- och samtyckesrevisionsinställningar från WordPress.
- **Serviceregister** – Registrerar tjänster som YouTube, Vimeo, Mediaflow,
  Visma Recruit och pluginets egen nödvändiga service.
- **Hantering av inbäddat innehåll** – Ersätter stödda innehålls-iframes med
  samtyckesstyrda `wstg-iframe`-platshållare innan original-iframe laddas,
  inklusive Municipios oEmbed- och Modularity-output.
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
  och visar ett adminmeddelande när ACF PRO-funktioner saknas. Bootstrapen
  stöder vanlig aktivering, nätverksaktivering, MU-plugin och sen WP-CLI-
  aktivering.
- **Assets i aktuell Municipio** – Förstapartsscript identifieras genom att
  URL-origin jämförs med `home_url()`. Det stöder `/wp`, separat `wp-content`,
  portar och domänbaserad multisite.
- **Iframes i aktuell Municipio** – Component Librarys inerta iframe-template
  kopplas till den globala tjänstekategorin utan ett andra samtyckestillstånd.
  Återkallande avlastar en redan aktiv iframe.
- **CSP i aktuell Municipio** – Pluginet äger frontendens CSP-header, använder
  nonce och snävt validerade hashvärden för Municipios bootstrap och JSON-LD
  samt hindrar WPMU Security från att skicka en konkurrerande policy.
- **Must-use plugins** – Inläsning av översättningar stödjer både vanlig
  plugininstallation och mu-plugin-installation.
- **Mediaflow-inbäddningar** – Ersättning av Mediaflow-wrappers hanteras via
  servicekonfigurationen, så originalets förhandsvisningswrapper kan ersättas i
  stället för bara den inre iframe:en.
- **CSP-inställningar** – Tomma extra `frame-src`-inställningar behandlas som en
  tom lista i stället för att orsaka fatala fel.
- **Samtyckeslagring** – Lokaliserade värden för samtyckesrevision omvandlas
  till nummer innan de skickas till frontend.
- **Matomo-start** – Direktspårning börjar kaklöst. En konfigurerad Tag
  Manager-container äger trackerstarten när både container-ID och site-ID finns
  och får `requireCookieConsent` före laddning. Det förhindrar dubbla
  sidvisningar och kakor före samtycke. Externa containrar får inte använda
  Matomos starkare `requireConsent` när kaklös grundmätning krävs.

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

Tjänster kan också deklarera samtyckesstyrda URL-prefix för `fetch` och
`sendBeacon` i egenskapen `requests`. Browsergrinden matchar både origin och
sökväg, nekar registrerade anrop tills tjänsten har godkänts och läser aktuell
samtyckesstatus inför varje nytt anrop. Det här är ett deklarerat
integrationskontrakt, inte en generell nätverksbrandvägg. Oregistrerade mål,
trafik inuti iframes, anrop före pluginets start, andra nätverks-API:er och
redan pågående anrop ligger utanför kontraktet. Matomos dokumenterade kaklösa
grundläge hanteras separat och registreras inte i grinden.

Varje sajt kan välja om YouTube-inbäddningar ska använda `www.youtube.com` eller
`www.youtube-nocookie.com`. Befintliga installationer behåller `www.youtube.com`
som standard. Läget med förbättrad integritet garanterar inte i sig att YouTube
avstår från tredjepartsspårning eller datadelning.

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

`apply_filters( 'wstg_network_request_rules', array $rules )` Filtrerar dom
serialiserbara regler som styr registrerade `fetch`- och `sendBeacon`-anrop.

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
