# Fase 6 — Bouwplan (SEO, performance, publicatie)

Onderdeel van de Westein Reisblog. Dit bouwplan documenteert Fase 6 en dient als
werkchecklist voor de productie-deploy (6.7).

> **Let op — secrets.** Vul gevoelige waarden (DB-wachtwoord, SMTP-wachtwoord, tokens)
> **niet** in deze repo-versie in. Houd die in een privé-kopie buiten de repo
> (Martin bewaart de ingevulde gegevens apart). De velden hieronder zijn bewust leeg.

---

## Status Fase 6

Sub-blokken 6.0 t/m 6.6 afgerond en gepusht (suite 693 → 705, ~1794 assertions):

| Blok | Inhoud | Commit |
| --- | --- | --- |
| 6.0 | Cleanup (flash-key-consistentie admin-controllers) | `99a35a0` |
| 6.1 | SEO-meta/OG/Twitter hand-rolled + favicon-set + PWA-manifest | `7913112` / `fa128cf` |
| 6.2 | JSON-LD (WebSite/Organization/BreadcrumbList + schema-methodes op modellen) | `9548eb9` / `10d388b` |
| 6.3 | Sitemap (scheduler) + RSS-feed `/feed` + robots.txt | `fca484e` |
| 6.4 | WebP-check (conversies al aanwezig, geen commit) | — |
| 6.5 | Response-cache — **uitgesteld** naar post-launch | — |
| 6.6 | a11y + Lighthouse/WCAG AA (contrast-fix + skip-link) | `9402abe` |

Beslissingen F6-1 t/m F6-13 (+ F6-14 t/m F6-18 voor 6.7) staan gelockt in `CLAUDE.md`.

**6.7 AFGEROND (29 aug 2026) — de site staat LIVE op https://reisblog.ml-westein.nl.**
Cloud86/Plesk, PHP 8.4, HTTPS + security-headers, queue-drain via cron, admin-account actief.
Suite 705 → 709. Volledige uitvoering + deploy-landmines: zie `CLAUDE.md`.
Resterend in Fase 6: **6.8** (echte content via de admin) + **6.9** (backups/monitoring).

---

## 6.7 — Deploy-roadmap (overzicht)

- [x] **6.7.a** — Plesk-voorbereiding (subdomein, docroot `/public`, PHP 8.4, SSH, DB, SSL)
- [x] **6.7.b** — Productie-artefacten: prod-`.env`-template (`.env.production.example`), security-headers-middleware
      (CSP/HSTS/X-Frame/COOP), force-HTTPS (`URL::forceScheme` + `TrustProxies`),
      `ProductionSeeder` (rollen/permissies + categorieën + admin, géén demo-content)
- [x] **6.7.c** — Eerste deploy via SSH (deploy key, `composer install --no-dev`, migrate + seed, storage:link, assets via `scp`, caches)
- [x] **6.7.d** — Cron + scheduler (Plesk-cron `schedule:run` elke minuut → queue-drain + sitemap)
- [x] **6.7.e** — Live smoke-test (styling, sitemap/feed/robots, admin-login via wachtwoord-reset, mail door de queue) + HTTP→HTTPS-redirect

---

## 6.7.a — Plesk / Cloud86 checklist

### Al gedaan (uit het Plesk-dashboard, 29 aug 2026)

- [x] Subdomein `reisblog.ml-westein.nl` aangemaakt (status: Active)
- [x] Document root staat op `/public` (*"Website at reisblog.ml-westein.nl/public"*)

### Nog te doen

- [ ] **PHP-versie ophogen.** Staat nu op **8.2.33** → zet op **8.3 of 8.4**.
      Dev Tools → PHP (of Hosting & DNS → PHP-instellingen) → versie kiezen.
      *Waarom:* Laravel 13 vereist PHP 8.3+; lokaal draait 8.4.24 — kies bij voorkeur
      8.4 voor pariteit met dev.
