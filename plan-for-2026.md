# Plan for LTS 2026

Nuvarande LTS-head: `e0d4bab`. Aktuell pluginversion: `2025.12.9`. Jämförelseyta
för 2026: Municipio LTS 2026-stacken, `wp-theme-municipio` `7.7.18`, Municipio
Extended, Municipio Rekai, WordPress script/style APIs, Matomo och ACF PRO.

## Slutsats

Whitespace Tracking & GDPR är ett centralt LTS-paket och bör behandlas som
samtyckes-, scriptklassificerings- och CSP-lager för 2026. Det ersätts inte av
Helsingborgs nya bas i snabbpasset, men det påverkar flera andra paket, särskilt
Rekai, Extendeds embed-/scriptbeteenden och temats inline scripts.

Första passet pekar på att paketet bör behållas, men att CSP/scriptattribut,
iframehantering och service registry måste testas mot den nya 2026-markupen.
Detta är ett riskpaket eftersom fel här kan blockera externa scripts, bryta
embeds eller ge fel samtyckesklassning.

## Arbetsplan

- [ ] Behåll paketet som samtyckes- och CSP-lager om LTS 2026 fortsatt ska ha
      Whitespace-samtyckesdialogen.
- [ ] Verifiera CSP mot temats och Modularitys inline scripts efter 2026-rebas.
- [ ] Verifiera iframeersättning mot temats/Modularitys iframe-, video- och
      Mediaflow-output.
- [ ] Kontraktstesta service registry med Municipio Rekai och andra externa
      scriptintegreringar.
- [ ] Gå igenom ACF PRO-kravet och admin-noticebeteendet i nya stacken.
- [ ] Kontrollera Matomo constants, inställningar och Tag Manager-flöde.
- [ ] Lämna `dist/` och språkfiler orörda tills ordinarie build/i18n-steg.

## Beslutstabell

| Område                  | Vår slutändring                                                                                     | 2026-läge                                                                             | Bedömning                                                                   |
| ----------------------- | --------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- | --------------------------------------------------------------------------- |
| Paketering              | Composerpaket `municipio/wp-plugin-tracking-gdpr` med runtimeberoenden `didom` och ACF.             | Eget LTS-paket utan direkt Helsingborg-upstream.                                      | Behåll; verifiera ACF PRO-krav och package policy.                          |
| Bootstrap och ACF-skydd | Stödjer vanlig plugin/MU-plugin och stoppar featureladdning när ACF PRO saknas.                     | Fortsatt relevant i LTS.                                                              | Behåll.                                                                     |
| Cookie consent dialog   | Lokaliserar Vanilla CookieConsent-inställningar från WordPress och service registry.                | Ingen motsvarande 2026-yta hittad i snabbpasset.                                      | Behåll; funktionstesta dialog och revisioner.                               |
| Service registry        | `wstg_register_service`, kategorier och serviceinställningar för scripts, iframes, cookies och CSP. | Rekai och andra plugins kan bygga på detta kontrakt.                                  | Behåll som publikt kontrakt; dokumentera ändringar strikt.                  |
| Scriptklassificering    | Lägger `data-category` och `data-service` på externa scripts via WordPress script attributes.       | Temat och plugins kan ändra script handles och loadingstrategier.                     | Verifiera manuellt mot 2026-assets.                                         |
| CSP                     | Nonces för scripts/styles, servicebaserade källor och extra `frame-src`-inställningar.              | Temat `7.7.18` och inbäddad Modularity har egna inline scripts som måste fungera.     | Hög prioritet; testa med verkliga sidor.                                    |
| Iframehantering         | Ersätter YouTube, Vimeo, Mediaflow, Visma Recruit och valbara okända iframes med `wstg-iframe`.     | Modularity iframe/video-output kan ha ändrats i temat.                                | Behåll; verifiera parser och replacement targets.                           |
| Iframe report           | Adminrapport för publicerade poster, sidor och `mod-iframe`.                                        | Posttyper och modulstruktur kan ändras när Modularity ligger i temat.                 | Återskapa smalare om nya modulposttyper/fields kräver det.                  |
| Matomo                  | ACF/constant-baserade inställningar för URL, container ID och site ID.                              | Content Insights använder Matomo API-statistik; Rekai/andra scripts behöver samtycke. | Behåll som tracking/samtyckesansvar; håll isär från redaktionell statistik. |
| Consent revision        | Spårar samtyckespåverkande inställningar och publicerar revisioner.                                 | Fortsatt centralt för samtyckesändringar.                                             | Behåll; verifiera revision bump och adminflöde.                             |
| Assets                  | TypeScript/Vite och Vanilla CookieConsent-bundle i `dist/`.                                         | Ska inte uppdateras i snabbpasset.                                                    | Ej relevant tills implementation.                                           |

## Rekommenderad rebase-plan

1. Behåll pluginen som egen bas och börja med CSP-/scriptinventering mot nya
   temat.
2. Verifiera service registry-kontraktet med Rekai innan Rekai porteras.
3. Testa iframeersättning på vanliga content-iframes, Modularity iframe/video
   och Mediaflow.
4. Kör en adminrunda för Data sharing, settings, iframe report och consent
   revision.
5. Bygg assets och uppdatera språkfiler först när kodändringar kräver release.

## Risker att verifiera

- CSP kan blockera temats, Modularitys eller tredjepartsplugins inline scripts.
- `wp_script_attributes` och `wp_inline_script_attributes` måste samspela med
  tema och andra plugins som också ändrar attribut.
- Iframeparsern kan missa ny markup eller ersätta fel wrapper.
- Service registry är publikt kontrakt för Rekai och eventuella kundplugins.
- ACF PRO-avbrott ska vara tydligt men inte fälla hela wp-admin.
- Samtyckesrevisioner måste bumpas när rätt inställningar ändras.

## Kommandon körda

- `git status --short`
- `git rev-parse --short HEAD`
- `rg --files`
- `sed` av README, Composer och pluginbootstrap.
- Riktade `rg`-sökningar efter hooks, ACF, `wstg_*`, Matomo, CSP,
  script/style-attribut, service registry och iframehantering.
