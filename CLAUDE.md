# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 4 mei 2026 — Fase 2 in uitvoering (Stap 2.1)
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
- 🔄 **Fase 2 — Authenticatie & autorisatie** _(in uitvoering — Stap 2.1: mail werkt, Fortify-config volgt)_
- ⏳ **Fase 3 — Database & content modellen**
- ⏳ **Fase 4 — Afgeschermd Admin-gedeelte**
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

## Wat staat er nu

**Uit Fase 1 (afgerond):**

- Laravel 11 draait op `https://westein-reisblog.test`
- Database `westein_reisblog` bereikbaar via DBngin (root, geen wachtwoord, lokaal)
- Alle Composer- en NPM-packages volgens masterplan §4.3 geïnstalleerd
- Telescope geïnstalleerd, Debugbar geïnstalleerd
- Design tokens definitief in `resources/scss/design-tokens.scss` (alleen "Modern magazine")
- Mapindeling aangelegd, layout-skeletten bestaan (leeg)
- `.gitattributes` regelt LF line-endings, `.vscode/settings.json` heeft format-on-save
- Pint draait schoon op de codebase
- Private GitHub-repo `westein-reisblog` aangemaakt en initial commit gepusht

**Uit Fase 2 (deels):**

- SMTP geconfigureerd via `ml-westein.nl` (Hostnet), poort 465, SSL
- `MAIL_USERNAME=website.support@ml-westein.nl`, `MAIL_FROM_ADDRESS` gelijk gehouden (vereiste voor de meeste mailservers)
- Test-mail komt aan in inbox
- Wachtwoord dat per ongeluk in chat lekte → geroteerd in Hostnet-paneel

## Stap 2.1 — Fase 2 plan

| Stap    | Inhoud                                                                 | Status                                               |
| ------- | ---------------------------------------------------------------------- | ---------------------------------------------------- |
| **2.1** | Mail-config + Fortify installeren                                      | 🔄 mail werkt, Fortify-vendor:publish + config volgt |
| **2.2** | Bootstrap-views maken voor alle auth-pagina's, magazine-stijl          | ⏳                                                   |
| **2.3** | E-mailverificatie inschakelen + flow testen                            | ⏳                                                   |
| **2.4** | Spatie Permission seeden (rollen + permissies) + User-model uitbreiden | ⏳                                                   |
| **2.5** | 2FA voor Admin/Editor + Honeypot + rate-limiting + admin-middleware    | ⏳                                                   |

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

## Volgende concrete actie

Stap 2.1 afmaken — Fortify configureren:

1. `php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"`
2. `php artisan migrate` (publisht 2FA-kolommen op users)
3. `bootstrap/providers.php` controleren op `App\Providers\FortifyServiceProvider::class`
4. `config/fortify.php` features-array uitbreiden (registration, resetPasswords, emailVerification, updateProfileInformation, updatePasswords, twoFactorAuthentication)
5. `app/Providers/FortifyServiceProvider.php` view-routes + rate-limiters toevoegen
6. Verifiëren met `php artisan route:list --path=login`
7. Pint + commit

Daarna: Stap 2.2 — auth-views bouwen in magazine-stijl.
