# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 12 mei 2026 — Fase 3 in uitvoering (Stap 3.1 afgerond)
**Masterplan:** zie `westein-reisblog-masterplan.md` voor volledige architectuur

---

## Wat dit project is

Een schaalbare, veilige Laravel-reisblog voor familievakanties van de familie Westein. Server-side rendered Blade, geen SPA. Multi-generatie publiek (familie, vrienden), Nederlandstalig, Dodger Blue als primaire kleur. Doel: SEO-groei en duurzaam onderhoud op Nederlandse shared hosting.

## Stack — definitief

- **Backend:** Laravel 13.7, PHP 8.3+, MySQL 8 (project liep oorspronkelijk op L11 volgens masterplan, is ergens tijdens Fase 1/2 op L13 gekomen — geen blockers, L13 heeft zero breaking changes)
- **Frontend:** Blade + Bootstrap 5 + Alpine.js + Vite
- **Editor:** TipTap (in admin)
- **Kaarten:** Leaflet
- **Auth:** Laravel Fortify
- **Permissions:** Spatie Laravel Permission (rollen: Admin, Editor, Auteur, Geregistreerde gebruiker)
- **Media:** Spatie Media Library
- **SEO:** Spatie SEO + Spatie Sitemap
- **Slugs:** Spatie Sluggable
- **Spam:** Spatie Honeypot
- **Tests:** Pest 4 (geen PHPUnit-classes — zie conventie #14)
- **Lokaal dev:** Herd + DBngin + VS Code op Windows. Projectroot: `C:\Herd\westein-reisblog` (bewust buiten OneDrive — zie leerpunten Fase 2)
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

## Beslissingen Fase 3 — tot nu toe

- **Tags:** polymorfe pivot (taggables) — herbruikbaar voor Locations/Routes later
- **Tag-namen:** lowercase forceren via mutator (voorkomt 'Camper' én 'camper' als duplicaten)
- **Categorie-volgorde:** `order`-veld op `categories` (int, voor handmatige sortering in UI)
- **Posts ↔ Categories:** *(nog te beslissen — BelongsTo of BelongsToMany)*

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
14. **Pest-syntax in tests, niet PHPUnit-classes.** Tests zijn `test('beschrijving', function () { ... });` op file-niveau, geen `class XTest extends TestCase` met methods. Pest negeert PHPUnit-classes stil — "No tests found" is dan het symptoom. `RefreshDatabase` is centraal actief via `tests/Pest.php`.
15. **PowerShell-quoting:** voor regex-filters met `|` (zoals `--filter=`) gebruik single quotes (`'...'`), niet double. Of gebruik een padargument als `php artisan test tests/Feature/Models` om de filter helemaal te omzeilen.

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
- 🔄 **Fase 3 — Database & content modellen** _(in uitvoering — Stap 3.1 afgerond)_
- ⏳ **Fase 4 — Afgeschermd Admin-gedeelte**
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

**Uit Fase 1 (afgerond):**

- Laravel 11 op `https://westein-reisblog.test`, MySQL via DBngin (later geüpgraded naar L13)
- Alle Composer- en NPM-packages volgens masterplan §4.3 geïnstalleerd
- Telescope + Debugbar geïnstalleerd
- Design tokens "Modern magazine" definitief
- Mapindeling, layout-skeletten, Pint, line-endings, GitHub-repo — staan

**Uit Fase 2 (afgerond):**

- SMTP via `ml-westein.nl` (Hostnet, poort 465 SSL) — mails komen aan
- Project verhuisd naar `C:\Herd\westein-reisblog\` (uit OneDrive)
- Fortify volledig geconfigureerd: registration, email-verification, password-reset, profile-update, 2FA
- Auth-views in split-screen magazine-stijl
- Spatie Permission: 4 rollen, 17 permissies, idempotente `RolePermissionSeeder`
- `Gate::before` super-admin shortcut in `AppServiceProvider`
- `User`-model: `HasRoles` + `TwoFactorAuthenticatable` traits, `bio`/`avatar_path`/`social_links` velden
- Admin-routes-bestand `routes/admin.php` via `bootstrap/app.php` afgeschermd met middleware `auth + verified + role:admin|editor|auteur`
- Rate-limiters: login 5/min, 2FA 5/min, verification.send 6/min
- Honeypot globaal actief (forms met `@honeypot`)
- NL-vertalingen voor `auth`-strings

**Uit Fase 3 (deels):**

- **Stap 3.1 afgerond** — Categories, Tags, polymorfe Taggables
- Drie migraties: `categories` (incl. `order`-veld), `tags`, `taggables` (polymorfe pivot met composite unique)
- Models: `Category`, `Tag` met Spatie Sluggable; Tag dwingt lowercase af via mutator
- `App\Models\Concerns\HasTags` trait (met `@mixin Model` PHPDoc) voor hergebruik op Post/Location/Route
- `CategorySeeder` met vier vaste categorieën (Verslag, Tips, Eten, Activiteit), idempotent via `firstOrCreate`
- Factories voor `Category` en `Tag`
- 9 Pest-tests groen (slug-generatie, uniqueness, lowercase, trimming, idempotentie)
- `tests/Pest.php`: `RefreshDatabase` trait actief voor Feature-suite

## Fase 2 — overzicht (afgerond)

| Stap    | Inhoud                                                                 | Status      |
| ------- | ---------------------------------------------------------------------- | ----------- |
| **2.1** | Mail-config + Fortify installeren                                      | ✅ afgerond |
| **2.2** | Bootstrap-views maken voor alle auth-pagina's, magazine-stijl          | ✅ afgerond |
| **2.3** | E-mailverificatie inschakelen + flow testen                            | ✅ afgerond |
| **2.4** | Spatie Permission seeden (rollen + permissies) + User-model uitbreiden | ✅ afgerond |
| **2.5** | 2FA voor Admin/Editor + Honeypot + rate-limiting + admin-middleware    | ✅ afgerond |

## Fase 3 — overzicht (in uitvoering)

| Stap    | Inhoud                                                                                  | Status      |
| ------- | --------------------------------------------------------------------------------------- | ----------- |
| **3.1** | Foundation: Categories, Tags, polymorfe Taggables + Sluggable                           | ✅ afgerond |
| **3.2** | Geografische kern: Destinations, Locations, Posts + locatie-keuze validatie (§3.4)      | ⏳          |
| **3.3** | Media Library: collecties op Post/Location/Destination/User + WebP-conversies queued    | ⏳          |
| **3.4** | Rest: Comments, Routes + Waypoints, Subscribers, Newsletters, Pages, FamilyMembers, DemoSeeder, tests | ⏳ |

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
- **PowerShell-syntaxis** voor terminal-commando's (Windows). Let op single quotes bij regex-filters.
- **Pest-syntax** voor tests, geen PHPUnit-classes (conventie #14).
- **Nederlands** in uitleg en commits, Engels in code.
- **Wees eerlijk over trade-offs.** Als een keuze later last kan geven: zeg het.
- **Geen onnodige herhaling van het masterplan.** Verwijs ernaar (`§3.4`) i.p.v. te kopiëren.
- **Bij gevoelige info in user-output:** waarschuw de gebruiker direct als er een wachtwoord/API-key/secret in de chat staat. Adviseer roteren.
- **Bestandsnamen exact in casing.** Windows is case-insensitive, maar Git en Pest niet. PowerShell-`New-Item`-commando's altijd met juiste hoofdletters.

## Volgende concrete actie — Stap 3.2

Geografische kern + locatie-keuze validatie:

1. Migraties: `destinations` → `locations` → `posts` (in deze volgorde i.v.m. FK's)
2. Models: `Destination`, `Location`, `Post` — alle drie `HasSlug`; `Post` gebruikt `HasTags`-trait
3. Relaties: `Destination hasMany Location`, `Location hasMany Post`, `Post belongsTo User`, `Post belongs(ToMany) Category` _(keuze nog te maken)_
4. **Validatie-regel §3.4** in `Post::booted()`: als `location_id` gevuld → `destination_id` automatisch afleiden via boot-method
5. Factories voor alle drie
6. Pest-tests: relaties + §3.4-regel + slug-generatie

Verwachting: 2-3 dagen werk.
