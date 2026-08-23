# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 23 augustus 2026 — Fase 5 volledig afgerond (t/m 5.6): eindcheck + `fase-5-bouwplan.md` geschreven. Alle publieke pagina's staan; cleanups getrieerd naar Fase 6. Suite 693 groen (1750 assertions). Volgende: Fase 6 (SEO/performance + productie-deploy).**Masterplan:** `westein-reisblog-masterplan.md` voor volledige architectuur, ERD, URL-structuur
**Bouwplannen:** Fase 2 → `fase-2-bouwplan.md`. Fase 4 → `fase-4-bouwplan.md`. Fase 5 → wordt na afronding van alle Fase-5-stappen in één keer geschreven (F5-1), niet incrementeel.

---

## Status

Fase 4 volledig afgerond en gemerged naar main (Stap 4.14).

**Fase 5.0 (Fundament + homepage) afgerond** in vier sub-blokken met 24 beslissingen (F5-1 t/m F5-24). Testsuite ging van 526 → 553.

**Fase 5.1 volledig afgerond** — publieke bestemmingen- en locatie-pagina's live:
- **5.1.a** (data-blokker) — DemoContentSeeder verrijkt (6 destinations, 14 locations, 30 posts, 6 routes), 62 Pexels fixture-images, is_featured data-laag. Beslissingen F5-25 t/m F5-32. Suite 553 (data-only).
- **5.1.b** (is_featured admin-toggle UX) — drie sub-blokken (Destination/Route/Post) + twee chores. Beslissingen F5-33/F5-34. Suite 553 → 571.
- **5.1.c** (`/bestemmingen` index) — één commit. Beslissingen F5-35 t/m F5-39. Suite 571 → 577.
- **5.1.d** (`/bestemmingen/{destination}` detail) — één commit, voorafgegaan door descriptions-chore. Beslissingen F5-40 t/m F5-48. Suite 577 → 584.
- **5.1.e-i** (`/bestemmingen/{destination}/{location}` detail, statisch) — hero + bento-gallery + breadcrumb-partial site-breed + terug-CTA. Beslissingen F5-49 t/m F5-57. Suite 584 → 593.
- **5.1.e-ii** (Leaflet-kaart op location-detail) — vanilla-JS-module + OSM-tiles. Beslissingen F5-58 t/m F5-65. Suite 593 → 595.

**Fase 5.2 in uitvoering** — Posts + comments + blog-index + reistips.

- **5.2.0 (blocker-chore) afgerond** — `scopePublished()` op Post (F5-67) + post-content-verrijking (F5-68) + reistips-seeding (F5-69). 30 posts kregen realistische NL-excerpts, 7 (incl. featured) volledige NL-body's, rest korte-maar-echte NL-body's; 5 losse reistips toegevoegd (3 bestemming-gebonden, 2 algemeen). Nul Lorem meer. Twee commits (`736680d` scope-infra, `00953c4` content-chore), beide gepusht. Suite 595 (geen tests toegevoegd — data/scope-chore).
- **5.2.a (blog-index + post-detail-fundament) afgerond** — `$post->url()`-helper (F5-71/F5-72/F5-74), publieke routes (`posts.index` op `/verhalen`, `posts.show` 3-segment, `reistips.show`), publieke `PostController` met `show`/`showTip`/`renderDetail` (F5-78), kale detail-view (F5-73), herbruikbare `<x-public.post-card>` (F5-75), homepage-kaart gemigreerd naar de component (fixt de kapotte 2-segment-URL), "Verhalen"-nav-item (F5-79). 19 nieuwe tests. Eén commit `0c198cd`. Suite **595 → 614 (1505 assertions)**.

- **5.2.b (post-detail afmaken + comments) afgerond** — in twee commits (F5-80). **5.2.b-i** (`0c7cedf`): edge-to-edge hero + `large`-conversie op `featured` (F5-82), breadcrumb (F5-83), SEO-meta via override-kolommen (F5-84), body-prose-scope + purify-at-save (F5-81), gerelateerde posts (F5-85). 7 tests. **5.2.b-ii** (`8471464`): publiek comments-systeem — `POST /reacties/{post:slug}` + `CommentController@store` + `StoreCommentRequest` + honeypot (F5-90), volledige 1-niveau-threading met reply-toggle (F5-86), eigen pending zichtbaar met label (F5-87), inlog-oproep voor gasten (F5-88), oudste-eerst (F5-89), `<x-public.comment>` + `<x-public.comment-form>`. 12 tests. Suite **614 → 633 (1568 assertions)**.

- **5.2.c (reistips-categorie-view op `/reistips`) afgerond** — in twee commits. **5.2.c-i** (`01a2fd1`): `indexTips()` op de publieke `PostController` + `reistips.index`-route (`/reistips`), nieuwe view `reistips/index.blade.php` (hergebruikt `.post-grid` + `<x-public.post-card>`), tips geweerd uit `/verhalen`, "Reistips"-breadcrumb-kruimel op tip-detail nu een echte link (F5-91 t/m F5-95). 6 tests. **5.2.c-ii** (`33c2fb5`): cross-linking — tips-strook "Reistips voor deze reis" op destination-detail, linkt naar de canonieke `/reistips/{slug}` (F5-96, sluit de F5-72-loose-end). 5 tests. Suite **633 → 644 (1597 assertions)**.

- **Fase 5.3 volledig afgerond** — publieke reisroutes + fotogalerij:
  - **5.3.0** (blocker-chore, `fa7d16a`) — alle 6 seeder-routes gepubliceerd (`is_published`+`published_at=travel_date`); lucht `/reisroutes` én het homepage-featured-routes-blok op. Descriptions waren al echt-NL (geen Lorem-chore). F5-99. Suite 644 (data-only).
  - **5.3.a** (`4ed204f`) — `/reisroutes`-index (featured-voorrang + badge) + kale route-detail; `<x-public.route-card>` geëxtraheerd, Route.hero-conversies gealigneerd, `isPublished()`. F5-98 t/m F5-103. Suite 644 → 655.
  - **5.3.b** (`aee7a77`) — route-detail compleet: `leaflet-route.js` (genummerde markers + polylijn), waypoint-links + notes, bestemming-link, "Verhalen van deze reis"-strook. F5-104/F5-105. Suite 655 → 659.
  - **5.3.c** (`b6f00fa`) — `/fotos`-galerij: progressive bestemming/locatie-pills, uniform 3:2-grid, eigen Alpine-lightbox (`photo-lightbox.js`). F5-106/F5-107. Suite 659 → 665.

- **Fase 5.4 volledig afgerond** — auteurs + statische pagina's:
  - **5.4.0** (blocker-chore, `7281f52`) — FamilyMember-bio's + Page-body's (over-ons/privacy/contact) van Lorem naar echte NL; dubbele FamilyMember-seeder geconsolideerd (8 → 4 leden). F5-116. Data-only, suite 665.
  - **5.4.a** (`90527a0`) — `/auteurs/{familyMember:slug}` (naam/rol/bio + initialen-avatar + gepagineerde verhalenlijst bij gekoppelde auteurs) + `/over-ons` (Page-intro + FamilyMembers-grid) + `<x-public.avatar>`. F5-109/F5-110/F5-114/F5-115. Suite 665 → 673.
  - **5.4.b-i** (`1ed0628`) — statische pagina's via catch-all `/{page:slug}` (single-segment, sluit `reserved_slugs` uit via lookahead-constraint) + `Page::isPublished()`. F5-111. Suite 673 → 679.
  - **5.4.b-ii** (`b726b56`) — open contactformulier `/contact` (honeypot + throttle, `ContactMail` queued, mail-only). F5-112/F5-113. Suite 679 → 682.

- **Fase 5.5 volledig afgerond** — publieke nieuwsbrief-kant (contact was al in 5.4.b):
  - **5.5.a** (`e219179`) — publieke aanmelding + double-opt-in-bevestiging: `GET/POST /nieuwsbrief` (throttle:6,1 + honeypot) + `GET /nieuwsbrief/bevestigen/{token}`, unique-loze `SubscribeRequest`, publieke `NewsletterSubscriptionController` (show/store/confirm), eigen resultaatpagina's, footer-link. Leunt op bestaande `SubscribeAction`/`SendConfirmationMailAction`/`ConfirmSubscriptionAction`. Sluit de confirm-placeholder die de admin-mail al gebruikte. F5-117 t/m F5-122. Suite 682 → 690.
  - **5.5.b** (`51e2406`) — publieke unsubscribe: `GET /nieuwsbrief/uitschrijven/{token}` + `unsubscribe()` + eigen resultaatpagina, leunt op bestaande `UnsubscribeAction` (idempotent). Sluit F4-N11 (testmail-footer-placeholder landt nu netjes i.p.v. 404). F5-123. Suite 690 → 693.

- **Fase 5.6 afgerond** — eindcheck-verificatiepass + `fase-5-bouwplan.md` (fase-4-format, repo-root). Geen cleanups meegepakt; die staan getrieerd voor Fase 6. F5-124.

- **Fase 5 volledig afgerond.
** Volgende: **Fase 6** — SEO (Spatie SEO/OG/JSON-LD, sitemap, robots, RSS), response-cache, WebP, cookie-banner/analytics-afweging, Lighthouse/WCAG, en de productie-deploy naar NL shared hosting. Vooraf: echte content via de admin invoeren (demo-seeder draait niet mee op productie).

