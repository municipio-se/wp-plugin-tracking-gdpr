# Ändringslogg

[English version](CHANGELOG.md)

## v2026.8.2 – 2026-09-01

- **Content Security Policy** – Lade till det dokumenterade filtret
  `wstg_csp_sources` så andra plugin kan registrera avgränsade källor utan att
  ta över frontendens header. Pluginet importerar också befintliga
  `WpSecurity/Csp`-registreringar utan att providers behöver vara beroende av
  Tracking GDPR.

## v2026.8.1 – 2026-09-01

- **Composer-identitet** – Återställde `municipio/wp-plugin-tracking-gdpr` som
  paketnamn efter att repot flyttats till Municipio-organisationen.
  Current-linjen hålls fortsatt avgränsad genom sin exakta `2026.x`-tagg, medan
  LTS-krav på `^2025.12` endast kan välja LTS-releaser.

## v2026.8.0 – 2026-08-30

- **Stöd för aktuell Municipio** – Portade pluginet till Municipio Deployment 5
  och Municipio-tema 6 utan att ändra befintliga inställningar och
  samtyckesdata.
- **Multisite och assets** – Lade till multisite-säker bootstrap, kontroll av
  ACF Pro, översättningsinläsning och identifiering av förstapartsassets i
  aktuell Municipio.
- **Content Security Policy** – Lät pluginet äga en enda frontendpolicy och lade
  till snävt avgränsat stöd för aktuell Municipios bootstrap-skript.
- **Inbäddat innehåll** – Lade till inerta, samtyckesstyrda iframe-platshållare,
  avlastning vid återkallat samtycke och val av YouTube-värd per sajt.
- **Analys** – Behöll kaklös Matomo-mätning före samtycke och gjorde uppstart av
  direkt tracker och Tag Manager deterministisk. Containersamtycke kopplas till
  varje konfigurerad tracker utan att skapa en extra standardtracker som kan
  skriva om kakor med svagare attribut när sidan lämnas. Containersamtycket
  sparas först efter att den konfigurerade trackern har satt sina säkra
  kakattribut. Samtyckeshändelser spelas inte upp igen vid navigation, så
  analyskakorna överlever sidvisningar utan överflödiga pingar.
- **Nätverksanrop** – Lade till en uttrycklig, tjänsteägd spärr för deklarerade
  fetch- och beacon-destinationer.
- **Tillgänglighet** – Samordnade Municipios menylåda med samtyckesdialogen och
  återställde fokus när dialogen stängs.
- **Releaselinje** – Etablerade den första `2026.x`-releasen för current
  Municipio. `v25.x` förblir källbranch för Municipio LTS.

## v2025.12.9 – 2026-06-01

- **Adminskript** – Undantog WordPress admin från omskrivning av samtyckesskript
  så externa editorberoenden kan köras normalt.
- **Iframe-indexering** – Hanterade äldre eller felaktig iframe-markup utan
  tolkat ersättningsmål vid indexering.

## v2025.12.8 – 2026-04-29

- **Samtyckesrevisioner** – Omvandlade lokaliserade värden för samtyckesrevision
  till nummer innan de skickas till frontend-inställningarna.

## v2025.12.7 – 2026-04-29

- **Samtyckesrevisioner** – Lade till publicering av samtyckesrevisioner så
  administratörer kan kräva nytt samtycke efter ändrade inställningar.

## v2025.12.6 – 2026-02-17

- **Mediaflow-inbäddningar** – Flyttade ersättning av Mediaflow-wrappers till
  servicekonfigurationen för att hålla iframe-ersättning servicebaserad.

## v2025.12.5 – 2026-02-17

- **Frontend-stil** – Uppdaterade beskrivningar så de ärver textfärg från sitt
  omgivande gränssnitt.

## v2025.12.4 – 2026-02-17

- **Innehålls-iframes** – Förbättrade URL-matchning vid tolkning av
  innehålls-iframes.

## v2025.12.3 – 2026-01-30

- **Content Security Policy** – Förhindrade ett fatalt fel när den tillåtna
  `frame-src`-inställningen var tom.

## v2025.12.2 – 2026-01-29

- **Content Security Policy** – Rättade extra tillåtna `frame-src`-inställningar
  så varje konfigurerad källa används.
- **Underhåll** – Tog bort debugkod från CSP-hanteringen.

## v2025.12.1 – 2026-01-16

- **Inbäddat innehåll** – Rättade uppdatering av iframes vid första samtycket.

## v2025.12.0 – 2025-12-30

- **Licensiering** – Uppdaterade licensfiler för att följa GPL-2.0-or-later.
- **Dokumentation** – Uppdaterade README för den initiala
  `2025.12`-release-linjen.