- [ ] **PHP-extensies bevestigen** na het wisselen: `gd`, `pdo_mysql`, `mbstring`, `intl`.
      *Waarom:* nodig voor Laravel + Spatie Media Library (GD-driver) + `intl`.
- [ ] **SSH inschakelen** en de systeemgebruiker een shell geven (`/bin/bash`).
      Web Hosting Access (of via Cloud86-instelling/support als het gated is).
      *Waarom:* composer draait op de server (F6-13); clone/deploy gaat via SSH.
- [ ] **MySQL-database + gebruiker** aanmaken. Databases → Database toevoegen: een lege DB
      + aparte DB-gebruiker met sterk wachtwoord en alle rechten op díé DB.
- [ ] **SSL (Let's Encrypt)** bevestigen. Het dashboard meldt *"Issues will be fixed
      automatically"* — controleer dat het cert voor `reisblog.ml-westein.nl` daadwerkelijk
      groen/geldig wordt. *Waarom:* we forceren HTTPS + HSTS in 6.7.b.
- [ ] **DNS bevestigen.** `reisblog.ml-westein.nl` moet resolven naar `45.82.189.136`.
      Check met `nslookup reisblog.ml-westein.nl`.
- [ ] **Serverpad ophalen.** Files & Databases → **Connection Info** (of File Manager):
      het absolute pad van de subdomein-map noteren (waar we de repo clonen).
- [ ] **Hostnet SMTP-gegevens opzoeken** voor `website.support@ml-westein.nl`
      (host, poort, encryptie, gebruikersnaam) — nodig voor de prod-`.env` in 6.7.b.

---

## Gegevens (invullen in je privé-kopie — NIET committen)

Bekende, niet-gevoelige waarden zijn alvast ingevuld. Vul de rest in je privé-download in.

### Server & toegang

| Veld | Waarde |
| --- | --- |
| Subdomein | `reisblog.ml-westein.nl` |
| IPv4 | `45.82.189.136` |
| IPv6 | `2a0e:7280:0:189:136:777:0:1` |
| Systeemgebruiker | `nvxvunro` |
| Document root | `.../reisblog.ml-westein.nl/public` |
| Serverpad subdomein-map | `__________________________` |
| PHP-versie (na wijziging) | `__________________________` |
| SSH-host | `__________________________` |
| SSH-poort | `__________________________` |
| SSH-gebruiker | `__________________________` |

### Database

| Veld | Waarde |
| --- | --- |
| DB-host | `__________________________` (meestal `localhost`) |
| DB-naam | `__________________________` |
| DB-gebruiker | `__________________________` |
| DB-wachtwoord | **privé — niet hier** |

### Mail (Hostnet SMTP)

| Veld | Waarde |
| --- | --- |
| Van-adres | `website.support@ml-westein.nl` |
| SMTP-host | `__________________________` |
| SMTP-poort | `__________________________` |
| Encryptie (TLS/SSL) | `__________________________` |
| SMTP-gebruiker | `__________________________` |
| SMTP-wachtwoord | **privé — niet hier** |

---

## Wat ik nodig heb voor 6.7.b (zonder wachtwoorden)

Om de productie-artefacten te bouwen, geef door: PHP-versie (na ophogen),
DB-host/naam/gebruiker, SSH-host/poort/gebruiker, het serverpad van de subdomein-map,
en de SMTP-host/poort/encryptie/gebruiker. Wachtwoorden vullen we rechtstreeks op de
server in de `.env` in — die komen nooit in de chat of de repo.

---

## Volgende fasen (na 6.7)

- [ ] **6.8** — Content-invoer + media-migratie (echte reisverhalen + foto's via de admin;
      `DemoContentSeeder` draait niet op productie)
- [ ] **6.9** — Backups + monitoring (db-dump + media, uptime + foutmail)
- [ ] **Post-launch fast-follows** — response-cache (F6-9), Sass `@use`-migratie,
      Tailwind uit `package.json`, volledige Lighthouse-Performance-run tegen productie