State-check volgende sessie: `git log --oneline -6` (verwacht `51e2406` of de CLAUDE.md-docs-commit daarboven, clean), `git status` (clean), `php artisan test` (verwacht 693). **Let op:** er staat nu een catch-all `/{page:slug}` (fallback voor statische pagina's) als laatste route — nieuwe publieke één-segment-routes moeten ná registratie ook in `config('westein.reserved_slugs')` (anders blokkeert F4-11 niet, en is een gelijknamige pagina onbereikbaar).

## Loose ends

Opgelost in Fase 5.0:
- ~~`welcome.blade.php` vervangen door eigen homepage~~ (5.0.c)
- ~~`ExampleTest.php` verwijderen~~ (5.0.c)
- ~~Sessies-invalidatie bij email-change door admin (F4-U18)~~ (5.0.d)

Opgelost in Fase 5.1:
- ~~`is_featured`-flag toevoegen aan Destination/Post/Route~~ (5.1.a — kolommen + scopes, 5.1.b — admin-toggle)
- ~~`SubscriberDemoSeeder` weesbestand~~ (5.1.a — verwijderd)
- ~~`config('app.faker_locale') = 'en_US'`~~ (5.1.a — via `.env` op `nl_NL`; localiseert alleen data-methodes, niet tekstgenerators — zie landmine)
- ~~`.location-card__link` `href="#"` placeholder~~ (5.1.e-i — vervangen door `route('locations.show', ...)`)

Opgelost in Fase 5.2:
- ~~Post-URL-helper voor null-destination~~ (5.2.a — `$post->url()` model-methode, F5-71/F5-74; de drie post-vormen correct, location-loze niet-tip faalt luid)
- ~~Faker-Lorem-valkuil op post-detail~~ (5.2.0 — F5-68 content-verrijking, nul Lorem meer)
- ~~Home-item in blog-nav wel/niet houden~~ (F5-79 — blijft; "Verhalen" toegevoegd tussen Bestemmingen en Reistips)
- ~~Post-hero `large`-conversie ontbreekt~~ — de `featured`-media-collectie op Post registreert alleen `thumb` (400) + `medium` (800), geen `large`. Voor een edge-to-edge post-hero (5.2.b) zit het plafond op 800px, wat op 1440+ viewports upscalet. Afwegen in 5.2.b: `large`-conversie toevoegen (migratie-vrij, maar vereist re-conversie van bestaande media) of hero-breedte beperken.
- ~~Cross-linking destination-detail → tips~~ (5.2.c-ii — tips-strook "Reistips voor deze reis" op destination-detail, F5-96)

Opgelost in Fase 5.3:
- ~~Reisroutes + Foto's dode nav-links~~ (5.3.a/5.3.c — `/reisroutes` en `/fotos` zijn nu levende routes; alleen Contact blijft dood tot 5.4).
- ~~Route.hero ↔ Location.gallery conversie-mismatch ("alignen we tijdens views-stap")~~ (F5-103 — Route.hero-conversies hernoemd naar thumb/medium/large).

Opgelost in Fase 5.4:
- ~~Pages-routing bestaat nog niet~~ (5.4 — `/over-ons` eigen route, Privacy via catch-all `/{page:slug}`, Contact eigen route + formulier).
- ~~Contact dode nav-link~~ (5.4.b — laatste dode nav-link nu levend).
- ~~Catch-all-volgorde-waarschuwing~~ — opgelost én gecorrigeerd: de oude aanname ("catch-all als láátste vóór de auth-groep") klopte niet, want `admin.php` laadt ná `web.php`. Nu een single-segment GET-catch-all die `reserved_slugs` uitsluit via een lookahead-constraint (F5-111). Zie landmines.

Opgelost in Fase 5.5:
- ~~Publieke unsubscribe-route `/nieuwsbrief/uitschrijven/{token}` (F4-N11)~~ (5.5.b — live, testmail-placeholder landt nu netjes).
- ~~Confirm-route-placeholder in `SubscriberConfirmationMail`~~ (5.5.a — `/nieuwsbrief/bevestigen/{token}` live, admin-verzonden bevestigingsmail werkt end-to-end).

Nog open:
- **Destination-brede post-URL (2-segment) uitgesteld** (F5-74) — `/bestemmingen/{dest}/{slug}` botst structureel met `locations.show` en komt niet voor in de data (elke niet-tip-post heeft een location). Later toe te voegen via gedeelde-route-resolver (met slug-namespace-validatie) of onderscheidend segment, zónder F5-74 terug te draaien. `url()` faalt luid als het geval ooit optreedt.
- **Hero-intro-tekst verfijnen** in `home.blade.php` + intro op `/verhalen`-index — placeholders met TODO gemarkeerd. Martin verfijnt later.
- **Flash-key inconsistentie in admin-controllers** — `RouteController` gebruikt `->with('success', ...)`, andere (Destination, Location, Comment) gebruiken `->with('status', ...)`. De `admin._partials.flash`-partial rendert alleen `success/error/info/warning`, dus `status`-flash-messages worden nooit getoond. Fix in Fase 6-cleanup: kies één convention en migreer alle controllers.
- **Lege `resources/views/public/`-directory** — vermoedelijk Fase 1-scaffolding-restant. Alle publieke views leven in `resources/views/{destinations,locations,posts}/`, `home.blade.php`, en `partials/`. Kandidaat voor 5.6 eindcheck-cleanup.
- **Tailwind 4.0 uit `package.json` verwijderen** (`@tailwindcss/vite`, `tailwindcss`) — Laravel 11-scaffold-restant, we gebruiken Bootstrap. Kandidaat voor 5.6 of Fase 6.
- **Sass 3.0-migratie voor Bootstrap 5.3 SCSS** — `npm run build` produceert honderden deprecation warnings: `@import` → `@use` (alle SCSS entry-imports), Bootstrap 5.3 SCSS `if()`-syntax, `mix()` → `color.mix()`, `unit()`-globals, `red()`/`green()`/`blue()` → `color.channel()`. Bootstrap's eigen SCSS is niet forward-compatible; onze eigen partials gebruiken de nieuwe syntax al niet. Migrator `sass-migrator module` beschikbaar. Vendor-upgrade of migratie naar `@use` in eigen imports. Fase 6-cleanup.
- **Import-conventie inconsistentie** — sommige public partials worden als `@import 'public/foo'` zonder underscore geïmporteerd, andere (`_destinations-index`, `_destinations-show`, `_posts-index`) mét underscore. Functioneel identiek (Sass resolvet beide). Mee te nemen in Fase 6-cleanup.

---

## Project

Schaalbare, veilige Laravel-reisblog voor familievakanties Westein. Server-side Blade, geen SPA. NL-talig, multi-generatie publiek. Doel: SEO + duurzaam onderhoud op NL shared hosting.

## Stack — definitief

- **Backend:** Laravel 13.7, PHP 8.3+, MySQL 8
- **Frontend:** Blade + Bootstrap 5 + Alpine.js + Vite
- **Editor:** TipTap **v3** — `rich` (Posts) en `simple` (Pages, Newsletter)
- **HTML-sanitization:** `mews/purifier` — named configs per profiel
- **Kaarten:** Leaflet
- **Auth:** Laravel Fortify
- **Permissions:** Spatie Laravel Permission (rollen: Admin, Editor, Auteur, Lid)
- **Media:** Spatie Media Library + intervention/image v4 (GD-driver, portable)
- **SEO/Slugs/Spam:** Spatie SEO + Sitemap + Sluggable + Honeypot
- **E-mail rendering:** Pelago Emogrifier (CSS-inlining voor newsletters)
- **Tests:** Pest 4 (geen PHPUnit-classes — `RefreshDatabase` centraal, rollen per testfile in `beforeEach`)
- **Lokaal dev:** Herd + DBngin + VS Code op Windows. Projectroot: `C:\Herd\westein-reisblog` (buiten OneDrive)
- **Versiebeheer:** Git + GitHub (private repo)
- **Hosting:** NL shared hosting t.b.d. (Hostnet voor mail)
- **Vertalingen:** `lang/nl/` met `auth.php`, `validation.php`, `passwords.php`, `pagination.php` + `lang/nl.json`. `APP_FALLBACK_LOCALE=en`. Lang-bestanden **UTF-8 zonder BOM** (BOM op `<?php` crasht de translator).
- **PHP upload-limieten lokaal:** `upload_max_filesize=16M` / `post_max_size=32M` (Herd default 2M/8M). Restart Herd na php.ini-wijziging.

## Designkeuze — definitief

**"Modern magazine"** (Voorstel B uit Fase 1).

- Achtergrond: zandbeige `#F8F6F2`, tekst: `#14213D`
- Headings: Playfair Display (serif). Body: Inter (sans-serif)
- Accenten: perzik `#E8A87C`, salie-groen `#41B3A3`, gedempt rosé `#C38D9E`
- Stijl: edge-to-edge fotografie, magazine-uitstraling
- Design tokens: `resources/scss/design-tokens.scss`. Admin SCSS-partials in `resources/scss/admin/`.

## Werkstijl voor Claude

- Iteratief, stap voor stap. Niet alles in één keer.
- Verduidelijkende vragen via `ask_user_input_v0` met 2-4 opties. Eén beslissing per keer.
- Code in copy-pasteable blokken met duidelijke bestandsnamen.
- PowerShell-syntaxis (Windows). Single quotes bij regex-filters.
- Pest-syntax voor tests.
- Nederlands in uitleg en commits, Engels in code.
- Eerlijk over trade-offs.
- Geen herhaling van masterplan — verwijs ernaar (`§3.4`).
- Waarschuw bij secrets in chat. Adviseer roteren.
- Bestandsnamen exact in casing (Git en Pest zijn case-sensitive).
- **State-check (`git log`, file-existence, `php artisan test`) als allereerste stap bij elke sessie** — niet design-vragen, niet ontwerp, eerst feiten van de werkelijke codebase.
- **Test-cadans.** `--filter=<Module>` tijdens het bouwen (snelle feedback op alleen het relevante gebied); de volledige `php artisan test` als poort **vóór elke commit** (de tel die we per sub-blok bijhouden). Pure SCSS-/JS-/Blade-markup-stukjes: suite overslaan — die kunnen geen PHP-test breken, browser-eyeball volstaat. Optioneel `php artisan test --parallel` (mits `brianium/paratest`) om de volledige run te versnellen.

---

## Conventies — werk altijd zo

1. **Eén plek voor één ding.** Geen business-logic in Blade. Geen validatie in controllers. Geen queries in models.
2. **Naamgeving:** Engels in code, Nederlands in URL's en UI.
3. **Form Requests altijd** voor POST/PUT validatie. `authorize()` in de Request doet de policy-check voor store/update.
   - Namespace: `App\Http\Requests\Admin\{Module}\{Action}Request` (bv. `Admin\Newsletters\StoreNewsletterRequest`).
4. **Policies altijd** voor autorisatie. Laravel 11+: `$this->authorize('action', $model)` per controller-method (geen `authorizeResource()`, geen `middleware()` op controller — `AuthorizesRequests`-trait staat in base `Controller.php`).
5. **Eager loading discipline.** `with()` overal waar relaties getoond worden.
6. **Database-indexen vanaf het begin.**
7. **Tests:** Feature-tests voor kritische paden. Admin-tests in `tests\Feature\` direct, naamconventie `{Module}ManagementTest.php`. Model-tests in `tests\Feature\Models\`. Geen 100% coverage als doel.
8. **Pint vóór elke commit.**
9. **Line endings = LF.**
10. **Na élke `.env`-wijziging: `php artisan config:clear`.**
11. **Nooit echte secrets in chats/issues plakken.**
12. **Geen Laravel-projecten in OneDrive.**
13. **Per CRUD-module: server-side patroon.** Querystring-gestuurde filters/sort/paginate, `withQueryString()` op de paginator, `<x-admin.sort-link>` voor kolom-headers.
14. **Inline-delete via `<x-admin.delete-button>`** (tabellen) of **`<x-admin.card-actions-menu>`** (cards).
15. **Check bestaande componenten/conventies vóór nieuwe verzinnen.** Grep/Get-Content op een bestaande module draaien vóór je een form/CSS-patroon schrijft. Specifiek voor tabel-indexen: leen van `pages/index.blade.php`. Project gebruikt straight Bootstrap-utilities, geen `.admin-*`-custom-stelsel behalve `.admin-field`, `.admin-breadcrumbs__current`.
16. **Form-helpers die geen kolom zijn** (zoals `is_published`, `remove_portrait`, `remove_header`): filter uit `$validated` via `Arr::except($data, [...])` vóór `Model::create()`/`update()`.
17. **State-machine modules gebruiken verb-routes**, niet één PATCH met status-veld. Comments: `approve`/`reject`/`spam`. Subscribers: `confirm`/`unsubscribe`. Newsletters: `send-test`/`dispatch`. Status niet client-tamperbaar, leesbare logs. Edit-forms met meerdere velden tegelijk (Posts, Pages) houden hun status gewoon in de PATCH.
18. **Controller-method-naam vermijdt clash met framework-helpers.** `dispatch()` botst met `dispatch()`-helper, `Bus::dispatch()`, `->dispatch()`. Patroon: `dispatchSend()` of `dispatchTo()`. Route-naam mag wél `*.dispatch` blijven (verb-route mapping is expliciet).

## Architectuur — kernkeuzes

- Content-hiërarchie: Destination → Location → Post (Post mag óók direct aan Destination hangen)
- Reistips: categorie binnen Posts, geen aparte tabel
- Reacties: alleen ingelogde gebruikers, met moderatie
- Routes: geordende lijst van Locations + waypoints, Leaflet trekt rechte lijnen
- Foto's: album per Location (`gallery`), Post heeft eigen `featured` + `inline_images`
- Newsletter: eigen beheer (Subscriber + Newsletter + queued sending, double opt-in)
- Talen: alleen NL nu, structuur klaar voor uitbreiding (`__()` overal)

Volledige database-architectuur, ERD en URL-structuur: zie masterplan §3.

---

## Beslissingen — chronologisch genummerd

### Fase 2 (auth)
- F2-1. Registratie: open + e-mailverificatie verplicht
- F2-2. 2FA: verplicht voor Admin/Editor, optioneel voor andere rollen
- F2-3. Mail (dev + prod): SMTP via eigen domein `website.support@ml-westein.nl`
- F2-4. Rollen-model: meerdere rollen per gebruiker (Spatie default)
- F2-5. `Gate::before` returnt `null` (niet `false`) — laat policy-fallthrough toe

### Fase 3 (data)
- F3-1. Tags: polymorfe pivot, lowercase via mutator
- F3-2. Categorieën ↔ Posts: BelongsToMany, met `order`-veld voor handmatige sortering
- F3-3. Location-slugs: globaal uniek (afwijking van masterplan §3.3 — simpeler routing)
- F3-4. §3.4-validatie (location↔destination): in Form Request via `withValidator()`, niet in model
- F3-5. Slug-stabiliteit: alle HasSlug-modellen `doNotGenerateSlugsOnUpdate()`
- F3-6. FK-strategie Posts: `user_id` = `restrictOnDelete`, `destination_id`/`location_id` = `nullOnDelete`. Locations cascaderen bij destination hard-delete, blijven bij soft-delete.
- F3-7. Media collecties: Post=`featured`(single)+`inline_images`(multi), Location=`gallery`, Destination=`hero`+`gallery`, User=`avatar`, FamilyMember=`portrait`(single), Page=`hero`(single, niet ontsloten in UI), Newsletter=`header`(single). MIME: JPEG/PNG/WebP.
- F3-8. Post `author()`-relatie: hernoemd van `user()` voor consistentie met Comment + Newsletter — FK `user_id` blijft, `belongsTo()` krijgt expliciet `'user_id'` als 2e arg.

### Fase 4 — algemeen
- F4-1. UI-stack: strikt Blade + Bootstrap + Alpine, geen Livewire/Filament
- F4-2. Lijst-patroon: server-side via querystring + Laravel paginate (geen Alpine-fetch debouncing)
- F4-3. TipTap output: HTML + server-side sanitization via `mews/purifier`. Twee profielen: `rich` (Posts, alle extensions) en `simple` (Pages, Newsletter — StarterKit met `heading.levels:[2,3,4]`, link config). **v3 StarterKit levert Link + Underline zelf** — niet apart importeren.
- F4-4. Soft deletes op Posts, Destinations, Locations, Routes, Pages + `/admin/prullenbak` + auto-purge 30d. **Niet** op Comments, Users (AVG), Subscribers, FamilyMembers, Newsletters.
- F4-5. Slug-bewerking: bewerkbaar bij create, read-only bij update. Pages-patroon = simpelweg weglaten uit `rules()` van UpdateRequest (tamper-proof, geen `slug_display`-truc nodig).
- F4-6. Index-patroon: tabel voor Categories/Tags/Pages/Subscribers/Comments/Newsletters, cards voor FamilyMembers/Posts/Destinations/Routes/Locations.
- F4-7. Form-layout: two-column (`<x-admin.form-layout>`) voor modules met >4 velden. Categories/Tags blijven single-column.
- F4-8. User-deactivatie: `deactivated_at` (timestamp nullable) + `deactivation_reason` (text nullable). Geen hard-delete via UI.
- F4-9. Generieke media-endpoints met eigenaar-policy via `$media->model`. Client-side model-type via whitelist in `config('westein.gallery_models')` — nooit rauwe class-strings vertrouwen. Routevolgorde: statische `media/reorder` MOET vóór dynamische `media/{media}`.
- F4-10. AJAX-flow voor gallery: upload + reorder + delete los van form-submit. `<x-admin.gallery-upload>` op EDIT-pagina; `store→edit`-redirect zorgt dat het model bestaat.
- F4-11. Reserved slugs centraal in `config/westein.php`, gevalideerd via `App\Rules\NotReservedSlug` (alleen in StoreRequest).
- F4-12. Image-picker (4.6) browse-scope = projectbreed, gefilterd op content-collecties (`gallery`/`hero`/`featured`/`inline_images`). Avatars + portraits expliciet uitgesloten.
- F4-13. Inline-images landen in post-eigen `inline_images`-collectie. Geen centrale media-pool in v1.
- F4-14. Image-alignment via class (`img-align-{left|center|right|full}`), geen inline style. Purifier `URI.AllowedSchemes` = `http|https|mailto`.
- F4-15. Routes publicatie-model = `is_published` boolean + `published_at` timestamp (geen full enum). Hero met fallback-keten: eigen `hero` → eerste-waypoint-`gallery`-foto → null.
- F4-16. Routes waypoint-sync = delete-then-recreate. JSON in één hidden field als bron-van-waarheid. Revisits toegestaan (Fase-3-unique-constraint gedropt in 4.8).
- F4-17. Subscribers status afgeleid uit timestamps (`pending|active|unsubscribed`), geen kolom. Double-opt-in altijd, ook bij admin-add. CSV-import zonder auto-mail-dispatch. Uitgeschreven subscribers worden bij re-import silent gerespecteerd (geen reactivate — AVG).
- F4-18. Geen `dns`-rule op Subscriber email-validatie (alleen `email:rfc`). Te traag/flaky in dev en tests.
- F4-19. CSV-import + foutrapport-CSV via League\Csv. Foutregels op `local`-disk onder `imports/subscriber-errors/{ulid}.csv`. Generieke flash-partial-uitbreiding: `flash_action_url` + `flash_action_label` voor herbruikbare download-knoppen.

### Fase 4 — Newsletter (Stap 4.10)
- F4-N1. Newsletter beeld = `header`-collectie (Media Library, single), geen inline-images in body. Body = TipTap-simple + Purifier-simple. Inline-foto-galerijen linken naar Posts.
- F4-N2. Newsletter-templates hardcoded als Blade-files in `resources/views/emails/newsletter/templates/` (`announcement`, `digest`, `plain`). `template`-kolom op `newsletters`-tabel, default `plain`. Geen DB-driven template-beheer.
- F4-N3. Newsletter test-modus = "Stuur naar mezelf"-knop, geen vrij invulveld. Subject krijgt `[TEST]`-prefix, geen `newsletter_sends`-row.
- F4-N4. Newsletter audit-trail = sent + per-subscriber timestamp. Geen tracking-pixel (AVG), geen bounce-tracking (Hostnet-SMTP levert geen webhooks). `bounced_at`/`opened_at`-kolommen blijven leeg in v1, schema is forward-compatible.
- F4-N5. Newsletter dispatch vereist modale confirmation met expliciete recipient-count + subject. Onomkeerbaar zodra in queue.
- F4-N6. Newsletter scheduling uitgesteld naar v2. `scheduled_at` + status `scheduled` blijven in schema/factory voor stabiliteit. v1 flow: `draft → sending → sent`.
- F4-N7. Spatie Media Library conversies project-breed `->nonQueued()` sinds 4.10d. In dev draait geen permanent `queue:work`; sync-conversie van ~70ms per WebP-resize is praktischer voor een familieblog. Geldt voor élk model met `RegistersMediaConversions`-trait. Queue-driven kan terug in Fase 6 bij supervised hosting.
- F4-N8. `NewsletterMail` is **niet** `ShouldQueue`. Mailable = data, Job = transport. Testmail draait sync (controller → `Mail::to()->send()`); bulk-dispatch (blok f) wikkelt de Mailable in `SendNewsletterJob` (`ShouldQueue`) dat per subscriber een eigen queued send doet. Asymmetrie met `SubscriberConfirmationMail` (wél `ShouldQueue`) is geredeneerd: signup-confirmation hangt op publieke HTTP-respons, newsletter-test op admin-feedback-loop die `queue:work` niet vereist.
- F4-N9. Announcement-template heeft geen apart CTA-veld op het model. Body (TipTap) bevat zelf de link. Visueel onderscheid met `plain` zit in kop-styling, niet in een knop-block.
- F4-N10. Digest-template haalt op render-tijd de meest recente gepubliceerde posts op, count via `config('westein.newsletter.digest_post_count', 5)`. Testmail = snapshot van *nu*, dispatch = snapshot van *send-time* — gelijktijdige publicaties tussen test en dispatch kunnen de digest beïnvloeden. Acceptabel voor v1.
- F4-N11. Testmail unsubscribe-placeholder = realistische URL `/nieuwsbrief/uitschrijven/{64-nul-token}`. Klikt naar 404 tot Fase 5 publieke unsubscribe-route levert; bewust realistisch ipv `#`-anchor zodat de footer in de testmail visueel identiek is aan de uiteindelijke productie-mail.
- F4-N12. Newsletter dispatch via `Bus::batch()` — N `SendNewsletterJob`'s in één batch met `finally()`-callback voor status-flip. Boven onafhankelijke jobs (geen completion-callback) en self-check-pattern (race-gevoelig). `job_batches`-tabel zit al in Laravel 11+ default-migratie `0001_01_01_000002_create_jobs_table.php`; geen extra migratie nodig.
- F4-N13. `DispatchNewsletterAction` doet eager-create: bulk-insert `newsletter_sends`-rijen (alle pending) via `NewsletterSend::insert()` in dezelfde transactie als status-flip + batch-build. Job neemt `int $newsletterSendId` als signature. Authoritative ontvanger-snapshot vanaf t=0; DB-unique-constraint op `(newsletter_id, subscriber_id)` blokkeert race-double-dispatch via transactie-rollback. Geen `WithoutOverlapping` nodig.
- F4-N14. Status-flip `sending → sent` via `Bus::batch()->finally()`-callback (closure-capture van `$newsletter`). Vuurt zowel bij volledige success als bij partial-failure — "sent" betekent "alle delivery-pogingen afgerond", niet "iedereen ontvangen". Per-subscriber-uitkomst leeft in `newsletter_sends.sent_at` vs `failed_at`; Show-pagina aggregeert.
- F4-N15. `DispatchNewsletterAction` is graceful bij zero actieve subscribers — `DispatchNewsletterRequest::withValidator()` is enige guard. Action vertrouwt op die enkele check, dispatcht in dat geval een lege batch (`finally()` flipt status alsnog correct).
- F4-N16. `@stack('modals')` in `layouts/admin.blade.php` als project-brede modal-conventie. Modules pushen via `@push('modals') ... @endpush`. Centraal modal-niveau (body-z-index/backdrop/focus-trap), schoner dan elke module z'n eigen sibling-DOM-positie. Geldt voor blok-f dispatch-confirm en latere modules (prullenbak 4.12, etc.).
- F4-N17. Newsletter Show-pagina is status-dashboard-stijl: vier KPI-cards (Totaal / Bezorgd / Mislukt / In wachtrij) bovenaan, gepagineerde tabel van alle `newsletter_sends`-rijen daaronder met statusfilter + sort op `sent_at`/`failed_at`/`created_at`. KPI's via één `DB::table()`-query met conditionele `SUM(CASE WHEN...)` + `COALESCE` + `(int)`-cast.
- F4-N18. Show-pagina werkt op alle drie statussen: bij `draft` info-alert "nog niet verzonden", bij `sending`/`sent` het KPI+tabel-overzicht. Geen redirect of 404 — admin kan elke status openen om context te zien.

### Fase 4 — Prullenbak (Stap 4.12)
- F4-T1. RBAC: Admin + Editor via nieuwe permission `trash.manage`. Auteur/Lid geen toegang. Parallel met 4.11's `media.browse`. Per-model policies (PostPolicy etc.) blijven voor de daadwerkelijke restore/force-actie; `trash.manage` gate't alleen de browser.
- F4-T2. Layout: **unified single index** met model-type-filter (4.11-patroon). Eén `TrashController@index`, één view, heterogene rijen genormaliseerd naar (titel + type-badge + context-subline + verwijderd-datum + acties). Geen tabs, geen sub-pagina's.
- F4-T3. Audit-trail: **geen** `deleted_by`-tracking. Alleen `deleted_at`. YAGNI voor familieblog-schaal (vrijwel altijd één admin). Later toevoegen als concreet gemist.
- F4-T4. Force-delete cascade: **blokkeren** zolang zelfstandige-content-children bestaan (levend óf soft-deleted). In v1 raakt dat alleen Destination → Locations. Voor Route → waypoints (pivot) en Post → comments/inline_images (media-children) geen blokkade — die tellen niet als zelfstandige inhoud. Communicatie: pre-computed blocked_reason in DTO, `<x-admin.delete-button>` pre-disabled met Bootstrap-tooltip.
- F4-T5. Restore-cascade: **omhoog** door de keten (Post → Location → Destination) in één transactie, met expliciete flash-melding die alle mee-hersteld items noemt. Asymmetrisch met T4 en bewust: destructie krijgt wrijving als veiligheid, herstel krijgt smoothness als admin-intentie.
- F4-T6. Bulk-acties: **alleen bulk-restore**, geen bulk-force-delete. Bulk-restore is safe (T5 werkt lineair per item); bulk-force-delete voegt destructief risico toe met beperkte waarde voor familieblog-schaal. Sticky action-bar toont één knop.
- F4-T7. Per-item UX: **beide inline**. Herstel = simpele form-POST-knop. Definitief = `<x-admin.delete-button>`-patroon met pre-disabled bij children > 0. Geen modal per rij (T4-B haalt cascade-info-behoefte al weg — force-delete klikbaar alleen als item "alleen" staat).
- F4-T8. Filters: **minimaal**. Alleen type-filter, sort fixed op `deleted_at desc`. Geen tekst-zoek, datum-range of sort-toggle. Match schaal (0-10 items typisch) met scope.
- F4-T9. Sessie-scope: seeder-cleanup meepakken (legacy `media.upload`/`media.delete` verwijderd) + ad-hoc `@can('trash.manage')` rond alleen de nieuwe Prullenbak-link. Volledige `<x-admin.nav-link>`-retrofit uitgesteld naar 4.13. Auto-purge = **Fase 6** (cron), niet 4.12 — v1 is puur handmatig.

**Gedeelde infrastructuur uit 4.12:**
- `App\Services\Trash\TrashBrowser` — per-model `onlyTrashed()`-queries mergen tot Collection, sort op `deleted_at` desc, paginate via `LengthAwarePaginator`. Public `const TYPES` als whitelist voor type-filter. Heterogene DTOs (stdClass) met `type`, `type_label`, `title`, `context`, `deleted_at`, `blocked_reason`. Pre-computed children-count voor Destination via `withCount + withTrashed()`-closure.
- `App\Actions\Trash\RestoreTrashItemAction` — `match($type)` → `onlyTrashed()->find()`, `collectAncestors()` traverseert Post → Location → Destination met dedup (dubbele Destination-paden via Post FK én Location). Wrap in `DB::transaction`. `RestoreResult` DTO met ancestors-first array + `flashMessage()`.
- `App\Actions\Trash\ForceDeleteTrashItemAction` — `blockingReason()` gate't. Throw `RuntimeException` met exact zelfde tekst als tooltip, controller converteert naar error-flash.
- `App\Actions\Trash\BulkRestoreTrashItemsAction` — leunt op single-action, silent-skip op `ModelNotFoundException` (bijv. race met force-delete), ancestor-dedup op `type:title`-key over de hele batch. `BulkRestoreResult` DTO met tri-count (primary/ancestor/failed).
- `App\Http\Requests\Admin\Trash\BulkRestoreRequest` — max:100 cap, `Rule::in(...)` op type, `prepareForValidation()` decode't JSON-string payload.

### Fase 4 — Users + rollen beheer (Stap 4.13)
- F4-U1. Scope volledig: index + create + edit + rollen + deactivate/reactivate + admin-triggered wachtwoord-reset + 2FA-status inspectie + 2FA-force-disable.
- F4-U2. Beide role-guards: geen zelf-lock op admin-rol + geen laatste-admin-verlies. Enforcement via `withValidator()` in `UpdateUserRequest`; bulk-spiegel in `BulkDeactivateUsersRequest`.
- F4-U3. Create-flow via invite-mail met Fortify password-reset-link (hergebruikt de Fortify-token-broker; niet Fortify's default `sendResetLink` maar handmatig `Password::createToken()` + custom Mailable).
- F4-U4. `email_verified_at` wordt automatisch op `now()` gezet bij succesvolle password-reset via invite-link, via listener op `Illuminate\Auth\Events\PasswordReset`. Idempotente no-op voor al-geverifieerde users.
- F4-U5. Gedeactiveerde users: hard block via Fortify `authenticateUsing()`-callback, dezelfde generic error als bij fout wachtwoord (geen info-leak).
- F4-U6. Bestaande content blijft zichtbaar met auteur-naam bij deactivatie (geen retroactieve anonimisering). Gedeactiveerde users blijven zichtbaar in admin-paneel met status-badge en reactivate-knop.
- F4-U7. Index-patroon: tabel (niet cards) — avatar-thumb naast naam, sortable kolommen, badges voor rollen/status. Consistent met andere operationele modules (Subscribers, Comments).
- F4-U8. Filters: tekst-zoek (naam+email) + rol-filter + status-filter. Sort: naam/email/created_at, default `created_at desc`. Geen 2FA-filter (F2-2 impliceert dat Admin/Editor sowieso 2FA hebben, marginale use case).
- F4-U9. Bulk-acties: alleen bulk-deactivate + bulk-reactivate, geen bulk-rol-toewijzen. Hergebruikt `Alpine.store` + sticky action-bar patroon (F4-M8/M9).
- F4-U10. Laatste-admin-guard strikt: alleen actieve admins tellen. Guard blokkeert zowel rol-verwijdering als deactivate van de laatste actieve admin. Concrete query: `User::role('admin')->active()->where('id', '!=', $editedUser->id)->count() > 0`.
- F4-U11. RBAC: alleen Admin via bestaande permission `users.manage`. Editor/Auteur/Lid krijgen 403. `roles.manage`-permission uit seeder blijft voor toekomstige rol-CRUD.
- F4-U12. Tabel-kolommen minimaal: avatar+naam, email, rollen, status, acties. Geen created_at-kolom (blijft wel sort-optie), geen 2FA-badge (die zit op edit-view).
- F4-U13. Sidebar volledig retrofit: `<x-admin.nav-link>` krijgt optionele `:can`-prop. Alle sidebar-items retrofit met permission-strings. Dode `admin.locaties.index`-link gedropt. Ad-hoc `@can('trash.manage')`-wrap uit 4.12 vervangen door prop.
- F4-U14. Sub-blok-opdeling zeven blokken: a (foundation + sidebar), b (index), c (create + invite), d (edit + guards), e (deactivate + login-block), f (admin-reset + 2FA-disable), g (bulk).
- F4-U15. `Route::resource(...)` `->except(['show', 'destroy'])` — géén destroy-route en géén destroy-method op controller. Defense-in-depth voor "geen hard-delete via UI" (F4-8).
- F4-U16. Rollen-UI op create: multi-select checkboxes met `lid` default aangevinkt. Op edit: multi-select met huidige rollen aangevinkt (geen default-preselect).
- F4-U17. Invite-mail: custom `UserInvitationMail` (queued Mailable) met eigen "Welkom, activeer je account"-tekst, niet Fortify's default `ResetPassword`-notification. Markdown-template in `resources/views/emails/users/invitation.blade.php`. Onder de motorkap zelfde token-flow.
- F4-U18. Edit-scope: name/email/roles. Email-wijziging reset `email_verified_at` naar null en dispatcht nieuwe invite-mail via `SendUserInvitationAction`. Controller detecteert email-change vóór save. Bij edit gebruikt `Rule::unique(...)->ignore($user->id)`. Sessies-invalidatie bij email-change is niet expliciet afgehandeld (edge case op familieblog-schaal).
- F4-U19. Beide guards (F4-U2 en F4-U10) worden afzonderlijk gecheckt in `withValidator()` — bij overlap tonen beide foutmeldingen. Bewust: informatiever dan opeenvolgende checks.
- F4-U20. Deactivate-flow: optioneel `deactivation_reason`-veld in confirm-modal (mag leeg blijven, max 500 tekens). Toont in edit-view banner + tooltip op status-badge. Bij reactivate wordt zowel `deactivated_at` als `deactivation_reason` op null gezet — geen historie-tracking (F4-T3-parallel).
- F4-U21. Edit-view UI: aparte "Beheeracties"-sectie onderaan het form met wachtwoord-reset + 2FA-uitzetten (conditioneel) + deactiveren. Deactiveren verplaatst uit form-onderkant naar deze zone. Sectie verborgen als user zichzelf bewerkt (F4-U2-spiegel op UI-niveau).
- F4-U22. Bulk-actie-UI: twee knoppen altijd zichtbaar bij selectie (Deactiveren + Reactiveren). Silent-skip op reeds-in-target-state users via `whereNull`/`whereNotNull`-clauses in de Action. Geen contextuele knoppen op basis van selectie-mix (overengineered voor schaal).

**Gedeelde infrastructuur uit 4.13:**
- `App\Actions\Users\SendUserInvitationAction` — genereert password-reset-token via `Password::createToken()` (niet `Password::broker()->createToken()` —
- `App\Actions\Users\BulkDeactivateUsersAction` + `BulkReactivateUsersAction` — silent-skip via `whereNull`/`whereNotNull('deactivated_at')`-scope. `DB::transaction` wrapt de loop. Return `int $affected` voor flash-pluralisatie via `trans_choice`.
- `App\Mail\UserInvitationMail` — queued Mailable (`implements ShouldQueue`), constructor `User $user, string $activationUrl`. Envelope-subject via `config('app.name')`. Markdown-content in `emails.users.invitation`.
- `App\Listeners\MarkEmailVerifiedAfterPasswordReset` — geregistreerd in `AppServiceProvider::boot()` via `Event::listen(PasswordReset::class, ...)`. Guard-clause: al-geverifieerd → no-op. Anders `forceFill(['email_verified_at' => now()])->save()`.
- `App\Http\Requests\Admin\Users\StoreUserRequest` — email:rfc + `Rule::unique('users', 'email')`, roles-array met `Rule::in()`-whitelist tegen tampering (rol-namen uit `Role::pluck('name')`).
- `App\Http\Requests\Admin\Users\UpdateUserRequest` — bovenop Store: `Rule::unique(...)->ignore($editedUser->id)`, plus twee guards via `withValidator()`: `guardNoSelfLock` (F4-U2) + `guardNoLastAdminRoleLoss` (F4-U10). Beide voegen foutmeldingen toe onder `roles`-key.
- `App\Http\Requests\Admin\Users\DeactivateUserRequest` — reason nullable max 500. Bulk-spiegel-guards via `withValidator()`: `guardNoSelfDeactivate` (F4-U2) + `guardNoLastAdminDeactivate` (F4-U10). Foutmeldingen onder `reason`-key.
- `App\Http\Requests\Admin\Users\BulkDeactivateUsersRequest` — `prepareForValidation()` decode't JSON-string payload uit hidden form input. `guardNoSelfInSelection` (F4-U2) + `guardNoLastAdminInSelection` (F4-U10). Max 100 IDs.
- `App\Http\Requests\Admin\Users\BulkReactivateUsersRequest` — zelfde payload-decode + rules, géén guards (reactiveren kan geen schade doen).
- `Fortify::authenticateUsing()` in `FortifyServiceProvider::boot()` — handmatige lookup + `Hash::check()` + gedeactiveerd-check. Retourneert `null` bij elke fail (generic error, geen info-leak). Bestaande rate limiters + view bindings ongewijzigd.

### Fase 4 — Media browser (Stap 4.11)
- F4-M1. Scope = volledige v1: read-only browser + per-item delete + bulk-selectie + bulk-delete via confirm-modal. Geen upload-flow in v1 (blijft via eigenaar-modellen, conform masterplan-#7).
- F4-M2. RBAC = aparte permission `media.browse`, toegekend aan Admin (via `Gate::before`) + Editor. Auteur en Lid hebben geen toegang. Per-item-eigenaar-policy bij delete blijft staan (F4-9), maar het policy-mix-scenario binnen bulk-delete is in productie-rollen-matrix niet realistisch (Editor heeft via `content.manage` + `posts.update.any` overal toegang) — getest met custom test-rol `media-browser-only`.
- F4-M3. Filters: collectie + eigenaar-modeltype + bestandsnaam-zoek + sort (kolommen `created_at`/`name`/`size`, default `created_at` desc). Filter-state in querystring (F4-2). Owner-type-filter via nieuwe config-key `browsable_media_owners` (5 modellen: destination, location, post, route, newsletter) — bewust losgekoppeld van `gallery_models` (upload-doelen vs browse-bron). Geen eigenaar-instance-filter in v1 — natuurlijker thuis op eigenaar-edit-pagina via deeplink in latere ronde.
- F4-M4. Layout = grid van thumbs (6/4/2/1 kolommen responsive), niet tabel. Thumb-image is primair visueel signaal bij media-beheer; sortable headers boven het grid behouden tabel-functionaliteit zonder visuele opoffering. Geen view-toggle.
- F4-M5. Per-item delete = inline-confirm-toggle in een grid-specifieke overlay-component (`<x-admin.media-delete-overlay>`), niet via `<x-admin.delete-button>` (form-gebaseerd, past niet bij AJAX-flow in grid-context). Modals blijven voorbehouden aan zwaargewicht-mutaties (bulk + newsletter-dispatch) — signaalwerking van risico-schaal.
- F4-M6. Bulk-selectie = pagina-scoped (1a). "Selecteer alle zichtbare"-control boven het grid; geen "selecteer alle X resultaten op filter"-feature. `POST admin/media/bulk-delete` met `ids[]`-payload (max 100), `DB::transaction` met harde rollback bij élke policy-fail (geen best-effort-delete UX).
- F4-M7. Implementatie-opdeling = drie sub-blokken (4.11.a foundation + browser, 4.11.b per-item delete, 4.11.c bulk-flow), conform Stap 4.10's commit-discipline.
- F4-M8. Action-bar locatie = sticky-bottom (Gmail-stijl). Scrollvolgend, dichtbij waar selectie plaatsvindt op grid-onderkant, geen overlap met top-content. `z-index: 1030` (boven navbar, onder modal).
- F4-M9. Action-bar inhoud = minimaal: counter + Selectie wissen + Verwijderen. Geen "X van M geselecteerd"-formulering (cognitive load), geen disabled-placeholders voor v2-features.

**Gedeelde infrastructuur uit 4.11:**
- `App\Services\Media\MediaQueryBuilder` — centrale query-laag voor browse-scope, gedeeld door `MediaPickerController` (4.6) en `MediaBrowserController` (4.11). Public consts `ALLOWED_COLLECTIONS` en `ALLOWED_SORT_COLUMNS`. Statische `contextLabel(Media $m)`-helper met cases voor alle vijf eigenaar-modellen (Route + Newsletter waren ontbrekend in de 4.6-versie, meegelift in de extractie).
- `Admin\MediaBrowserController` — naast bestaande `MediaController` (4.4 gallery-AJAX) en `MediaPickerController` (4.6 picker-JSON). Drie controllers, drie verantwoordelijkheden.
- `Alpine.store('mediaSelection', ...)` — eerste store i.p.v. data-factory in het project. Reden: `@push('modals')`-blok rendert op `</body>`-niveau, buiten elke component-scope; store is cross-scope bereikbaar via `$store`.
---

## Fase 5 beslissingen

### Fase-macro
- **F5-1** — Fase 5 opgedeeld in 7 stappen: 5.0 fundament + homepage, 5.1 bestemmingen + locaties, 5.2 posts + comments + blog-index + reistips, 5.3 routes + fotogalerij, 5.4 auteurs + statische pagina's, 5.5 newsletter + contact, 5.6 eindcheck + bouwplan. Cookie-banner + Analytics + Spatie SEO + sitemap + RSS gaan naar Fase 6.
- **F5-2** — 5.0 opgedeeld in vier sub-blokken: a (layout+nav+footer), b (/mijn-account), c (homepage + welcome-cleanup), d (F4-U18 sessies-invalidatie).

### Layout, navigatie, styling
- **F5-3** — Hoofdmenu bevat: Home + Bestemmingen + Reistips + Reisroutes + Foto's + Contact. Statische pagina's (Over ons, Privacy) staan in de footer. Home-item blijft voorlopig zichtbaar (twijfelpunt).
- **F5-4** — Site-nav (gedeeld met ml-westein subdomein-familie) als Blade-partial met hardcoded items in `resources/views/partials/site-nav.blade.php`. Active-state hardcoded op "Reizen".
- **F5-5** — Site-nav SCSS-strategie **A-hybrid**: kleuren identiek aan hoofdsite (blauw menu-links, rood-roze onderlijn), font Inter erft van blog-body. CSS-vars gescoped naar `.main-nav` (`--sitenav-primary`, `--sitenav-accent`, etc.) om conflict met blog-tokens te voorkomen.
- **F5-6** — Logo in site-nav via absolute URL naar hoofdsite (`https://ml-westein.nl/assets/img/logo_v3_192x149.png`) — single source of truth.
- **F5-7** — Twee-lagen-scheiding tussen site-nav en blog-nav: kleurverschillende banden (witte site-nav boven, dark navy blog-nav eronder).
- **F5-8** — Blog-nav macro-structuur: tekst-brand "Westein Reisblog" links (Playfair, klikbaar naar `/`), menu-items rechts (variant B).
- **F5-9** — Blog-nav achtergrondkleur = dark navy `#14213D` (`--color-text`).
- **F5-10** — Auth-state UI: profiel-dropdown rechtsboven met "Mijn account" + "Naar admin" (voor Admin/Editor/Auteur) + "Uitloggen". Uitgelogd: "Inloggen"-link. Alpine-patroon adapted van admin-usermenu.
- **F5-11** — `/dashboard` verwijderd; iedereen na login → `/`. Fortify's `home` config van `/dashboard` → `/`. `resources/views/dashboard.blade.php` verwijderd.
- **F5-16** — Magazine-accent = bestaande `--color-accent-1` (perzik `#E8A87C`) uit Fase 1 design-tokens. Terracotta-idee verworpen ten gunste van bestaand palet.
- **F5-17** — Bootstrap `$primary` blijft `#1E90FF` (blauw, matcht hoofdsite). Perzik-accent voor blog via custom classes (`.section-label`, `.btn-accent`, hover-onderlijnen). Bootstrap-standaard-elementen (`.btn-primary`) blijven blauw.
- **F5-18** — Fase 1 dead code weggehaald uit `app.scss`: `.site-header`, `.site-footer`, `.hero-magazine`.
- **F5-19** — Footer klassiek drie-kolommen structuur (brand + Ontdek + Info), analoog aan hoofdsite-patroon.
- **F5-20** — Footer inhoud: tagline "Onze reizen, verhalen en foto's" (hergebruikt op homepage-hero), Ontdek-kolom (content-nav), Info-kolom (statische pagina's), geen social. Dark navy achtergrond.

### /mijn-account (5.0.b)
- **F5-12** — `/mijn-account` als echte pagina toegevoegd; sub-blok 5.0.b nieuw ingevoegd (dropdown-item "Mijn account" moet daarheen linken).
- **F5-13** — Scope: naam editable (custom `AccountController::updateProfile`, alleen `name`-veld), email + rol read-only (F4-U2), wachtwoord editable (Fortify's `user-password.update`), 2FA volledig geïntegreerd (variant 3).
- **F5-14** — UI: één lange pagina met drie kaarten onder elkaar (persoonlijke gegevens, wachtwoord, tweefactor).
- **F5-15** — 2FA UX vereenvoudigd t.o.v. eerder plan: kaart-state volgt automatisch uit user-model (`two_factor_secret`, `two_factor_confirmed_at`). Geen query-string-flow nodig; Fortify's password-confirm redirect terug naar `/mijn-account` en de kaart rendert de juiste state.
- **`/profiel/2fa`** — 301-redirect naar `/mijn-account#2fa`. Naam `profile.two-factor` behouden voor `admin.blade.php`-link (die nu naar `account.show` wijst).

### Homepage (5.0.c)
- **F5-21** — Macro-structuur: hero + featured destination + laatste posts + featured routes + CTA-strook (variant B).
- **F5-22** — Hero: tekst-blok links (titel "Onze Reisverhalen" in Playfair, tagline in perzik, intro-tekst-placeholder), featured image rechts. Statische foto in `public/images/hero-home.jpg`. Intro-tekst is TODO — Martin verfijnt later.
- **F5-23** — "Featured" semantiek zonder `is_featured`-flag: laatst-toegevoegde destination (`latest()`), gepubliceerde routes gesorteerd op reisdatum (`published()->orderedByTravelDate()`). Optie voor expliciete `is_featured` blijft open voor Fase 5.1+.

### Sessies-invalidatie (5.0.d)
- **F5-24** — Bij email-change door admin: `DB::table('sessions')->where('user_id', $user->id)->delete()` in `Admin\UserController::update()`, naast de F4-U2 auto-invite. Vereist `SESSION_DRIVER=database` (bevestigd in .env). Admin die z'n eigen email wijzigt wordt uitgelogd (intentioneel).

- **F5-25** (5.1.a — data-blokker): DemoSeeder verrijken + fixture-images als eerste blokkerende sub-blok van Fase 5.1, vóór publieke pagina's kunnen worden gebouwd. Reden: dev-DB was rommelig gevuld met handmatige test-data (dubbele Spanje, "Nieuw test land"), 0 media op locations — bouwen zonder visuele feedback onmogelijk.
- **F5-26** (foto-strategie): Gecommitte fixture-images in `database/seeders/fixtures/` i.p.v. runtime API-fetch, placeholder-generator, of hybrid. Reden: offline-veilige `migrate:fresh --seed`, reproduceerbare demo-content, geen API-key of rate-limit-afhankelijkheid. Foto's van Pexels (identieke license als Unsplash: commercieel hergebruik zonder verplichte attributie).
- **F5-27** (content-omvang): 6 destinations / 14 locations. Nieuwe: Canarische Eilanden (Tenerife/Lanzarote), Duitsland (Berlijn/Zwarte Woud), Verenigde Staten (New York/Miami). Bestaande drie (Italië, Schotland, Slovenië) met hun 8 locaties behouden.
- **F5-28** (foto-omvang): Minimalistisch — 1 hero per destination, 4 gallery per locatie, 0 destination-gallery. Totaal 62 foto's, ~20 MB repo-groei. Implicatie: destination-detail-pagina (5.1.d) heeft geen eigen gallery-strook — locations vormen de primaire visuele content op die pagina.
- **F5-29** (sub-blok-opdeling 5.1): 5 sub-blokken met is_featured tussenin. 5.1.a=data+fixtures, 5.1.b=is_featured admin-toggle UX, 5.1.c=/bestemmingen index, 5.1.d=/bestemmingen/{dest} detail, 5.1.e=/bestemmingen/{dest}/{loc} detail. Data-laag van is_featured hoorde technisch in 5.1.a (dependency: seeder moet markering kunnen zetten); 5.1.b is enkel UI.
- **F5-30** (is_featured scope): Destination + Route + Post krijgen `is_featured`-boolean. Location bewust uitgesloten (geen homepage- of index-slot voor "featured location"). Consistente coverage om latere retrofit-migraties te voorkomen.
- **F5-31** (is_featured constraint): Boolean, meerdere records mogen tegelijk featured zijn per model. Controllers picken via `->featured()->latest('updated_at')` — meest recent gewijzigde wint. Simpelste implementatie, meest flexibel voor Post (blog-index kan carousel tonen), geen model-observer of boot-hook nodig.
- **F5-32** (post/route content-schaal): 30 posts en 6 routes in seeder (was 18/2). Één route per destination met 2-3 waypoints elk. Voldoende materiaal voor 5.2 blog-paginatie en 5.4 reisroutes-index zonder later terug te hoeven naar de seeder.

### 5.1.b — admin-toggle UX

- **F5-33** (sub-blok-opdeling 5.1.b): Per model — 5.1.b-i Destination, 5.1.b-ii Route, 5.1.b-iii Post. Elk sub-blok bewijst het patroon voor de volgende; bij een test-fail is de blast-radius klein tot één model. Volgorde simpel → complex: Destination heeft kleinste edit-form, Post grootste met featured_image + categories + tags + status.
- **F5-34** (is_featured badge-conventie): Bootstrap `.badge.bg-warning.text-dark` met `bi bi-star-fill`-icoon + tekst "Uitgelicht". Positionering afhankelijk van index-layout: absolute-positioned linksboven bij card-grid (Destination), inline naast titel bij tabel-index (Route, Post). Bewust géén aparte SCSS — Bootstrap position-utilities volstaan. Terminologie: "Uitlichten" (werkwoord) voor de admin-form-section-titel, "Uitgelicht" (deelwoord) voor de badge-tekst, "Uitgelicht op de homepage en index" voor de checkbox-label. Bij Post specifiek: bewust gescheiden naast bestaande "Uitgelichte afbeelding"-section (featured media-collection is een ander concept dan is_featured presentation-flag).

### 5.1.c — publieke destinations-index

- **F5-35** (macro-structuur `/bestemmingen`): Uniforme 3-koloms grid (`col-md-6.col-lg-4`), featured herkenbaar aan F5-34 ster-badge + subtiele perzik-outline (`outline: 2px solid var(--color-accent-1); outline-offset: -2px`) op de kaart. Geen aparte featured-hero-strook — dubbelop met homepage-featured-hero (F5-21). Uniforme grid schaalt naar N featured (F5-31-vriendelijk). `outline` in plaats van `border` om kaart-hoogte gelijk te houden bij mixed featured/non-featured rijen. Overwogen alternatieven: aparte featured-hero-strook bovenaan (kwetsbaar bij N > 1 featured), asymmetrische grid met featured breed bovenaan (leunt te sterk op precies één featured).
- **F5-36** (kaart-inhoud destination-card): hero (4:3 aspect-ratio, `loading="lazy"`) + naam als h2 + description-snippet (`Str::limit(strip_tags(...), 140)`) + locatie-teller (`bi-geo-alt` + count + `plek`/`plekken`). Anchor omvat de hele kaart (image + body) — geen aparte "Lees meer"-knop, spiegelt `.post-card`-patroon uit homepage. Controller-implicatie: `->withCount('locations')` voor N+1-preventie. Overwogen alternatieven: minimalistisch (hero + naam + country-code) — te weinig SEO-content; rijk (+ laatste-post-datum) — overbelast voor eerste-bezoek-experience.
- **F5-37** (sortering `/bestemmingen`): `->orderByDesc('is_featured')->latest('created_at')`. Featured cluster bovenaan, rest chronologisch nieuwste-eerst. Badge blijft primair signaal, positie ondersteunt. Overwogen alternatieven: puur alfabetisch (badge als enige signaal) — te subtiel; puur `updated_at desc` — verwarrend want minor edit schuift destination naar boven.
- **F5-38** (geen country-meta op destination-card): destination-naam is de titel; landscontext komt eventueel op detail-pagina 5.1.d. Reden: voor 4 van de 6 destinations is destination-naam === landsnaam (Italië, Slovenië, Duitsland, Verenigde Staten), voor 2 informatief (Schotland → VK, Canarische Eilanden → Spanje) — de winst was cosmetisch redundantie waard, uiteindelijk gekozen voor cleaner design. Consequentie: geen `country_names_nl`-lookup in `config/westein.php` nodig voor deze pagina; komt eventueel terug in 5.1.d.
- **F5-39** (sub-blok-omvang 5.1.c): Geleverd als één sub-blok, één commit. Reden: geen forms, geen validatie, alleen lees-view — grondslag om te splitsen ontbreekt. Contrast met 5.1.b dat drie sub-blokken had (Destination + Route + Post) omdat elk model een eigen form-integratie was.
- **F5-40 Hero-macro detail-pagina** — Edge-to-edge foto volle breedte, daaronder `.section-label` + h1 + description als losse alinea. Gekozen boven overlay-banner (leesbaarheid teksten over foto) en split-hero (reveal-verlies na doorklik vanaf homepage-featured-blok). Rationale: respecteert F5-28 (enige destination-foto krijgt volle viewport-impact) én laat description als echte alinea in flow leesbaar zijn.

- **F5-41 Location-tegel patroon** — Nieuwe `.location-card`, foto-first minimalistisch: image + naam-heading, geen description-teaser, geen meta. Gekozen boven `.destination-card`-hergebruik (visueel verschil tussen destinations als containers en locations als plekken) en location-card+teaser (Faker-Lorem-valkuil + SEO al gedekt door destination.description hoger op de pagina). Voordeel: 5.1.e krijgt echte reveal met tekst + gallery + kaart.

- **F5-42 Locations-strook grid** — Vast 3-koloms grid analoog aan `.destinations-grid` uit 5.1.c. Bij destinations met 2 locations: rij van 2 tiles + lege slot rechts (leest editorial-eerlijk als "twee bezochte plekken"). Bij 3 locations: volle rij. Gekozen boven 2-koloms grid (verticaal-verweesde tile op rij 2 leest sterker als orphan) en center-justify met max-width (te veel CSS-nuance voor de winst).

- **F5-43 Cross-links onderaan** — Kleine "← Alle bestemmingen bekijken" terug-CTA-strook met `.btn-accent`, gecentered. Editorial-afsluiting van de pagina. Gekozen boven YAGNI (pagina-einde zonder afsluiting oogt onaf), prev/next-navigatie (destinations zijn evergreen containers, geen artikelen met canonieke volgorde), en andere-destinations-strook (bij dataset van 6 dupliceert dat homepage-featured).

- **F5-44 Location-card image aspect** — 3:2 landscape. Klassiek fotografisch DSLR-formaat, mobile-vriendelijk (~233px hoog bij 350 wide). Gekozen boven 4:5 portrait (viewport-vullend op mobile, forse scroll) en 1:1 (geen editorial karakter) en 4:3 (te dicht bij destinations-tiles, wringt met F5-41-visuele-onderscheid).

- **F5-45 SEO-metadata conventie detail-pagina's** — `title = {model->name}`, `meta_description = Str::limit(strip_tags({model->description ?? ''}), 160)`. Dynamisch uit content, unieke SERP-string per URL, fallback op layout-default bij lege description. Gekozen boven templated (Google waardeert templated meta-descriptions lager, en SEO-groei is Fase-5-primary-goal). Precedent voor 5.1.e (location), 5.2 (post), 5.3 (route).

- **F5-46 Sub-blok-opdeling 5.1.d** — Één sub-blok, één commit. Analoog aan 5.1.c: geen forms, geen validatie, alleen lees-view. Locations-strook is scope-uitbreiding maar niet groot genoeg voor splits (5.1.d-i zonder locations zou eindstaat-onbereikbaar zijn).

- **F5-47 Destination-descriptions verrijkt** — Seeder `$destSpecs` uitgebreid met `description`-key per spec: 2-zins-Nederlandse teksten, ~140 tekens elk, concrete locatie-details (Toscane, Bled, Tenerife, oostkust, etc.), family-first taalgebruik zonder promo-taal. Aparte chore-commit `1ad2888` vóór 5.1.d. Rationale: één-zin-descriptions uit oorspronkelijke seeder (`"Familievakanties in {name}."`) gaven onrealistische dev-visuele-test + mager meta_description. `migrate:fresh --seed` toegepast omdat `firstOrCreate` idempotent is.

- **F5-48 Hero aspect-ratio detail-pagina** — 2:1 magazine-cover. Op 1440px viewport = 720 hoogte. Gekozen boven 16:9 (te "video-still", pushes description volledig weg) en 16:7 (te bescheiden voor F5-28-weight). Goldilocks tussen visuele weight en pagina-flow.

### 5.1.e-i — publieke location-detail (statisch deel)

- **F5-49 Sub-blok-opdeling 5.1.e** — Twee sub-blokken: 5.1.e-i (statisch: route + controller + hero + intro + bento-gallery + breadcrumb + terug-CTA + retro-fit destination-detail + tests) en 5.1.e-ii (Leaflet-kaart + tests). Rationale: Leaflet is de enige echte scope met risico op onverwachte JS-issues (Vite-marker-imports, publieke bundle-integratie, mogelijk nieuwe test-strategy). Splitsen isoleert dat risico — 5.1.e-i levert een compleet-werkende location-detail-pagina minus kaart, blijft op main groen als 5.1.e-ii ontspoort. Gekozen boven één sub-blok (5.1.c/5.1.d-precedent) omdat 5.1.e groter is dan 5.1.d door gallery + Leaflet, en boven drie sub-blokken (i basis / ii gallery / iii Leaflet) omdat gallery bewezen simpel CSS-grid-werk is dat geen isolatie verdient.

- **F5-50 Location-descriptions verrijkt (chore)** — Seeder `$locSpecs` uitgebreid met `description`-key per location: één concrete zin per plek (~65-95 tekens), concrete plaats-details (Colosseum + Sint-Pieter, Duomo, Fairy Pools, Timanfaya + La Geria, etc.). Aparte chore-commit `d8e6462` vóór 5.1.e-i. Rationale: `fake()->paragraph()` blijft Lorem ondanks nl_NL (F5-32), en Lorem in dev is een no-go voor F5-45 dynamische meta_description. Gekozen boven 2-3 zinnen per location (te veel schrijfwerk voor 14 records, meer blog-post-fragment dan location-omschrijving) en Fase-6-uitstel (Lorem in dev direct zichtbaar op detail-pagina, meta_description onbruikbaar). Consistent met F5-47-precedent (destinations kregen dezelfde behandeling in 5.1.d). Fixed helpertekst `"Bezoek aan {name}."` in seeder vervangen door concrete zinnen.

- **F5-51 Aparte publieke LocationController** — `app/Http/Controllers/LocationController.php` met alleen `show(Destination $destination, Location $location): View`. Route-declaratie: `Route::get('/bestemmingen/{destination:slug}/{location:slug}', [LocationController::class, 'show'])->scopeBindings()->name('locations.show');`. Gekozen boven `DestinationController::showLocation()`-erin-frotten. Rationale: consistent met admin-precedent (`Admin\LocationController` is al aparte file), locations-scoped concerns landen conceptueel op de juiste controller, `DestinationController` blijft compact. `->scopeBindings()` valideert automatisch dat `location->destination_id === destination->id` — cross-parent-combinaties (bv. `/bestemmingen/italie/ljubljana`) leveren 404 zonder handmatige guard.

- **F5-52 Location hero-macro** — Edge-to-edge 2:1 hero uit `gallery[0]`, geen aparte hero-collectie op Location. Gekozen boven aparte hero-collectie (breekt F5-28 "0 destination-gallery, 4 gallery per locatie, 0 aparte hero op location"-scope, vereist migratie + factory + seeder + 14 extra fixtures + `registerMediaCollections()`-uitbreiding, YAGNI-gevoelig voor familieblog) en boven geen-hero (asymmetrische reveal-flow met destination-detail; user komt van destination-detail met hero, verwacht op location-detail ook een hero). Consequentie: `gallery[0]` verschijnt zowel als 2:1 hero-crop bovenaan als tile in de bento-gallery eronder — bewuste dubbeling, verschillende visuele functies (sfeer-crop vs. thumbnail). Media-URL fallback-chain analoog F5-40+F5-48: `$heroMedia?->getUrl('large') ?: getUrl('medium') ?: getUrl()`.

- **F5-53 Magazine masonry-gallery** — 1 groot + 3 klein layout voor de gallery-strook (toont alle 4 fotos uit F5-28). Gekozen boven symmetrische 2x2 grid (te repetitief voor 4 vergelijkbare vakantie-fotos, mijn oorspronkelijke lean maar Martin koos voor editorial), 4x1 rij (te breed-plat bij desktop, meer mobile-breakpoint-tuning) en 4 verticale volle-breedte fotos (verlies van "gallery = strook"-signaal, te veel scroll). Impliciete keuze bij deze layout: welke foto = groot wordt in F5-54 vastgelegd.

- **F5-54 Bento-configuratie** — Groot links (2fr breed) + 3 klein stacked rechts (1fr breed, `grid-row: 1 / span 3` op groot). Groot = `gallery[0]`, matches hero-foto — story-arc consistent (dé foto is dé foto). Gekozen boven groot-bovenaan-full-width + 3 klein-eronder (twee volle-breedte slabs boven elkaar met hero + gallery-hero botsen visueel) en boven bento-2x2 (Instagram-feed-feel past minder bij "modern magazine" designkeuze). Mobile-fallback: 1 kolom, groot eerst met `aspect-ratio: 3/2`, dan 3 klein onder elkaar met `aspect-ratio: 3/2`.

- **F5-55 Breadcrumb bovenaan + terug-CTA onderaan** — Breadcrumb `<x-public.breadcrumb>` bovenaan de pagina met items array (`[label => url]`, laatste item zonder url = `aria-current="page"`). Terug-CTA onderaan naar parent destination: "← Terug naar {destination->name}" via `route('destinations.show', $destination)`. Gekozen boven alleen-terug-CTA-naar-parent (mist navigatie-context op deep-link direct binnenkomen), alleen-terug-CTA-naar-index (springt over destination heen), alleen-breadcrumb-bovenaan (redundant met terug-CTA-precedent uit 5.1.d), en breadcrumb-zonder-terug-CTA (breekt CTA-onderaan-conventie). Rationale: breadcrumb geeft context bij direct-binnenkomen (SEO deep-links), terug-CTA sluit de pagina editorial-af.

- **F5-56 Retro-fit destination-detail met breadcrumb** — `destinations/show.blade.php` krijgt in dezelfde 5.1.e-i-commit een breadcrumb-partial bovenaan (`Bestemmingen > {destination}`, laatste item zonder url). Gekozen boven asymmetrie-accepteren (niveau 1 zonder breadcrumb, niveau 2 met — inconsistent) en aparte-chore-commit-na-5.1.e-i (breekt "één sub-blok = één commit"-conventie). Rationale: retro-fit is bijna gratis met de herbruikbare partial, consistentie tussen niveaus is inhoudelijk overtuigend, test-scope-toevoeging is minimaal (één `assertSee` in `DestinationsShowTest.php`). Breadcrumb-conventie site-breed geïntroduceerd — volgende publieke detail-pagina's (5.2 post, 5.3 route) volgen dit patroon.

- **F5-57 SEO-title met em dash niveau-specifieke uitzondering** — Location-detail-pagina rendert `<title>{location->name} — {destination->name}</title>` (bv. "Rome — Italië"). `layouts.public` prependt `— Westein Reisblog` na de title — eindresultaat: "Rome — Italië — Westein Reisblog". F5-45-conventie voor destinations blijft `title = {destination->name}` puur (destinations-namen zijn zelfstandig). Rationale voor niveau-uitzondering: location-namen kunnen ambigu zijn ("Rome" — Italië of NY? "Miami" — Florida of Ohio?), SERP-duidelijkheid en SEO-groei is F5-primary-goal per F5-45, em dash typografisch consistent met "modern magazine" designkeuze, lengte-groei niet problematisch bij deze location-namen (max ~24 tekens vóór layout-suffix, ruim binnen Google's ~60-teken-truncate). `meta_description` blijft F5-45 puur: `Str::limit(strip_tags($location->description), 160)`.

### 5.1.e-ii — Leaflet-kaart op location-detail

- **F5-58 Kaart-plaatsing na de bento-gallery** — De Leaflet-sectie komt tussen `.location-detail__gallery` en `.location-detail__back` (terug-CTA). Rationale: editorial story-arc hero → intro → gallery (visuele climax) → kaart ("waar ligt dit?") → terug-navigatie. Gekozen boven tussen-intro-en-gallery (breekt de tekst→beeld-reveal met een functioneel blok) en side-by-side-met-intro (te complex voor familieblog-schaal). Degradeert elegant: bij een location zonder gallery (`@if $gallery->isNotEmpty()`) staat de kaart direct na de intro als enige visuele element.
- **F5-59 Kaart-dimensies + zoom** — Full-width binnen `.container` (niet edge-to-edge; edge-to-edge is gereserveerd voor fotografie/hero F5-48, kaart is functioneel). Vaste hoogte 400px desktop / 300px mobile (<768px), geen aspect-ratio (kaart heeft geen intrinsieke ratio; 16:9 wordt op mobile te laag om te oriënteren). Default-zoom 12 (stad/wijk) via `setView([lat,lng], 12)` — niet `fitBounds` zoals admin, want single marker. Zoom 12 is vergevingsgezind voor seeder-coördinaat-onnauwkeurigheid; 15 zou fouten pijnlijk zichtbaar maken.
- **F5-60 Default Leaflet-marker** — Blauwe pin + schaduw, met verplichte Vite-PNG-fix (`delete L.Icon.Default.prototype._getIconUrl` + `mergeOptions` met drie geïmporteerde PNG's). Gekozen boven custom perzik-marker (divIcon of PNG-asset): herkenbaarheid als universeel "hier-is-een-locatie"-symbool weegt op familieblog zwaarder dan merk-consistentie, en custom voegt onderhoudsoppervlak toe (CSS-pin-vorm + iconAnchor-tuning, of asset-committen) tegen marginale winst (YAGNI).
- **F5-61 Vanilla-JS-module + statische import + DOM-guard** — `resources/js/leaflet-location.js` exporteert `initLocationMap()`, statisch geïmporteerd in `app.js`. Geen Alpine (read-only kaart, geen form-reactiviteit — omzeilt de Alpine-in-modal-landmine-familie), geen modal (container direct zichtbaar bij page-load, geen `shown.bs.modal`-defer zoals admin). DOM-guard `querySelector('[data-location-map]')` → early return: Leaflet zit in de gedeelde `app.js`-bundle (elke publieke pagina) maar init draait alleen waar de container staat. Gekozen boven inline-script (business-logic in Blade, niet Vite-bundelbaar), dynamische `import()`/lazy chunk (overbodige complexiteit; ~44 kB gzipped verwaarloosbaar op familieblog-schaal) en aparte Vite entry-point (meeste config).
- **F5-62 OpenStreetMap-tiles** — `https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png` met automatische `attribution` op de tileLayer. Gekozen boven Mapbox/ESRI (gratis, geen API-key-beheer). Consistent met admin route-waypoints.js.
- **F5-63 Permanente tooltip met location-naam** — `bindTooltip(name, { permanent: true, direction: 'top' })` op de marker. Gekozen boven klik-popup (vereist interactie, toont wat al als h1/breadcrumb op de pagina staat) en kale marker (voelt onaf). Permanente tooltip geeft een altijd-zichtbaar label zonder redundantie-per-klik.
- **F5-64 Interactie-scope: scroll-wheel-zoom uit** — `scrollWheelZoom: false`; zoom-knoppen, drag-panning en touch-pinch default aan. Rationale: bij een full-width kaart midden in een verticaal scrollende pagina "vangt" scroll-wheel-zoom het wiel en hijackt de page-scroll (klassieke content-pagina-frustratie). Pinch (mobile) botst niet met page-scroll (één-vinger-swipe) en blijft aan. Mirrort admin route-waypoints.js.
- **F5-65 Test-strategy: twee tests + view-guard** — Pest kan Leaflet-rendering niet testen (geen JS-execution), dus de contract-grens is "staan de coördinaten correct in de DOM?". Twee tests in `LocationsShowTest.php`: happy-path (`assertSee('data-location-map', false)` + `assertSee('41.9028')` / `'12.4964'`) en guard (`null` coördinaten → `assertDontSee('data-location-map', false)`). View krijgt `@if ($location->latitude && $location->longitude)` om het hele kaart-blok — voorkomt de Null-Island-bug (`NaN`-coördinaten → Leaflet centreert op `[0,0]`). Data-attributes bewust op één regel gehouden (assertSee-substring-landmine). Assert op korte substring i.p.v. volledige gecastte `decimal:7`-string (robuuster tegen cast-formattering).

### 5.2 — Posts + comments + blog-index + reistips

- **F5-66 Sub-blok-opdeling 5.2** (herzien door F5-73) — Vier sub-blokken: **5.2.0** (blocker-chore: `scopePublished()` + post-content-verrijking), **5.2.a** (publieke blog-index), **5.2.b** (post-detail + comments samen), **5.2.c** (reistips-categorie-view op `/reistips`). Gekozen boven vijf blokken (detail+comments delen pagina + route — apart committen laat een zichtbaar gat) en zes blokken (over-planning). Detail+comments is het grootste blok; intern gefaseerd (routing → rendering → comments-weergave → form), elk afzonderlijk testbaar vóór één gezamenlijke commit. **Herziening F5-73:** de post-detail-route + `url()` + kale detail-view zijn naar 5.2.a gehaald; 5.2.b maakt de detail-pagina áf.

- **F5-67 scopePublished() dubbele check** — `scopePublished()` op Post filtert `where('status', 'published')->where('published_at', '<=', now())`. Spiegelt Route's `published()`-scope (F4-15). Dekt alle vier statussen af: `status`-check sluit draft/scheduled/archived uit (archived behoudt een oude `published_at <= now()` — zonder status-check zou die lekken), `published_at <= now()`-check is vangnet tegen een toekomst-datum per ongeluk gecombineerd met status `published`. NULL-`published_at` valt buiten `<= now()`. **Gekoppeld aan `isPublished()` (F5-77):** beide delen dezelfde twee condities zodat query-scope en single-record-check niet divergeren.

- **F5-68 Content-verrijking 5.2.0 (niveau 2)** — Alle 30 posts realistische NL-excerpt (schoon op index + als `meta_description`-bron), subset van 7 (incl. de 3 featured) een volledig uitgeschreven NL-body met variatie (koppen, meerdere alinea's), rest een korte-maar-echte NL-body. Nul Lorem. Posts-loop herschreven van random-koppeling naar expliciete `$postSpecs`-array: elke titel aan de inhoudelijk juiste location, categorie passend bij het onderwerp. Gekozen boven alle-30-volledig (30 lange teksten is een andere schrijfwerk-orde dan de 20 korte van F5-47/F5-50) en gedeelde-body-pool (body's herhalen zichtbaar). Dev-fixtures; in Fase 6 vervangen door Martins echte reisverhalen. `migrate:fresh --seed` nodig (idempotente `if (Post::count() === 0)`-guard).

- **F5-69 Reistips-seeding** — 5 tip-posts (categorie 'Tips') bovenop de bestaande 30 (die intact blijven). Mix conform de twee reële soorten: **3 bestemming-gebonden** (`destination_id` gevuld, waarvan 1 óók met `location_id`) + **2 algemeen** (beide null). Beide verschijnen op `/reistips`; de 2 algemene leveren op natuurlijke wijze de null-destination-fixture-data — inhoudelijk-correcte content (§3.4 staat destination-loze tips toe), niet als geforceerde test-hack. Oorspronkelijke lean (alle tips null) verworpen: verwrong data om de test te dienen.

- **F5-70 Blog-index op /verhalen** — Publieke blog-index op `/verhalen` (route-naam `posts.index`). §3.5 specificeert geen kale blog-index-URL; ingevuld. Gekozen boven `/artikelen` (neutraler) en `/blog` (Engels, tegen NL-URL-conventie). Sluit aan op merktaal: hero "Onze Reisverhalen" (F5-22), footer-tagline "verhalen" (F5-20). `/verhalen` is puur de index-ingang; post-detail-URL's blijven de §3.5-bestemmingen-boom.

- **F5-71 url() model-methode** — Post-URL-logica in `$post->url()` op Post. Handelt de post-vormen af: location-post → `route('posts.show', [dest, loc, post])` → `/bestemmingen/{dest}/{loc}/{slug}`, tip → `route('reistips.show', post)` → `/reistips/{slug}`, location-loze niet-tip → `LogicException`. Gekozen boven view-composer (URL is model-eigenschap, moet ook buiten views werken voor Fase-6 RSS/sitemap) en inline Blade-keten (conventie #1 + duplicatie). Lost de null-destination-loose-end structureel op; testbaar in isolatie. Controllers eager-loaden `categories`+`destination`+`location` om N+1 te voorkomen. Fixt meteen de bestaande kapotte homepage-kaart-URL.

- **F5-72 Canonieke tip-URL: categorie leidend** — Elke post met categorie 'Tips' krijgt `/reistips/{slug}` als canonieke URL, óók met `destination_id`/`location_id`. `url()` checkt eerst op de tips-categorie; alleen niet-tips vallen door naar de bestemmingen-boom. Gekozen boven boom-leidend: één voorspelbare regel, `/reistips` wordt de volledige canonieke thuisbasis van élke tip (consistent met F5-3 hoofd-nav), SEO-helder (geen duplicate content). Destination-koppeling blijft nuttig voor context/filtering/cross-linking maar bepaalt de URL niet. Implicatie: `url()` heeft `categories` nodig (eager-load). Gehandhaafd in de controllers (F5-78): `show()` weigert tips, `showTip()` weigert niet-tips → 404.

- **F5-73 Blok-grens 5.2.a/5.2.b herzien** — De post-detail-route + minimale controller + kale-maar-echte detail-view + `$post->url()` verhuizen naar 5.2.a, zodat de blog-index via `route()` klikbaar is — consistent met de rest van de app (overal benoemde routes) i.p.v. hardcoded pad-strings. 5.2.a levert index + werkende (kale) detail-pagina (titel/excerpt/body). **5.2.b** maakt de detail-pagina áf: TipTap-rendering-strategie, hero, breadcrumb, SEO-meta, gerelateerde posts + comments. Gekozen boven placeholder-variant: elk sub-blok blijft een compleet-werkend geheel (5.1-principe). Body-rendering in 5.2.a leunt op de purify-at-save die de admin al doet; de bewuste rendering-strategie-beslissing is geparkeerd voor 5.2.b.

- **F5-74 Contentmodel: destination-paraplu, verhalen aan location** — Destination = paraplu (de reis), locations = de bezochte plekken, posts = verhalen per plek. Een verhaal hangt aan een location; puur destination-brede posts zijn een randgeval (kan onder een representatieve location, als reistip, of als tekst op de destination-detailpagina). Publieke post-routes in 5.2: **alleen** location-post `/bestemmingen/{destination:slug}/{location:slug}/{post:slug}` (3-segment, geen botsing met `locations.show`) + tip `/reistips/{post:slug}`. De 2-segment destination-post-URL wordt uitgesteld: botst structureel met `locations.show` (identieke vorm), komt niet voor in de data. Geen eenrichtingsdeur — later toe te voegen zonder F5-74 terug te draaien. `$post->url()` faalt luid als het geval optreedt.

- **F5-75 Post-card component** — Post-card geëxtraheerd naar herbruikbare Blade-component `<x-public.post-card :post="$post" />`. Home-view, `/verhalen`-index en (5.2.b) gerelateerde posts delen 'm. Gebruikt `$post->url()` (F5-71), waarmee de bestaande kapotte 2-segment-URL op de homepage meteen gefixt is — één plek, drie klanten. Gekozen boven dupliceren: DRY, en de homepage moest tóch aangeraakt worden voor de URL-bugfix. Behoudt bestaande kaart-opbouw (featured-beeld medium + placeholder-fallback, destination-meta, titel, excerpt `Str::limit 120`, footer met auteur + `translatedFormat('j F Y')`).

- **F5-76 Index-layout /verhalen** — Posts chronologisch (nieuwste `published_at` eerst), géén featured-voorrang (de index is het neutrale archief; featured-curatie blijft homepage-mechanisme). `paginate(12)` met `withQueryString()` (admin-patroon); 12 deelbaar door 2/3/4 voor nette grid-rijen. Categorie/tag-filtering **uitgesteld** naar een eigen sub-blok: §3.5 geeft `/categorie/{slug}` en `/tag/{slug}` eigen URL's, hoort niet als querystring-filter op `/verhalen`. Hergebruikt `.post-grid` + `.post-card` uit `_home.scss`.

- **F5-77 Published-enforcement via controller-check** — Op de publieke detail-route: `abort_if(! $post->isPublished(), 404)`, niet via scoped binding (vereist custom `resolveRouteBinding`-machinerie) of global scope (zou de admin ook filteren). Route gebruikt gewone model-binding. `isPublished()`-helper op Post deelt exact dezelfde twee condities als `scopePublished()` (F5-67) — `status === 'published' && published_at <= now()` — zodat query-scope en single-record-check niet divergeren.

- **F5-78 Controller-structuur publieke posts** — Eén `App\Http\Controllers\PostController` (naast de bestaande `Admin\PostController`) met `index`, `show`, `showTip`. `show()` (location-post-route) doet `abort_if(tip, 404)` — tips horen op `/reistips` (F5-72). `showTip()` doet `abort_unless(tip, 404)`. Beide handhaven de canonieke URL en voorkomen duplicate content; beide `abort_if(! isPublished, 404)` (F5-77). Gedeelde private `renderDetail($post)`-helper zodat detail-logica niet dupliceert. Alle drie de methoden eager-loaden `categories`+`destination`+`location`(+`author`). Gekozen boven één gecombineerde `show` (bindings verschillen 3-segment vs 1-segment) en gesplitste controllers (overkill voor familieblog-schaal). `show()` behoudt een defensieve `location_id !== $location->id`-vangnetcheck naast `scopeBindings` (dubbelop maar veilig).

- **F5-79 "Verhalen"-nav-item** — Toegevoegd aan de blog-nav tussen Bestemmingen en Reistips (Home → Bestemmingen → Verhalen → Reistips → Reisroutes → Foto's → Contact). `request()->is('verhalen*')` voor de active-state. De nav linkt sowieso al vooruit naar nog-niet-gebouwde pagina's (Reistips/Reisroutes/Foto's/Contact zijn dode links); Verhalen is nu wél een levende link. Volgorde-logica: Bestemmingen (waar) → Verhalen (alle verhalen) → Reistips (praktisch).

### 5.2.b — post-detail afmaken + comments

- **F5-80 Twee commits** — 5.2.b intern opgedeeld in twee commit-korrels: 5.2.b-i (detail-pagina áf: hero, breadcrumb, SEO-meta, body-prose, gerelateerde posts) en 5.2.b-ii (comments). Gekozen boven één gezamenlijke commit (F5-66's default) omdat comments een eigen volwaardig write-path is; elk blok blijft een compleet-werkend, groen geheel (5.1-principe). Boven drie commits (comments-weergave los van write-path — zou een zichtbaar gat laten).

- **F5-81 Body-rendering: purify-at-save** — de detail-view rendert `{!! $post->body !!}`; de body is bij admin-opslag al door Purifier-'rich' gehaald (F4-3, single source of truth op de input-grens). Geen dubbel-purify-at-output: kost CPU per render, botst met de Fase-6 response-cache, en riskeert legitieme markup (tabellen, `img-align-*`) te strippen bij config-drift. Prose via een `.post-detail__body`-SCSS-scope. Kanttekening: seeder-body's (5.2.0) gingen niet door de admin-Purifier maar zijn eigen vertrouwde fixture-content; productie loopt wél via de admin.

- **F5-82 Post-hero: edge-to-edge 2:1 + large-conversie** — volle-breedte 2:1 hero uit `featured` met placeholder-variant, consistent met destination/location-detail (F5-40/F5-48/F5-52). `large` (2400px, matcht de hero-conventie uit 5.1.d) toegevoegd aan `registerMediaConversions()` op Post. Nu goedkoop: geen featured images in de DB (F5-68/Optie A), dus `media:regenerate` is no-op — conversie geldt voor toekomstige uploads. Gekozen boven contained-hero-op-medium en geen-hero.

- **F5-83 Breadcrumb spiegelt de canonieke URL** — location-post: Bestemmingen → destination → location → posttitel (4 niveaus). Reistip: Reistips → titel, waarbij "Reistips" nu een niet-klikbare kruimel is (`<x-public.breadcrumb>` rendert url-loze items als platte tekst) en in 5.2.c een link naar `/reistips` wordt. Gekozen boven tip-roott-in-Verhalen en boven een live `/reistips`-link die tot 5.2.c op 404 landt.

- **F5-84 SEO-meta: override-kolommen, geen auto-context** — `title = meta_title ?: title`, `meta_description = Str::limit(strip_tags(meta_description ?: excerpt), 160)`. Benut de posts-only override-kolommen (§3.3) die destinations/locations niet hebben. F5-57's em-dash-context NIET automatisch op posts (titels zijn beschrijvende zinnen → redundantie + Google-truncate); de `meta_title`-kolom is de handmatige escape.

- **F5-85 Gerelateerde posts: per-type** — location-post → andere gepubliceerde posts uit dezelfde destination (de reis), excl. de post zelf én excl. tips. Reistip → andere reistips. Max 3, nieuwste eerst, `<x-public.post-card>` hergebruikt (F5-75), verborgen bij 0. `PostController::relatedPosts()`. Gekozen boven getrapt (plek→reis) en zelfde-categorie-voor-alles.

- **F5-86 Comments: volledige 1-niveau-threading** — approved top-level + approved replies ingesprongen, plus een reply-form per top-level comment via Alpine-toggle (`x-data="{ replying: false }"`). Leunt op het Comment-model (booted()-auto-status, `replies()`, `scopeTopLevel`). Gekozen boven top-level-only-posten en plat-zonder-threading.

- **F5-87 Eigen pending zichtbaar met label** — auteur ziet z'n eigen pending comment met "wacht op goedkeuring"-badge; anderen niet. `PostController::visibleComments()`: top-level + replies waar `status = approved` OR (`pending` AND `user_id = auth()->id()`), genest, oudste eerst; rejected/spam altijd verborgen. Plus flash na plaatsing. Gekozen boven alleen-flash en niets-bijzonders.

- **F5-88 Uitgelogd: reacties publiek + inlog-oproep** — approved reacties zichtbaar voor iedereen (lees-waarde + SEO); voor gasten wordt het form vervangen door een "Log in / maak een account"-blokje. Gekozen boven geen-oproep en comments-verbergen-voor-gasten.

- **F5-89 Volgorde: oudste eerst** — top-level chronologisch oudste bovenaan, replies chronologisch onder parent. Na plaatsing ankert de redirect naar `#reactie-{id}`. Gekozen boven nieuwste-eerst voor de lage reactie-volumes van een familieblog.

- **F5-90 Write-path: POST /reacties/{post:slug}** — één losstaande route, post via (globaal unieke) slug-binding, ontkoppeld van de twee weergave-URL-vormen. Nieuwe publieke `CommentController@store` in de auth+verified-groep, `ProtectAgainstSpam`-middleware (`@honeypot` geleend uit auth/register), `StoreCommentRequest` (body-regels + `parent_id` hoort bij dezelfde post & is top-level; error-bag `comment`; `prepareForValidation` maakt lege parent_id null). Redirect naar `$post->url().'#reactie-{id}'` met flash. Auto-status via het model. Gekozen boven flat-met-post_id-in-body (tamper-oppervlak) en genest-onder-de-weergave-URL (dubbele routes + binding-overlap).

### 5.2.c — reistips-categorie-view op /reistips

- **F5-91 Route + controller: indexTips() op de publieke PostController** — `reistips.index` op `/reistips` → `PostController@indexTips`, dat `index()` spiegelt: `published()` + `whereHas('categories', slug='tips')` + dezelfde eager-loads + `orderByDesc('published_at')` + `paginate(12)->withQueryString()`. Gekozen boven een aparte controller (F5-78 koos bewust één publieke PostController; tweede controller voor één lees-view is overkill) en boven een getakte `index()` (vermengt twee verantwoordelijkheden). Route als named één-segment-route pal vóór `reistips.show`, en daarmee vóór een toekomstige `/{page:slug}`-catch-all (loose-end).

- **F5-92 Index-layout: hergebruik post-card + post-grid** — nieuwe view `reistips/index.blade.php` spiegelt `posts/index.blade.php` (section-label + section-title + intro-placeholder + grid + paginering + empty-state) en hergebruikt `.post-grid` + `<x-public.post-card>` + de `.posts-index`-wrapperstyling. Geen eigen tip-kaart: de post-card handelt de null-destination al af (`@if ($post->destination)` → meta-regel valt weg bij algemene tips, verschijnt bij de bestemming-gebonden). DRY en visueel consistent met `/verhalen`.

- **F5-93 Groepering: één chronologisch grid** — alle tips door elkaar, nieuwste `published_at` eerst, geen bestemming-gebonden-vs.-algemeen-splitsing. Spiegelt F5-76 (index als neutraal archief). Het bestemming-onderscheid blijft zichtbaar via de destination-meta op de kaart; twee half-lege secties (3 vs. 2 tips) oogden dun. Schaalt vanzelf mee bij groei.

- **F5-94 Tips geweerd uit /verhalen** — `index()` kreeg `whereDoesntHave('categories', slug='tips')`. `/verhalen` = reisverslagen, `/reistips` = tips: schone scheiding conform de nav-split (F5-3) en de canonieke-URL-intentie (F5-72), nu `/reistips` de tips-thuisbasis is. Raakt F5-76's "neutraal archief"-framing bewust — voorkeur ging naar geen overlap tussen twee neutrale archieven. `index()` en `indexTips()` staan naast elkaar: alleen `whereDoesntHave` vs. `whereHas` + view verschillen, zodat beide grenzen leesbaar blijven.

- **F5-95 Nav + breadcrumb levend** — de "Reistips"-nav-link was al gebouwd (F5-79) met `href="/reistips"` + active-state `reistips*`; alleen de route ontbrak, dus nav vergde geen edit. De niet-klikbare "Reistips"-breadcrumb-kruimel op de tip-detail (F5-83) kreeg een `url` naar `route('reistips.index')` en is nu een echte link — daarmee is F5-83 afgemaakt.

- **F5-96 Cross-linking destination-detail → tips (2e commit)** — `DestinationController@show` laadt de gepubliceerde tips van die bestemming; `destinations/show.blade.php` toont ze in een strook "Reistips voor deze reis" tussen de locations-strook en de terug-CTA, via `<x-public.post-card>` die naar de canonieke `/reistips/{slug}` linkt (`$post->url()`, F5-72). Géén cap (nut-strook, geen teaser — alle praktische tips voor die reis horen zichtbaar; aantallen zijn klein), verborgen bij 0 tips. Sluit de loose-end die sinds F5-72 open stond. Nieuw `.destination-detail__tips`-klasje in `_destinations-show.scss`, spiegelt de padding van `.destination-detail__locations`.
### Hero-verfijning (chore na 5.2.c)

- **F5-97 Hoogte-plafond op de detail-hero's (verfijnt F5-48)** — de edge-to-edge 2:1 hero groeide breedte-gedreven zonder plafond, waardoor 'ie op brede desktopschermen de hele vouw vulde en alle tekst zónder scroll-cue verborg (F5-48 was afgewogen op 1440px, niet op bredere schermen). Fix: `max-height: 62vh` op `.destination-detail__hero-image`/`-placeholder`, `.location-detail__hero-image`/`-placeholder` en `.post-detail__hero-image`/`-placeholder`. De verhouding blijft 2:1 (via `aspect-ratio`); het plafond bepaalt de effectieve hoogte = `min(breedte/2, 62vh)`, `object-fit: cover` crop de foto. Label + titel piepen nu altijd net onder de vouw mee als scroll-signaal; de breadcrumb dekt de titel-context al, dus geen tekst-overlay nodig (overlay-variant verworpen, consistent met F5-40). Mobiel onveranderd (2:1 van een smal scherm is al lager dan 62vh). Bewust literale waarde in de drie partials i.p.v. een CSS-variabele in het themed `design-tokens.scss` — helderder en risicolozer voor drie hero's; hoist naar een variabele als het ooit veel getuned wordt. Commit `8e54e5f`.

### 5.3 — Routes + fotogalerij

- **F5-98 Sub-blok-opdeling 5.3** — vier sub-blokken: 5.3.0 (publish-chore), 5.3.a (routes-index + kale detail), 5.3.b (route-detail compleet met Leaflet-polylijn), 5.3.c (fotogalerij + lightbox). Routes en galerij zijn losse concerns; detail+Leaflet apart isoleert het JS-risico (5.1.e-precedent). Elk sub-blok een eigen commit.

- **F5-99 5.3.0 blocker-chore: routes publiceren** — alle 6 seeder-routes op `is_published=true` + `published_at=travel_date` (alle data in het verleden → meteen `<= now`). Zonder dit gaf `Route::published()` een lege set → lege `/reisroutes`-index én leeg homepage-featured-routes-blok (F5-23) in dev. Descriptions waren al echt-NL (géén Lorem-chore nodig, i.t.t. F5-47/F5-50 — gecheckt vóór de bouw). `migrate:fresh --seed`. Gekozen `published_at=travel_date` boven `now()` (semantisch "gepubliceerd sinds de reis", voedt `orderedByTravelDate` consistent) en boven een draft-fixture (tests bouwen hun eigen draft). Commit `fa7d16a`.

- **F5-100 5.3.a-grens (F5-73-precedent)** — index + kale-maar-werkende detail samen: `reisroutes.index` (`/reisroutes`) + `reisroutes.show` (`/reisroutes/{route:slug}`) + kale detail-view (hero + description + platte waypoint-namen + breadcrumb + terug-CTA). Homepage route-card + featured gemigreerd van hardcoded `url('/reisroutes/…')` naar `route('reisroutes.show', $route)`. 5.3.b maakt de detail compleet. Gekozen boven index-only (een index vol 404-links is een zwakkere tussenstand dan één dode nav-link).

- **F5-101 Routes-index-sortering + featured** — `orderByDesc('is_featured')->orderByDesc('travel_date')` + F5-34 ster-badge + perzik-outline op featured route-cards. Model = `/bestemmingen` (F5-37/F5-35): routes zijn evergreen containers zoals bestemmingen, niet artikelen (F5-76 `/verhalen`). De /bestemmingen-precedent accepteert de overlap homepage-featured + index-voorrang al (F5-21+F5-37). Geen paginering (kleine evergreen set); `.route-grid` als-is (2-koloms).

- **F5-102 `<x-public.route-card>` geëxtraheerd** — route-kaart uit `home.blade.php` naar een herbruikbare component (F5-75-model, parallel aan post-card), met de F5-34 featured-badge erin. Home + `/reisroutes`-index (+ 5.3.b gerelateerde routes) delen 'm; badge-logica op één plek; fixt meteen de hardcoded homepage-URL.

- **F5-103 Route.hero-conversies gealigneerd + `isPublished()`** — Route.hero-conversies `webp-1600/800/400` → `thumb`/`medium`/`large` (gelijk aan Location.gallery + Destination.hero), zodat `displayHeroUrl()`-fallback een geschaalde WebP levert i.p.v. het origineel (lost de "alignen we tijdens views-stap"-TODO op; alle 6 routes zijn heroless → fallback altijd actief). `media:regenerate` no-op (geen route-hero's in DB). Plus `isPublished()` op Route (F5-77-patroon, deelt de condities van `scopePublished()`) voor de 404-check op de detail-route.

- **F5-104 Route-kaart: genummerde markers + polylijn (5.3.b)** — publieke `resources/js/leaflet-route.js` (read-only variant van `admin/route-waypoints.js`): genummerde divIcon-markers **1-2-3** + rechte polylijn (perzik) + `fitBounds` + naam-hover-tooltip. Genummerd i.p.v. default-pin+permanente-tooltip (F5-60): bij een route heeft de stopvolgorde echte informatiewaarde (niet cosmetisch). Data via `data-waypoints`-JSON-attribuut (`@json`), DOM-guard, géén marker-PNG-fix (divIcons). Waypoint-lijst opgewaardeerd: genummerd, elke stop linkt naar z'n location-detail (met de pivot-`notes`); bestemmingsnaam in de meta nu een link.

- **F5-105 "Verhalen van deze reis"-strook (5.3.b)** — op de route-detail een strook met gepubliceerde posts uit de bestemming van de route (excl. tips), max 3, nieuwste eerst, via `<x-public.post-card>` (F5-85-model), verborgen bij 0. Sluit de kring route ↔ verhalen. `RouteController::relatedPosts()`.

- **F5-106 Fotogalerij-lightbox: eigen Alpine (5.3.c)** — `resources/js/photo-lightbox.js`, géén nieuwe dependency (F4-1). Overlay + prev/next + ESC/pijltjes + klik-buiten + scroll-lock + "bekijk locatie"-link. Progressive enhancement: tegels zijn gewone links naar de location-detail, Alpine onderschept de klik (werkt zonder JS). Gekozen boven een JS-lightbox-lib (eerste externe frontend-dependency, tegen de minimal-lijn) en boven geen-lightbox (masterplan noemde 'm expliciet).

- **F5-107 Foto-filtering: progressive pills (5.3.c)** — bestemming-pills + locatie-sub-pills (verschijnen bij een actieve bestemming), querystring `?bestemming=&locatie=` server-side (F4-2). Realiseert de masterplan-filter (bestemming/locatie) volledig. Foto-bron = location-`gallery`-collecties (F5-28), query via Locations zodat elke foto z'n location+destination-context draagt. Uniform 3:2-grid, lazy-loaded, geen paginering. `PhotoController` (publiek, niet-`Admin`-namespace), route `fotos.index` op `/fotos`. Ongeldige bestemming-slug valt netjes terug op alles.

### 5.4 — Auteurs + statische pagina's

- **F5-108 Sub-blok-opdeling 5.4** — drie sub-blokken: 5.4.0 (blocker-chore), 5.4.a (auteurs + Over ons), 5.4.b (statische pagina's + catch-all + contactformulier, intern in twee commits b-i/b-ii). Chore apart = schone data/code-scheiding (5.2.0/5.3.0-precedent); catch-all + contact-write-path geïsoleerd van de auteurs-pagina's.

- **F5-109 Auteurs op FamilyMember** — `/auteurs/{familyMember:slug}` voor álle 4 familieleden (alleen FamilyMember heeft een slug; `FamilyMember.user_id` overbrugt naar de User met de posts). `FamilyMember.bio` is de bio-bron (niet `User.bio` — vermijdt een dubbele bron). Verhalen-strook alleen bij aan-User-gekoppelde leden (Jan, Marieke); Sophie/Tim krijgen dezelfde pagina zonder strook. Gekozen boven "alleen gekoppelde leden klikbaar" (inconsistente grid) en "puur op Users" (User heeft geen slug → migratie + dubbele bio-bron).

- **F5-110 Over ons = eigen route** — dedicated `/over-ons` (naam `about`, `AuthorController@overview`): leest de `over-ons`-Page als bewerkbare intro (titel + body + meta) en rendert daaronder de FamilyMembers-card-grid; elke kaart linkt naar `/auteurs/{slug}`. `over-ons` reserved. Gekozen boven puur-dynamische grid (verliest bewerkbare intro) en grid-injectie-in-de-catch-all (koppelt de generieke controller aan één slug, tegen conventie #1).

- **F5-111 Statische pagina's via catch-all** — Privacy + toekomstige admin-pagina's via één catch-all i.p.v. named routes per pagina (masterplan §3.5; maakt de admin-Pages-module pas bruikbaar zonder code per pagina). Implementatie: single-segment GET `/{page:slug}` die `reserved_slugs` uitsluit via een negatieve-lookahead-constraint (`->where('page', '(?!('.$reserved.')$)[^/]+')`), model-binding op slug + `abort_unless($page->isPublished(), 404)`. **Correctie op de oude loose-end** ("catch-all als láátste vóór de auth-groep"): een kale catch-all in `web.php` is NIET globaal laatste — `routes/admin.php` laadt ná `web.php`, dus een gewone `/{page:slug}` kaapte `/admin` (404). `Route::fallback()` is wél globaal laatste, maar matcht élk pad voor GET → onbekende multi-segment POSTs werden 405 i.p.v. 404 (brak TrashManagementTest). De reserved-slug-constraint lost beide op: `/admin` (reserved) valt door naar `admin.php`, en `[^/]+` houdt 'm single-segment zodat multi-segment POSTs 404 blijven. `reserved_slugs` uitgebreid met `verhalen`, `over-ons`, `contact`, `mijn-account`.

- **F5-112 Contactformulier al in 5.4** (i.p.v. 5.5) — bewust naar voren gehaald, ondanks masterplan/F5-1 die 't in 5.5 plaatsten. Contact krijgt daardoor, net als Over ons (F5-110), een eigen route (het formulier is het "iets extra's" bovenop een kale Page).

- **F5-113 Contact: open form, honeypot + throttle, mail-only** — eigen `/contact` (GET `ContactController@show`: contact-Page-intro + form) + POST `/contact` (`@send`, naam `contact.send`). Open voor iedereen (geen auth-groep — dat is het punt van contact); spam-afweer via `ProtectAgainstSpam` (honeypot) + `throttle:6,1`. `StoreContactRequest` (naam/e-mail/onderwerp/bericht, `email:rfc` zonder dns). `ContactMail` (ShouldQueue, reply-to de afzender) naar `config('westein.contact.recipient')` (env `CONTACT_RECIPIENT`, default `website.support@ml-westein.nl`). **Mail-only, geen DB-opslag** — masterplan §8; betrouwbaarheid via queued + `failed_jobs` i.p.v. een `contact_messages`-mini-module (Fase-6-upgrade indien ooit volume). Scoped `contact_success`-flash (layouts.public heeft geen globale flash).

- **F5-114 Portretten: initialen-fallback in dev** — geen portret-fixtures; `HasAvatarFallback` (initialen + accent-kleur) dekt de lege staat, echte familiefoto's later via admin. Consistent met F5-68/Optie-A + F5-28-minimalisme; stockfoto's voor echte personen zouden misleiden.

- **F5-115 Auteur-pagina toont de volledige gepagineerde verhalenlijst** — alle gepubliceerde verhalen van de auteur (`user_id`-match, `published()`, tips uitgesloten F5-94), nieuwste eerst, `paginate(12)->withQueryString()`, via `<x-public.post-card>`. Verborgen bij 0 (Sophie/Tim). Gekozen boven een teaser-strook van 3 (onderbenut een productieve auteur op een pagina die z'n werk hoort te tonen).

- **F5-116 5.4.0 blocker-chore: content + seeder-consolidatie** — FamilyMember-bio's en Page-body's waren Lorem (`fake()->paragraph`/`paragraphs`, F5-32-valkuil) → echte NL. Plus: er draaiden **twee** FamilyMember-seeders (`DemoContentSeeder` keyt op slug, los `FamilyMemberSeeder` op naam) → 8 i.p.v. 4 familieleden. `FamilyMemberSeeder` verwijderd uit `DatabaseSeeder` + bestand opgeruimd; familie enkel nog via `DemoContentSeeder` (4 leden, jan/marieke gekoppeld aan User). Data-only. Commit `7281f52`.

### 5.5 — Newsletter + publieke unsubscribe

- **F5-117 Sub-blok-opdeling 5.5** — twee sub-blokken: 5.5.a (publieke aanmelding + double-opt-in-bevestiging) en 5.5.b (unsubscribe). Contact was al in 5.4.b-ii geleverd, dus 5.5 is puur de nieuwsbrief-kant. **Géén 5.5.0 content-chore**: er is geen publiek-zichtbare seeder-data (abonnees worden nergens getoond), alleen inline UI-copy in de views. **Géén model-/migratie-/action-werk**: de datalaag (Subscriber met `confirmation_token`/`confirmed_at`/`unsubscribe_token`/`unsubscribed_at`, auto-token-generatie in `booted()`, status-afleiding + scopes) én de vier Actions (`SubscribeAction`, `ConfirmSubscriptionAction`, `UnsubscribeAction`, `SendConfirmationMailAction`) bestonden al F4-17-compleet uit Fase 4. 5.5 = publieke routes + controller + één Form Request + views + tests.

- **F5-118 Plaatsing aanmeldformulier** — eigen `/nieuwsbrief`-pagina (spiegelt `/contact`), footer-link in de Info-kolom, **géén hoofdnav-item** (nav zit al vol op 7 items; een nieuwsbrief-aanmelding hoort per webconventie in de footer). `nieuwsbrief` stond al in `reserved_slugs` → de single-segment catch-all `/{page:slug}` kaapt 'm niet; de 2/3-segment confirm/unsubscribe-routes sowieso niet. Publieke routes staan vóór de catch-all in `web.php`. Gekozen boven een site-brede footer-strook (site-brede form-complexiteit + flash op elke pagina) en een homepage-blok (minder canoniek thuis voor de confirm/unsubscribe-landingspagina's).

- **F5-119 Publieke SubscribeRequest is unique-loos** — aparte `App\Http\Requests\SubscribeRequest` (publiek, `authorize()=true`), bewust zónder de `Rule::unique` die de admin-`StoreSubscriberRequest` wél heeft: publiek weigeren op een duplicaat lekt e-mail-enumeratie ("dit adres bestaat al"). `email:rfc` zonder dns (F4-18), `prepareForValidation` normaliseert email (lowercase+trim) en naam (trim→null). Naam optioneel.

- **F5-120 Anti-enumeratie in store()** — de controller toont ALTIJD dezelfde generieke "check je inbox"-melding, ongeacht of het adres nieuw, onbevestigd of al bevestigd is. Flow: `SubscribeAction->execute()` (idempotent) + `SendConfirmationMailAction->execute()` (skipt zelf al-bevestigd/uitgeschreven, dus naar een bevestigd adres gaat géén mail). **F4-17-nuance:** `SubscribeAction` zet een uitgeschreven adres bij publieke zelf-heraanmelding terug naar **pending** (`confirmed_at=null`, vers token) → verplichte herbevestiging. Dat is géén "silent reactivate" (die F4-17 verbiedt bij CSV-import) — de persoon meldt zich zélf opnieuw aan en moet opnieuw door double-opt-in. Twee verschillende paden, allebei AVG-correct.

- **F5-121 Eigen resultaatpagina's voor confirm + unsubscribe** — geen redirect-met-flash: deze URL's worden vanuit een e-mail aangeklikt, niet vanaf de site, dus een eigen Blade-resultaatpagina leest beter. `newsletter/confirmed.blade.php` en `newsletter/unsubscribed.blade.php`, beide met een `$subscriber`-null-tak voor de neutrale foutmelding. **Confirm is one-shot** (`ConfirmSubscriptionAction` wist het token bij succes) → tweede klik = neutrale "ongeldig of al gebruikt". **Unsubscribe is idempotent** (`UnsubscribeAction` wist het token nóóit) → tweede klik = zelfde "je bent uitgeschreven"-pagina. De neutrale unsubscribe-tak biedt een contact-link (AVG: iemand die eruit wil maar een kapotte link heeft).

- **F5-122 Spam/rate-limiting + scoped flash** — de POST `/nieuwsbrief` spiegelt contact (F5-113): `->middleware(['throttle:6,1', ProtectAgainstSpam::class])` (honeypot via `@honeypot` in de form). Scoped `newsletter_success`-flash (de publieke layout heeft geen globale flash-partial); de aanmeldpagina rendert 'm zelf. `reserved_slugs` was al compleet — geen wijziging nodig.

- **F5-123 Sluit F4-N11 (5.5.b)** — `/nieuwsbrief/uitschrijven/{token}` is nu live. De newsletter-testmail-footer (F4-N11) linkt naar een 64-nullen-token; die landt nu op de neutrale "deze uitschrijflink werkt niet"-pagina i.p.v. 404. Impliciet sloot 5.5.a ook de confirm-placeholder: `SubscriberConfirmationMail` (die de admin via `send-confirmation`/`send-bulk-confirmations` al verstuurde) bouwde al een `confirmUrl` naar `/nieuwsbrief/bevestigen/{token}` die tot nu 404'de — de bevestigingsknop werkt nu end-to-end.

### 5.6 — Eindcheck

- **F5-124 Scope 5.6 = puur afsluiten** — eindcheck-verificatiepass + `fase-5-bouwplan.md` + roadmap op ✅. Alle opgespaarde cleanups (flash-key-inconsistentie, lege `resources/views/public/`-dir, Tailwind uit `package.json`, Sass-`@use`-migratie, import-conventie) bewust doorgeschoven naar Fase 6 — admin-scope + build/tooling, blokkeren de livegang niet. Prioriteit verschoven naar snel-naar-live. `fase-5-bouwplan.md` in repo-root (naast fase-2/fase-4) + in de projectomgeving. Vooruitblik Fase 6 (uit deze sessie): host = zelfde als ml-westein.nl (Laravel-geschikt: SSH/PHP 8.3+/MySQL/cron); content-strategie = eerst echte reisverhalen + foto's via de admin, dán live (demo-seeder niet op productie); de **flash-key-bug is de hoogste cleanup-prioriteit**.

## Herbruikbare admin-componenten
Opgebouwd tijdens Fase 4 — hergebruiken in volgende modules:

- **`<x-admin.field>`** — label + input/textarea/number, error-mapping, hint, readonly. Basis-veld. Project gebruikt straight Bootstrap, `.admin-field` is de uitzondering.
- **`<x-admin.form-layout>`** — two-column form-wrapper (slots: `main`, `side`, `actions`). Form-tag zit IN de component; views geven slots + `enctype` mee.
- **`<x-admin.form-section>`** — subtiele groepering binnen een kolom (uppercase mini-header + body).
- **`<x-admin.image-upload>`** — drag-and-drop upload, generiek (`remove_{name}`-checkbox-naming). Props: `name`, `shape`, `current-url`, `max-mb`, `min-width`, `min-height`.
- **`<x-admin.gallery-upload>`** — multi-image galerij met AJAX upload/reorder/delete. Hoort op EDIT-pagina.
- **`<x-admin.tiptap-editor>`** — simple-profiel met toolbar. Initial content uit hidden field (`this.$refs.hidden.value`), niet via x-data-argument.
- **`<x-admin.image-picker-modal>`** — twee-tabs (browse + upload) voor TipTap rich. Coördinatie via `Alpine.store('imagePicker')`. Upload-tab disabled op create-view.
- **`<x-admin.delete-button>`** — inline delete-confirm voor tabelrijen. Geen `:confirm`-prop — confirm zit ingebakken via `x-data="{ confirming: false }"`. Sinds 4.12.b.2 uitgebreid met optionele `:disabled` + `:disabled-reason` props. Disabled-branch wrapt de knop in een `<span data-bs-toggle="tooltip">` met `pointer-events: none` op de button — vereist omdat Bootstrap-tooltips niet direct op disabled elementen werken.
- **`<x-admin.card-actions-menu>`** — driepuntsmenu (⋮) met Bewerken + inline delete-confirm voor cards.
- **`<x-admin.avatar-initials>`** — portret of initialen-fallback met deterministische accent-kleur (`crc32(id) % palette`). Prop = `subject`. Werkt op FamilyMember + User.
- **`<x-admin.sort-link>`** — kolom-header met sorteer-toggle. Prop = `sort` (kolom-id), niet `column`.
- **`<x-admin.route-thumb>`** — inline-SVG route-mini-kaart uit waypoints (lat/lng-bounds → SVG-polylijn). Geen JS, geen tiles.
- **`<x-admin.comment-actions>`** / **`<x-admin.comment-status-badge>`** — contextuele knoppen + Bootstrap-badge per comment-status.
- **`App\Models\Concerns\HasAvatarFallback`** — trait met `initials()` + `accentColor()`. Per model lokale `avatarUrl()` (verschillende collecties).
- **`App\Rules\NotReservedSlug`** — validatieregel voor top-level routes (Pages, etc.).
- **`tagPills`** Alpine-factory — multi-value input via hidden komma-string + autocomplete + keyboard-handling.
- **`routeWaypoints`** Alpine-factory — SortableJS + JSON-serialisatie. DOM-revert in `onEnd` → Alpine-array-mutation pattern.
- **`<x-admin.media-delete-overlay>`** (Stap 4.11.b) — grid-specifieke per-item delete met inline confirm-toggle (vuilnisbak → check/cross). Alpine `x-data` met AJAX-fetch naar `DELETE admin/media/{media}`, DOM-remove op success. Geen form-tag (anders dan `<x-admin.delete-button>`); past in grid-overlay-context met `position: absolute`. Props: `:media-id`.
- **`Alpine.store('mediaSelection', ...)`** (Stap 4.11.c) — eerste Alpine-store in project (i.p.v. data-factory). Beheert bulk-selectie-state pagina-scoped: `selected: Set`, `toggle(id)`, `selectAllVisible()`, `clear()`, `count()`, `hasSelection()`, `allVisibleSelected()`, `destroy()`. Cross-scope bereikbaar via `$store.mediaSelection.*` — vereist wanneer state gedeeld moet worden tussen view-body en `@push('modals')`-content.
- **`Alpine.store('trashSelection', ...)`** (Stap 4.12.c) — tweede Alpine-store in project, parallel aan `mediaSelection` maar met **composite keys** (`"{type}:{id}"`) omdat trash-IDs niet globally uniek zijn (Post.1 ≠ Destination.1). API: `reset()` leest visible keys uit `[data-trash-key]` attributes, `isSelected(type, id)`, `toggle(type, id)`, `selectAllVisible()`, `clear()`, `count()`, `hasSelection()`, `allVisibleSelected()`, `destroy()` serialiseert selection naar hidden form-input + submit. Naam `destroy()` is bewust API-parity met mediaSelection maar semantisch misleidend hier — betekent "voer bulk-actie uit" = bulk-**restore**. Comment in code legt uit.
- **`<x-admin.user-status-badge>`** (Stap 4.13.b) — status-badge voor User met twee visuele states: `Actief` (groen-subtle, check-circle-icon) en `Gedeactiveerd` (grijs, person-slash-icon + tooltip met deactivatie-datum). Prop = `user`. Gebruikt in index-tabel + edit-view-header (bij gedeactiveerde users).
- **`Alpine.store('userSelection', ...)`** (Stap 4.13.g) — derde Alpine-store in project, parallel aan `mediaSelection` en `trashSelection`. Plain integer-keys (User-IDs uniek — geen composite zoals trash). API: `reset()` leest visible keys uit `[data-user-id]`-attributes, `isSelected(id)`, `toggle(id)`, `selectAllVisible()`, `clear()`, `count()`, `hasSelection()`, `allVisibleSelected()`. Twee destroy-methods: `destroyDeactivate()` en `destroyReactivate()`, elk submit't naar een eigen hidden form (`users-bulk-deactivate-form` / `users-bulk-reactivate-form`) via interne `_submitForm(formId)`-helper. Bulk-actie-parity met bulk-restore uit trash, maar met twee bestemmingen ipv één.
- **`resources/views/admin/_partials/sidebar.blade.php`** (chore vóór 4.12) — sidebar-markup verhuisd uit `layouts/admin.blade.php` naar aparte partial. Vindbaarheid: `Get-ChildItem *sidebar*` returnde niets omdat het in de layout zat. Parallel met bestaande `admin._partials.flash`-conventie. Bevat alle nav-groepen (Content/Engagement/Beheer) plus mobile-backdrop-div. Alpine-context (`mobileOpen`, `toggleCollapse`) blijft werken omdat `@include` server-side templating is, geen JS-boundary.

### Herbruikbare publieke componenten

- `resources/views/partials/site-nav.blade.php` — gedeelde ml-westein site-nav-partial (A-hybrid, kleuren van hoofdsite, absolute URL logo, active hardcoded op Reizen).
- `resources/views/partials/blog-nav.blade.php` — dark navy blog-nav met tekst-brand + menu-items + profiel-dropdown. Alpine-usermenu-patroon geadapteerd van admin.
- `resources/views/partials/footer.blade.php` — drie-kolommen footer (brand+tagline / Ontdek / Info) + copyright-onderbalk.
- `resources/views/layouts/public.blade.php` — publieke layout met head (fonts + vite + title/meta-conventie), main met `@yield('content')`, stacks voor head/modals/scripts.
- SCSS-partials in `resources/scss/public/`: `_layout.scss`, `_site-nav.scss`, `_blog-nav.scss`, `_footer.scss`, `_account.scss`, `_home.scss`.
- **Utility-classes uit `_home.scss`** (worden herbruikbaar in latere Fase 5-stappen):
  - `.section-label` — kleine kapitalen in perzik-accent, meta-lijn boven section-titles.
  - `.section-title` — Playfair section-heading met clamp-sizing.
  - `.btn-accent` — perzik CTA-knop met color-mix hover-darkening.
  - `.post-card` en `.route-card` — grid-card-patronen met hover-lift + shadow (hergebruikbaar in bestemmingen-index 5.1, blog-index 5.2, routes-index 5.3).
- `resources/views/account/show.blade.php` + `_partials/` — one-page account met kaart-patroon; kaart-styling `.account-card` (header + body-blokken) is generiek herbruikbaar.
- **`layouts.public` `@section('title')` / `@section('meta_description')` conventie** — elke publieke pagina zet z'n eigen title en description; layout heeft fallbacks.
- `.destination-detail__hero` (edge-to-edge, `aspect-ratio: 2/1`, F5-48) — hero-container voor detail-pagina's; buiten `.container` gerenderd voor volle viewport-breedte. Placeholder-variant met `.bi-image` icoon bij ontbrekende media. Hero-image/placeholder heeft sinds F5-97 `max-height: 62vh` (geldt site-breed voor de drie detail-hero's) zodat de foto de vouw niet volledig vult.
- `.destination-detail__intro` — sectie-container voor label + h1 + description-alinea, `padding: var(--space-5) 0 var(--space-4)`.
- `.destination-detail__description` — description-alinea styling: `max-width: 720px`, `font-size: 1.1rem`, `line-height: 1.65`. Leesbaar voor 2-3 zins-alinea's.
- `.destination-detail__locations` — sectie-container voor locations-strook onder de intro.
- `.destination-detail__back` — gecentered terug-CTA-strook (F5-43).
- `.locations-grid` (3-koloms, F5-42) — responsive analoog aan `.destinations-grid`: 3 kols default, 2 kols <992px, 1 kol <576px.
- `.location-card` + `__link`, `__image-wrap`, `__image` (3:2 F5-44), `__image-placeholder`, `__title` — foto-first minimalistische tile. Naam als h3 met Playfair, geen description/meta.

- `.location-detail__map-section` + `.location-detail__map` (F5-58/F5-59) — kaart-sectie-container (`padding: var(--space-5) 0`) + kaart-div (full-width, 400px/300px mobile). Container krijgt `data-location-map` + `data-lat`/`data-lng`/`data-name` (op één regel voor assertSee) die `leaflet-location.js` oppikt via de DOM-guard.
- `resources/js/leaflet-location.js` — herbruikbaar vanilla-JS-init-patroon voor publieke Leaflet-kaarten (Vite-PNG-marker-fix + OSM-tiles + DOM-guard). Basis voor toekomstige publieke kaarten (5.3 route-fotogalerij/kaart).

- `<x-public.breadcrumb :items="[...]">` (F5-55 + F5-56) — Blade-component in `resources/views/components/public/`. `items`-prop is array van associative arrays met `label` (verplicht) en optioneel `url`. Laatste item krijgt `aria-current="page"`, andere items met url worden links. `aria-label="Kruimelspoor"`. Separator `/` tussen items (typografisch schoner dan `>`). Site-brede conventie: elke publieke detail-pagina (destination, location, post, route, page) toont breadcrumb bovenaan.
- `.public-breadcrumb` + `__list` / `__item` / `__link` / `__current` / `__separator` (uit `_locations-show.scss`) — SCSS voor bovenstaande component. `font-size: 0.875rem`, muted links met perzik-accent hover, current-item vetgedrukt. Gebruikt in destinations/show + locations/show.
- `.location-detail__hero` / `__hero-image` / `__hero-placeholder` (F5-52) — edge-to-edge 2:1 hero met object-fit cover, placeholder-variant met `.bi-image`-icoon. Analoog aan `.destination-detail__hero` maar bron is `gallery[0]` in plaats van `hero`-collectie.
- `.location-detail__intro` / `__description` — sectie-container en description-alinea styling, analoog aan destination-detail-varianten. `max-width: 720px` voor leesbaarheid.
- `.location-detail__gallery` / `.location-gallery` / `__item--large` / `__item--small` / `__image` (F5-53 + F5-54) — bento-grid met `grid-template-columns: 2fr 1fr`, groot spant 3 rijen. Klein items hebben expliciet `aspect-ratio: 3/2`. Mobile (<768px): 1 kolom, groot ook `aspect-ratio: 3/2`. Image `object-fit: cover` op alle tiles voor consistente crop uit landscape-fotos.
- `.location-detail__back` — gecentered terug-CTA-strook naar parent destination met `.btn-accent`. Analoog aan `.destination-detail__back` (F5-43-patroon) maar wijst naar `destinations.show` in plaats van `destinations.index`.

Media-URL fallback-patroon voor edge-to-edge hero-uses: `getFirstMediaUrl('hero', 'large') ?: getFirstMediaUrl('hero', 'medium') ?: getFirstMediaUrl('hero')`. Uitbreiding van het 5.1.c-patroon (dat alleen medium→original had), noodzakelijk omdat medium (1200px) upscalet op 1440+ viewports.

_Toevoegingen uit 5.2.a:_
- `$post->url()` (model-methode, F5-71/F5-72/F5-74) — canonieke publieke URL voor een post. Drie takken: location-post → `route('posts.show', [dest, loc, post])`, tip → `route('reistips.show', post)`, location-loze niet-tip → `LogicException`. Vereist eager-loaded `categories`+`destination`+`location`.
- `$post->isPublished()` (model-methode, F5-77) — `status === 'published' && published_at <= now()`, deelt de waarheid met `scopePublished()`. Voor de single-record-404-check op de detail-route.
- `<x-public.post-card :post="$post" />` (F5-75) — herbruikbare post-kaart (featured-beeld medium + placeholder-fallback, destination-meta, titel, excerpt `Str::limit 120`, footer auteur + datum). Gebruikt door home, `/verhalen`-index, en (5.2.b) gerelateerde posts. Linkt via `$post->url()`.
- `App\Http\Controllers\PostController` (publiek, F5-78) — `index` (`/verhalen`), `show` (location-post), `showTip` (reistip) + private `renderDetail`. Canonieke-URL-handhaving (tips ↔ bestemmingen-boom) via `abort_if`/`abort_unless`.
- Routes: `posts.index` (`/verhalen`), `posts.show` (`/bestemmingen/{destination:slug}/{location:slug}/{post:slug}`, `->scopeBindings()`), `reistips.show` (`/reistips/{post:slug}`).
- `resources/views/posts/show.blade.php` — kale detail-view (F5-73), titel + excerpt + body via `{!! $post->body !!}` (leunt op purify-at-save). Wordt in 5.2.b afgemaakt met hero/breadcrumb/SEO/gerelateerde-posts/comments.
- `resources/views/posts/index.blade.php` — `/verhalen`-index, hergebruikt `.post-grid` + `<x-public.post-card>`, `paginate(12)`.
- `.posts-index` + `__intro` / `__pagination` / `__empty` (uit `_posts-index.scss`, spiegelt `_destinations-index.scss`) — index-wrapper-styling. Grid + kaarten al gedekt door `_home.scss`.

_Toevoegingen uit 5.2.b:_
- `.post-detail__hero` / `__hero-image` / `__hero-placeholder` (F5-82) — edge-to-edge 2:1 post-hero uit de `featured`-collectie; `large ?: medium ?: original`-fallback. `large` (2400px) toegevoegd aan `registerMediaConversions()` op Post.
- `.post-detail__intro` / `__excerpt` / `__meta` + `.post-detail__body` prose-scope (F5-81) — typografie voor TipTap-'rich'-output (koppen/alinea's/lijsten/links/blockquote/inline-images `img-align-*`/tabellen), `max-width: 720px`. In `_posts-show.scss`.
- `.post-detail__related` (F5-85) — gerelateerde-posts-strook op `--color-surface`, hergebruikt `.post-grid` + `<x-public.post-card>`.
- `<x-public.comment :comment="$comment">` (F5-86) — één reactie: avatar-initialen + meta + tekst (`nl2br(e())`) + pending-badge; `$slot` voor reply-toggle/-form (alleen top-level meegegeven).
- `<x-public.comment-form :post :parent>` (F5-90) — herbruikbaar reactieformulier (top-level + reply), `@honeypot`, error-bag `comment`, `route('comments.store', $post)`.
- `App\Http\Controllers\CommentController@store` + `App\Http\Requests\StoreCommentRequest` (F5-90) — publieke comment-write-path. `PostController::visibleComments()` + `relatedPosts()` (F5-85/F5-87). Comments-SCSS (`.post-comments`, `.comment__*`, `.comment-form`) in `_posts-show.scss`.

_Toevoegingen uit 5.2.c:_
- `PostController::indexTips()` (F5-91) — publieke reistips-index op `/reistips`; spiegelt `index()` met `whereHas('categories', slug='tips')`. `index()` weert nu tips (`whereDoesntHave`, F5-94).
- Route `reistips.index` (`/reistips`, F5-91) — named één-segment-route pal vóór `reistips.show`; moet vóór een toekomstige `/{page:slug}`-catch-all blijven.
- `resources/views/reistips/index.blade.php` (F5-92/F5-93) — hergebruikt `.post-grid` + `<x-public.post-card>` + `.posts-index`-styling; één chronologisch grid, intro-placeholder met TODO (Martin verfijnt later).
- `.destination-detail__tips` (F5-96) — tips-strook op destination-detail (tussen locations en terug-CTA), hergebruikt `.post-grid` + `<x-public.post-card>`. In `_destinations-show.scss`.

_Toevoegingen uit 5.3:_
- `App\Http\Controllers\RouteController` (publiek, F5-100/F5-105) — `index` (`/reisroutes`, featured-voorrang) + `show` (route-detail) + private `relatedPosts`. Naast de bestaande `Admin\RouteController` (andere namespace).
- `<x-public.route-card :route>` (F5-102) — route-kaart met F5-34 featured-badge, linkt via `route('reisroutes.show', $route)`. Gebruikt door home + `/reisroutes`-index. `.route-card__image-wrap` + `__badge` + `.route-card--featured` (outline) in `_home.scss`.
- `resources/js/leaflet-route.js` (F5-104) — publieke genummerde-marker + polylijn-kaart; data via `data-waypoints`. `.route-marker`/`__num` + `.route-detail__map`-styling in `_routes-show.scss`.
- `Route::isPublished()` (F5-103) — single-record-variant van `scopePublished()`. Route.hero-conversies zijn nu `thumb`/`medium`/`large`.
- `App\Http\Controllers\PhotoController` + route `fotos.index` (`/fotos`, F5-107) — gallery-media van alle locaties, gefilterd op bestemming/locatie via querystring.
- `resources/js/photo-lightbox.js` (F5-106) — Alpine-lightbox-factory (`Alpine.data('photoLightbox', …)` in `app.js`), progressive enhancement.
- Views: `routes/index`, `routes/show`, `photos/index`. SCSS-partials: `_routes-index`, `_routes-show`, `_photos-index`.

_Toevoegingen uit 5.4:_
- `App\Http\Controllers\AuthorController` (publiek) — `overview()` (`/over-ons`, naam `about`: `over-ons`-Page-intro + FamilyMembers-grid) + `show(FamilyMember)` (`/auteurs/{familyMember:slug}`, naam `authors.show`: naam/rol/bio + initialen-avatar + gepagineerde verhalenlijst bij gekoppelde auteurs).
- `App\Http\Controllers\PageController` (publiek) — `show(Page)` via de catch-all `/{page:slug}`; reserved-slug-lookahead-constraint + `abort_unless(isPublished)`.
- `App\Http\Controllers\ContactController` — `show()` (contact-Page-intro + form) + `send(StoreContactRequest)` (queued mail-only, scoped `contact_success`-flash).
- `<x-public.avatar :subject :size>` — portret via `avatarUrl()`, anders initialen-fallback (`HasAvatarFallback`); eigen `.author-avatar`-SCSS (publiek, los van de admin-avatar). Werkt op FamilyMember + User.
- `Page::isPublished()` (F5-77-patroon) — `published_at !== null && published_at <= now()`, deelt de waarheid met `scopePublished()`.
- `App\Mail\ContactMail` (ShouldQueue, reply-to de afzender) + `App\Http\Requests\StoreContactRequest` + `resources/views/emails/contact/message.blade.php` (markdown-mail).
- Views: `authors/overview`, `authors/show`, `pages/show`, `contact/show`. SCSS-partials: `_authors`, `_pages` (incl. contactform-styling).
- Routes: `about` (`/over-ons`), `authors.show` (`/auteurs/{familyMember:slug}`), `pages.show` (catch-all `/{page:slug}`, reserved-constraint, allerlaatste route), `contact` + `contact.send` (`/contact` GET/POST).
- `config('westein.contact.recipient')` — ontvanger contactformulier (env `CONTACT_RECIPIENT`).
- `reserved_slugs` uitgebreid met `verhalen`, `over-ons`, `contact`, `mijn-account`.

_Toevoegingen uit 5.5:_
- `App\Http\Controllers\NewsletterSubscriptionController` (publiek) — `show` (`/nieuwsbrief`), `store` (double-opt-in stap 1, anti-enumeratie), `confirm` (`/nieuwsbrief/bevestigen/{token}`), `unsubscribe` (`/nieuwsbrief/uitschrijven/{token}`). Leunt op de vier bestaande `Actions\Subscribers\*`.
- `App\Http\Requests\SubscribeRequest` (publiek, unique-loos, `email:rfc` zonder dns) — F5-119.
- Views: `newsletter/show` (form + intro + scoped `newsletter_success`-flash), `newsletter/confirmed`, `newsletter/unsubscribed` (beide eigen resultaatpagina met neutrale null-tak). SCSS-partial `_newsletter.scss` (`.newsletter-page`/`.newsletter-form`/`.newsletter-result`).
- Routes: `newsletter.show` (GET `/nieuwsbrief`), `newsletter.subscribe` (POST `/nieuwsbrief`, throttle:6,1 + honeypot), `newsletter.confirm` (`/nieuwsbrief/bevestigen/{token}`), `newsletter.unsubscribe` (`/nieuwsbrief/uitschrijven/{token}`).
- Footer: Nieuwsbrief-link in de Info-kolom.

---

## Landmines & patronen — volgende sessie wakker schudden

### TipTap + Alpine
- **`Alpine.raw(this.editor)` voor ÁLLE TipTap-aanroepen, niet alleen state-syncs.** ProseMirror's identity-checks (`tr.before.eq(state.doc)`) falen op de Vue-reactivity-Proxy met `RangeError: Applying a mismatched transaction`. Geldt voor mutaties, query-calls (`isActive`, `getAttributes`), én chain-commands. Centraliseer via een `chain()`-helper op de factory die `Alpine.raw(this.editor).chain().focus()` returnt. `isDestroyed`-guard als eerste regel: `if (!rawEditor || rawEditor.isDestroyed) return;`. Toolbar-buttons binnen het editor-element lijken een ander code-pad te raken (vaak werkend zonder `raw()`), maar externe triggers (modal, sidebar, callbacks) en `syncState()` falen consistent. Standaardaanname voor élke nieuwe TipTap-aanraking.
- **Wrap externe TipTap-aanroepen in `try/finally`** aan de aanroep-kant — anders blijft de image-picker modal in een half-open state hangen bij een onverwerkte error.
- **TipTap v3 StarterKit levert Link + Underline standaard.** Importeren als losse extensions geeft `Duplicate extension names found`-warning. Tabel-extensies (`@tiptap/extension-table` etc.) gebruiken **named exports**, niet default — één default-import gooit een SyntaxError en sloopt de hele admin.js vóór `Alpine.data()`-registraties. Symptoom: alle Alpine-componenten lijken dood. Check eerst de browserconsole.
- **TipTap initial content uit `this.$refs.hidden.value` lezen**, niet via x-data-argument. Content met apostrofs/quotes breekt de JS-string-interpolatie in het `x-data`-attribuut.
- **Custom attribute via `Extension.extend({ addAttributes() })`** met `parseHTML` uit class-attribuut, `renderHTML` terug naar class. Geen inline `style` — Purifier-allowlist blijft strikt op `[class]` + `Attr.AllowedClasses`.
- **Alpine factory: gedestructureerde argumenten staan NIET automatisch op `this`.** Elk genoemd argument moet ook in de `return { ... }`. Symptoom: `this.locations` is `undefined`, getter-methods stil-falen.
- **Alpine roept `init()` automatisch aan.** Een component met zowel `x-data="factory()"` ALS `x-init="init()"` triggert dubbele initialisatie. Defensief: `if (this.editor) return;` als eerste regel in `init()`.
- **Alpine `x-show` + Bootstrap display-utility (`d-flex`, `d-block`, etc.) op hetzelfde element = onzichtbaar conflict.** Bootstrap's `display: X !important` overschrijft Alpine's inline `style.display = 'none'`. Element blijft zichtbaar ondanks correcte `x-show`-evaluatie. Fix: wrap in een extra `<div>` met de `x-show`-directive; zet de Bootstrap utility op het kind. Geldt niet voor `visibility`/`opacity`-utilities (geen `display`-property). Geconstateerd op `media-delete-overlay`'s check+cross-knoppen in 4.11.b.
- **`[x-cloak] { display: none !important; }` moet globaal staan, niet form-scoped.** Tot 4.11.b was deze CSS-regel scoped onder `_forms.scss` (matched alleen `[x-cloak]` binnen `<form>`-context). Componenten buiten een form — sidebar-dropdown, image-picker, gallery-upload, en straks media-overlays — matchten niet, met flash-of-unconfirmed-content tot Alpine de initial state had toegepast. Verplaatst naar `_layout.scss` in eigen commit (0ab2d2d). Hint voor toekomstige SCSS-edits: globale Alpine-helpers (`[x-cloak]`, `[x-transition]`-resets, etc.) horen in `_layout.scss`, niet in domein-specifieke partials.

### Landmines geleerd in Fase 5.0

- **`.navbar-expand-lg` verplicht op nav-tag** wanneer je `.collapse.navbar-collapse` gebruikt — zonder die klasse blijft de collapse ook op desktop-viewport hidden (Bootstrap 5 forceert dit via CSS-cascade). Niet weglaten "omdat we geen `.navbar` gebruiken" — 't heeft niks met de container te maken.
- **`.navbar-toggler-icon`** krijgt zijn SVG alleen binnen `.navbar`-scope. Zonder `.navbar` parent-klasse blijft de knop leeg. Alternatief: gebruik `<i class="bi bi-list"></i>` en style de font-size + kleur zelf (schoner).
- **Pest `actingAs` import-conventie varieert per testbestand.** Check eerste ~20 regels van het bestand vóór je een nieuwe test schrijft. Met `use function Pest\Laravel\actingAs;` bovenin: `actingAs($user)` plain. Zonder die import: `$this->actingAs($user)`. Zelfde geldt voor `get`, `put`, `post`. Failure-signal: "Call to undefined function actingAs()".
- **`git commit -F commit-msg.txt` valkuil op Windows**: als je `git add -A` doet vóór `git commit -F`, wordt de commit-message-file zelf in de commit meegepakt. Fix: plaats de temp-file in `$env:TEMP` (buiten working tree). Amend na de fout: `git add -A ; git commit --amend --no-edit`.
- **Tailwind 4.0 zit in `package.json`** als leftover van Laravel 11's default-scaffold, terwijl we Bootstrap gebruiken. Kan uit devDependencies weg (`@tailwindcss/vite`, `tailwindcss`). Niet acuut maar staat op de opruim-lijst voor 5.6 of Fase 6.
- **`welcome.blade.php` met inline Tailwind** faalt zonder `npm run dev` actief (MissingViteManifestException) — was de reden van de `ExampleTest`-loose-end. Nu opgelost door welcome te vervangen door onze eigen `home.blade.php` die `layouts.public` extends.
- **`is_featured` bestaat niet op Destination / Post / Route** in Fase 3-schema. Gebruik `latest()` (laatst-toegevoegde) of model-scopes zoals `Route::published()->orderedByTravelDate()` als fallback. Als je bewust wilt kunnen kiezen wat "featured" is: aparte migration + admin-toggle nodig (kandidaat voor Fase 5.1).
- **Fortify's `updateProfileInformation`-action accepteert email standaard.** Voor F4-U2-restrictie (email-change alleen via admin): gebruik eigen controller-methode (`AccountController::updateProfile()`) die alleen `name`-veld valideert. Fortify-action ongemoeid laten voor eventueel later gebruik.
- **Fortify wachtwoord-form errors landen in aparte error-bag** genaamd `updatePassword`. Anders zijn foutmeldingen onzichtbaar. Blade: `@error('current_password', 'updatePassword')`, test: `assertSessionHasErrorsIn('updatePassword')`.
- **Landmine: `git add -A` bij multi-commit-workflows.** Bij parallelle foto/asset-shopping tijdens code-commits pakt `git add -A` untracked bestanden mee die bij een LATERE commit horen. Symptoom in 5.1.a Commit 2: `.commit-msg.tmp` (transport-file voor `git commit -F`) en 18 fixture-JPG's kwamen per ongeluk in de is_featured-commit terecht. Fix: `git reset --soft HEAD~1`, unstage met `git restore --staged <paths>`, cleane hercommit. Preventie: **altijd expliciete paths bij `git add`** (bijv. `git add database/seeders/DemoContentSeeder.php`), nooit `-A` in workflows met gemixte pending changes. `.commit-msg.tmp` moet expliciet buiten staging blijven — het is transport, geen content.
- **Landmine: Faker-locale switch localiseert alleen data-methodes, geen tekstgenerators.** `APP_FAKER_LOCALE=nl_NL` maakt `fake()->name()`, `->city()`, `->address()` Nederlands, maar `->sentence()`, `->paragraph()`, `->paragraphs()`, `->word()`, `->text()` blijven **altijd Lorem Ipsum** ongeacht locale. Geen bug in Faker — hardcoded gedrag. Voor 5.1 acceptabel: post-body's en family-bio's zijn niet zichtbaar op bestemmingen-pagina's (alleen title/excerpt, die zijn hardcoded Nederlands). Voor 5.2 (post-detail-pagina) wordt Lorem zichtbaar — dan afwegen: custom NL-tekst-fixture-array, of Lorem accepteren als "even not-real content" tijdens dev.
- **Landmine: fixture-media-attach zonder `->preservingOriginal()` verhuist bronbestand.** Bij `$model->addMedia($path)->toMediaCollection(...)` *verplaatst* Spatie Media Library het bronbestand naar `storage/app/public/{media-id}/`. Bij gecommitte fixture-images is dit fataal: na eerste `migrate:fresh --seed` is de `fixtures/`-directory leeg en werkt de tweede seed niet meer. **Altijd `->preservingOriginal()`** bij fixture-attach. Sanity-test: na `migrate:fresh --seed` moet count van `fixtures/**/*.jpg` gelijk zijn aan wat je committed hebt.
- **Legacy bug ontdekt en gefixed: oude DemoContentSeeder gaf `country_code` niet door aan destinations.** De `$destSpecs`-array had een `'country'`-key gedefinieerd, maar die werd niet meegegeven aan `Destination::firstOrCreate()`. Resultaat: alle destinations in de dev-DB hadden `country_code = NULL`. Fix in 5.1.a Commit 3: key hernoemd naar `'country_code'` en toegevoegd aan create-body. Locations erven nu `country_code` van hun destination voor consistentie.

### Landmines geleerd in Fase 5.1.b

- **PowerShell 5.1 default console gebruikt CP1252, niet UTF-8.** UTF-8 multi-byte-tekens zoals `«` (`C2 AB`) en `»` (`C2 BB`) worden per byte als CP1252 gerendered → `Â«` en `Â»`. File is bytes-correct; console liegt. Bij zichtbare "encoding-fout" in `Get-Content`: (a) expliciet `[Console]::OutputEncoding = [System.Text.Encoding]::UTF8` én `-Encoding UTF8` op `Get-Content`, of (b) browser-render als ground truth voor Blade-output. `Select-String -Pattern "c2 ab|c2 bb"` op `Format-Hex`-output matcht niet betrouwbaar over regelgrenzen — geen goede bytes-verify.
- **HTML-entities in PHP-flash-message-strings tonen rauw in Blade.** `"Reisroute &laquo;{$name}&raquo; bijgewerkt."` in de controller → Blade's `{{ }}` escapet `&` naar `&amp;` → gebruiker ziet `&laquo;...&raquo;`. Fix: literale UTF-8-karakters in de PHP-source (`«{$name}»`). VS Code default encoding (UTF-8 zonder BOM) is fine. Entities alleen in lang-files die via `@lang` of `{!! !!}` gerendered worden (bijv. `lang/nl/pagination.php` waar Laravel's paginator-view unescaped rendert). Herkomst van deze bug in RouteController: waarschijnlijk restant van een PowerShell-here-string-workaround uit een eerdere sessie.
- **VS Code find-replace is niet atomair over meerdere search-terms.** Bij chained replace-rondes (`&laquo;` → `«`, dan `&raquo;` → `»`) kan één van beide misgaan, waardoor je een mixed state overhoudt zoals `«{$route->name} >>` (deels vervangen). Post-verify moet altijd zoeken naar zowel oude patronen als naar de verwachte eindstaat. Voor N replacements: N grep-verifies met verwachte-count-assertion.
- **Flash-key inconsistentie ontdekt (niet gefixt in 5.1.b).** RouteController gebruikt `->with('success', ...)`. DestinationController, LocationController, CommentController gebruiken `->with('status', ...)`. De `admin._partials.flash`-partial loopt alleen door `['success', 'error', 'info', 'warning']` — dus `status`-flash-messages worden nooit getoond. Impact: bij Destination create/update/delete wordt de gebruiker naar de index geredirect zonder feedback-melding. Loose end voor Fase 6-cleanup: kies één convention (`status` of `success`) en migreer alle controllers. Zie ook oudere landmine over flash-key-shape onder "Spatie + framework-defaults".

### Landmines geleerd in Fase 5.1.e (statisch deel)

- **Herd PHP auto-update triggert Windows SAC-block (recurring).** Symptoom: `'C:\Users\marti\.config\herd\bin\php84\php.exe' was blocked by your organization's Device Guard policy` verschijnt mid-sessie ook na eerdere succesvolle `php`-calls. Event Viewer Code Integrity Log toont Event 3118 (Smart App Control Block) + 3077 ("did not meet Enterprise signing level"). Oorzaak: Herd heeft z'n PHP-binary ge-auto-update, nieuwe binary heeft nog geen SmartScreen-reputatie opgebouwd bij Microsoft. Fix in deze sessie: reboot (SAC-cache refetcht tegen inmiddels-warme SmartScreen-status). Landmine-vervolg: het gaat zich herhalen bij elke Herd PHP-auto-update zolang SAC aan staat. Permanente ontsnappingen: (a) SAC uitzetten (one-way — permanent tot volgende Windows-reinstall — security-trade-off op privé-machine), (b) PHP via andere gesigneerde bron installeren (windows.php.net of Chocolatey) en Herd erop wijzen, (c) wachten (uren tot dagen, geen SLA), (d) terug naar oudere Herd-PHP-versie. Voor deze sessie: reboot won. Diagnostic-reflex: bij "was blocked by Device Guard policy" op een eerder werkende PHP-binary, check `Get-WinEvent -LogName "Microsoft-Windows-CodeIntegrity/Operational" -MaxEvents 5` — Event ID 3118 is de fingerprint voor SAC (niet enterprise-WDAC, ondanks de "Enterprise signing"-tekst in de begeleidende events).

- **Duplicate `@import` in SCSS wordt door Sass silent geaccepteerd.** `resources/scss/app.scss` had twee opeenvolgende `@import 'public/_destinations-index';` — Sass 1.x compileert zonder waarschuwing, de partial wordt gewoon tweemaal geïnjecteerd (CSS-output identiek want geen mutable state). Ontdekt tijdens 5.1.e-i cleanup toen ik `_locations-show` als import wilde toevoegen. Fix in dezelfde 5.1.e-i-commit meegepakt. Preventie voor volgende SCSS-partial-toevoegingen: `Select-String -Pattern "@import 'public/" resources\scss\app.scss | Group-Object Line | Where-Object Count -gt 1` om duplicates te detecteren.

- **`@section('title', 'A — B')` met em dash rendert correct in `layouts.public` maar assert-syntax is subtiel.** `layouts.public`-title-shell is `<title>@hasSection('title')@yield('title') — @endif{{ config('app.name', 'Westein Reisblog') }}</title>` — dus voor location-detail wordt de output `<title>Rome — Italië — Westein Reisblog</title>` (twee em dashes: één uit F5-57, één uit de layout). `assertSee`-string moet de volledige geneste em-dash-string bevatten (`'<title>Rome — Italie — '.config('app.name')`) niet alleen het pagina-specifieke deel. In tests bewust `Italie` zonder trema gebruikt om Windows PowerShell + Pest console-encoding-drama te vermijden — factory-strings zijn puur test-data, niet productie-content.

- **Vite-PNG-marker-fix is nodig bij élke `import 'leaflet'`, niet alleen admin-modal-context.** Admin route-waypoints.js gebruikt `window.L` (globale Leaflet) en ontkomt aan de fix; de publieke module importeert Leaflet via de Vite-bundle (`import L from 'leaflet'`) en dán resolven de default-marker-PNG's niet zonder `delete L.Icon.Default.prototype._getIconUrl` + `L.Icon.Default.mergeOptions({ iconRetinaUrl, iconUrl, shadowUrl })` met drie expliciete PNG-imports uit `leaflet/dist/images/`. Bevestigd: `npm run build` bundelt de drie PNG's + `leaflet.css` zichtbaar.
- **`decimal:7`-cast padt coördinaten naar 7 trailing decimalen** (`41.9028` → `"41.9028000"`, string). Voor `assertSee` op coördinaten: gebruik een korte substring (`'41.9028'`) die binnen de gepadde string matcht — hard-coden van de volledige gecastte waarde is bros. Leaflet's `Number()`-guard pakt de gepadde string probleemloos op. (Terzijde: de cast-check via throwaway `.php` toonde de `Set-Content -Encoding UTF8`-BOM als `﻿` vóór de output — bekende PowerShell-BOM-landmine, tinker-output-artefact, geen invloed op de cast zelf.)

### Landmines geleerd in Fase 5.1.c

- **Blade `{{ }}`-echo's op aparte regels breken `assertSee`-substrings.** Blade rendert elke echo met omringende whitespace uit de source. `{{ $count }}` en `{{ $unit }}` op twee opeenvolgende Blade-regels produceren `<n>\n    <unit>` in de output-HTML — de browser collapsed die whitespace bij render, maar de source-HTML bevat de newline. `assertSee('3 plekken')` faalt want de substring bestaat niet aaneengesloten. Fix: gerelateerde echo's op één Blade-regel met precies één spatie ertussen: `{{ $count }} {{ $unit }}`. Diagnose: bij een verrassende `assertSee`-fail, controleer eerst de gerenderde bron-HTML (staat in de faal-output) op newlines tussen echo's, niet op de browser-render.
- **PowerShell interpreteert `->method()` in tinker `--execute` als redirect-target.** `php artisan tinker --execute="app(Controller::class)->index()"` produceert een leeg bestand met de naam `index()` in de working directory — PowerShell's parser ziet `->` niet als PHP-operator maar als redirect-token en probeert de output naar `index()` te schrijven. Symptoom: silente terugkeer zonder output, plus een untracked bestand. Fix: wegwerp-`.php`-bestand voor tinker-werk (conform bestaande landmine over multi-statement tinker), niet `--execute` met object-method-chains. Verwante shell-tokens die je in tinker moet vermijden: `>`, `>>`, `<`, `|`, `&`, backticks, ronde haken. Voor pure query-inspecties zonder method-chains kan `--execute` nog steeds werken, mits singlequotes eromheen én geen `->`.

### Landmines geleerd in Fase 5.1.d
- **Media Library conversies op Destination**: hero collection heeft medium (1200px) en large (2400px); gallery collection heeft thumb (400), medium (1200), large (2400) — allemaal WebP via `registerWebpConversion`-trait met Fit::Max. Voor edge-to-edge full-width heroes op detail-pagina's is 'large' de juiste keuze; 'medium' upscalet zichtbaar. Voor `.location-card`-tiles (~364px wide) is gallery 'medium' ruim voldoende.
- **`firstOrCreate` is idempotent**: DemoContentSeeder gebruikt dit patroon site-breed. Nieuwe waarden in seeder-arrays worden niet toegepast op bestaande records — alleen bij nieuwe slugs. Om nieuwe waarden op te leggen: `php artisan migrate:fresh --seed` (nucleair, herbouwt DB + herhaalt media-attachments). Alternatief `updateOrCreate` breekt idempotency-conventie; niet toegepast.

- **Herd PHP CLI default `memory_limit` 128M** — te laag voor Spatie Image GD-driver bij hero/media-attachment via seeder. Symptoom: `Allowed memory size of 134217728 bytes exhausted in vendor\spatie\image\src\Drivers\Gd\GdDriver.php`. Fix ad-hoc: `php -d memory_limit=1G artisan migrate:fresh --seed`. Fix permanent: in php.ini (locatie via `php --ini`) → `memory_limit = 512M`.
- **Git PATH niet automatisch op nieuwe Windows-installatie** — Git for Windows setup vraagt "Adjusting your PATH"; kies "Git from the command line and also from 3rd-party software". Symptoom: `git : The term 'git' is not recognized`. Fix na installatie: `[Environment]::SetEnvironmentVariable("PATH", "...;C:\Program Files\Git\cmd", "User")` en shell herstarten.
- **Git config `user.name` + `user.email` zijn per-machine** — bij verhuizing opnieuw zetten met `git config --global`. Zonder deze zijn commits niet aan GitHub-profiel gekoppeld.
- **GitHub-authenticatie via Git Credential Manager (GCM)** — sinds 2021 geen wachtwoord in terminal meer. Bij eerste `git push` opent browser voor GitHub OAuth, daarna cached in Windows Credential Manager. `git fetch` zonder output = succes (Git spreekt alleen bij verandering/fouten).
- **PHP 8.4.24 op nieuwe laptop, was 8.3+ op oude** — Laravel 13 compatible, geen actie. Wel om te noteren voor toekomstige `composer.json`-`platform`-config bij deployment.

### Tests (Pest + Laravel)
- **`assertRedirect(route('login'))` faalt voor `getJson()`/`postJson()`-requests.** Laravel honoreert de `Accept: application/json`-header en stuurt 401 JSON, geen 302 redirect. Gebruik `->assertUnauthorized()`.
- **`->for($model, 'relation')` met expliciete relatienaam vereist voor élke hernoemde belongsTo.** Comment + Post + Newsletter gebruiken `author()` (FK `user_id`). Zonder expliciete arg: `Call to undefined method ...::user()`. Suite valt en masse op de eerste factory-call.
- **`tests/Pest.php` runt geen seeders.** Elke testfile zet z'n eigen rollen/permissies op in `beforeEach`. Bewust — zelfvoorzienende suites.
- **AJAX-endpoint tests vereisen `Accept: application/json`-header.** Bij file-upload geen `postJson()` (multipart breekt). Gebruik `->withHeaders(['Accept' => 'application/json'])->post(...)`. Zonder header probeert Laravel bij validatie-fout een redirect-response en je krijgt cryptische "Call to a member function all() on array".
- **"Call to a member function all() on array" treedt OOK op bij URL-mismatch in `assertRedirect()`.** Diagnose: `$response->dumpSession()->dump()`. Vrijwel altijd is 't een Form Request die faalt (vaak op `email:rfc,dns` in test-domain — drop `dns`).
- **Faker PRNG-state is process-wide en advance't bij elke `fake()`-call.** Een test die op commit-A groen draait en op commit-B rood zonder dat de relevante productiecode is veranderd is bijna altijd een Faker-collision door tussenliggende tests die de sequence verschuiven. Specifiek voor multi-column LIKE-searches: zet álle searchable kolommen expliciet in de fixture, niet alleen de kolom waar de zoekterm in zit. Concrete trigger in 4.10e: 11 nieuwe newsletter-tests verschoven Faker zover dat `en_US`-locale consequent "Jansen" als surname genereerde, wat `SubscriberManagementTest::"zoekt op email"` brak terwijl die test sinds 4.9 niet was aangeraakt.
- **`assignRole()` returnt geen User.** Splits `$user = User::factory()->create();` en `$user->assignRole(...)` over twee regels — fluent chain zet de verkeerde waarde in de variabele.
- **Spatie HasSlug auto-appends suffixes** (-2, -3) ipv exception bij dubbele slug. Voor uniqueness-tests: bypass Eloquent met `DB::table()->insert()`.
- **Factory-defaults lekken door tests.** Bij `->create([...])`-overrides worden alleen genoemde kolommen overschreven; andere defaults uit `definition()` blijven actief. Voor "negatieve" states (failed, deactivated, draft) waar een default-veld op iets niet-nul moet, gebruik factory-states (`->failed()`, `->draft()`) ipv overrides — die zetten expliciet en de bijbehorende kolom op een coherent geheel. Symptoom: aggregaten of scope-queries tellen meer dan de test verwacht, zonder dat de test-fixture-code dat zichtbaar maakt. Concreet voor `NewsletterSendFactory`: default-`sent_at` is `fake()->dateTimeBetween('-1 month', '-1 day')`; alleen `->failed()`-state zet 'em terug op null.
- **Factory `count(N)->create([...])` met literal-override op een uniek-constrained kolom faalt op de tweede insert.** De override-expressie wordt één keer geëvalueerd vóór de count-loop, dus alle N rijen krijgen dezelfde waarde. Gebruik een closure: `'col' => fn () => Factory::new()->create()->id` — Eloquent's `expandAttributes()` roept callables per-rij aan. Symptoom: `UniqueConstraintViolationException` of `Integrity constraint violation` op een rij die je dacht uniek-gevarieerd te hebben.
- **Verifieer patches op disk vóór her-diagnose bij dezelfde failure.** Wanneer een test faalt na een patch met dezelfde aard van error als ervoor, eerste actie is `Get-Content path | Select-Object -Skip N -First M` op de gewijzigde regels — niet opnieuw diagnoseren op basis van wat we *dachten* dat de patch deed. Copy-paste tussen chat en VS Code kan delen van een diff overslaan, vooral bij multi-locatie-patches in één file. Sneller dan diagnose-rondes blijven draaien op een gedachten-versie van de code.

### PowerShell + Windows + Git
- **PowerShell parsert de regel vóór 't commando draait.** `{`, `}`, `[`, `]`, `;`, `&`, `|`, `$` zijn gevoelig.
  - Paden met speciale tekens: `-LiteralPath '...'` op alle `Get-Item`/`Get-Content`/`Remove-Item`.
  - Multi-statement of variabele-zwaar werk: schrijf een wegwerp `.php`-bestand. Niet vechten met `--execute` of here-string-piping naar Psy.
- **`Set-Content -Encoding UTF8` schrijft UTF-8 mét BOM op Windows-PowerShell-5.1.**
  - Voor PHP/lang-files = fataal (translator crasht op BOM bij `<?php`).
  - Voor git commit-messages = cosmetisch maar zichtbaar in `git log` als spookkarakter.
  - BOM-vrij schrijven: `[System.IO.File]::WriteAllText($path, $content, [System.Text.UTF8Encoding]::new($false))`.
  - Verifieer met `Format-Hex $path | Select-Object -First 3` — eerste drie bytes moeten content zijn, niet `EF BB BF`. PowerShell 7+ schrijft default zonder BOM, maar Herd-stacks draaien op 5.1.
- **Non-ASCII in PowerShell here-strings = mojibake door console-codepage.** `«`/`»`/`é`/`'` direct plakken wordt door de console gemangeld vóór de data in een variabele belandt. Routes: (a) HTML-entities voor lang-files (`&laquo;`/`&raquo;`); (b) direct in VS Code editen; (c) JSON (delimiters zijn ASCII). Langere PHP/Blade-bestanden vanuit chats: bouw direct in VS Code, niet via PowerShell here-string.
- **VS Code als git-editor vereist `.cmd`-suffix op Windows.** Git slaat `core.editor` op als raw string. Als je pad in `%PATH%` naar `code` (zonder suffix) verwijst, faalt `git commit` (zonder `-m`) met `No such file or directory`. Fix: `git config --global core.editor '"C:\Users\<user>\AppData\Local\Programs\Microsoft VS Code\bin\code.cmd" --wait'`. Alternatief: `git config --global core.editor notepad`. Voor éénmalige commit met multi-line message: temp-file-pattern met `git commit -F` (BOM-vrij via `[System.IO.File]::WriteAllText(..., UTF8Encoding(false))`, zie landmine hierboven).
- **Multi-paragraph `git commit -m` met meerdere `-m`-flags faalt bij lege `-m ""` ertussen.** PowerShell + backtick-continuation laat git de volgende string als pathspec interpreteren. Patroon: schrijf de message naar `.git\COMMIT_EDITMSG_TEMP.txt` (BOM-vrij, zie hierboven) en commit met `git commit -F path` — meteen idiomatisch én leesbaar.
- **OPcache + Blade view-cache op Windows:** `Remove-Item storage\framework\views\*.php -Force` soms nodig wanneer `view:clear` alleen niet werkt.
- **`@php($x = ...)` Blade shorthand compileert stuk** naar `<?php($x = ...)` zonder spatie — ongeldige PHP, geeft misleidende error op regel 1. Diagnose: `php -l storage\framework\views\*.php`. Altijd blok-vorm voor assignments: `@php $x = ...; @endphp`.
- **`@@extends`-mojibake compileert tot letterlijke `@extends`-output.** Blade behandelt `@@` als escape voor letterlijk `@`. Als je bij het plakken van een view per ongeluk een dubbele `@` op regel 1 krijgt (VS Code Blade-autocomplete + shift-select-fouten), rendert de hele view als plaintext — je krijgt geen layout, geen extends, gewoon de source-code als HTTP-response. Diagnose: `Format-Hex path\view.blade.php | Select-Object -First 2` — eerste bytes moeten `40 65` zijn (@e), niet `40 40 65` (@@e). Fix: verwijder de extra `@`. Kan óók gebeuren in `@section`, `@include` etc.
- **PowerShell here-strings + `[System.IO.File]::WriteAllText` verwijderen lege regels in git-commit-messages.** Symptoom: commit-message opgeslagen correct qua inhoud (alle bullets aanwezig, geen data-verlies), maar de blank-line ná de titel-regel ontbreekt in `git log --format=%B`. Betekent dat GitHub-web-UI en andere tools titel + body als één blok tonen. Vermoedelijk normaliseert `WriteAllText`+`git commit -F` opeenvolgende newlines. Praktische workaround: in VS Code een `COMMIT_EDITMSG_TEMP.txt` editen met de gewenste layout, dan `git commit -F path`. Voor familieblog-schaal is dit cosmetisch en niet blokkerend — voor OSS-projecten met PR-reviews wel relevant.
- **Streaming-race bij grote code-blokken tijdens copy-paste.** Symptoom: de gebruiker plakt een codeblok terwijl Claude nog aan het genereren is; de laatste regels ontbreken en het bestand krijgt een afgekapte versie. Blade-views produceren dan `syntax error, unexpected end of file`; PHP-classes zijn syntactisch invalid. Preventie: Claude eindigt grote codeblokken met een expliciete "einde-marker" (`--- EINDE VAN CODE-BLOK, veilig om te kopiëren ---`) of vraagt om bevestiging vóór verifiëren. Diagnose bij symptoom: `Get-Content path | Measure-Object -Line` + `Get-Content path | Select-Object -Last 10` om te zien of het bestand overtuigend eindigt (met `@endsection`, `}`, of ander verwacht slot-teken).
- **Windows auto-gc lockt tijdens `git commit`.** Bij een grotere commit triggert git's `gc.auto` een repack; op Windows kan git de nu-lege `.git\objects\xx`-mapjes daarna niet verwijderen omdat een ander proces file-handles vasthoudt (meestal Defender real-time scan, soms VS Code's git-integratie/indexer). Symptoom: een lange stroom `Deletion of directory '.git/objects/xx' failed. Should I try again? (y/n)`. **De commit zélf slaagt** (objecten zitten al in de packfile) — alleen de gc-opruiming faalt. Antwoord de prompts met `n`, verifieer met `git fsck --full` (alleen "dangling"-meldingen = onschuldig). Preventie: `git config gc.auto 0` (solo-repo; draai af en toe handmatig `git gc`) of `.git` uitsluiten van Defender real-time scanning. Achtergebleven lege object-mapjes zijn onschadelijk.

### Spatie + framework-defaults
- **`storage/media-library/`** hoort in `.gitignore`. Spatie schrijft tijdelijke conversion-kopieën onder random hash-paden; bij crashes of `->queued()` zonder running worker blijven die liggen.
- **Flash-key-shape moet matchen wat `admin._partials.flash.blade.php` verwacht.** De partial leest `session('success')`, `session('error')`, `session('info')`, `session('warning')` als top-level strings — géén nested `session('flash')` met `['type' => ..., 'message' => ...]`-payload. Als je een controller schrijft die `session()->flash('flash', [...])` doet, slaagt `assertSessionHas('flash')` in tests wél maar de admin ziet niks in de browser. Diagnose: browser-sanity na een test-groene action. Fix: `session()->flash('success', $message)` (of `error`/`info`/`warning`). Optionele download-link via `flash_action_url` + `flash_action_label` (uit 4.9).
- **Framework-defaults uit eerdere fasen falen stil tot een nieuwe module ze triggert.** `Paginator::useBootstrapFive()` ontbrak sinds Fase 1 maar viel pas in 4.9 op (eerste index >25 rijen). `queue:work` onthulde 2 weken Spatie image-conversion jobs. Bij elke nieuwe module: niet alleen module-specifieke gaten checken, ook of de nieuwe schaal/data-volume framework-defaults eindelijk onthult.
- **Check Fase-3-`unique`-constraints tegen actueel module-gebruik vóór een CRUD opent.** `route_waypoints.unique(route_id, location_id)` werd in Fase 3 by-default toegevoegd, conflicteerde in 4.8 met revisit-roadtrips. Dropping via migratie — Eloquent dwingt identiteit-via-PK al af.

### Sanitization & validatie
- **Purifier `Attr.AllowedClasses` werkt globaal, niet per-element.** Eén whitelist voor het HELE document. Bij toevoegen van `table[class]` aan `HTML.Allowed` óók de bestaande `tiptap-table`-class aan de whitelist toevoegen.
- **`mail()`-validation op test-domains:** drop `dns` uit `email:rfc,dns`. Test-domains hebben geen MX-records; bounce-detectie hoort thuis in de mail-bounce-flow, niet in validation.

### Componenten + Blade
- **Check component-prop-namen door de component-bron op te zoeken**, niet door te gokken uit CLAUDE.md-vermeldingen. `<x-admin.sort-link>` = `sort`-prop (niet `column`); `<x-admin.delete-button>` heeft géén `:confirm`-prop. Reflex: `Get-Content -LiteralPath resources\views\components\admin\{naam}.blade.php` zodra je 'n component gebruikt waarvan je de signature niet recent hebt gezien.
- **Geneste apostrofs/quotes mixen in Blade-attributen.** `:title="__('Pagina\'s')"` triggert ParseError. Drop de `:`-prefix voor hardcoded NL: `title="Pagina's"`. Binnen `{{ ... }}` werkt escape wél (geen attribuut-context).
- **Geneste resource-routes met `scoped(['child' => 'slug'])`** valideren parent↔child-relatie automatisch (404 bij cross-parent). Test expliciet met `assertNotFound()`.
- **`@push('modals')`-blokken vereisen een `x-data`-marker op de modal-root.** Modals die via `@push('modals')` op `</body>`-niveau renderen vallen buiten elke component-scope. Alpine processed de subtree niet automatisch — `@click`-attributen en andere directives binden niet (`_x_attributeCleanups: false`). Fix: voeg `x-data` (leeg) toe aan de modal-root-`<div>`. `$store`-toegang van binnenin werkt dan ongewijzigd. Symptoom dat dit uitwijst: knop reageert nergens op, geen JS-error, attribuut zit gewoon in DOM. Geldt voor élke pushed-modal die Alpine-directives gebruikt (geconstateerd op `mediaBulkDeleteModal` in 4.11.c — newsletter-dispatch-modal had het toevallig al door andere modal-content).
- **`<x-admin.nav-link>` accepteert optionele `:can`-prop sinds Stap 4.13.a.** Als gezet en de user heeft de permission niet, rendert de component niets (via early `return` uit `@php`-blok — Blade honoreert dat correct). Alle sidebar-items in `_partials/sidebar.blade.php` gebruiken de prop. Nieuwe modules moeten hun sidebar-link met `can="..."` toevoegen, niet met `@can`-wrap eromheen.
- **`Password::createToken($user)` gebruiken, niet `Password::broker()->createToken($user)`.** Beide werken runtime. Verschil: de facade-signature van `Password::createToken()` heeft correcte return-types, terwijl `Password::broker()` een `PasswordBroker`-**interface** returnt zonder `createToken()`-method — Intelephense rood-onderstreept dan de tweede-call. `Password::createToken()` delegeert intern naar de default broker; functioneel identiek, statisch schoner.
- **`$event->user->forceFill(...)` op `PasswordReset`-event triggert Intelephense-warning.** Event heeft type-hint `Illuminate\Contracts\Auth\CanResetPassword` (interface, geen `forceFill`). Runtime is 't onze User (extends Model). Fix: `/** @var \App\Models\User $user */`-annotation vóór de assignment. Zelfde false-positive-familie als de `Password::broker()`-warning.

### Leaflet (Vite)
- **Marker-iconen** vereisen `delete L.Icon.Default.prototype._getIconUrl` + `L.Icon.Default.mergeOptions({...})` met PNG's via Vite-imports.
- **Modal-init** vereist `shown.bs.modal`-listener vóór `L.map()` (anders dood canvas), `hidden.bs.modal` voor cleanup via `.remove()`.

### SortableJS
- **Revert DOM, re-render uit model.** SortableJS muteert DOM direct; Alpine ziet dat als out-of-band. Patroon in `onEnd`: eerst item op `event.oldIndex` terugplaatsen, DAN Alpine-array splice'n, force-notify met `this.array = [...this.array]`.

### Observaties (te volgen, niet acuut)
- ~~`config('app.faker_locale')` = `en_US`~~ opgelost in 5.1.a (nu `nl_NL` via `.env`; localiseert alleen data-methodes, niet tekstgenerators — nieuwe landmine gedocumenteerd).
- ~~`ExampleTest.php` (welcome-view Vite-manifest-fout)~~ opgelost in 5.0.c (`welcome.blade.php` + `ExampleTest.php` verwijderd).

---

### Landmines & observaties Fase 5.2

- **`scopeBindings()` op een 3-segment-route accepteert de post-aan-location-koppeling runtime probleemloos.** De zorg was of Laravel de `{post}`-binding correct aan `{location}` scopet (post heeft `location_id`, de impliciete `location`-relatie). Zowel route-registratie (`route:list` zonder fout) als runtime (de cross-parent-404-test groen) bevestigen dat het werkt zonder custom `resolveRouteBinding`. De controller houdt een defensieve `location_id !== $location->id`-vangnetcheck (F5-78) — dubbelop maar goedkoop; niet nodig gebleken maar veilig.
- **Seeder-posts krijgen bewust geen fixture-featured-images (F5-68/Optie A).** De `<x-public.post-card>` toont dan de `post-card__image-placeholder` (grijs vlak + `bi-image`-icoon) — dat is de correcte lege-staat, geen bug. Echte featured images komen via de admin (productie) of Martins content (Fase 6). De featured-image-strategie zelf (incl. de ontbrekende `large`-conversie, post-hero) hoort bij het 5.2.b-hero-gesprek.
- **Twee `PostController`-klassen leven naast elkaar.** `App\Http\Controllers\PostController` (publiek, 5.2.a) en `App\Http\Controllers\Admin\PostController` (admin, Fase 4). Verschillende namespaces, geen conflict. Bij het aanmaken van de publieke: let op dat je 'm niet per ongeluk in de `Admin`-namespace zet.
- **`@section('title', $x)` inline vs. `@section('content') ... @endsection` block.** De publieke views hebben twee inline title/meta-secties (geen `@endsection`) plus één block content-sectie (mét `@endsection`). Een naïeve `@section`/`@endsection`-balanscheck telt dan een "mismatch" die geen fout is. De titel-shell in `layouts.public` is `<title>@hasSection('title')@yield('title') — @endif{{ config('app.name') }}</title>` — een view die `@section('title','Verhalen')` zet, produceert `<title>Verhalen — Westein Reisblog</title>` (em-dash + spaties).
- **`Category::factory()->create(['name' => 'Tips'])` levert slug `tips`** via `HasSlug` (`generateSlugsFrom('name')`). Nodig in tests voor de tip-detectie (`categories->contains('slug', 'tips')`). Geen expliciete slug meegeven nodig. Category gebruikt overigens `use HasFactory, HasSlug;` op één regel (enige comma-separated-traits-plek; niet aanraken).
- **Geen catch-all in `routes/web.php`.** De nieuwe named één-segment-routes (`/verhalen`) botsen met niks. Zodra er ooit een Pages-catch-all `/{page:slug}` bijkomt (voor `/over-ons` etc.), moet die als láátste vóór de auth-groep staan — anders vangt 'ie `/verhalen` en `/reistips` weg. Zie loose-ends.
- **`{{ $posts->links() }}` rendert Bootstrap-5-paginering** dankzij `Paginator::useBootstrapFive()` in `AppServiceProvider`. Geen extra config nodig; de admin-indexen leunen er al op.
- **FormRequest onder de verkeerde namespace/map = `ReflectionException` "Class ... does not exist".** `StoreCommentRequest` belandde eerst in `app\Http\Requests\Admin\` terwijl de controller `App\Http\Requests\StoreCommentRequest` type-hintte — PSR-4 map ≠ namespace, dus Composer vond de class niet en de POST gaf een 500 (de GET-pagina rendert wél, want die raakt de class niet). Fix: bestand naar `app\Http\Requests\` verplaatsen + `namespace App\Http\Requests;`. Reflex bij "Class ... does not exist" op een net-toegevoegde class: check of map en namespace matchen.
- **Honeypot staat in tests default AAN.** `phpunit.xml` zet geen `HONEYPOT_ENABLED`, dus `config('honeypot.enabled')` is `true` in de suite en `ProtectAgainstSpam` draait mee. Zet 'm per test-file uit met `config(['honeypot.enabled' => false])` in `beforeEach` zodat je de eigen logica toetst. Een "honeypot blokkeert spam"-test is bros door `randomize_name_field_name` (onvoorspelbare veldnaam) — die werking leunt op Spatie's eigen suite + browser-verificatie.
- **De publieke layout rendert geen globale flash.** `layouts.public` heeft geen flash-partial (anders dan admin). Publieke flash moet je zelf renderen — de comment-flow toont `session('comment_success')` scoped in de comments-sectie (past ook bij het `#reactie-{id}`-anker). Latere publieke flows die flash nodig hebben: overweeg een publieke flash-partial in `layouts.public`, of render scoped.
- **`/verhalen` en `/reistips` delen het Post-model maar zijn wederzijds exclusief gefilterd (F5-94).** `index()` weert tips (`whereDoesntHave('categories', slug='tips')`), `indexTips()` toont alleen tips (`whereHas`). Bij de uitgestelde categorie/tag-index (F5-76) deze twee grenzen consistent houden zodat content niet in twee neutrale archieven tegelijk opduikt.

### Landmines geleerd in Fase 5.3

- **`route(...)` vereist de slug-kolom in de eager-load-select.** `route('locations.show', [$dest, $loc])` faalt met `UrlGenerationException: Missing parameter: location` als de select de location's `slug` niet meepakt (binding op slug). Selecteer altijd `slug` mee bij modellen die je in `route()` gebruikt. Symptoom trad pas op bij het renderen van de link, niet bij de query. (5.3.b)
- **Een Leaflet-container heeft een expliciete CSS-hoogte nodig.** Een `[data-…map]`-div zonder hoogte → Leaflet rendert in 0px, dus je ziet een lege gap i.p.v. een kaart. Geef de container hoogte in de SCSS; op een fresh page-load met die hoogte init Leaflet correct (geen `invalidateSize()` nodig). (5.3.b/5.3.c)
- **`@json($x)` in een HTML-attribuut is veilig** dankzij de default HEX-flags (`JSON_HEX_TAG|APOS|AMP|QUOT`): apostrofs/quotes worden `\u00xx`, dus `data-x='@json(...)'` in single quotes breekt niet, en bare getallen blijven leesbaar (`assertSee` op een coördinaat werkt). Gebruikt voor `data-waypoints`. (5.3.b)
- **CRLF op nieuw-aangemaakte files.** VS Code schrijft nieuwe bestanden soms met CRLF; git waarschuwt bij `add` en normaliseert naar LF (conventie #9 blijft gedekt). Zet VS Code's default EOL op `\n` om de waarschuwing te vermijden.
- **Media in tests: `Storage::fake('public')` + `UploadedFile::fake()->image()` + `addMedia()->toMediaCollection('gallery')`.** Werkt met de nonQueued-conversies (F4-N7, GD draait sync in de test). Nodig om galerij-/media-afhankelijke views te testen. (5.3.c `PhotosIndexTest`)

### Landmines geleerd in Fase 5.4

- **Twee seeders die op verschillende keys `firstOrCreate`'en stapelen stil.** `DemoContentSeeder` keyt FamilyMember op `slug`, de losse `FamilyMemberSeeder` op `name` → geen botsing, wél dubbele records (8 i.p.v. 4). Beide stonden in `DatabaseSeeder`. Diagnose-reflex bij "index/grid toont te veel": check of meerdere seeders hetzelfde model vullen (`Select-String -Path database\seeders\*.php -Pattern "Model::"`).
- **Een catch-all in `web.php` is NIET globaal laatste.** `routes/admin.php` (en mogelijk andere) laden ná `web.php`, dus een kale `Route::get('/{page:slug}')` als laatste regel van `web.php` kaapt tóch `/admin` (single-segment → 404); `/admin/pages` e.d. (multi-segment) ontsnappen. Fortify's `/login` werd niet gekaapt → laadvolgorde is **Fortify → web.php → admin.php**.
- **`Route::fallback()` is wél globaal laatste, maar matcht élk pad voor GET|HEAD.** Gevolg: een onbekende **niet-GET** request (bv. `POST /admin/trash/restore/badtype/1`) matcht de fallback qua pad maar niet qua methode → **405** i.p.v. 404 (brak `TrashManagementTest`'s "bad type → 404"). Fallback dus niet gebruiken als je 404-semantiek voor onbekende POSTs wilt behouden.
- **Gekozen catch-all-patroon:** single-segment GET met reserved-slug-uitsluiting via negatieve lookahead: `Route::get('/{page:slug}', ...)->where('page', '(?!('.implode('|', config('westein.reserved_slugs')).')$)[^/]+')`. `[^/]+` = single-segment (multi-segment POSTs blijven 404), lookahead sluit echte routes uit (`/admin` valt door naar `admin.php`). `reserved_slugs` doet zo dubbel werk: F4-11 page-creation-block én catch-all-uitsluiting. **Bij elke nieuwe publieke één-segment-route: toevoegen aan `reserved_slugs`.**
- **`Mail::to()->send($mailable)` queue't automatisch als het mailable `ShouldQueue` is.** In tests: `Mail::fake()` + `Mail::assertQueued(...)` (niet `assertSent`). In dev zonder draaiende `queue:work` blijft de contactmail in de `jobs`-tabel staan; de success-flash verschijnt wél meteen (bewust: publieke respons hangt niet op SMTP, F4-N8-parallel).

## Roadmap — fase-status

- ✅ **Fase 1 — Project setup & design system** _(afgerond 2 mei 2026)_
- ✅ **Fase 2 — Authenticatie & autorisatie** _(afgerond 10 mei 2026)_
- ✅ **Fase 3 — Database & content modellen** _(afgerond 13 mei 2026)_
- ✅ **Fase 4 — Afgeschermd Admin-gedeelte** _(afgerond)_
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's** _(afgerond 23 augustus 2026)_
- ⏳ **Fase 6 — SEO, performance en publicatie**

### Fase 5 — overzicht

| Stap         | Inhoud                                                                             | Suite     | Status |
| ------------ | ---------------------------------------------------------------------------------- | --------- | ------ |
| **5.0.a**    | Publieke layout + site-nav + blog-nav + footer                                     | 526 → 526 | ✅     |
| **5.0.b**    | `/mijn-account` met geïntegreerde 2FA                                              | 526 → 542 | ✅     |
| **5.0.c**    | Homepage + welcome-vervanging + ExampleTest opruimen                               | 542 → 553 | ✅     |
| **5.0.d**    | Sessies-invalidatie F4-U18 bij email-change door admin                             | 553 → 553 | ✅     |
| **5.1.a**    | DemoContentSeeder verrijken + fixture-images + is_featured data-laag               | 553 → 553 | ✅     |
| **5.1.b**    | is_featured admin-toggle UX (Destination + Route + Post, drie sub-blokken)         | 553 → 571 | ✅     |
| **5.1.c**    | `/bestemmingen` publieke index-pagina                                              | 571 → 577 | ✅     |
| **5.1.d**    | `/bestemmingen/{destination}` detail-pagina                                        | 577 → 584 | ✅     |
| **5.1.e-i**  | `/bestemmingen/{destination}/{location}` detail-pagina (statisch + breadcrumb)     | 584 → 593 | ✅     |
| **5.1.e-ii** | Leaflet-kaart op location-detail                                                   | 593 → 595 | ✅     |
| **5.2.0**    | Blocker-chore: `scopePublished()` + post-content-verrijking + reistips-seeding     | 595 → 595 | ✅     |
| **5.2.a**    | Publieke blog-index `/verhalen` + post-detail-fundament + `url()`-helper           | 595 → 614 | ✅     |
| **5.2.b**    | Post-detail afmaken (hero, breadcrumb, SEO) + comments (threaded + honeypot)       | 614 → 633 | ✅     |
| **5.2.c**    | Reistips-categorie-view `/reistips` + cross-linking destination → tips             | 633 → 644 | ✅     |
| **5.3.0**    | Blocker-chore: routes publiceren (is_published + published_at)                     | 644 → 644 | ✅     |
| **5.3.a**    | Publieke `/reisroutes`-index + kale route-detail                                   | 644 → 655 | ✅     |
| **5.3.b**    | Route-detail compleet: Leaflet-polylijn + waypoint-links + verhalen-strook         | 655 → 659 | ✅     |
| **5.3.c**    | Fotogalerij `/fotos` + progressive filters + Alpine-lightbox                       | 659 → 665 | ✅     |
| **5.4.0**    | Blocker-chore: content (bio's + page-body's) + seeder-consolidatie                 | 665 → 665 | ✅     |
| **5.4.a**    | Auteurs `/auteurs/{slug}` + Over ons `/over-ons`                                   | 665 → 673 | ✅     |
| **5.4.b-i**  | Statische pagina's via catch-all `/{page:slug}`                                    | 673 → 679 | ✅     |
| **5.4.b-ii** | Contactformulier `/contact` (honeypot + throttle, mail-only)                       | 679 → 682 | ✅     |
| **5.5.a**    | Nieuwsbrief-aanmelding + double-opt-in-bevestiging (`/nieuwsbrief`)                | 682 → 690 | ✅     |
| **5.5.b**    | Publieke unsubscribe `/nieuwsbrief/uitschrijven/{token}` (sluit F4-N11)            | 690 → 693 | ✅     |
| **5.6**      | Eindcheck + `fase-5-bouwplan.md` schrijven                                         |           | ✅     |

**Totaal suite-status:** 693 groen (1750 assertions).
