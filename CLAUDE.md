# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 30 juni 2026 — Stap 4.11 (`/admin/media` browser) volledig afgerond, suite 422 groen, klaar voor push.
**Masterplan:** `westein-reisblog-masterplan.md` voor volledige architectuur, ERD, URL-structuur
**Bouwplannen:** Fase 2 → `fase-2-bouwplan.md`. Fase 4 → wordt na 4.14 vastgelegd in `fase-4-bouwplan.md`.

---

## Status

Fase 4 in uitvoering. Stap 4.11 (`/admin/media` browser) volledig afgerond in vier sub-blokken (4.11.a.1 + 4.11.a.2 + 4.11.b + 4.11.c) plus twee chores onderweg (`chore(test-infra)` en `refactor(scss)` voor x-cloak-verhuizing). Lokaal **6 commits ahead van `origin/main`** — push staat gepland aan einde van deze sessie. Testsuite **422 groen, deterministisch**.

## Volgende concrete actie — Stap 4.12: `/admin/prullenbak`

Stap 4.11 afgerond (zie commits 5cb106d t/m 7e3baff). Volgende module = Stap 4.12 prullenbak, volgens roadmap.

Voorbereiding op te focussen vóór ontwerp-vragen:
- Soft-delete-scope: F4-4 zegt soft-deletes op Posts, Destinations, Locations, Routes, Pages — vijf modules met eigen `restore`/`forceDelete`-flows.
- Auto-purge 30d: cron-config is Fase-6-territory; v1 prullenbak toont alleen handmatige acties.
- Filter-patroon: vermoedelijk model-type-dropdown (vergelijkbaar met owner_type uit 4.11) + datumrange.
- Permission: `trash.manage` (Admin + Editor?). Te bevestigen in F4-T1 als eerste design-vraag.

Beslissingen voor Stap 4.12 worden geprefixt `F4-T1, F4-T2, …` (T voor Trash).

---

### Twee loose ends uit Stap 4.11 die meegaan naar latere sessie

1. **Legacy `media.upload` / `media.delete` permissions in `RolePermissionSeeder`.** Niet gebruikt door enige policy (F4-9 zegt expliciet "geen losse media-permission — eigenaar-policy via `$media->model`"). Onschadelijk maar onnetjes. Mini-cleanup-commit waardig, bijvoorbeeld tijdens Stap 4.13 (Users + rollen beheer) wanneer de seeder toch grondig geraakt wordt.

2. **Sidebar-leakage project-breed.** `<x-admin.nav-link>` doet alleen `Route::has()`-existence-check, geen `@can`-permission-check. Auteur ziet links naar Familie, Pagina's, Media, Prullenbak, Gebruikers die naar 403 leiden. Geen 4.11-probleem — patroon zit door de hele sidebar. Fix: component extenden met optionele `:can`-prop, álle items in één pass retrofitten. Geschikt voor Stap 4.13.

**Stap 4.10 vordering (definitief):**
- [x] Blok a — datalaag + composer + model + factory + `template`-veld
- [x] Blok b — `NewsletterPolicy` + 4 Form Requests
- [x] Blok c — routes + `NewsletterController::index` + index-view
- [x] Blok d — CRUD + compose-form + `RegistersMediaConversions` flip naar `->nonQueued()` + `tiptap-simple` Alpine.raw-fix
- [x] Blok e — `NewsletterMail` + 3 templates + base-layout + `InlineCss`-service (Emogrifier) + `sendTest()` + testmail-knop + 11 tests
- [x] Blok f — `DispatchNewsletterAction` + `SendNewsletterJob` + `FinaliseNewsletterDispatchAction` + `dispatchSend()` + Bootstrap-modal-confirm + 28 tests (5+5+9+6+3 over zes sub-commits f.1 t/m f.5c)
- [x] Blok g — Show-pagina met KPI-dashboard + paginated `newsletter_sends`-tabel met filter/sort + index-link + 13 tests
- [x] Blok h — CLAUDE.md final update + `git push origin main`

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

## Herbruikbare admin-componenten

Opgebouwd tijdens Fase 4 — hergebruiken in volgende modules:

- **`<x-admin.field>`** — label + input/textarea/number, error-mapping, hint, readonly. Basis-veld. Project gebruikt straight Bootstrap, `.admin-field` is de uitzondering.
- **`<x-admin.form-layout>`** — two-column form-wrapper (slots: `main`, `side`, `actions`). Form-tag zit IN de component; views geven slots + `enctype` mee.
- **`<x-admin.form-section>`** — subtiele groepering binnen een kolom (uppercase mini-header + body).
- **`<x-admin.image-upload>`** — drag-and-drop upload, generiek (`remove_{name}`-checkbox-naming). Props: `name`, `shape`, `current-url`, `max-mb`, `min-width`, `min-height`.
- **`<x-admin.gallery-upload>`** — multi-image galerij met AJAX upload/reorder/delete. Hoort op EDIT-pagina.
- **`<x-admin.tiptap-editor>`** — simple-profiel met toolbar. Initial content uit hidden field (`this.$refs.hidden.value`), niet via x-data-argument.
- **`<x-admin.image-picker-modal>`** — twee-tabs (browse + upload) voor TipTap rich. Coördinatie via `Alpine.store('imagePicker')`. Upload-tab disabled op create-view.
- **`<x-admin.delete-button>`** — inline delete-confirm voor tabelrijen. Geen `:confirm`-prop — confirm zit ingebakken via `x-data="{ confirming: false }"`.
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
- **Multi-paragraph `git commit -m` met meerdere `-m`-flags faalt bij lege `-m ""` ertussen.** PowerShell + backtick-continuation laat git de volgende string als pathspec interpreteren. Patroon: schrijf de message naar `.git\COMMIT_EDITMSG_TEMP.txt` (BOM-vrij, zie hierboven) en commit met `git commit -F path` — meteen idiomatisch én leesbaar.
- **OPcache + Blade view-cache op Windows:** `Remove-Item storage\framework\views\*.php -Force` soms nodig wanneer `view:clear` alleen niet werkt.
- **`@php($x = ...)` Blade shorthand compileert stuk** naar `<?php($x = ...)` zonder spatie — ongeldige PHP, geeft misleidende error op regel 1. Diagnose: `php -l storage\framework\views\*.php`. Altijd blok-vorm voor assignments: `@php $x = ...; @endphp`.

