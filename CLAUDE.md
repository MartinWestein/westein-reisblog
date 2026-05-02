# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 2 mei 2026 — einde Fase 1
**Masterplan:** zie `westein-reisblog-masterplan.md` voor volledige architectuur

---

## Wat dit project is

Een schaalbare, veilige Laravel-reisblog voor familievakanties van de familie Westein. Server-side rendered Blade, geen SPA. Multi-generatie publiek (familie, vrienden), Nederlandstalig, Dodger Blue als primaire kleur. Doel: SEO-groei en duurzaam onderhoud op Nederlandse shared hosting.

## Stack — definitief

- **Backend:** Laravel 11, PHP 8.3+, MySQL 8
- **Frontend:** Blade + Bootstrap 5 + Alpine.js + Vite
- **Editor:** TipTap (in admin)
- **Kaarten:** Leaflet
- **Auth:** Laravel Fortify
- **Permissions:** Spatie Laravel Permission (rollen: Admin, Editor, Auteur, Geregistreerde gebruiker)
- **Media:** Spatie Media Library
- **SEO:** Spatie SEO + Spatie Sitemap
- **Slugs:** Spatie Sluggable
- **Spam:** Spatie Honeypot
- **Lokaal dev:** Herd + DBngin + VS Code op Windows
- **Hosting:** Nederlandse shared hosting (provider t.b.d.)

## Designkeuze — definitief

**"Modern magazine"** (Voorstel B uit Fase 1).

- Achtergrond: zandbeige `#F8F6F2`
- Tekst: `#14213D`
- Headings: Playfair Display (serif)
- Body: Inter (sans-serif)
- Accenten: perzik `#E8A87C`, salie-groen `#41B3A3`, gedempt rosé `#C38D9E`
- Stijl: edge-to-edge fotografie, magazine-uitstraling, kleine kapitalen voor tags
- Design tokens staan in `resources/scss/design-tokens.scss`

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
- ⏳ **Fase 2 — Authenticatie & autorisatie** _(volgende)_
- ⏳ **Fase 3 — Database & content modellen**
- ⏳ **Fase 4 — Afgeschermd Admin-gedeelte**
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

## Wat staat er nu

- Laravel 11 draait op `https://westein-reisblog.test`
- Database `westein_reisblog` bereikbaar via DBngin (root, geen wachtwoord, lokaal)
- Alle Composer- en NPM-packages volgens masterplan §4.3 geïnstalleerd
- Telescope geïnstalleerd, Debugbar geïnstalleerd
- Design tokens definitief in `resources/scss/design-tokens.scss` (alleen "Modern magazine")
- `app.scss` schoongemaakt, alleen Playfair Display + Inter geladen
- Mapindeling aangelegd (`Actions/`, `Controllers/Public/`, `Controllers/Admin/`, `views/public/`, `views/admin/`, etc.)
- Layout-skeletten `layouts/public.blade.php` en `layouts/admin.blade.php` bestaan (leeg)
- `.gitattributes` regelt LF line-endings
- `.vscode/settings.json` heeft `files.eol: "\n"` en `editor.formatOnSave: true`
- Pint draait schoon op de codebase
- Git-repo initieel committed (GitHub-push: bevestigen of dit gedaan is)

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

## Volgende stap — Fase 2

Authenticatie & autorisatie:

1. Fortify views publiceren en aanpassen naar Bootstrap 5 + magazine-design
2. Login / register / wachtwoord-reset / e-mailverificatie inrichten
3. Optioneel 2FA (alleen voor Admin/Editor)
4. Spatie Permission: rollen + permissies seeder
5. User-profiel uitbreiden (bio, avatar via Media Library, social_links JSON)
6. Eerste Policies opzetten (PostPolicy, CommentPolicy)
7. Honeypot + rate limiting op registratie/login
8. Admin-middleware aanmaken en testen

Verwachting: ~1 week.
