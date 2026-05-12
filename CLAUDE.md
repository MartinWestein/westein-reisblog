# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 10 mei 2026 — Fase 2 volledig afgerond
**Masterplan:** zie `westein-reisblog-masterplan.md` voor volledige architectuur

---

## Wat dit project is

Een schaalbare, veilige Laravel-reisblog voor familievakanties van de familie Westein. Server-side rendered Blade, geen SPA. Multi-generatie publiek (familie, vrienden), Nederlandstalig, Dodger Blue als primaire kleur. Doel: SEO-groei en duurzaam onderhoud op Nederlandse shared hosting.

## Stack — definitief

- **Backend:** Laravel 13, PHP 8.3+, MySQL 8
- **Frontend:** Blade + Bootstrap 5 + Alpine.js + Vite
- **Editor:** TipTap (in admin)
- **Kaarten:** Leaflet
- **Auth:** Laravel Fortify
- **Permissions:** Spatie Laravel Permission (rollen: Admin, Editor, Auteur, Geregistreerde gebruiker)
- **Media:** Spatie Media Library
- **SEO:** Spatie SEO + Spatie Sitemap
- **Slugs:** Spatie Sluggable
- **Spam:** Spatie Honeypot
- **Lokaal dev:** Herd + DBngin + VS Code op Windows. Projectroot: 'C:\Herd\westein-reisblog' (bewust buiten OneDrive - zie #leerpunten Fase 2)
- **Versiebeheer:** Git + GitHub (private repo) via GitHub CLI
- **Hosting:** Nederlandse shared hosting (provider t.b.d. — Hostnet wordt nu gebruikt voor mail)

## Designkeuze — definitief

**"Modern magazine"** (Voorstel B uit Fase 1).

- Achtergrond: zandbeige `#F8F6F2`
- Tekst: `#14213D`
- Headings: Playfair Display (serif)
- Body: Inter (sans-serif)
- Accenten: perzik `#E8A87C`, salie-groen `#41B3A3`, gedempt rosé `#C38D9E`
- Stijl: edge-to-edge fotografie, magazine-uitstraling, kleine kapitalen voor tags
- Design tokens staan in `resources/scss/design-tokens.scss`

## Beslissingen Fase 2 — definitief

- **Registratie:** open + e-mailverificatie verplicht (account pas actief na klik in mail)
- **2FA:** verplicht voor Admin/Editor, optioneel voor andere rollen
- **Mail (dev + prod):** SMTP via eigen domein `website.support@ml-westein.nl` — geen Mailtrap
- **Rollen-model:** meerdere rollen per gebruiker (Spatie default, flexibel)

## Conventies — werk altijd zo

1. **Eén plek voor één ding.** Geen business-logic in Blade. Geen validatie in controllers. Geen queries in models.
2. **Naamgeving:** Engels in code (Post, Destination), Nederlands in URL's en UI ("/bestemmingen", "Reistips").
3. **Form Requests altijd** voor POST/PUT validatie.
4. **Policies altijd** voor autorisatie. Geen `if ($user->isAdmin())` in controllers.
5. **Eager loading discipline.** `with()` overal waar relaties getoond worden. Telescope detecteert N+1's in dev.
6. **Queues vanaf dag 1**, ook al draait de driver op `database`. Mail, image-conversies en sitemap-builds zijn altijd queued.
7. **Database-indexen vanaf het begin.** Slug, status, foreign keys, samengestelde indexen waar nodig.
8. **Tests:** Feature-tests voor admin CRUD, reactie-moderatie, double-opt-in nieuwsbrief, RBAC. Geen 100% coverage als doel.
9. **Pint vóór elke commit.** `.\vendor\bin\pint` lokaal, format-on-save staat aan in VS Code.
10. **Line endings = LF.** `.gitattributes` regelt het, `.vscode/settings.json` ook.
11. **Na élke `.env`-wijziging: `php artisan config:clear`.** Laravel cached config en blijft anders oude waardes gebruiken — dit veroorzaakt zwerm-debug-sessies. Geleerd tijdens Fase 2 mail-setup.
12. **Nooit echte secrets in chats/issues plakken.** Wachtwoorden, API-keys, tokens altijd vervangen door `***` of `[REDACTED]`. Bij per ongeluk lekken: direct roteren in het hosting-paneel.
13. **Geen Laravel-projecten in OneDrive.** OneDrive zet ACL-restricties op mappen die `is_writable()` op Windows misleiden, ook na verplaatsing. Project staat in `C:\Herd\` om die reden. `vendor/` en `node_modules/` willen sowieso geen sync-engine bovenop.

## Architectuur — kernkeuzes

- **Content-hiërarchie:** Destination → Location → Post (Post mag óók direct aan Destination hangen).
- **Reistips:** zijn een categorie binnen Posts, geen aparte tabel.
- **Reacties:** alleen voor ingelogde gebruikers, met moderatie (`pending` → `approved`).
- **Routes:** geordende lijst van Locations, Leaflet trekt rechte lijnen ertussen.
- **Foto's:** album per Location, Post heeft een eigen featured image.
- **Newsletter:** volledig in eigen beheer (Subscriber + Newsletter + queued sending, double opt-in).
- **Talen:** alleen NL nu, structuur klaar voor uitbreiding (`__()` overal).

Volledige database-architectuur, ERD en URL-structuur: zie masterplan §3.

## Roadmap — fase-status

- ✅ **Fase 1 — Project setup & design system** _(afgerond 2 mei 2026)_
- ✅ **Fase 2 — Authenticatie & autorisatie** _(afgerond 10 mei 2026)_
- ⏳ **Fase 3 — Database & content modellen** _(volgende)_
- ⏳ **Fase 4 — Afgeschermd Admin-gedeelte**
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

**Uit Fase 1 (afgerond):**

- Laravel 11 op `https://westein-reisblog.test`, MySQL via DBngin
- Alle Composer- en NPM-packages volgens masterplan §4.3 geïnstalleerd
- Telescope + Debugbar geïnstalleerd
- Design tokens "Modern magazine" definitief
- Mapindeling, layout-skeletten, Pint, line-endings, GitHub-repo — staan

**Uit Fase 2 (afgerond):**

- SMTP via `ml-westein.nl` (Hostnet, poort 465 SSL) — mails komen aan
- Project verhuisd naar `C:\Herd\westein-reisblog\` (uit OneDrive)
- Fortify volledig geconfigureerd: registration, email-verification, password-reset, profile-update, 2FA
- Auth-views in split-screen magazine-stijl: login, register, forgot-password, reset-password, verify-email, confirm-password, two-factor-challenge
- Auth-layout `layouts/auth.blade.php` + SCSS in `_auth.scss`
- Foto in fotokolom: `public/images/auth-hero.jpg`, ingeladen via inline-style + `asset()`
- Honeypot werkt globaal (`ProtectAgainstSpam`-middleware appended) — alleen forms met `@honeypot` worden beschermd
- Spatie Permission: 4 rollen (admin, editor, auteur, lid), 17 permissies, idempotente `RolePermissionSeeder`
- `Gate::before` super-admin shortcut in `AppServiceProvider`
- `User`-model: `HasRoles` + `TwoFactorAuthenticatable` traits, `bio`/`avatar_path`/`social_links` velden
- Eigen admin-account aangemaakt + 2FA actief
- Tijdelijke `/dashboard` (auth + verified middleware) — placeholder, definitief in Fase 4
- Tijdelijke `/profiel/2fa` voor 2FA-beheer (QR + recovery codes) — placeholder
- Admin-routes-bestand `routes/admin.php` geregistreerd via `bootstrap/app.php` met prefix `/admin`, name `admin.*`, middleware `auth + verified + role:admin|editor|auteur`
- Tijdelijke `/admin` placeholder — definitief in Fase 4
- Rate-limiters: login 5/min, 2FA 5/min, verification.send 6/min — getest
- NL-vertalingen voor `auth`-strings (`failed`, `password`, `throttle`) in `lang/nl/auth.php`

## Fase 2 — overzicht (afgerond)

| Stap    | Inhoud                                                                 | Status      |
| ------- | ---------------------------------------------------------------------- | ----------- |
| **2.1** | Mail-config + Fortify installeren                                      | ✅ afgerond |
| **2.2** | Bootstrap-views maken voor alle auth-pagina's, magazine-stijl          | ✅ afgerond |
| **2.3** | E-mailverificatie inschakelen + flow testen                            | ✅ afgerond |
| **2.4** | Spatie Permission seeden (rollen + permissies) + User-model uitbreiden | ✅ afgerond |
| **2.5** | 2FA voor Admin/Editor + Honeypot + rate-limiting + admin-middleware    | ✅ afgerond |

## Wat NIET gedaan is — bewust uitgesteld

Zie masterplan §8. Highlights om niet te vergeten:

- Site-zoekfunctie (Eloquent fulltext of Scout)
- RSS-feed
- Contactformulier
- Analytics (keuze: Plausible / Umami / GA4)
- Cookie-banner (afhankelijk van analyticskeuze)
- Logo, favicon, OG-default-afbeelding
- Backup-strategie
- Monitoring (UptimeRobot + foutmail)
- Privacybeleid + cookieverklaring
- WCAG 2.1 AA-check (Fase 6)

## Werkstijl voor Claude

- Bouwen we **iteratief, stap voor stap**. Niet alles in één keer uitspuwen.
- **Stel verduidelijkende vragen** als iets ambigu is — gebruik de `ask_user_input` tool met 2-4 opties.
- **Eén beslissing per keer** waar mogelijk. Niet drie tegelijk laten kiezen tenzij ze elkaar logisch raken.
- **Code in copy-pasteable blokken** met duidelijke bestandsnamen erboven.
- **PowerShell-syntaxis** voor terminal-commando's (Windows).
- **Nederlands** in uitleg en commits, Engels in code.
- **Wees eerlijk over trade-offs.** Als een keuze later last kan geven: zeg het.
- **Geen onnodige herhaling van het masterplan.** Verwijs ernaar (`§3.4`) i.p.v. te kopiëren.
- **Bij gevoelige info in user-output:** waarschuw de gebruiker direct als er een wachtwoord/API-key/secret in de chat staat. Adviseer roteren.

## Volgende concrete actie — Fase 3 starten

Database & content modellen volgens masterplan §3:

1. Migraties bouwen voor: destinations, locations, posts, categories, tags, taggables, comments, routes, route_waypoints, subscribers, newsletters, newsletter_sends, pages, family_members
2. Models met relaties: BelongsTo, HasMany, BelongsToMany, polymorphic Taggable
3. Spatie Sluggable op Post, Destination, Location, Page
4. Spatie Media Library configureren (collecties: featured-image op Post, photos op Location, avatars op User, hero op Destination)
5. Image-conversies: WebP + meerdere groottes (klein 400px, medium 800px, groot 1600px) — queued
6. Factories voor alle hoofdmodellen
7. Seeders: CategorySeeder (Verslag, Tips, Eten, Activiteit), DemoContentSeeder
8. Validatie-regel uit §3.4 (Post locatie-keuze) implementeren in boot-method op Post-model
9. Unit tests voor de relaties (BelongsTo, HasMany, polymorphic)

Verwachting: 1-2 weken.
