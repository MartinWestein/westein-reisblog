# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 28 mei 2026 — Fase 4 in uitvoering (Stap 4.3.3 afgerond)
**Masterplan:** zie `westein-reisblog-masterplan.md` voor volledige architectuur
**Bouwplannen:** Fase 2 staat vast in `fase-2-bouwplan.md`. Fase 4 wordt na afronding vastgelegd in `fase-4-bouwplan.md`.

---

## Wat dit project is

Een schaalbare, veilige Laravel-reisblog voor familievakanties van de familie Westein. Server-side rendered Blade, geen SPA. Multi-generatie publiek (familie, vrienden), Nederlandstalig, Dodger Blue als primaire kleur. Doel: SEO-groei en duurzaam onderhoud op Nederlandse shared hosting.

## Stack — definitief

- **Backend:** Laravel 13.7, PHP 8.3+, MySQL 8
- **Frontend:** Blade + Bootstrap 5 + Alpine.js + Vite
- **Editor:** TipTap (in admin) — twee profielen: `rich` (Posts) en `simple` (Pages, Newsletter)
- **Kaarten:** Leaflet
- **Auth:** Laravel Fortify
- **Permissions:** Spatie Laravel Permission (rollen: Admin, Editor, Auteur, Lid)
- **Media:** Spatie Media Library
- **SEO:** Spatie SEO + Spatie Sitemap
- **Slugs:** Spatie Sluggable
- **Spam:** Spatie Honeypot
- **Tests:** Pest 4 (geen PHPUnit-classes — zie conventie #14)
- **Lokaal dev:** Herd + DBngin + VS Code op Windows. Projectroot: `C:\Herd\westein-reisblog` (bewust buiten OneDrive)
- **Versiebeheer:** Git + GitHub (private repo) via GitHub CLI
- **Hosting:** Nederlandse shared hosting (provider t.b.d. — Hostnet wordt gebruikt voor mail)
- **Vertalingen:** `lang/nl/` met `auth.php`, `validation.php`, `passwords.php`, `pagination.php`. `APP_FALLBACK_LOCALE=en` zodat ontbrekende NL-keys terugvallen op vendor-strings i.p.v. de raw key tonen. Lang-bestanden moeten UTF-8 **zonder BOM** zijn (BOM op `<?php` laat de translator crashen).
- **PHP upload-limieten (lokaal):** php.ini opgehoogd naar `upload_max_filesize=16M` / `post_max_size=32M` (Herd default was 2M/8M). Herd restarten na php.ini-wijziging.

## Designkeuze — definitief

**"Modern magazine"** (Voorstel B uit Fase 1).

- Achtergrond: zandbeige `#F8F6F2`
- Tekst: `#14213D`
- Headings: Playfair Display (serif)
- Body: Inter (sans-serif)
- Accenten: perzik `#E8A87C`, salie-groen `#41B3A3`, gedempt rosé `#C38D9E`
- Stijl: edge-to-edge fotografie, magazine-uitstraling, kleine kapitalen voor tags
- Design tokens staan in `resources/scss/design-tokens.scss`
- Admin SCSS-partials in `resources/scss/admin/_layout.scss`, `_sidebar.scss`, `_topbar.scss`, `_components.scss`, `_forms.scss`, `_form-layout.scss`, `_family-members.scss`, `_image-upload.scss`

## Beslissingen Fase 2 — definitief

- Registratie: open + e-mailverificatie verplicht
- 2FA: verplicht voor Admin/Editor, optioneel voor andere rollen
- Mail (dev + prod): SMTP via eigen domein `website.support@ml-westein.nl`
- Rollen-model: meerdere rollen per gebruiker (Spatie default, flexibel)

## Beslissingen Fase 3 — definitief

- Tags: polymorfe pivot (taggables) — herbruikbaar voor Locations/Routes later
- Tag-namen: lowercase forceren via mutator
- Categorie-volgorde: `order`-veld op `categories` (int, voor handmatige sortering in UI)
- Posts ↔ Categories: BelongsToMany
- Location-slugs: globaal uniek (afwijking van masterplan §3.3 — eenvoudiger in code en URL-routing)
- Validatie §3.4 (auto-set destination_id vanuit location_id): **niet** geïmplementeerd in model — Admin moet beide velden handmatig kiezen, Form Request-regel valideert consistentie. UX-helper in Fase 4 via JS.
- Slug-stabiliteit: alle HasSlug-modellen gebruiken `doNotGenerateSlugsOnUpdate()` (Destination, Location, Post, Category, Page, **Tag** sinds Stap 4.3.2, **FamilyMember** sinds Fase 3)
- FK-strategie posts: `user_id` is `restrictOnDelete`, `destination_id`/`location_id` zijn `nullOnDelete`
- Cascading destination: Locations cascaderen mee bij hard-delete; bij soft-delete blijven Locations actief (zie Fase 4 beslissing soft-delete)
- Media Library collecties: Post=`featured` (single) + `inline_images` (multi sinds Stap 4.0), Location=`gallery`, Destination=`hero` + `gallery`, User=`avatar`, **FamilyMember=`portrait` (single)**. Acceptable MIME: JPEG, PNG, WebP.
- WebP-conversies per collectie afgestemd, queued, origineel bewaard. Quality 82, `Fit::Max`. FamilyMember `portrait`: `webp-600` (Max 600×600) + `webp-300` (Max 300×300).
- Image driver: GD (portable voor shared hosting)
- Post `author()`-relatie (sinds Stap 4.2): gerenamed van `user()` voor consistentie met Comment en Newsletter — FK kolom blijft `user_id`, `belongsTo()` krijgt expliciet `'user_id'` als 2e arg

## Beslissingen Fase 4 — definitief

1. **UI-stack:** strikt Blade + Bootstrap + Alpine, geen Livewire/Filament
2. **Bouwvolgorde:** foundation eerst, dan CRUD
3. **Lijst-patroon:** server-side via querystring + Laravel paginate (geen Alpine-fetch debouncing)
4. **TipTap output:** HTML + server-side HTMLPurifier sanitization
5. **TipTap profielen:** `rich` (Posts, alle extensions) + `simple` (Pages, Newsletter — geen tabellen/YouTube/image)
6. **Image picker:** tabbed modal — 'Uit Location-album' + 'Nieuwe upload', auto-draft-on-image bij nieuwe post
7. **Media-browser:** volledig `/admin/media` met grid, filters, bulk-acties (geen losse upload in v1 — uploaden via model)
8. **Newsletter:** TipTap simple + 3 vaste templates (`announcement`, `digest`, `plain`), Emogrifier inliner, batch-50 queued
9. **Soft deletes:** op Posts, Destinations, Locations, Routes, Pages + `/admin/prullenbak` + auto-purge 30d. **Niet** op Comments, Users (AVG), Subscribers, **FamilyMembers**.
10. **Slug-bewerking:** bewerkbaar bij create, read-only bij update. Admin heeft 'ontgrendel'-knop bij Posts/Destinations (komt in 4.4/4.5)
11. **Tests:** kritische paden — Posts CRUD, Comment moderatie, Newsletter dispatch, RBAC matrix
12. **Index-patroon per module:** tabel voor Categories/Tags/Pages/Subscribers/Comments, cards voor FamilyMembers/Posts/Destinations
13. **Delete-bevestiging:** inline confirm via Alpine (`<x-admin.delete-button>` component; cards gebruiken `<x-admin.card-actions-menu>` met ingebouwde confirm)
14. **User-deactivatie:** veld `deactivated_at` (timestamp nullable) + `deactivation_reason` (text nullable) — toegevoegd in Stap 4.0
15. **Inline_images op Post:** aparte Media-collectie (multi-file) naast `featured` (single) — voor TipTap upload-tab
16. **Form-layout:** two-column (`<x-admin.form-layout>`) — hoofdcontent links, metadata rechts. Standaard voor alle modules met meer dan ~4 velden (FamilyMembers, Pages, Posts, Destinations, Locations). Categories/Tags blijven single-column.
17. **FamilyMember card-layout:** ronde portret/initialen centraal, 4 per rij desktop, driepuntsmenu rechtsboven, bio inline-expand.
18. **FamilyMember user-koppeling:** dropdown alle users + disabled 'Nieuwe gebruiker'-knop (ontgrendelt in Stap 4.13). `family.manage` toegekend aan Admin (via Gate::before) + Editor.

## Conventies — werk altijd zo

1. **Eén plek voor één ding.** Geen business-logic in Blade. Geen validatie in controllers. Geen queries in models.
2. **Naamgeving:** Engels in code, Nederlands in URL's en UI.
3. **Form Requests altijd** voor POST/PUT validatie. Authorize() in de Request doet de policy-check voor store/update.
4. **Policies altijd** voor autorisatie. Geen `if ($user->isAdmin())` in controllers. **Patroon Laravel 11+:** `$this->authorize('action', $model)` per controller-method (zie leerpunten).
5. **Eager loading discipline.** `with()` overal waar relaties getoond worden. Telescope detecteert N+1's in dev.
6. **Queues vanaf dag 1**, ook al draait de driver op `database`.
7. **Database-indexen vanaf het begin.**
8. **Tests:** Feature-tests voor kritische paden. Geen 100% coverage als doel.
9. **Pint vóór elke commit.**
10. **Line endings = LF.**
11. **Na élke `.env`-wijziging: `php artisan config:clear`.**
12. **Nooit echte secrets in chats/issues plakken.**
13. **Geen Laravel-projecten in OneDrive.**
14. **Pest-syntax in tests, niet PHPUnit-classes.** `RefreshDatabase` is centraal actief via `tests/Pest.php`.
15. **PowerShell-quoting:** single quotes (`'...'`) voor regex-filters met `|`. Of gebruik padargument om filter te omzeilen. Voor `tinker --execute` met PHP-variabelen: outer single quotes + escape PHP-strings met `\"`.
16. **Per CRUD-module: server-side patroon.** Querystring-gestuurde filters/sort/paginate, `withQueryString()` op de paginator, `<x-admin.sort-link>`-component voor kolom-headers.
17. **Inline-delete via `<x-admin.delete-button>`** (tabellen) of **`<x-admin.card-actions-menu>`** (cards) voor consistente UX over alle CRUDs.
18. **Check bestaande componenten/conventies vóór je nieuwe verzint.** Grep/Get-Content op een bestaande module (Categories/Tags) draaien vóór je een form/CSS-patroon schrijft. Het echte form-patroon is `<x-admin.field>` + `.admin-field`, plus `<x-admin.form-layout>` + `<x-admin.form-section>` voor de two-column wrapper.

## Architectuur — kernkeuzes

- Content-hiërarchie: Destination → Location → Post (Post mag óók direct aan Destination hangen)
- Reistips: categorie binnen Posts, geen aparte tabel
- Reacties: alleen ingelogde gebruikers, met moderatie
- Routes: geordende lijst van Locations, Leaflet trekt rechte lijnen
- Foto's: album per Location, Post heeft eigen featured image
- Newsletter: eigen beheer (Subscriber + Newsletter + queued sending, double opt-in)
- Talen: alleen NL nu, structuur klaar voor uitbreiding (`__()` overal)

Volledige database-architectuur, ERD en URL-structuur: zie masterplan §3.

## Herbruikbare admin-componenten

Opgebouwd tijdens Fase 4 — hergebruiken in volgende modules:

- **`<x-admin.field>`** — label + input/textarea/number, error-mapping, hint, readonly. Basis-veld.
- **`<x-admin.form-layout>`** — two-column form-wrapper (slots: `main`, `side`, `actions`). Form-tag zit IN de component; views geven slots + `enctype` mee.
- **`<x-admin.form-section>`** — subtiele groepering binnen een kolom (uppercase mini-header + body).
- **`<x-admin.image-upload>`** — drag-and-drop upload. Alpine-factory in `resources/js/admin/image-upload.js`, geregistreerd via `alpine:init` → `Alpine.data('imageUpload', ...)`. Client-side validatie (MIME/size/dimensions) + server-side Form Request. Props: `name`, `shape` (square/circle), `current-url`, `max-mb`, `min-width`, `min-height`. File-input `name="X"` + remove-checkbox `name="remove_X"`.
- **`<x-admin.avatar-initials>`** — portret of initialen-fallback met deterministische accent-kleur (`crc32(id) % palette`). Props: `member`, `size`.
- **`<x-admin.card-actions-menu>`** — driepuntsmenu (⋮) met Bewerken + inline delete-confirm (Alpine). Props: `edit-url`, `delete-url`, `delete-confirm`.
- **`<x-admin.delete-button>`** — inline delete-confirm voor tabel-rijen (bestond al sinds 4.3.1).

## Roadmap — fase-status

- ✅ **Fase 1 — Project setup & design system** _(afgerond 2 mei 2026)_
- ✅ **Fase 2 — Authenticatie & autorisatie** _(afgerond 10 mei 2026)_
- ✅ **Fase 3 — Database & content modellen** _(afgerond 13 mei 2026)_
- 🔄 **Fase 4 — Afgeschermd Admin-gedeelte** _(in uitvoering)_
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

## Fase 4 — overzicht (in uitvoering)

| Stap     | Inhoud                                                                                  | Status      |
| -------- | --------------------------------------------------------------------------------------- | ----------- |
| **4.0**  | Fundament: soft deletes op kern-content, users opruimen + `deactivated_at`, Post `inline_images` collectie | ✅ afgerond |
| **4.1**  | Admin-layout: inklapbare sidebar (flexbox), gegroepeerde nav, topbar met gebruikersmenu, flash + form-componenten | ✅ afgerond |
| **4.2**  | Dashboard met 6 KPI-cards + gemixte activity feed. Rename `Post::user()` → `Post::author()` | ✅ afgerond |
| **4.3.1** | Categories CRUD                                                                        | ✅ afgerond |
| **4.3.2** | Tags CRUD (met morphedByMany op Posts)                                                 | ✅ afgerond |
| **4.3.3** | FamilyMembers CRUD — eerste cards-layout + eerste media-upload                         | ✅ afgerond |
| **4.3.4** | Pages CRUD — eerste TipTap (simple-profiel)                                            | ⏳ volgende |
| **4.4**  | Destinations + Locations CRUD                                                           | ⏳          |
| **4.5**  | Posts CRUD inclusief TipTap rich                                                        | ⏳          |
| **4.6**  | TipTap image-picker modal                                                               | ⏳          |
| **4.7**  | Comment-moderatie                                                                       | ⏳          |
| **4.8**  | Routes + Waypoints CRUD                                                                 | ⏳          |
| **4.9**  | Subscribers + import/export                                                             | ⏳          |
| **4.10** | Newsletter compose & dispatch                                                           | ⏳          |
| **4.11** | `/admin/media` browser                                                                  | ⏳          |
| **4.12** | `/admin/prullenbak`                                                                     | ⏳          |
| **4.13** | Users + rollen beheer                                                                   | ⏳          |
| **4.14** | Tests, Pint, fase-4-bouwplan document, commit                                           | ⏳          |

## Wat staat er nu in Fase 4

- **Soft deletes** op `posts`, `destinations`, `locations`, `routes`, `pages`. `users` heeft `deactivated_at` + `deactivation_reason`. `users.avatar_path` dropped (Media Library `avatar`-collectie heeft 'm vervangen).
- **Admin-layout** met inklapbare sidebar (flexbox, niet grid), gegroepeerde nav (Content / Engagement / Beheer), topbar met breadcrumbs + gebruikersmenu, flash-partial. localStorage-state voor sidebar-collapse. Mobiel: hamburger + sliding overlay + backdrop. Breadcrumbs: flat patroon met `<i class="bi bi-chevron-right">`-separators + `<span class="admin-breadcrumbs__current">` (geen Bootstrap `<ol class="breadcrumb">`).
- **Dashboard** met 6 KPI-cards (Posts published / Drafts / Reacties totaal / Te modereren / Abonnees / Geplande brieven) en gemixte activity feed via `ActivityFeed`-service.
- **Categories CRUD** (`/admin/categories`) — server-side filters, sort, paginate, inline delete-confirm, locked slug bij edit.
- **Tags CRUD** (`/admin/tags`) — server-side filters, sort, paginate, inline delete-confirm. `Tag::posts()` via `morphedByMany`, `withCount('posts')` voor "gebruikt in N posts"-teller. Tag heeft `doNotGenerateSlugsOnUpdate()`.
- **FamilyMembers CRUD** (`/admin/family-members`) — eerste cards-layout (4 per rij, ronde portret/initialen, driepuntsmenu, bio inline-expand), eerste media-upload via `<x-admin.image-upload>` (drag-and-drop, `portrait`-collectie). Two-column form via `<x-admin.form-layout>`. Server-side search/sort/paginate. `FamilyMemberPolicy` op `family.manage` (Admin + Editor). Demo-seeder met 4 familieleden (geen foto's → initialen-fallback). 15 Pest-tests (CRUD, RBAC-matrix, validatie, media-upload, media-remove).

## Leerpunten Fase 4 — bewaar voor volgende keer

1. **Laravel 11+ controllers hebben geen `middleware()` en geen `authorizeResource()`.** `Illuminate\Routing\Controller` is zo goed als leeg. Patroon: `AuthorizesRequests`-trait toevoegen aan base `app/Http/Controllers/Controller.php`, dan `$this->authorize('action', $model)` per method.

2. **`Post::user()` → `Post::author()` consistentie.** FK-kolom blijft `user_id`; relatie heet `author()` met expliciet `'user_id'` als 2e arg in `belongsTo()`. Comment en Newsletter idem. Test-factories die `->for($user)->...` op Post deden moeten nu `->for($user, 'author')->...`.

3. **`isolation: isolate` op de mobiel-sidebar** lost stacking-context issues op. Backdrop niet onder de sidebar leggen via z-index, maar met `left: 280px` ernaast positioneren.

4. **Alpine `x-data` met multi-line objecten** kan in sommige Blade-contexts fragile zijn. Gebruik een named function in `<script>` of een Alpine-factory geregistreerd via `alpine:init` → `Alpine.data(...)`.

5. **Voor brede flex-layouts: `min-width: 0` op de inner flex-item.** Anders duwen brede tabellen het grid kapot.

6. **CSS Grid kan onverwacht uitlijnen bij sticky children.** Voor admin-shell switched naar flexbox.

7. **`morphedByMany` is de inverse van `morphToMany`.** Tag heeft `morphedByMany(Post::class, 'taggable')`.

8. **Slug-locking op edit-form:** veld heet bewust `slug_display` (niet `slug`) en is `readonly`. Want field met name `slug` zou in fillable/validated terechtkomen. In FamilyMemberRequest wordt `slug_display` expliciet via `offsetUnset()` uit de input gehaald in `prepareForValidation()`.

9. **Check bestaande componenten/conventies vóór nieuwe verzinnen.** Bij FamilyMember-form eerst een fictief `.admin-form__*`-stelsel geschreven dat niet bestond — het echte patroon was `<x-admin.field>` + `.admin-field`. Altijd eerst grep/Get-Content op een bestaande module draaien.

10. **`<x-admin.form-layout>` + `<x-admin.form-section>` = herbruikbaar two-column form-patroon** (main links, side rechts). Form-tag zit IN de component; views geven alleen slots + `enctype` mee. Gebruik `@isset($enctype)` i.p.v. `@if ($enctype)` voor robuustheid bij undefined prop.

11. **`<x-admin.image-upload>` = herbruikbare drag-and-drop upload.** Alpine-factory in `resources/js/admin/image-upload.js`, geregistreerd via `alpine:init` → `Alpine.data()`. Client-side validatie (MIME/size/dimensions) + server-side Form Request. `name="portrait"` file-input + `name="remove_portrait"` checkbox. DataTransfer-trick schrijft drop-FileList terug naar de input.

12. **php.ini upload-limieten.** Herd default = `upload_max_filesize=2M`. Opgehoogd naar 16M (upload) / 32M (post). `validation.uploaded` "uploaden mislukt" = bestand overschrijdt PHP-limiet, niet de Laravel-regel. Herd restarten na php.ini-wijziging.

13. **lang/nl vereist `validation.php`, `passwords.php`, `pagination.php`** (publiceren via `php artisan lang:publish` of handmatig). `APP_FALLBACK_LOCALE=en` zodat ontbrekende NL-keys terugvallen op vendor-strings i.p.v. de raw key. Let op UTF-8 **zonder BOM** — BOM op `<?php` laat de translator crashen met `array_replace_recursive(): Argument #2 must be of type array`.

14. **Avatar-fallback patroon:** `<x-admin.avatar-initials>` met deterministische accent-kleur (`crc32(id) % palette`). Herbruikbaar voor comment-moderatie + gebruikersbeheer.

15. **`<x-admin.card-actions-menu>` = driepuntsmenu** met inline delete-confirm (Alpine). Herbruikbaar voor alle card-based modules.

16. **`assignRole()` returnt geen User.** Splits `$user = User::factory()->create();` en `$user->assignRole(...)` over twee regels in tests — de fluent chain `->create()->assignRole()` zet de verkeerde waarde in de variabele. De "Undefined property `$this->admin`"-melding in Pest is overigens IDE-cosmetica, geen runtime-fout.

## Werkstijl voor Claude

- Iteratief, stap voor stap. Niet alles in één keer.
- Verduidelijkende vragen via `ask_user_input_v0` met 2-4 opties.
- Eén beslissing per keer waar mogelijk.
- Code in copy-pasteable blokken met duidelijke bestandsnamen.
- PowerShell-syntaxis (Windows). Single quotes bij regex-filters.
- Pest-syntax voor tests.
- Nederlands in uitleg en commits, Engels in code.
- Eerlijk over trade-offs.
- Geen onnodige herhaling van masterplan. Verwijs ernaar (`§3.4`).
- Waarschuw bij secrets in chat. Adviseer roteren.
- Bestandsnamen exact in casing (Git en Pest zijn case-sensitive).

## Volgende concrete actie — Stap 4.3.4: Pages CRUD

Generieke statische pagina's (Over ons, Privacy, Contact). **Eerste module met TipTap** (simple-profiel: geen tabellen/YouTube/image-extensions). Tabel-patroon (niet cards), conform Fase 4 beslissing #12.

Vragen die we vooraf moeten beslissen:

1. **Bestaande staat checken** — `Page`-model + migratie: welke kolommen? Heeft het al `title`, `slug`, `body` (longtext voor TipTap-HTML), `published_at`, `order`? Soft-deletes actief (Page staat in de soft-delete-lijst, beslissing #9)?
2. **TipTap simple-profiel** — eerste keer dat we TipTap integreren. Welke extensions exact in `simple`? (StarterKit minus tabellen/YouTube/image; wel: bold, italic, headings, lists, link, HR). Hoe initialiseren we het in een Alpine-component? Output = HTML, server-side HTMLPurifier sanitization (beslissing #4).
3. **HTMLPurifier** — package nog niet geïnstalleerd. `mews/purifier` of `ezyang/htmlpurifier` direct? Config: welke tags/attributes whitelisten voor `simple`?
4. **Slug-strategie** — Pages zijn de catch-all route `/{slug}` (masterplan §3.5). Slug bewerkbaar bij create, locked bij update (beslissing #10). Reserved slugs voorkomen (bv. `admin`, `login`, `bestemmingen`)?
5. **Publicatie-status** — `published_at` nullable: draft vs published. Toggle in form? Aparte "Publiceren"-actie?
6. **Form-layout** — hergebruik `<x-admin.form-layout>` two-column: titel + TipTap-body links, slug + publicatie-status + order rechts.

Begin met vraag 1 (huidige staat checken via PowerShell), daarna de ontwerpvragen één voor één via `ask_user_input_v0`.

Verwachting: 1.5-2 dagen werk (TipTap-integratie + HTMLPurifier zijn nieuw, dus extra debug-marge).
