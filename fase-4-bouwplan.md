# Fase 4 — Afgeschermd Admin-gedeelte

**Schoon bouwplan, herhaalbaar van scratch**
Versie: 1.0 — opgesteld na afronding op 11 juli 2026

> Dit document beschrijft hoe Fase 4 uiteindelijk gebouwd is, in 14 stappen (4.0
> t/m 4.13), zonder de zijwegen en debug-sessies onderweg. Per stap: doel,
> beslissingen (F4-x-refs), key infrastructuur, en test-coverage. Voor
> implementatie-details staat de code in de commits; voor conventies en
> landmines zie `CLAUDE.md`.
>
> **Voorwaarde:** Fase 3 is afgerond. Alle content-modellen (Post, Destination,
> Location, Route, Comment, Subscriber, Newsletter, Page, FamilyMember, Tag,
> Category) bestaan met factories en seeders. Suite draait groen op ~155 tests.
>
> **Design:** Voorstel B "Modern magazine" is gekozen in Fase 1. Achtergrond
> `#F8F6F2`, tekst `#14213D`, headings Playfair Display, body Inter.

---

## Inhoudsopgave

1. [Doelstelling Fase 4](#doelstelling-fase-4)
2. [Fase 4 op één pagina](#fase-4-op-één-pagina)
3. [Stap 4.0 — Fundament: soft deletes + user-cleanup + inline_images](#stap-40--fundament-soft-deletes--user-cleanup--inline_images)
4. [Stap 4.1 — Admin-layout: sidebar + topbar + flash + form-componenten](#stap-41--admin-layout-sidebar--topbar--flash--form-componenten)
5. [Stap 4.2 — Dashboard: 6 KPI-cards + activity feed](#stap-42--dashboard-6-kpi-cards--activity-feed)
6. [Stap 4.3.1 — Categories CRUD](#stap-431--categories-crud)
7. [Stap 4.3.2 — Tags CRUD (morphedByMany op Posts)](#stap-432--tags-crud-morphedbymany-op-posts)
8. [Stap 4.3.3 — FamilyMembers CRUD (eerste cards-layout + eerste media-upload)](#stap-433--familymembers-crud-eerste-cards-layout--eerste-media-upload)
9. [Stap 4.3.4 — Pages CRUD (eerste TipTap simple + HTMLPurifier)](#stap-434--pages-crud-eerste-tiptap-simple--htmlpurifier)
10. [Stap 4.4 — Destinations + Locations CRUD + generieke gallery-component](#stap-44--destinations--locations-crud--generieke-gallery-component)
11. [Stap 4.5 — Posts CRUD + TipTap rich + own/any-policy](#stap-45--posts-crud--tiptap-rich--ownany-policy)
12. [Stap 4.6 — TipTap image-picker modal (browse + upload, alignment-classes)](#stap-46--tiptap-image-picker-modal-browse--upload-alignment-classes)
13. [Stap 4.7 — Comment-moderatie (state-machine, verb-routes)](#stap-47--comment-moderatie-state-machine-verb-routes)
14. [Stap 4.8 — Routes + Waypoints CRUD (SortableJS, Leaflet)](#stap-48--routes--waypoints-crud-sortablejs-leaflet)
15. [Stap 4.9 — Subscribers + CSV import/export (double-opt-in, error-CSV)](#stap-49--subscribers--csv-importexport-double-opt-in-error-csv)
16. [Stap 4.10 — Newsletter compose & dispatch (a-h)](#stap-410--newsletter-compose--dispatch-a-h)
17. [Stap 4.11 — Media browser (`/admin/media`)](#stap-411--media-browser-adminmedia)
18. [Stap 4.12 — Prullenbak (`/admin/prullenbak`)](#stap-412--prullenbak-adminprullenbak)
19. [Stap 4.13 — Users + rollen beheer + bulk-acties](#stap-413--users--rollen-beheer--bulk-acties)
20. [Stap 4.14 — Eindcheck (deze oplevering)](#stap-414--eindcheck-deze-oplevering)
21. [Fase 4 — leerpunten voor volgende fase](#fase-4--leerpunten-voor-volgende-fase)

---

## Doelstelling Fase 4

Een volledig zelfvoorzienend admin-paneel bereikbaar via `/admin/*` waarin een
Admin, Editor of Auteur alle content, engagement en systeembeheer kan uitvoeren
zonder toegang tot code of database. Niets hard-coded in seeders. Alles
CRUD-baar via het paneel. Beveiligd via Fortify + Spatie Permission. Gecoverd
door 526 groene tests.

## Fase 4 op één pagina

| Categorie   | Modules                                                         |
| ----------- | --------------------------------------------------------------- |
| Content     | Posts, Destinations, Locations, Categories, Tags, Routes, Pages, FamilyMembers |
| Engagement  | Comments, Subscribers, Newsletters                              |
| Beheer      | Media browser, Prullenbak, Users + rollen                       |
| Fundament   | Soft deletes, admin-layout, dashboard, Alpine-stores (3 stuks), gedeelde components |

**Rollen (Spatie Permission):** Admin, Editor, Auteur, Lid.

**UI-stack (F4-1):** Blade + Bootstrap 5 + Alpine.js + Vite. Geen Livewire, geen Filament, geen Inertia.

**Lijst-patroon (F4-2):** Server-side via querystring, Laravel-paginate met `withQueryString()`, geen Alpine-fetch.

**Editor (F4-3):** TipTap v3 met twee profielen (`rich` voor Posts, `simple` voor Pages/Newsletter). Output HTML gesanitized via HTMLPurifier.

**Soft deletes (F4-4):** Posts, Destinations, Locations, Routes, Pages. Niet op Comments, Users, Subscribers, FamilyMembers, Newsletters.

---
## Stap 4.0 — Fundament: soft deletes + user-cleanup + inline_images

**Doel:** Fase 3's data-modellen voorbereiden op admin-CRUD. Soft-delete-kolommen toevoegen waar F4-4 dat voorschrijft. Users opruimen tot minimaal (name/email/password + Fase 2 profielvelden). Post krijgt een `inline_images` media-collectie voor de TipTap image-picker later.

**Beslissingen die hier landen:**
- F4-4 (soft deletes selectie)
- F4-8 (`deactivated_at` + `deactivation_reason` op users, geen hard-delete via UI)
- F3-7 uitbreiden — Post krijgt `inline_images` als multi-file collectie naast `featured`

**Key infrastructuur:**
- Migraties: `add_soft_deletes_to_content_tables` (posts, destinations, locations, routes, pages) + `update_users_for_phase_4` (deactivated_at, deactivation_reason)
- User-model uitgebreid: `HasMedia`, `InteractsWithMedia`, `RegistersMediaConversions`-trait, `HasAvatarFallback`-trait
- Post-model: `inline_images`-collectie geregistreerd (multi-file, JPEG/PNG/WebP)
- Alle soft-delete-modellen: `SoftDeletes`-trait, `deleted_at` in `$dates`/casts

**Sanity-check aan het einde:**
```powershell
php artisan migrate:status | Select-String 'soft_deletes|update_users'
php artisan test
```

Verwacht: beide migraties Ran, suite groen.

---

## Stap 4.1 — Admin-layout: sidebar + topbar + flash + form-componenten

**Doel:** Alle admin-pagina's krijgen één gedeelde layout met inklapbare sidebar, breadcrumb-topbar, user-menu rechtsboven, en flash-partial. Herbruikbare Blade-components voor form-velden.

**Beslissingen die hier landen:**
- F4-1 (UI-stack Blade + Bootstrap + Alpine, geen Livewire/Filament)
- F4-6 vooruitlopend (tabel vs cards patroon per module — nog niet toegepast, maar componenten zijn er)
- F4-7 (form-layout-component voor >4 velden)

**Key infrastructuur:**
- `resources/views/layouts/admin.blade.php` — root layout met `@stack('modals')` (F4-N16-vooruitloper) + `@stack('scripts')` + user-menu
- `resources/views/admin/_partials/flash.blade.php` — leest `session('success'/'error'/'info'/'warning')` als top-level strings
- Alpine-context op body: `collapsed` (sidebar-state uit localStorage), `mobileOpen` (mobile drawer), `toggleCollapse()`
- Blade-components: `<x-admin.page-header>`, `<x-admin.card>`, `<x-admin.field>`, `<x-admin.form-layout>`, `<x-admin.form-section>`, `<x-admin.sort-link>`, `<x-admin.delete-button>`, `<x-admin.card-actions-menu>`
- SCSS-partials: `_admin-shell.scss`, `_forms.scss`, `_page-header.scss` (later in fase verplaatst — zie F4-M-landmine `[x-cloak]` scope)

**Sidebar (versie 4.1):** hardcoded links, alleen `Route::has()`-check. Nog geen permission-check (loose end voor 4.13.a).

**Sanity-check:** `https://westein-reisblog.test/admin` → placeholder-dashboard rendert met sidebar + topbar. Klik op elke sidebar-link → 404 (de routes bestaan nog niet — dat is prima).

---

## Stap 4.2 — Dashboard: 6 KPI-cards + activity feed

**Doel:** Landing-page van `/admin` toont snel-scan-cijfers: totaal posts, gepubliceerd, drafts, comments te modereren, abonnees, laatste nieuwsbrief. Plus een compacte activity feed van de laatste 10 acties.

**Beslissingen die hier landen:**
- F3-8 (Post `author()`-relatie rename — hier gebeurt de daadwerkelijke controller/view-migratie; migratie zelf komt uit Fase 3 opgeleverd)

**Key infrastructuur:**
- `Admin\DashboardController` — enkele `__invoke()`-method, geen resource
- View `admin.dashboard`: KPI-grid + activity-feed component
- Alle KPI-queries eager-loaded via één service-object of inline query-count-methods
- Post `user()`-relatie hernoemd naar `author()` — inclusief `Post::factory()->for(...)->create()` in alle test-files aanpassen

**Landmine (F3-8-gerelateerd):** `Post::factory()->for($user, 'author')->create()` — expliciete relatie-naam vereist. Zonder de tweede arg: `Call to undefined method Post::user()`. Suite valt en masse op eerste factory-call. Grep `->for($user)` project-breed vóór commit.

**Sanity-check:** dashboard laadt met echte cijfers uit demo-seeder-data. Activity-feed toont recente items.

---

## Stap 4.3.1 — Categories CRUD

**Doel:** Eerste "echte" CRUD-module. Categorieën zijn simpel (naam + slug + description + order) en dienen als template voor volgende modules. Blogpost-classificatie ("Verslag", "Tips", "Eten", etc.).

**Beslissingen die hier landen:**
- F3-2 (Categorieën ↔ Posts many-to-many met `order`-veld)
- F4-5 (slug bewerkbaar bij create, read-only bij update — weglaten uit `UpdateRequest::rules()`)
- F4-6 (tabel-layout voor Categories)

**Key infrastructuur:**
- `Admin\CategoryController` (resource, `except(['show'])`)
- `App\Http\Requests\Admin\Categories\StoreCategoryRequest` + `UpdateCategoryRequest`
- `App\Policies\CategoryPolicy` — leunt op `content.manage`-permission
- View-tree: `admin.categories.index` (tabel) + `.create` + `.edit` (single-column form)

**Sanity-check:** admin kan categorie aanmaken/bewerken/verwijderen. Slug uniek. Bij edit is slug-veld disabled/afwezig.

---

## Stap 4.3.2 — Tags CRUD (morphedByMany op Posts)

**Doel:** Tags als losse taxonomie via polymorfe pivot (`taggables`-tabel uit Fase 3). Kunnen later ook op Locations of andere modellen.

**Beslissingen die hier landen:**
- F3-1 (tags lowercase via mutator, polymorfe pivot)

**Key infrastructuur:**
- `Admin\TagController` — identiek CRUD-patroon als Categories
- Tag-model: `name`-mutator `strtolower + trim`
- View `admin.tags.index` — tabel met tag-cloud-preview per rij
- `tagPills` Alpine-factory (nog niet in gebruik, komt in Posts stap 4.5)

**Sanity-check:** tags aanmaken/hernoemen/verwijderen. Bij delete: `taggable`-rijen cascaderen (dankzij FK).

---

## Stap 4.3.3 — FamilyMembers CRUD (eerste cards-layout + eerste media-upload)

**Doel:** Familieleden voor de "Over ons"-pagina. Naam, korte bio, `portrait`-foto (Spatie Media Library), volgorde. Eerste module met cards-layout en eerste met file-upload.

**Beslissingen die hier landen:**
- F4-6 (cards-layout voor FamilyMembers)
- F3-7 (Portrait = single-file collectie op FamilyMember)
- Introductie `<x-admin.avatar-initials>`-component + `HasAvatarFallback`-trait

**Key infrastructuur:**
- `Admin\FamilyMemberController` — resource met `family_member`-route-parameter (URL: `admin/familieleden`, F4-conventie: NL URL / EN route-naam)
- `<x-admin.image-upload>` — eerste versie: drag-drop, preview, `remove_{name}`-checkbox voor delete
- `App\Models\Concerns\HasAvatarFallback` — trait met `initials()` + `accentColor()` (deterministisch via `crc32(id) % palette`)
- `App\Models\Concerns\RegistersMediaConversions` — trait die `thumb`/`medium` WebP-conversies aanmaakt
- Cards-layout: 3-koloms grid, elke card met `<x-admin.card-actions-menu>` (⋮)

**Landmine:** Spatie Media Library `->queued()` in `RegistersMediaConversions` betekent dat conversies niet draaien zonder queue-worker. Voor dev: `->nonQueued()` (F4-N7). Officiële move naar `->nonQueued()` project-breed pas bij Stap 4.10d.

**Tests:** 15 (fixture-CRUD + media-upload-scenarios + policy-checks).

---

## Stap 4.3.4 — Pages CRUD (eerste TipTap simple + HTMLPurifier)

**Doel:** Statische pagina's (Over ons, Privacy, Contact). Title + slug + excerpt + body (TipTap simple) + publicatie-toggle + SEO-velden + `hero`-media (niet ontsloten in UI in v1).

**Beslissingen die hier landen:**
- F4-3 (TipTap simple-profiel: StarterKit met `heading.levels: [2,3,4]` + link)
- F4-11 (reserved slugs via `App\Rules\NotReservedSlug` — alleen in StoreRequest)
- Introductie HTMLPurifier via `mews/purifier` met named config `simple`

**Key infrastructuur:**
- `Admin\PageController` — resource met publish-toggle via `is_published` boolean + `published_at` timestamp normalisatie in Request's `publicationData()`-method
- `<x-admin.tiptap-editor>`-component — simple-profiel, toolbar met bold/italic/heading/link/lists
- `config/westein.php` — array `reserved_slugs` (bv. `admin`, `login`, `register`, `bestemmingen`, etc.)
- `App\Rules\NotReservedSlug` — validatie tegen die array
- `config/purifier.php` — named configs `simple` (Pages/Newsletter) en later `rich` (Posts)

**Landmine:** TipTap initial content moet gelezen worden uit `this.$refs.hidden.value`, niet via x-data-argument — content met apostrofs/quotes breekt de JS-string-interpolatie in het `x-data`-attribuut.

**Tests:** 18 (CRUD + publish-flow + slug-validation + TipTap sanitization roundtrip).

---

## Stap 4.4 — Destinations + Locations CRUD + generieke gallery-component

**Doel:** Bestemmingen (land/regio) met genesteld Locations (steden/plekken). Beide krijgen een fotogalerij (multi-file media-collectie). Eerste module met resource-nesting en met AJAX-gallery voor upload + reorder + delete zonder form-submit.

**Beslissingen die hier landen:**
- F3-3 (Location-slugs globaal uniek — afwijking van masterplan §3.3)
- F3-6 (FK-strategie: `nullOnDelete` op destination_id/location_id in posts)
- F3-7 (Destination `hero`+`gallery`, Location `gallery`)
- F4-6 (cards-layout voor beide)
- F4-9 (generieke media-endpoints met eigenaar-policy via `$media->model`, model-type-whitelist in `config('westein.gallery_models')`, route-volgorde: statische `media/reorder` vóór dynamische `media/{media}`)
- F4-10 (gallery-upload AJAX-flow, alleen op EDIT-pagina — `store→edit`-redirect zorgt dat model bestaat)

**Key infrastructuur:**
- `Admin\DestinationController` + `Admin\LocationController` — Location genest onder `bestemmingen.locaties` met `scoped(['location' => 'slug'])`
- URL-patroon: `admin/bestemmingen/{destination}/locaties/{location}` (NL slugs + EN route-namen)
- `Admin\MediaController` — 3 endpoints: `POST admin/media/upload`, `PATCH admin/media/reorder`, `DELETE admin/media/{media}`
- `<x-admin.gallery-upload>` component — multi-image gallery met SortableJS reorder + AJAX upload/delete
- `config('westein.gallery_models')` — whitelist van modellen die gallery-upload accepteren

**Landmine:** Geneste resource-routes met `scoped(['child' => 'slug'])` valideren parent↔child automatisch. Kruisverwijzing (Location van andere Destination openen) → 404. Test met `assertNotFound()`.

**Tests:** 42 (CRUD op beide + gallery-flow + policy-mix + scoped-route-verificatie + slug-uniekheid).

---

## Stap 4.5 — Posts CRUD + TipTap rich + own/any-policy

**Doel:** Kern-module. Post krijgt title + slug + excerpt + body (TipTap **rich**) + featured image + inline-images-collectie + categories (many) + tags (polymorph) + destination/location + publicatie-model + SEO-velden. Complexer dan andere modules.

**Beslissingen die hier landen:**
- F3-4 (§3.4-validatie in Form Request via `withValidator()`, niet in model)
- F3-8 (Post `author()` — hier al gebruikt, rename gebeurde in 4.2)
- F4-3 (TipTap rich-profiel met alle extensions incl. table, image, youtube, code-block)
- F4-7 (two-column form-layout via `<x-admin.form-layout>` — Posts is >4 velden dus krijgt de sidebar)
- F4-13 (inline-images landen in post-eigen `inline_images`-collectie, geen centrale media-pool in v1)
- Introductie abstract `PostRequest` base-class voor Store + Update

**Key infrastructuur:**
- `Admin\PostController` — resource met heavy update-logic
- Abstract `PostRequest` + concrete `StorePostRequest` / `UpdatePostRequest` extenden 'm (F4-5-conventie: slug alleen in Store's `rules()`)
- `App\Policies\PostPolicy` — own/any-splitsing: `posts.update.own`+`posts.update.any`, `posts.delete.own`+`posts.delete.any`. Auteur mag eigen posts editeren + verwijderen.
- `tagPills` Alpine-factory — hidden komma-string als serialisatie
- `<x-admin.tiptap-editor>` gebruikt hier het **rich**-profiel

**Landmine:** TipTap v3 StarterKit levert Link + Underline standaard. Importeren als losse extensions geeft duplicate-extension-warning. Tabel-extensies gebruiken named exports, niet default — één default-import breekt hele admin.js voor Alpine-registraties (symptoom: alle Alpine-componenten lijken dood, check browser-console eerst).

**Landmine:** `Alpine.raw(this.editor)` voor álle TipTap-aanroepen, niet alleen state-syncs. ProseMirror identity-checks falen op Alpine's reactivity-Proxy met `RangeError: Applying a mismatched transaction`. Centraliseer via `chain()`-helper op de factory.

**Tests:** 33 (CRUD + own/any-policy-mix + tag-pill-serialisatie + section-4-validatie + featured/inline-image-flow).

---

## Stap 4.6 — TipTap image-picker modal (browse + upload, alignment-classes)

**Doel:** TipTap rich-editor krijgt image-picker (in plaats van rauwe URL-invoer). Twee tabs: **Browse** (projectbreed door bestaande media) + **Upload** (nieuwe naar post's `inline_images`-collectie). Plus alignment-toggles (left/center/right/full) via CSS-classes.

**Beslissingen die hier landen:**
- F4-12 (browse-scope projectbreed maar gefilterd op content-collecties: `gallery`, `hero`, `featured`, `inline_images` — avatars/portraits expliciet uitgesloten)
- F4-14 (alignment via class `img-align-{left|center|right|full}`, geen inline style; Purifier `URI.AllowedSchemes = http|https|mailto`)

**Key infrastructuur:**
- `Admin\MediaPickerController` — JSON-endpoint met server-side filter (collectie + zoek) + paginate
- `<x-admin.image-picker-modal>` — twee-tabs component, gebruikt `Alpine.store('imagePicker')` voor coördinatie tussen modal + editor (upload-tab disabled op create-view — geen model-id nog)
- TipTap Extension `.extend({ addAttributes() })` voor `class`-attribuut op images
- `config/purifier.php` `Attr.AllowedClasses` uitgebreid met `img-align-*`-classes

**Landmine:** Purifier `Attr.AllowedClasses` werkt globaal, niet per-element. Bij toevoegen van `table[class]` aan `HTML.Allowed` óók de bestaande `tiptap-table`-class aan de whitelist toevoegen. Één whitelist voor het hele document.

**Landmine:** Wrap externe TipTap-aanroepen in `try/finally` aan de aanroep-kant — anders blijft de image-picker modal in half-open state hangen bij een onverwerkte error.

**Tests:** 25 (picker-endpoint + upload-flow + browse-filter + alignment-round-trip + Purifier-sanitization).

---

## Stap 4.7 — Comment-moderatie (state-machine, verb-routes)

**Doel:** Reacties van ingelogde lezers modereren. State-machine `pending → approved → rejected/spam`. Contextuele knoppen per rij afhankelijk van huidige status. Verb-routes ipv één PATCH met status-veld.

**Beslissingen die hier landen:**
- F4-6 (tabel-layout)
- Introductie van conventie #17 (state-machine modules gebruiken verb-routes, geen PATCH met status)
- Avatar-refactor: `<x-admin.avatar-initials>`-component uit blok 4.3.3 werkt óók op Comment (via `HasAvatarFallback` op User)

**Key infrastructuur:**
- `Admin\CommentController` — 5 endpoints: `index`, `approve`, `reject`, `spam`, `destroy`. Geen `edit`/`update` (comments worden niet gewijzigd, alleen gemodereerd).
- Verb-routes: `PATCH admin/reacties/{comment}/goedkeuren`, `.../afkeuren`, `.../spam` + `DELETE admin/reacties/{comment}`
- `<x-admin.comment-actions>` — contextuele knoppen per status
- `<x-admin.comment-status-badge>` — Bootstrap-badge per status
- CommentPolicy — leunt op `comments.moderate`-permission

**Sanity-check:** admin klikt "Goedkeuren" op een pending comment → status wijzigt → knop verdwijnt uit action-set, "Afkeuren"/"Spam" blijven staan.

**Tests:** 16 (state-transitions + permission-mix + policy-checks + destroy-flow).

---

## Stap 4.8 — Routes + Waypoints CRUD (SortableJS, Leaflet)

**Doel:** Reisroutes als geordende lijst van waypoints (Locations of ad-hoc coordinaten). SortableJS voor drag-reorder. Leaflet voor kaart-preview + polylijn tussen waypoints. SVG-thumbnail voor route-cards zonder Leaflet-tiles.

**Beslissingen die hier landen:**
- F4-15 (publicatie via `is_published` boolean + `published_at` timestamp; hero-fallback-keten `eigen hero → eerste-waypoint-gallery-foto → null`)
- F4-16 (waypoint-sync = delete-then-recreate, JSON in één hidden field als bron-van-waarheid, revisits toegestaan — Fase-3-unique-constraint `route_waypoints.unique(route_id, location_id)` gedropt in migratie)

**Key infrastructuur:**
- `Admin\RouteController` — resource op `admin/reisroutes`
- `routeWaypoints` Alpine-factory — SortableJS + JSON-serialisatie. `onEnd`-callback revert't DOM eerst, dan Alpine-array mutation, dan force-notify met `this.array = [...this.array]`.
- `<x-admin.route-thumb>` — inline-SVG polylijn uit waypoints (lat/lng-bounds → viewBox). Geen JS, geen tiles.
- Leaflet-init op edit-view via `shown.bs.modal`-listener (anders dood canvas op modal-open); `hidden.bs.modal` voor cleanup.
- Migratie `drop_unique_route_location_from_route_waypoints` — expliciet dropped omdat Eloquent al identiteit via PK afdwingt

**Landmine:** SortableJS muteert DOM direct — Alpine ziet dat als out-of-band. In `onEnd`: eerst item op `event.oldIndex` terugplaatsen, DAN Alpine-array splicen, force-notify. Zonder deze truc raakt Alpine's virtual state uit sync met de DOM.

**Landmine:** Leaflet marker-iconen breken in Vite-builds (path-issues). Fix: PNG-imports via Vite + `L.Icon.Default.mergeOptions({...})` + `delete L.Icon.Default.prototype._getIconUrl`.

**Tests:** 26 (CRUD + waypoint-persistence + drag-reorder-simulatie + hero-fallback-keten + revisit-scenarios).

---

## Stap 4.9 — Subscribers + CSV import/export (double-opt-in, error-CSV)

**Doel:** Nieuwsbrief-abonnees beheren. Status afgeleid uit timestamps (`pending|active|unsubscribed`). Bevestigingsmails via double-opt-in. CSV-import in bulk met per-rij foutrapport-CSV die admin kan downloaden.

**Beslissingen die hier landen:**
- F4-6 (tabel voor Subscribers)
- F4-17 (status afgeleid uit timestamps, geen kolom; double-opt-in altijd; CSV-import zonder auto-mail-dispatch; uitgeschreven users bij re-import silent gerespecteerd — AVG)
- F4-18 (geen `dns`-rule op email — te traag/flaky in tests en dev)
- F4-19 (foutrapport-CSV op `local`-disk onder `imports/subscriber-errors/{ulid}.csv`; flash-partial-uitbreiding met `flash_action_url` + `flash_action_label` voor download-knop)

**Key infrastructuur:**
- `Admin\SubscriberController` — resource + verb-routes (`sendConfirmation`, `sendBulkConfirmations`, `import`, `export`, `downloadErrorReport`)
- `App\Actions\Subscribers\SubscribeAction`, `ConfirmSubscriptionAction`, `UnsubscribeAction`, `SendConfirmationMailAction`, `ImportSubscribersAction`, `ExportSubscribersAction`
- `App\Mail\SubscriberConfirmationMail` (queued Mailable) + markdown-template
- League\Csv voor import/export met streaming (League\Csv is memory-efficient bij grote files)
- `<x-admin.subscriber-status-badge>` — driewaardig (pending/active/unsubscribed)
- Subscriber-scopes: `pending()`, `active()`, `unsubscribed()`

**Landmine:** `email:rfc,dns`-validatie faalt in test-environments (unreliable DNS). Gebruik alleen `email:rfc`.

**Landmine:** `Paginator::useBootstrapFive()` in `AppServiceProvider::boot()` — vergeten sinds Fase 1, viel pas op bij Subscribers omdat 't de eerste index >25 rijen was. Framework-defaults uit eerdere fasen falen stil tot een nieuwe module ze triggert.

**Tests:** 37 (subscribe-flow + CSV-import happy + import-with-errors + export + confirmation + unsubscribe + AVG-re-import).

---

## Stap 4.10 — Newsletter compose & dispatch (a-h)

**Doel:** Volledige newsletter-flow: samenstellen (subject + body TipTap-simple + optionele header-foto + template-keuze), testmail naar admin, en dispatch naar alle actieve subscribers via `Bus::batch()` met per-subscriber tracking. Meest complexe module van Fase 4 vanwege de queue-orchestratie.

**Beslissingen die hier landen:**
- F4-N1 (header-collectie single-file Media Library, geen inline-images in body)
- F4-N2 (templates hardcoded als Blade-files: `announcement`/`digest`/`plain`, kolom `template` op newsletters-tabel, default `plain`)
- F4-N3 (test-modus = "Stuur naar mezelf"-knop, geen vrij invulveld; subject `[TEST]`-prefix; geen `newsletter_sends`-row)
- F4-N4 (audit-trail = sent + per-subscriber timestamp; geen tracking-pixel AVG; geen bounce-tracking Hostnet-SMTP; `bounced_at`/`opened_at` blijven leeg in v1)
- F4-N5 (dispatch vereist modale confirmation met expliciete recipient-count + subject; onomkeerbaar in queue)
- F4-N6 (scheduling uitgesteld naar v2; kolom + status `scheduled` blijven in schema/factory)
- F4-N7 (Spatie Media Library conversies project-breed `->nonQueued()` vanaf 4.10d — geen permanent `queue:work` in dev)
- F4-N8 (`NewsletterMail` is niet `ShouldQueue` — Mailable = data, Job = transport; testmail sync; bulk-dispatch wikkelt Mailable in `SendNewsletterJob`)
- F4-N9 (Announcement-template geen apart CTA-veld; body bevat de link zelf)
- F4-N10 (Digest-template haalt op render-tijd meest recente gepubliceerde posts; count via `config('westein.newsletter.digest_post_count', 5)`)
- F4-N11 (Testmail unsubscribe-placeholder = realistische URL `/nieuwsbrief/uitschrijven/{64-nul-token}` — klikt naar 404 tot Fase 5)
- F4-N12 (Dispatch via `Bus::batch()` met `finally()`-callback voor status-flip)
- F4-N13 (`DispatchNewsletterAction` doet eager-create van `newsletter_sends`-rijen in dezelfde transactie; unique-constraint `(newsletter_id, subscriber_id)` blokkeert race-double-dispatch)
- F4-N14 (Status-flip `sending → sent` via `Bus::batch()->finally()`-closure; vuurt zowel bij success als partial-failure — "sent" = "alle delivery-pogingen afgerond")
- F4-N15 (`DispatchNewsletterAction` graceful bij zero actieve subscribers; `withValidator()` op DispatchRequest is enige guard)
- F4-N16 (Introductie `@stack('modals')` in layout als project-brede modal-conventie; modules pushen via `@push('modals')`)
- F4-N17 (Show-pagina status-dashboard: 4 KPI-cards + gepagineerde `newsletter_sends`-tabel met statusfilter + sort op `sent_at`/`failed_at`/`created_at`; KPI's via één `DB::table()`-query met `SUM(CASE WHEN...)` + `COALESCE` + `(int)`-cast)
- F4-N18 (Show werkt op alle drie statussen; `draft` toont info-alert, `sending`/`sent` toont KPI+tabel; geen redirect of 404)

**Sub-blok-opdeling (a t/m h):**
- **a** — Database + fundament (migraties `newsletters` + `newsletter_sends`, `template`-kolom, factory-states)
- **b** — Basic CRUD (index + create + edit + destroy zonder dispatch)
- **c** — Template-preview + rendering (Blade-templates in `emails/newsletter/templates/`)
- **d** — Media Library `->nonQueued()` project-breed (F4-N7) + header-upload-flow
- **e** — Testmail (send-to-self + `[TEST]`-prefix + Pelago Emogrifier voor CSS-inlining)
- **f** — Dispatch (Bus::batch + SendNewsletterJob + eager-create newsletter_sends + status-flip via finally)
- **g** — Show-pagina (KPI-dashboard + gepagineerde sends-tabel + status-filter)
- **h** — Dispatch-modal + confirmation-flow

**Key infrastructuur:**
- `Admin\NewsletterController` — resource + verb-routes (`sendTest`, `dispatchSend`)
- `App\Actions\Newsletter\DispatchNewsletterAction` + `FinaliseNewsletterDispatchAction` (F4-N14: closure-capture voor `->finally()`)
- `App\Jobs\SendNewsletterJob` — `implements ShouldQueue`, tries=3, backoff exponentieel
- `App\Mail\NewsletterMail` — Mailable met markdown, geen `ShouldQueue` (F4-N8)
- `resources/views/emails/newsletter/templates/{plain,announcement,digest}.blade.php`
- `<x-admin.tiptap-editor>` gebruikt hier simple-profiel

**Landmine:** `Bus::batch()->finally()` executeert niet onder `Bus::fake()`. Extract finalise-logica als aparte class (`FinaliseNewsletterDispatchAction`) zodat 'ie los testbaar is.

**Landmine:** `job_batches`-tabel zit al in Laravel 11+ default-migratie (`0001_01_01_000002_create_jobs_table.php`); `php artisan queue:batches-table` is een no-op — geen extra migratie nodig.

**Landmine:** `queue:work` onthulde 2 weken achterstallige Spatie image-conversion jobs bij eerste run. Sinds F4-N7 draaien conversies `->nonQueued()` — sync-conversie van ~70ms per WebP-resize is praktischer op familieblog-schaal dan queue-orchestratie voor dev.

**Landmine (test-hygiene):** Faker PRNG-state is process-wide en advance't bij elke `fake()`-call. 11 nieuwe newsletter-tests verschoven Faker zover dat `en_US`-locale consequent "Jansen" als surname genereerde, wat `SubscriberManagementTest::"zoekt op email"` brak — zonder wijziging in productiecode. Voor multi-column LIKE-searches: zet álle searchable kolommen expliciet in de fixture.

**Tests:** 88 (CRUD + template-rendering + testmail-flow + dispatch happy-path + graceful-zero-subscribers + batch-completion-callback via extracted action + Show-KPI-aggregatie + status-filter).

---

## Stap 4.11 — Media browser (`/admin/media`)

**Doel:** Projectbrede grid-browser voor alle geüploade media. Read-only view + per-item delete + bulk-selectie + bulk-delete. Geen upload-flow (dat blijft via eigenaar-modellen conform masterplan). Eerste module met Alpine.store + sticky action-bar patroon dat later door Trash (4.12) en Users (4.13) hergebruikt wordt.

**Beslissingen die hier landen:**
- F4-M1 (volledige v1: read-only browser + per-item delete + bulk-selectie + bulk-delete via confirm-modal)
- F4-M2 (RBAC: aparte permission `media.browse`, toegekend aan Admin via `Gate::before` + Editor; Auteur/Lid geen toegang. Getest met custom test-rol `media-browser-only`)
- F4-M3 (filters: collectie + eigenaar-modeltype + bestandsnaam-zoek + sort op `created_at`/`name`/`size`; owner-type-filter via `config('westein.browsable_media_owners')` — 5 modellen: destination, location, post, route, newsletter — bewust losgekoppeld van `gallery_models`)
- F4-M4 (layout = grid van thumbs 6/4/2/1 responsive kolommen, geen tabel; thumb is primair visueel signaal bij media-beheer)
- F4-M5 (per-item delete = inline-confirm-toggle in grid-specifieke overlay; niet via `<x-admin.delete-button>` want form-gebaseerd botst met AJAX-flow)
- F4-M6 (bulk-selectie = pagina-scoped; "Selecteer alle zichtbare"-control boven het grid; geen "selecteer alle X op filter"; `POST admin/media/bulk-delete` met `ids[]`-payload max 100; `DB::transaction` met harde rollback bij élke policy-fail)
- F4-M7 (implementatie-opdeling: 4.11.a foundation + browser, 4.11.b per-item delete, 4.11.c bulk-flow — conform 4.10's commit-discipline)
- F4-M8 (action-bar sticky-bottom, `z-index: 1030`, boven navbar, onder modal — Gmail-stijl)
- F4-M9 (action-bar minimaal: counter + Selectie wissen + Verwijderen; geen "X van M geselecteerd"-formulering, geen v2-placeholders)

**Sub-blok-opdeling (a t/m c):**
- **4.11.a** — Foundation: `MediaQueryBuilder` service + `MediaBrowserController@index` + grid-view + filters
- **4.11.b** — Per-item delete via `<x-admin.media-delete-overlay>`-component (inline confirm-toggle)
- **4.11.c** — Bulk-flow: `Alpine.store('mediaSelection')` + sticky action-bar + bulk-delete-modal + `MediaBrowserController@bulkDelete`

**Key infrastructuur:**
- `App\Services\Media\MediaQueryBuilder` — centrale query-laag gedeeld door `MediaPickerController` (4.6) en `MediaBrowserController` (4.11). Public consts `ALLOWED_COLLECTIONS` en `ALLOWED_SORT_COLUMNS`. Statische `contextLabel(Media $m)`-helper met cases voor alle vijf eigenaar-modellen (Route + Newsletter waren ontbrekend in de 4.6-versie, meegelift in de extractie)
- `Admin\MediaBrowserController` — naast bestaande `MediaController` (4.4 gallery-AJAX) en `MediaPickerController` (4.6 picker-JSON): drie controllers, drie verantwoordelijkheden
- `<x-admin.media-delete-overlay>` — grid-specifieke overlay met inline confirm-toggle (vuilnisbak → check/cross). Alpine `x-data` met AJAX-fetch naar `DELETE admin/media/{media}`. DOM-remove op success. Geen form-tag; positionering absolute op grid-cell.
- `Alpine.store('mediaSelection', ...)` — **eerste Alpine.store** in project (i.p.v. data-factory). Reden: `@push('modals')`-blok rendert op `</body>`-niveau, buiten elke component-scope; store is cross-scope bereikbaar via `$store`.
- `.media-action-bar` + `.media-action-bar__inner` — sticky-bottom CSS-classes hergebruikt door Trash (4.12) en Users (4.13)

**Landmine:** Alpine `x-show` + Bootstrap display-utility (`d-flex`, `d-block`) op hetzelfde element = onzichtbaar conflict. Bootstrap's `display: X !important` overschrijft Alpine's inline `style.display = 'none'`. Fix: wrap in extra `<div>` met `x-show`-directive; zet Bootstrap-utility op het kind. Geldt niet voor `visibility`/`opacity`-utilities.

**Landmine:** `[x-cloak] { display: none !important; }` moet globaal staan, niet form-scoped. Tot 4.11.b was deze regel scoped onder `_forms.scss` (matched alleen binnen `<form>`); componenten daarbuiten (sidebar-dropdown, image-picker, gallery-upload, media-overlays) matchten niet. Verplaats naar `_layout.scss`.

**Landmine:** `@push('modals')`-blokken vereisen een `x-data`-marker op de modal-root. Modals via `@push('modals')` renderen op `</body>`-niveau, buiten elke component-scope. Alpine processed die subtree niet automatisch — `@click`-attributen en andere directives binden niet. Fix: leeg `x-data` op modal-root-`<div>`. `$store`-toegang blijft werken. Symptoom: knop reageert nergens op, geen JS-error, attribuut zit in DOM.

**Tests:** ~35 (grid-render + filter-scenarios + per-item-delete + bulk-selectie + bulk-delete + RBAC-mix + AJAX-response-shapes).

---

## Stap 4.12 — Prullenbak (`/admin/prullenbak`)

**Doel:** Verzamelbak voor alle soft-deleted content (Posts, Destinations, Locations, Routes, Pages). Cross-model unified index met type-filter. Per-item restore of definitief verwijderen. Bulk-restore. Cascade-logica omhoog (restore) en blokkade beneden (force-delete). Handmatig; auto-purge komt in Fase 6.

**Beslissingen die hier landen:**
- F4-T1 (RBAC: nieuwe permission `trash.manage` — Admin + Editor; Auteur/Lid geen toegang. Per-model policies blijven voor daadwerkelijke restore/force-actie; `trash.manage` gate't alleen de browser)
- F4-T2 (unified single index met model-type-filter; één `TrashController@index`, één view, heterogene rijen genormaliseerd naar (titel + type-badge + context-subline + verwijderd-datum + acties); geen tabs, geen sub-pagina's)
- F4-T3 (audit-trail: **geen** `deleted_by`-tracking, alleen `deleted_at` — YAGNI voor familieblog-schaal)
- F4-T4 (force-delete cascade: **blokkeren** zolang zelfstandige-content-children bestaan levend óf soft-deleted; raakt in v1 alleen Destination → Locations; pivot-children en media-children tellen niet mee; communicatie via pre-computed `blocked_reason` in DTO + `<x-admin.delete-button>` pre-disabled met Bootstrap-tooltip)
- F4-T5 (restore-cascade: **omhoog** door de keten Post → Location → Destination in één transactie; expliciete flash-melding noemt alle mee-hersteld items; asymmetrisch met T4 en bewust: destructie krijgt wrijving als veiligheid, herstel krijgt smoothness als admin-intentie)
- F4-T6 (bulk-acties: **alleen bulk-restore**, geen bulk-force-delete; bulk-restore is safe want T5 werkt lineair per item; bulk-force-delete voegt destructief risico toe met beperkte waarde)
- F4-T7 (per-item UX: beide inline; herstel = simpele form-POST-knop; definitief = `<x-admin.delete-button>` met pre-disabled bij children > 0; geen modal per rij)
- F4-T8 (filters minimaal: alleen type-filter, sort fixed op `deleted_at desc`; geen tekst-zoek, datum-range of sort-toggle)
- F4-T9 (sessie-scope: seeder-cleanup meepakken (legacy `media.upload`/`media.delete` verwijderd) + ad-hoc `@can('trash.manage')` rond nieuwe Prullenbak-link; volledige nav-link retrofit uitgesteld naar 4.13; auto-purge = Fase 6 cron)

**Sub-blok-opdeling:**
- **chore(sidebar-extractie)** — sidebar-markup verhuisd uit `layouts/admin.blade.php` naar `resources/views/admin/_partials/sidebar.blade.php`
- **4.12.a.2** — RBAC (permission + policy-stub + tests-matrix)
- **chore(seeder)** — legacy `media.*`-permissions verwijderd
- **4.12.a.3** — Data-laag: `TrashBrowser`-service
- **4.12.b.1** — Per-item restore met ancestor-cascade
- **4.12.b.2** — Definitief verwijderen met T4-blokkade
- **4.12.c** — Bulk-restore met heterogene selectie + ancestor-cascade + composite-key store

**Key infrastructuur:**
- `App\Services\Trash\TrashBrowser` — per-model `onlyTrashed()`-queries mergen tot Collection, sort op `deleted_at` desc, paginate via `LengthAwarePaginator`. Public `const TYPES` als whitelist voor type-filter. Heterogene DTOs (stdClass) met `type`, `type_label`, `title`, `context`, `deleted_at`, `blocked_reason`. Pre-computed children-count voor Destination via `withCount + withTrashed()`-closure.
- `App\Actions\Trash\RestoreTrashItemAction` — `match($type)` → `onlyTrashed()->find()`, `collectAncestors()` traverseert Post → Location → Destination met dedup (dubbele Destination-paden via Post FK én Location). Wrap in `DB::transaction`. `RestoreResult` DTO met ancestors-first array + `flashMessage()`.
- `App\Actions\Trash\ForceDeleteTrashItemAction` — `blockingReason()` gate't. Throw `RuntimeException` met exact zelfde tekst als tooltip; controller converteert naar error-flash.
- `App\Actions\Trash\BulkRestoreTrashItemsAction` — leunt op single-action, silent-skip op `ModelNotFoundException` (bijv. race met force-delete), ancestor-dedup op `type:title`-key over de hele batch. `BulkRestoreResult` DTO met tri-count (primary/ancestor/failed).
- `App\Http\Requests\Admin\Trash\BulkRestoreRequest` — max:100 cap, `Rule::in(...)` op type, `prepareForValidation()` decode't JSON-string payload
- `Alpine.store('trashSelection', ...)` — **tweede Alpine-store** in project, parallel aan mediaSelection maar met **composite keys** `"{type}:{id}"` omdat trash-IDs niet globally uniek zijn (Post.1 ≠ Destination.1). `destroy()` serialiseert selection naar hidden form-input + submit.
- `<x-admin.delete-button>` uitgebreid met optionele `:disabled` + `:disabled-reason` props. Disabled-branch wrapt de knop in `<span data-bs-toggle="tooltip">` met `pointer-events: none` op inner button — vereist omdat Bootstrap-tooltips niet direct op disabled elementen werken.

**Landmine:** `<x-admin.delete-button>` heeft géén `:confirm`-prop. Confirm zit ingebakken via `x-data="{ confirming: false }"`. Check component-bron voor exacte prop-signatuur bij gebruik.

**Landmine:** Blade `@@extends`-mojibake compileert tot letterlijke `@extends`-output. Als je bij plakken per ongeluk dubbele `@` op regel 1 krijgt (VS Code Blade-autocomplete + shift-select-fouten), rendert de hele view als plaintext — geen layout, geen extends, source-code als HTTP-response. Diagnose: `Format-Hex path\view.blade.php | Select-Object -First 2` — eerste bytes moeten `40 65` zijn (@e), niet `40 40 65` (@@e). Kan óók gebeuren in `@section`, `@include`.

**Tests:** 43 (`TrashManagementTest.php`) — cross-model index + type-filter + restore-cascade-scenarios + force-delete-blokkade + bulk-restore mixed selection + RBAC-mix (4 rollen × endpoint).

---

## Stap 4.13 — Users + rollen beheer + bulk-acties

**Doel:** Volwaardig gebruikersbeheer voor admins. Index met filters, invite-mail-flow bij create, edit met rol-toewijzing en anti-lockout-guards, deactivate/reactivate + Fortify login-block, admin-triggered wachtwoord-reset, 2FA-force-disable, en bulk-flow met sticky action-bar. Alle drie de openstaande loose ends van vóór 4.13 opgelost (sidebar `@can`-retrofit, dode Locaties-link, legacy media-permissions al opgeruimd in 4.12).

**Beslissingen die hier landen:**
- F4-U1 (volledige scope: index + create + edit + rollen + deactivate/reactivate + admin wachtwoord-reset + 2FA-status + 2FA-force-disable)
- F4-U2 (beide guards: geen zelf-lock op admin-rol + geen laatste-admin-verlies via `withValidator()`; bulk-spiegel in `BulkDeactivateUsersRequest`)
- F4-U3 (create via invite-mail met Fortify password-reset-link — `Password::createToken()` + custom Mailable, niet Fortify's default `sendResetLink`)
- F4-U4 (`email_verified_at` automatisch op `now()` bij succesvolle password-reset via listener op `Illuminate\Auth\Events\PasswordReset`; idempotente no-op voor al-geverifieerde users)
- F4-U5 (gedeactiveerde users: hard block via Fortify `authenticateUsing()` met generic error, geen info-leak)
- F4-U6 (bestaande content blijft zichtbaar met auteur-naam; gedeactiveerde users blijven in admin met badge)
- F4-U7 (tabel-index met avatar-thumb + badges — consistent met Subscribers/Comments)
- F4-U8 (filters: tekst-zoek naam+email + rol + status; sort naam/email/created_at, default `created_at desc`)
- F4-U9 (bulk: alleen deactivate + reactivate, geen bulk-rol-toewijzen)
- F4-U10 (last-admin-guard strikt: alleen actieve admins tellen; guard op zowel rol-verwijderen als deactivate)
- F4-U11 (alleen Admin via `users.manage`-permission; Editor/Auteur/Lid → 403)
- F4-U12 (tabel-kolommen minimaal: avatar+naam, email, rollen, status, acties; geen created_at-kolom, geen 2FA-badge)
- F4-U13 (sidebar volledig retrofit: `<x-admin.nav-link>` optionele `:can`-prop; alle items retrofit; dode `admin.locaties.index` gedropt; 4.12's ad-hoc `@can('trash.manage')`-wrap vervangen door prop)
- F4-U14 (zeven sub-blokken)
- F4-U15 (`Route::resource(...)` `->except(['show', 'destroy'])` — geen destroy-route en géén controller-method: defense-in-depth voor "geen hard-delete")
- F4-U16 (rollen-UI: create = multi-select checkboxes met `lid` default aangevinkt; edit = multi-select met huidige rollen)
- F4-U17 (custom `UserInvitationMail` queued Mailable met eigen "Welkom, activeer je account"-tekst)
- F4-U18 (edit-scope: name/email/roles; email-wijziging reset `email_verified_at` en dispatcht nieuwe invite-mail; `Rule::unique(...)->ignore($user->id)`)
- F4-U19 (beide guards afzonderlijk in `withValidator()` — bij overlap tonen beide foutmeldingen)
- F4-U20 (optioneel `deactivation_reason`-veld in confirm-modal; toont in edit-view banner + tooltip; bij reactivate beide velden op null — geen historie-tracking)
- F4-U21 (edit-view UI: aparte "Beheeracties"-sectie onderaan met wachtwoord-reset + 2FA-uitzetten conditioneel + deactiveren; sectie verborgen als user zichzelf bewerkt)
- F4-U22 (bulk-actie-UI: twee knoppen altijd zichtbaar; silent-skip op reeds-in-target-state via `whereNull`/`whereNotNull`; geen contextuele knoppen)

**Sub-blok-opdeling (a t/m g):**
- **4.13.a** — Foundation + sidebar-retrofit: `<x-admin.nav-link>` `:can`-prop, sidebar retrofit, dode Locaties-link drop, `UserController`/`UserPolicy` stubs, RBAC-testfile (6 tests)
- **4.13.b** — Index: filters + sort + paginate + tabel met avatar/badges (+7 tests)
- **4.13.c** — Create + invite-flow: `StoreUserRequest`, `UserInvitationMail`, `SendUserInvitationAction`, `MarkEmailVerifiedAfterPasswordReset`-listener (+9 tests)
- **4.13.d** — Edit + rollen + guards: `UpdateUserRequest` met `withValidator()`-guards, email-change reset (+14 tests)
- **4.13.e** — Deactivate/reactivate + Fortify login-block: verb-routes, `DeactivateUserRequest`, Fortify `authenticateUsing()` (+7 tests)
- **4.13.f** — Admin wachtwoord-reset + 2FA-force-disable: verb-routes, Beheeracties-sectie in edit-view (+5 tests)
- **4.13.g** — Bulk-flow: `Alpine.store('userSelection')`, sticky action-bar, twee Actions + twee Requests + twee modals (+13 tests)

**Key infrastructuur:**
- `App\Actions\Users\SendUserInvitationAction` — `Password::createToken($user)` (niet `Password::broker()->createToken()` — laatste heeft interface-return-type dat Intelephense niet begrijpt). Bouwt `route('password.reset', [...])` en dispatcht queued `UserInvitationMail`. Hergebruikt door drie code-paden: create-flow, email-change bij update, admin-triggered reset.
- `App\Actions\Users\BulkDeactivateUsersAction` + `BulkReactivateUsersAction` — silent-skip via `whereNull`/`whereNotNull('deactivated_at')`. `DB::transaction` wrapt de loop. Return `int $affected` voor flash-pluralisatie via `trans_choice`.
- `App\Mail\UserInvitationMail` — queued Mailable, constructor `User $user, string $activationUrl`. Envelope-subject via `config('app.name')`. Markdown-content in `emails.users.invitation`.
- `App\Listeners\MarkEmailVerifiedAfterPasswordReset` — geregistreerd in `AppServiceProvider::boot()` via `Event::listen(PasswordReset::class, ...)`. Guard-clause: al-geverifieerd → no-op. Anders `forceFill(['email_verified_at' => now()])->save()`.
- `App\Http\Requests\Admin\Users\` — vijf Requests: `StoreUserRequest`, `UpdateUserRequest`, `DeactivateUserRequest`, `BulkDeactivateUsersRequest`, `BulkReactivateUsersRequest`. Update + Deactivate + BulkDeactivate hebben `withValidator()` met `guardNoSelfXxx` + `guardNoLastAdminXxx`-methods.
- `Fortify::authenticateUsing()` in `FortifyServiceProvider::boot()` — handmatige lookup + `Hash::check()` + gedeactiveerd-check. Retourneert `null` bij elke fail. Bestaande rate limiters + view bindings ongewijzigd.
- `<x-admin.user-status-badge>` — twee visuele states (Actief groen-subtle / Gedeactiveerd grijs), prop `user`, tooltip met deactivatie-datum
- `Alpine.store('userSelection', ...)` — **derde Alpine-store** in project, plain integer-keys (User-IDs uniek). Twee destroy-methods: `destroyDeactivate()` + `destroyReactivate()`, elk submit't naar een eigen hidden form via interne `_submitForm(formId)`-helper.
- User-model: nieuwe scopes `active()` + `deactivated()` (Builder-typed conform project-conventie)

**Landmine:** `Password::createToken($user)` gebruiken, niet `Password::broker()->createToken($user)`. Beide werken runtime; Intelephense rood-onderstreept alleen de tweede want `Password::broker()` returnt een interface zonder `createToken()`-method. `Password::createToken()` delegeert intern naar default broker.

**Landmine:** `$event->user->forceFill(...)` op `PasswordReset`-event triggert Intelephense-warning. Event heeft type-hint `Illuminate\Contracts\Auth\CanResetPassword` (interface). Fix: `/** @var \App\Models\User $user */`-annotation vóór assignment.

**Landmine:** `$request->string(...)` in controllers returned `Illuminate\Support\Stringable`, niet `string`. Voor cases waar de waarde later door een Mailable-envelope wordt geparst (`Envelope(to: [$this->user->email])`), leidt dat tot `Method Stringable::address does not exist`. Gebruik `$request->validated()` in plaats van `$request->string()` — de validated data is netjes typed.

**Landmine:** `Mail::assertSent(UserInvitationMail::class)` faalt op queued Mailables. Fortify + `implements ShouldQueue` betekent dat de mail in de queue belandt, niet direct verzonden. Gebruik `Mail::assertQueued()` voor queued Mailables. `Mail::assertNothingOutgoing()` dekt zowel sent als queued.

**Landmine:** F4-U10 pure testen (zonder F4-U2-overlap) is niet realistisch in familieblog-context. Reden: "laatste actieve admin deactiveren" vereist een niet-admin met `users.manage` — bestaat niet in productie-rollen. In tests dus altijd combinatie-scenarios; `assertSessionHasErrors('roles')` of `('reason')` dekt beide triggers.

**Tests:** 61 (`UserManagementTest.php`) — 6 RBAC-checks + 7 index (filter/sort/paginate/whitelist) + 9 create+invite+listener + 14 edit+guards+email-change + 7 deactivate+Fortify-block + 5 wachtwoord-reset+2FA + 13 bulk.

---

## Stap 4.14 — Eindcheck (deze oplevering)

**Doel:** Fase 4 formeel afsluiten. CLAUDE.md updaten met alle sessie-outcomes uit Stap 4.13. Dit bouwplan schrijven. Feature-branch mergen naar `main` en pushen naar `origin`. Feature-branch opruimen.

**Uitvoering:**
1. `CLAUDE.md` bijgewerkt: 22 nieuwe beslissingen (F4-U1 t/m F4-U22), gedeelde-infrastructuur-blok 4.13, nieuwe herbruikbare componenten (`<x-admin.user-status-badge>` + `Alpine.store('userSelection')`), zes nieuwe landmines, nav-link-landmine gecorrigeerd (was open loose end), roadmap-tabel op ✅, `ExampleTest.php`-observatie toegevoegd
2. `fase-4-bouwplan.md` opgeleverd (dit document)
3. Pint over 4.14-wijzigingen
4. Eén commit: `Stap 4.14: CLAUDE.md update + fase-4-bouwplan.md`
5. `git checkout main && git merge stap-4.13-users-rollen && git push origin main`
6. Lokale + remote feature-branch verwijderen

**Definition of Done Fase 4:**
- [x] Alle 14 stappen (4.0 t/m 4.13) opgeleverd
- [x] 22 F4-U-beslissingen chronologisch vastgelegd in CLAUDE.md
- [x] Suite 526 groen (van 155 baseline aan begin Fase 4)
- [x] Alle drie de openstaande loose ends van vóór 4.13 opgelost
- [x] `fase-4-bouwplan.md` opgeleverd
- [x] Merge naar `main` + push naar `origin`

---

## Fase 4 — leerpunten voor volgende fase

Dit is de destillaat van 14 stappen aan hard-won patronen die volgende fases moeten meenemen. Zie CLAUDE.md voor de volledige landmine-catalogus.

### Werkstijl-patronen die zichzelf hebben bewezen

1. **Sub-blok-opdeling per module.** Vanaf stap 4.10 hebben we grote modules opgedeeld in genummerde sub-blokken (a/b/c/... of a.1/a.2/...) met elk hun eigen commit. Voordelen: (a) elk sub-blok apart testbaar, (b) fine-grained git-blame achteraf, (c) mentale focus tijdens sessie op één afgebakend brok.
2. **State-check als allereerste stap van elke sessie.** `git log --oneline`, `git status`, `php artisan test`, plus module-specifieke checks. Niet ontwerp-vragen als eerste. Dit heeft meerdere keren voorkomen dat we op verkeerde aannames verder bouwden.
3. **Design-vragen één voor één via `ask_user_input_v0`.** Beslissingen sequentieel prefixen met module-letter (F4-U1, F4-U2, ...). Vastleggen in CLAUDE.md aan het einde van de sessie.
4. **Componenten hergebruiken vóór verzinnen.** Grep bestaande views/componenten vóór nieuwe optuigen. `<x-admin.card>`, `<x-admin.page-header>`, `<x-admin.delete-button>` — al deze bleken uit eerste modules bruikbaar in tienen anderen.
5. **Tests draaien vóór en na élke sub-blok.** Vroege detectie van regressies. De landmine over Faker PRNG-state (blok 4.10e) toont dat oude tests kunnen breken zonder productiecode-wijziging.

### Architectuur-patronen die zichzelf hebben bewezen

1. **Server-side patroon voor alle CRUD-modules** (F4-2): querystring-gestuurde filters, Laravel-paginate met `withQueryString()`, `<x-admin.sort-link>` voor headers. Zelfde patroon 8 keer, altijd gewerkt.
2. **Verb-routes voor state-machines** (conventie #17): Comments, Subscribers, Newsletters, Users deactivate/reactivate. Route-naam expliciet, status niet client-tamperbaar, log-leesbaar.
3. **Action-classes voor complexe business-logic**: Newsletter dispatch, Subscriber import/export, Trash restore/force-delete, User invite. Controller blijft dun, Action is testbaar in isolatie.
4. **Alpine.store voor cross-scope state** (F4-M-introductie): drie stores in Fase 4 (media/trash/user). Vooral cruciaal voor `@push('modals')`-blokken die op body-niveau renderen en componenten dus niet kunnen aanhaken.
5. **Guard-check via `withValidator()`** in Form Requests: Post's `§3.4`-validatie (F3-4), Newsletter's zero-subscribers-guard (F4-N15), User's beide anti-lockout-guards (F4-U2/U10). Cross-field logica hoort in Form Request, niet in controller of model.

### Landmines die drie keer voorkwamen

1. **`assignRole()` returnt geen User** — vier keer over gestruikeld tot 't in CLAUDE.md landde. Splits altijd over twee regels.
2. **Blade-view kapot na copy-paste** (streaming-race, `@@extends`-mojibake, half-plakken van layout in view-bestand). Diagnose: `Get-Content path | Select-Object -Last 5` én `-First 5`. Preventie: "einde-marker" bij grote codeblokken.
3. **PowerShell + UTF-8 + BOM** — geraakt bij lang-files (fataal), commit-messages (cosmetisch), en verifieer-scripts (verwarrend). Standaard: `[System.IO.File]::WriteAllText(..., UTF8Encoding(false))`.
4. **Intelephense false-positives** op runtime-correcte code met interface-type-hints (`Password::broker()->createToken()`, `$event->user->forceFill()`). Fix: expliciete facade-methode óf `@var`-annotatie.

### Blijvende scope-vragen voor Fase 5

- Publieke pagina's (Home, Bestemmingen, Locations, Posts, Routes, Reistips, Fotogalerij, Over ons) — masterplan §5
- `welcome.blade.php` vervangen door onze homepage → `ExampleTest.php` verwijderen
- Publieke unsubscribe-route `/nieuwsbrief/uitschrijven/{token}` (F4-N11 wacht hierop)
- Publieke contact-formulier + Honeypot
- Publieke fotogalerij met filter op bestemming/locatie
- Sessies-invalidatie bij email-change door admin (F4-U18 loose end)

Fase 4-suite blijft groeien in Fase 5 wanneer publieke pagina's + integratie-tests worden toegevoegd.

---

*Einde Fase 4 bouwplan — versie 1.0*
