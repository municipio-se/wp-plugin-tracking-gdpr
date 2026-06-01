# Ändringslogg

[English version](CHANGELOG.md)

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