### Spatie + framework-defaults
- **`storage/media-library/`** hoort in `.gitignore`. Spatie schrijft tijdelijke conversion-kopieën onder random hash-paden; bij crashes of `->queued()` zonder running worker blijven die liggen.
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
- **`<x-admin.nav-link>` doet géén `@can`-check.** Alleen `Route::has()`. Sidebar toont items waarvoor rollen geen toegang hebben → klik → 403. Geen 4.11-probleem (patroon project-breed), wel relevant bij Stap 4.13 (Users + rollen beheer) wanneer de component geretrofit moet worden met een optionele `:can`-prop.

### Leaflet (Vite)
- **Marker-iconen** vereisen `delete L.Icon.Default.prototype._getIconUrl` + `L.Icon.Default.mergeOptions({...})` met PNG's via Vite-imports.
- **Modal-init** vereist `shown.bs.modal`-listener vóór `L.map()` (anders dood canvas), `hidden.bs.modal` voor cleanup via `.remove()`.

### SortableJS
- **Revert DOM, re-render uit model.** SortableJS muteert DOM direct; Alpine ziet dat als out-of-band. Patroon in `onEnd`: eerst item op `event.oldIndex` terugplaatsen, DAN Alpine-array splice'n, force-notify met `this.array = [...this.array]`.

### Observaties (te volgen, niet acuut)
- **`config('app.faker_locale')` = `en_US`** ondanks NL project. Geen impact op productie, wel relevant als ooit besloten wordt naar `nl_NL` te switchen voor realistischer fixture-data — kanonnenladingen tests die nu toevallig groen draaien zouden deterministisch moeten worden gemaakt (Faker-collision-risico per testfile, zie leerpunt Faker-PRNG).

---

## Roadmap — fase-status

- ✅ **Fase 1 — Project setup & design system** _(afgerond 2 mei 2026)_
- ✅ **Fase 2 — Authenticatie & autorisatie** _(afgerond 10 mei 2026)_
- ✅ **Fase 3 — Database & content modellen** _(afgerond 13 mei 2026)_
- 🔄 **Fase 4 — Afgeschermd Admin-gedeelte** _(in uitvoering)_
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

### Fase 4 — overzicht

| Stap      | Inhoud                                                                              | Tests | Status |
| --------- | ----------------------------------------------------------------------------------- | ----- | ------ |
| **4.0**   | Fundament: soft deletes, users opruimen + `deactivated_at`, Post `inline_images`    |       | ✅     |
| **4.1**   | Admin-layout: inklapbare sidebar, gegroepeerde nav, topbar, flash + form-componenten |       | ✅     |
| **4.2**   | Dashboard met 6 KPI-cards + activity feed. Rename `Post::user()` → `Post::author()` |       | ✅     |
| **4.3.1** | Categories CRUD                                                                     |       | ✅     |
| **4.3.2** | Tags CRUD (morphedByMany op Posts)                                                  |       | ✅     |
| **4.3.3** | FamilyMembers CRUD — eerste cards-layout + eerste media-upload                      | 15    | ✅     |
| **4.3.4** | Pages CRUD — eerste TipTap simple + HTMLPurifier                                    | 18    | ✅     |
| **4.4**   | Destinations + Locations CRUD + generieke gallery-component                         | 42    | ✅     |
| **4.5**   | Posts CRUD + TipTap rich + own/any-policy + abstract `PostRequest`                  | 33    | ✅     |
| **4.6**   | TipTap image-picker modal (browse + upload, alignment-classes)                      | 25    | ✅     |
| **4.7**   | Comment-moderatie (state-machine, verb-routes, avatar-refactor)                     | 16    | ✅     |
| **4.8**   | Routes + Waypoints CRUD (SortableJS, Leaflet, SVG-thumbnail)                        | 26    | ✅     |
| **4.9**   | Subscribers + import/export (CSV, double-opt-in, error-CSV)                         | 37    | ✅     |
| **4.10**  | Newsletter compose & dispatch (a-h, alle blokken ✅)                                | 88    | ✅     |
| **4.11**  | `/admin/media` browser                                                              |       | ✅     |
| **4.12**  | `/admin/prullenbak` + auto-purge 30d                                                |       | ⏳     |
| **4.13**  | Users + rollen beheer + bulk-acties                                                 |       | ⏳     |
| **4.14**  | Eindcheck (Pint, Pest, fase-4-bouwplan.md, commit + push)                           |       | ⏳     |

**Totaal suite-status:** 401 groen.
