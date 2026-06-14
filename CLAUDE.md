# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 13 juni 2026 — Fase 4 in uitvoering (Stap 4.5 afgerond)
**Masterplan:** zie `westein-reisblog-masterplan.md` voor volledige architectuur
**Bouwplannen:** Fase 2 staat vast in `fase-2-bouwplan.md`. Fase 4 wordt na afronding vastgelegd in `fase-4-bouwplan.md`.

---

## Wat dit project is

Een schaalbare, veilige Laravel-reisblog voor familievakanties van de familie Westein. Server-side rendered Blade, geen SPA. Multi-generatie publiek (familie, vrienden), Nederlandstalig, Dodger Blue als primaire kleur. Doel: SEO-groei en duurzaam onderhoud op Nederlandse shared hosting.

## Stack — definitief

- **Backend:** Laravel 13.7, PHP 8.3+, MySQL 8
- **Frontend:** Blade + Bootstrap 5 + Alpine.js + Vite
- **Editor:** TipTap **v3** (in admin) — twee profielen: `rich` (Posts) en `simple` (Pages, Newsletter)
- **HTML-sanitization:** `mews/purifier` (wrapper rond ezyang/htmlpurifier) — named configs per profiel
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
- Admin SCSS-partials in `resources/scss/admin/_layout.scss`, `_sidebar.scss`, `_topbar.scss`, `_components.scss`, `_forms.scss`, `_form-layout.scss`, `_family-members.scss`, `_image-upload.scss`, `_tiptap.scss`

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
- Media Library collecties: Post=`featured` (single) + `inline_images` (multi sinds Stap 4.0), Location=`gallery`, Destination=`hero` + `gallery`, User=`avatar`, **FamilyMember=`portrait` (single)**, **Page=`hero` (single, nog niet ontsloten in admin-UI — zie Stap 4.3.4 v2)**. Acceptable MIME: JPEG, PNG, WebP.
- WebP-conversies per collectie afgestemd, queued, origineel bewaard. Quality 82, `Fit::Max`. FamilyMember `portrait`: `webp-600` (Max 600×600) + `webp-300` (Max 300×300).
- Image driver: GD (portable voor shared hosting)
- Post `author()`-relatie (sinds Stap 4.2): gerenamed van `user()` voor consistentie met Comment en Newsletter — FK kolom blijft `user_id`, `belongsTo()` krijgt expliciet `'user_id'` als 2e arg

## Beslissingen Fase 4 — definitief

1. **UI-stack:** strikt Blade + Bootstrap + Alpine, geen Livewire/Filament
2. **Bouwvolgorde:** foundation eerst, dan CRUD
3. **Lijst-patroon:** server-side via querystring + Laravel paginate (geen Alpine-fetch debouncing)
4. **TipTap output:** HTML + server-side sanitization via `mews/purifier`
5. **TipTap profielen:** `rich` (Posts, alle extensions — komt in Stap 4.5) + `simple` (Pages, Newsletter — StarterKit + nested link config, headings beperkt tot h2-h4). **TipTap v3 StarterKit levert Link + Underline zelf** — niet meer als losse extensions importeren.
6. **Image picker:** tabbed modal — 'Uit Location-album' + 'Nieuwe upload', auto-draft-on-image bij nieuwe post
7. **Media-browser:** volledig `/admin/media` met grid, filters, bulk-acties (geen losse upload in v1 — uploaden via model)
8. **Newsletter:** TipTap simple + 3 vaste templates (`announcement`, `digest`, `plain`), Emogrifier inliner, batch-50 queued
9. **Soft deletes:** op Posts, Destinations, Locations, Routes, Pages + `/admin/prullenbak` + auto-purge 30d. **Niet** op Comments, Users (AVG), Subscribers, **FamilyMembers**.
10. **Slug-bewerking:** bewerkbaar bij create, read-only bij update. Admin heeft 'ontgrendel'-knop bij Posts/Destinations (komt in 4.4/4.5). **Pages-patroon:** slug simpelweg weglaten uit `rules()` van UpdateRequest — geen `slug_display`-truc nodig, ook tamper-proof bewezen via test.
11. **Tests:** kritische paden — Posts CRUD, Comment moderatie, Newsletter dispatch, RBAC matrix
12. **Index-patroon per module:** tabel voor Categories/Tags/Pages/Subscribers/Comments, cards voor FamilyMembers/Posts/Destinations
13. **Delete-bevestiging:** inline confirm via Alpine (`<x-admin.delete-button>` component; cards gebruiken `<x-admin.card-actions-menu>` met ingebouwde confirm)
14. **User-deactivatie:** veld `deactivated_at` (timestamp nullable) + `deactivation_reason` (text nullable) — toegevoegd in Stap 4.0
15. **Inline_images op Post:** aparte Media-collectie (multi-file) naast `featured` (single) — voor TipTap upload-tab
16. **Form-layout:** two-column (`<x-admin.form-layout>`) — hoofdcontent links, metadata rechts. Standaard voor alle modules met meer dan ~4 velden (FamilyMembers, Pages, Posts, Destinations, Locations). Categories/Tags blijven single-column.
17. **FamilyMember card-layout:** ronde portret/initialen centraal, 4 per rij desktop, driepuntsmenu rechtsboven, bio inline-expand.
18. **FamilyMember user-koppeling:** dropdown alle users + disabled 'Nieuwe gebruiker'-knop (ontgrendelt in Stap 4.13). `family.manage` toegekend aan Admin (via Gate::before) + Editor.
19. **Pages-beslissingen (Stap 4.3.4):**
    - TipTap simple = `StarterKit.configure({ heading: { levels: [2,3,4] }, link: { openOnClick: false, autolink: true, HTMLAttributes: { rel: 'nofollow noopener', target: '_blank' } } })` — geen losse Link/Underline imports
    - Purifier `simple`-profiel: `HTML.Allowed` = `p,br,strong,em,u,s,h2,h3,h4,ul,ol,li,blockquote,code,pre,a[href|title|target|rel]` + `HTML.TargetBlank: true` + `HTML.Nofollow: true` (Purifier rewrite externe links automatisch)
    - Reserved slugs centraal in `config/westein.php`, gevalideerd via `App\Rules\NotReservedSlug` (alleen in StoreRequest; UpdateRequest heeft slug niet in rules)
    - Publicatie via toggle `is_published` (form-helper, geen kolom) + datetime-local-veld → genormaliseerd via `publicationData()`-methode op de FormRequest
    - Hero-image bewust uitgesteld naar v2: model heeft `hero`-collectie al, maar UI-veld is nog niet ontsloten (isoleerde TipTap + Purifier risico)
20. **Generieke media-endpoints met eigenaar-gebaseerde autorisatie.** `MediaController` met
    drie routes: `POST media/upload`, `PATCH media/reorder`, `DELETE media/{media}`. Autorisatie
    via `$this->authorize('update', $media->model)` — erft van het bovenliggende model, geen
    losse media-permission. Client-side model-type via whitelist in `config/westein.php`
    (`gallery_models`) — nooit rauwe class-strings van client vertrouwen.
21. **Gallery-flow:** AJAX direct opslaan (upload + reorder + delete), los van form-submit.
    Reorder via SortableJS + `Media::setNewOrder()` met volledige id-lijst uit DOM-volgorde.
    Component (`<x-admin.gallery-upload>`) hoort op de EDIT-pagina; "store→edit"-redirect zorgt
    dat een net aangemaakt model direct een werkpagina krijgt voor de galerij.
22. **Locations: volledig genest** onder Destinations (`/admin/bestemmingen/{destination}/locaties/{location}`)
    met Laravel `scoped()`-binding (`->scoped(['location' => 'slug'])`). Cross-destination-aanroepen
    geven automatisch 404. Locations hebben geen hero, alleen `gallery`-collectie; eerste foto
    dient als thumbnail in de index-cards.
22. **Posts status-flow:** dropdown met 4 statussen; `published_at` datetime-local verschijnt bij `scheduled`. UI verbergt `published`/`scheduled` voor wie geen `posts.publish` heeft; Form Request handhaaft dezelfde regel server-side via `Rule::in($this->allowedStatuses())`. Permissief in beide richtingen: `archived` is vrij voor iedereen met update-recht (een auteur mag z'n eigen werk archiveren), alleen het *publiek-maken* (`scheduled`/`published`) vraagt extra recht.
23. **Posts §3.4-validatie:** locatie↔bestemming-match altijd verplicht; bestemming verplicht *tenzij* de post de Tips-categorie heeft. Permissieve uitzondering: Tips-post **mag** een bestemming hebben (masterplan-conform). Anker-slug `tips` zit in `config/westein.php` (`general_tips_category_slug`), nooit hardcoded.
24. **Posts taxonomie-UI:** twee aparte secties in de side-kolom. Categorieën als checkbox-groep (eindige, geseed'de lijst). Tags via pill-input met autocomplete (`tagPills` Alpine-factory), één hidden komma-gescheiden string als bron-van-waarheid; Form Request splitst server-side terug naar array in `prepareForValidation()`.
25. **Abstracte basis voor Post Form Requests:** `PostRequest` (abstract) levert `rules()`/`prepareForValidation()`/`withValidator()`/`allowedStatuses()`/`hasTipsCategory()`/`publicationData()`/`messages()`. Store en Update erven en verschillen alleen in `authorize()` en `slugRules()`. Bewuste afwijking van Pages (twee onafhankelijke Requests met gedupliceerde `publicationData()`) — gerechtvaardigd door vier gedeelde stukken logica.
26. **Image-picker (4.6) browse-scope = projectbreed, alleen content-foto's.** Eén query op `media`-tabel gefilterd op `collection_name ∈ {gallery, hero, featured, inline_images}`. Avatars (User) en portretten (FamilyMember) blijven buiten beeld. Eigenaar eager-loaded voor context-labels ("Bestemming: Italië", "Locatie: Italië → Rome"). Cursor-paginatie (24/pagina). Browse-permission: PostPolicy.create (wie bij rich editor kan, mag bladeren).
27. **Image-upload (4.6) landt in post-eigen `inline_images`-collectie.** Autorisatie via PostPolicy.update (own/any). Bewust géén centrale media-pool — die komt eventueel in 4.11 als praktijk wijst dat veel kruislings hergebruik gepaard gaat met hard-deletes. Aanvaarde fragiliteit: een hard-deleted post neemt z'n `inline_images` mee, dus een andere post die diezelfde foto via browse hergebruikte verliest 'm — verzacht door soft-delete + 30-dagen-venster.
28. **Image-picker create-flow: browse altijd live, upload-tab disabled tot opgeslagen.** Bladeren/invoegen heeft geen post nodig (invoegt enkel `<img src>` in editor-HTML; pas bij submit met de body meegeschreven). Upload-tab toont "Sla eerst op als concept" tot store→edit-redirect. Geen stub-endpoint, geen §3.4-omzeiling, geen weesconcepten.
29. **Image-extensie (4.6) = invoegen + alignment via class.** Geen drag-resize (TipTap-resize-extensies hebben v3-compat-risico, zie leerpunt #30). Vier classes: `img-align-{left|center|right|full}`, met `img-`-prefix om Bootstrap-utility-collisions te vermijden. Default bij invoegen = `img-align-full`. Geen inline `style` — Purifier-allowlist bevat alleen `img[class]` plus `Attr.AllowedClasses`-whitelist op exact deze vier classes. `URI.AllowedSchemes` expliciet op `http|https|mailto` (geen `data:` of `javascript:`).

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
14. **Pest-syntax in tests, niet PHPUnit-classes.** `RefreshDatabase` is centraal actief via `tests/Pest.php`. Admin-tests in `tests\Feature\` met naamconventie `{Module}ManagementTest.php`.
15. **PowerShell-quoting:** single quotes (`'...'`) voor regex-filters met `|`. Of gebruik padargument om filter te omzeilen. Voor `tinker --execute` met PHP-variabelen: outer single quotes + escape PHP-strings met `\"`.
16. **Per CRUD-module: server-side patroon.** Querystring-gestuurde filters/sort/paginate, `withQueryString()` op de paginator, `<x-admin.sort-link>`-component voor kolom-headers.
17. **Inline-delete via `<x-admin.delete-button>`** (tabellen) of **`<x-admin.card-actions-menu>`** (cards) voor consistente UX over alle CRUDs.
18. **Check bestaande componenten/conventies vóór je nieuwe verzint.** Grep/Get-Content op een bestaande module (Categories/Tags) draaien vóór je een form/CSS-patroon schrijft. Het echte form-patroon is `<x-admin.field>` + `.admin-field`, plus `<x-admin.form-layout>` + `<x-admin.form-section>` voor de two-column wrapper.
19. **Form Request-namespace:** `App\Http\Requests\Admin\{Module}\{Action}Request` (bv. `Admin\Pages\StorePageRequest`).
20. **Form-helpers die geen kolommen zijn** (zoals `is_published`, `slug_display`, `remove_portrait`): filter uit `$validated` via `Arr::except($data, [...])` vóór `Model::create()` of `$model->update()`. Voorkomt MassAssignmentException.

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
- **`<x-admin.tiptap-editor>`** (Stap 4.3.4) — TipTap simple-profiel editor met toolbar (bold/italic/underline/strike, h2-h4, lijsten, blockquote, code, link, undo/redo). Alpine-factory in `resources/js/admin/tiptap-simple.js`, geregistreerd via `alpine:init` → `Alpine.data('tiptapSimple', ...)`. Output = HTML, gesaneerd via Purifier in controller. Props: `name`, `label`, `value`, `hint`, `placeholder`, `required`, `error`. **Initial content uit hidden field**, niet via x-data-argument (escape-proof voor apostrofs/quotes in content).
- **`<x-admin.avatar-initials>`** — portret of initialen-fallback met deterministische accent-kleur (`crc32(id) % palette`). Props: `member`, `size`.
- **`<x-admin.card-actions-menu>`** — driepuntsmenu (⋮) met Bewerken + inline delete-confirm (Alpine). Props: `edit-url`, `delete-url`, `delete-confirm`.
- **`<x-admin.delete-button>`** — inline delete-confirm voor tabel-rijen (bestond al sinds 4.3.1).
- **`<x-admin.gallery-upload>`** (Stap 4.4) — multi-image galerij met drag-drop upload,
  SortableJS-reorder, per-foto delete. Alle acties via AJAX (los van form-submit). Alpine-factory
  in `resources/js/admin/gallery-upload.js`. Props: `name` (collectie), `model` (HasMedia-instance),
  `max-mb`. Werkt voor elk model in de `gallery_models`-whitelist (Destination, Location, straks Posts).
- **`MediaController`** (Stap 4.4) — generieke endpoints (`upload`/`reorder`/`destroy`) met
  eigenaar-policy-check via `$media->model`. Geen losse media-permission nodig.
- **`App\Rules\NotReservedSlug`** (Stap 4.3.4) — validatieregel die slugs uit `config('westein.reserved_slugs')` weigert. Herbruikbaar voor andere slug-velden die top-level routes raken.
- **`<x-admin.image-picker-modal>`** (Stap 4.6) — modal met twee tabs (browse + upload) voor de TipTap rich-editor. Coördinatie via `Alpine.store('imagePicker')`: editor roept `openFor(this)`, modal roept `selectImage(src, alt)`. Lui laden, cursor-paginatie, filter op collectie, drag-and-drop upload. Upload-tab automatisch disabled op create-view (geen post-id → "Sla eerst op als concept"). Props: `post` (nullable Post-model).
- **`MediaPickerController` + `PostInlineImageController`** (Stap 4.6) — twee AJAX-endpoints voor de image-picker. `GET media-picker` projectbreed bladeren (filter op `collection_name ∈ {gallery, hero, featured, inline_images}`, autorisatie via `posts.create`); `POST posts/{post}/inline-images` upload naar de eigen `inline_images`-collectie (autorisatie via `posts.update` own/any). Response-shape consistent over beide: `{id, url, thumb_url, alt, context?}`.
- **`tiptapRich`-factory** uitgebreid (Stap 4.6) met `openImagePicker()`/`insertImage()`/`setImageAlign()`/`deleteImage()`. Image-extensie via `Image.extend({addAttributes})` met een `align`-attribute die parseert uit/rendert naar de `img-align-*` class. Alle TipTap-aanroepen via `Alpine.raw(this.editor)` — zie leerpunt #34.

## Roadmap — fase-status

- ✅ **Fase 1 — Project setup & design system** _(afgerond 2 mei 2026)_
- ✅ **Fase 2 — Authenticatie & autorisatie** _(afgerond 10 mei 2026)_
- ✅ **Fase 3 — Database & content modellen** _(afgerond 13 mei 2026)_
- 🔄 **Fase 4 — Afgeschermd Admin-gedeelte** _(in uitvoering)_
- ⏳ **Fase 5 — Ontwikkeling openbare pagina's**
- ⏳ **Fase 6 — SEO, performance en publicatie**

## Fase 4 — overzicht (in uitvoering)

| Stap      | Inhoud                                                                                  | Status      |
| --------- | --------------------------------------------------------------------------------------- | ----------- |
| **4.0**   | Fundament: soft deletes op kern-content, users opruimen + `deactivated_at`, Post `inline_images` collectie | ✅ afgerond |
| **4.1**   | Admin-layout: inklapbare sidebar (flexbox), gegroepeerde nav, topbar met gebruikersmenu, flash + form-componenten | ✅ afgerond |
| **4.2**   | Dashboard met 6 KPI-cards + gemixte activity feed. Rename `Post::user()` → `Post::author()` | ✅ afgerond |
| **4.3.1** | Categories CRUD                                                                         | ✅ afgerond |
| **4.3.2** | Tags CRUD (met morphedByMany op Posts)                                                  | ✅ afgerond |
| **4.3.3** | FamilyMembers CRUD — eerste cards-layout + eerste media-upload                          | ✅ afgerond |
| **4.3.4** | Pages CRUD — eerste TipTap (simple-profiel) + HTMLPurifier                              | ✅ afgerond |
| **4.4**   | Destinations + Locations CRUD + generieke gallery-component                             | ✅ afgerond |
| **4.5**   | Posts CRUD inclusief TipTap rich                                                        | ✅ afgerond |
| **4.6**   | TipTap image-picker modal                                                               | ✅ afgerond |
| **4.7**   | Comment-moderatie                                                                       | ⏳          |
| **4.8**   | Routes + Waypoints CRUD                                                                 | ⏳          |
| **4.9**   | Subscribers + import/export                                                             | ⏳          |
| **4.10**  | Newsletter compose & dispatch                                                           | ⏳          |
| **4.11**  | `/admin/media` browser                                                                  | ⏳          |
| **4.12**  | `/admin/prullenbak`                                                                     | ⏳          |
| **4.13**  | Users + rollen beheer                                                                   | ⏳          |
| **4.14**  | Tests, Pint, fase-4-bouwplan document, commit                                           | ⏳          |

## Wat staat er nu in Fase 4

- **Soft deletes** op `posts`, `destinations`, `locations`, `routes`, `pages`. `users` heeft `deactivated_at` + `deactivation_reason`. `users.avatar_path` dropped (Media Library `avatar`-collectie heeft 'm vervangen).
- **Admin-layout** met inklapbare sidebar (flexbox, niet grid), gegroepeerde nav (Content / Engagement / Beheer), topbar met breadcrumbs + gebruikersmenu, flash-partial. localStorage-state voor sidebar-collapse. Mobiel: hamburger + sliding overlay + backdrop. Breadcrumbs: flat patroon met `<i class="bi bi-chevron-right">`-separators + `<span class="admin-breadcrumbs__current">` (geen Bootstrap `<ol class="breadcrumb">`).
- **Dashboard** met 6 KPI-cards (Posts published / Drafts / Reacties totaal / Te modereren / Abonnees / Geplande brieven) en gemixte activity feed via `ActivityFeed`-service.
- **Categories CRUD** (`/admin/categories`) — server-side filters, sort, paginate, inline delete-confirm, locked slug bij edit.
- **Tags CRUD** (`/admin/tags`) — server-side filters, sort, paginate, inline delete-confirm. `Tag::posts()` via `morphedByMany`, `withCount('posts')` voor "gebruikt in N posts"-teller. Tag heeft `doNotGenerateSlugsOnUpdate()`.
- **FamilyMembers CRUD** (`/admin/family-members`) — eerste cards-layout (4 per rij, ronde portret/initialen, driepuntsmenu, bio inline-expand), eerste media-upload via `<x-admin.image-upload>` (drag-and-drop, `portrait`-collectie). Two-column form via `<x-admin.form-layout>`. Server-side search/sort/paginate. `FamilyMemberPolicy` op `family.manage` (Admin + Editor). Demo-seeder met 4 familieleden (geen foto's → initialen-fallback). 15 Pest-tests.
- **Pages CRUD** (`/admin/pages`) — **eerste TipTap-integratie (simple-profiel)** + **eerste HTMLPurifier sanitization**. Tabel-patroon met drie statusbadges (Concept/Gepland/Gepubliceerd) + statusfilter dropdown. Two-column form via `<x-admin.form-layout>`. Publicatie via Alpine-toggle + datetime-local-veld (scheduling werkt via `Page::published()`-scope). Slug locked bij update (uit `rules()` weggelaten, tamper-proof bewezen). Reserved-slug validatie via `App\Rules\NotReservedSlug` + `config/westein.php`. Soft delete via `$page->delete()`. 18 Pest-tests (RBAC-matrix, CRUD, scheduling, slug-locking, sanitization).
- **Destinations CRUD** (`/admin/bestemmingen`) — cards-layout met hero-image-achtergrond +
  overlay-naam + landcode-badge + locaties-teller. Two-column form (naam/slug/beschrijving links,
  landcode + hero-upload rechts). Slug locked bij update (Pages-patroon). `<x-admin.image-upload>`
  generiek gemaakt (`remove_{{ $name }}` i.p.v. hardcoded `remove_portrait`). 15 Pest-tests.
- **Generieke gallery-component** (`<x-admin.gallery-upload>`) + `MediaController` met drie AJAX-
  endpoints (upload/reorder/destroy). SortableJS toegevoegd via npm. Model-type whitelist in
  `config/westein.php`. 9 Pest-tests (RBAC, whitelist-validatie, cross-model-afwijzing).
- **Locations CRUD** (`/admin/bestemmingen/{destination}/locaties/{location}`) — volledig genest
  met `scoped()`-binding (cross-destination = 404). Cards-layout met gallery-thumbnail. Banner-
  header met destination-context boven de index. Two-column form met lat/lng-getalvelden (Leaflet
  later). Geen hero, alleen gallery. 18 Pest-tests (incl. scoped 404, lat/lng-range-validatie).
- **Posts CRUD** (`/admin/posts`) — **eerste TipTap rich-profiel** + eerste own/any-policy.
  Tabel-index met featured-thumbnail per rij, filters voor status/auteur/bestemming, server-side
  sort/paginate. Two-column form via `<x-admin.form-layout>`: TipTap rich-editor links met tabel-
  invoeg + codeblok + scheidingslijn; rechts twee aparte secties Categorieën (checkbox-groep,
  geordend op `Category.order`) en Tags (pill-input met autocomplete via Alpine-factory
  `tagPills`, één hidden comma-string als bron-van-waarheid). Status-dropdown met 4 statussen;
  `published_at` datetime-local verschijnt bij `scheduled`. Featured image bij create én edit via
  bestaande `<x-admin.image-upload>` (Destination-hero-patroon). Slug locked bij update (Pages-
  patroon, weggelaten uit `rules()`). §3.4-validatie via `withValidator`-`after`-closure: locatie
  moet binnen bestemming vallen, bestemming verplicht tenzij Tips-categorie (permissief,
  masterplan-conform). Abstracte basis `PostRequest` waar Store en Update van erven (vier
  gedeelde stukken logica — bewuste afwijking van het Pages-patroon met twee onafhankelijke
  Requests). Status-vork bewaakt door `posts.publish`: `published`/`scheduled` weggelaten uit UI-
  dropdown voor wie geen recht heeft + server-side `Rule::in()` als waarborg. Body gesaneerd via
  `mews/purifier` 'rich'-profiel (tabellen, code, links, scheidingslijn; géén iframes/img tot
  4.6). Tags lowercase/dedupe/firstOrCreate via bestaande `Post::syncTagsByName()` na komma-split
  in `PostRequest::prepareForValidation()`. Store→edit-redirect (i.p.v. Pages' →index — edit-
  pagina wordt in 4.6 inline-image-werkplek). PostPolicy met own/any-logica: editor mag any,
  auteur mag alleen eigen (`update`/`delete` checken `$post->user_id === $user->id`). 33 Pest-
tests groen (RBAC-matrix incl. own/any, CRUD met relatie-sync, §3.4-consistentie, status-vork,
  slug-locking, rich-sanitization met tabel-behoud, tag-dedupe).
- **TipTap image-picker** (Stap 4.6) — twee tabs (Bladeren + Uploaden) gecoördineerd via
  `Alpine.store('imagePicker')`. Browse-tab: projectbreed grid uit de `media`-tabel gefilterd op
  content-collecties (`gallery`, `hero`, `featured`, `inline_images`), met eigenaar-context per
  thumbnail ("Bestemming: Italië → Rome"), zoekveld op bestandsnaam, collectie-filter, cursor-
  paginatie van 24/pagina. Upload-tab: drag-and-drop naar de post-eigen `inline_images`-
  collectie, alt-veld voor toegankelijkheid, automatisch disabled op create-view tot de post is
  opgeslagen. TipTap Image-extensie uitgebreid met een `align`-attribute (parseert uit/rendert
  naar `img-align-{left|center|right|full}`); toolbar kreeg image-knop + vier alignment-knoppen
  die activeren zodra een image geselecteerd is. Modal-CSS (`_image-picker.scss`) en image-
  alignment-CSS in de editor (in dezelfde partial). Purifier `rich`-config uitgebreid met
  `img[src|alt|title|class]` + `Attr.AllowedClasses` op exact de vier `img-align-*`-namen +
  `URI.AllowedSchemes` op `http|https|mailto` (geen `data:`, geen `javascript:`). Post-model
  webp-conversies (`thumb` 400px, `medium` 800px) toegevoegd op `inline_images`. 25 nieuwe
  Pest-tests (13 MediaPicker incl. RBAC, scope-isolatie van avatars/portretten, collectie-
  filter, zoeken, context-labels, cursor-paginatie; 12 PostInlineImage incl. RBAC own/any-
  matrix, response-shape, alt-bewaring, mime/size/length-validatie). PostManagementTest van 33
  → 36 tests: bestaande "verwijdert img-tags"-test omgekeerd naar "behoudt img-tags met
  alignment-class" + drie nieuwe sanitization-tests (onbekende classes gestript, javascript:-
  scheme gestript, data:-URI gestript). Totaal nu **61 groene tests** voor de Post-stack.

## Leerpunten Fase 4 — bewaar voor volgende keer

1. **Laravel 11+ controllers hebben geen `middleware()` en geen `authorizeResource()`.** `Illuminate\Routing\Controller` is zo goed als leeg. Patroon: `AuthorizesRequests`-trait toevoegen aan base `app/Http/Controllers/Controller.php`, dan `$this->authorize('action', $model)` per method.

2. **`Post::user()` → `Post::author()` consistentie.** FK-kolom blijft `user_id`; relatie heet `author()` met expliciet `'user_id'` als 2e arg in `belongsTo()`. Comment en Newsletter idem. Test-factories die `->for($user)->...` op Post deden moeten nu `->for($user, 'author')->...`.

3. **`isolation: isolate` op de mobiel-sidebar** lost stacking-context issues op. Backdrop niet onder de sidebar leggen via z-index, maar met `left: 280px` ernaast positioneren.

4. **Alpine `x-data` met multi-line objecten** kan in sommige Blade-contexts fragile zijn. Gebruik een named function in `<script>` of een Alpine-factory geregistreerd via `alpine:init` → `Alpine.data(...)`.

5. **Voor brede flex-layouts: `min-width: 0` op de inner flex-item.** Anders duwen brede tabellen het grid kapot.

6. **CSS Grid kan onverwacht uitlijnen bij sticky children.** Voor admin-shell switched naar flexbox.

7. **`morphedByMany` is de inverse van `morphToMany`.** Tag heeft `morphedByMany(Post::class, 'taggable')`.

8. **Slug-locking op edit-form — twee patronen:**
   - FamilyMember-patroon: veld heet `slug_display` (readonly), met `offsetUnset()` in `prepareForValidation()`.
   - Pages-patroon (eenvoudiger, sinds Stap 4.3.4): slug simpelweg **weglaten uit `rules()` van UpdateRequest**. `validated()` retourneert 'm dan niet, dus 'ie wordt nooit gesaved — ook bij POST-tampering. Per-test bewezen.

9. **Check bestaande componenten/conventies vóór nieuwe verzinnen.** Bij FamilyMember-form eerst een fictief `.admin-form__*`-stelsel geschreven dat niet bestond — het echte patroon was `<x-admin.field>` + `.admin-field`. Altijd eerst grep/Get-Content op een bestaande module draaien.

10. **`<x-admin.form-layout>` + `<x-admin.form-section>` = herbruikbaar two-column form-patroon** (main links, side rechts). Form-tag zit IN de component; views geven alleen slots + `enctype` mee. Gebruik `@isset($enctype)` i.p.v. `@if ($enctype)` voor robuustheid bij undefined prop.

11. **`<x-admin.image-upload>` = herbruikbare drag-and-drop upload.** Alpine-factory in `resources/js/admin/image-upload.js`, geregistreerd via `alpine:init` → `Alpine.data()`. Client-side validatie (MIME/size/dimensions) + server-side Form Request. `name="portrait"` file-input + `name="remove_portrait"` checkbox. DataTransfer-trick schrijft drop-FileList terug naar de input.

12. **php.ini upload-limieten.** Herd default = `upload_max_filesize=2M`. Opgehoogd naar 16M (upload) / 32M (post). `validation.uploaded` "uploaden mislukt" = bestand overschrijdt PHP-limiet, niet de Laravel-regel. Herd restarten na php.ini-wijziging.

13. **lang/nl vereist `validation.php`, `passwords.php`, `pagination.php`** (publiceren via `php artisan lang:publish` of handmatig). `APP_FALLBACK_LOCALE=en` zodat ontbrekende NL-keys terugvallen op vendor-strings i.p.v. de raw key. Let op UTF-8 **zonder BOM** — BOM op `<?php` laat de translator crashen met `array_replace_recursive(): Argument #2 must be of type array`.

14. **Avatar-fallback patroon:** `<x-admin.avatar-initials>` met deterministische accent-kleur (`crc32(id) % palette`). Herbruikbaar voor comment-moderatie + gebruikersbeheer.

15. **`<x-admin.card-actions-menu>` = driepuntsmenu** met inline delete-confirm (Alpine). Herbruikbaar voor alle card-based modules.

16. **`assignRole()` returnt geen User.** Splits `$user = User::factory()->create();` en `$user->assignRole(...)` over twee regels in tests — de fluent chain `->create()->assignRole()` zet de verkeerde waarde in de variabele. De "Undefined property `$this->admin`"-melding in Pest is overigens IDE-cosmetica, geen runtime-fout.

17. **TipTap v3 StarterKit levert Link + Underline standaard mee** (afwijking van masterplan §4.3 dat nog v2 noemt). **Niet** als losse extensions importeren — leidt tot `Duplicate extension names found: ['link', 'underline']`-warning. Patroon: `StarterKit.configure({ heading: { levels: [...] }, link: { ...nested config... } })`. Underline heeft geen extra config nodig.

18. **Alpine roept `init()` op een `x-data`-object automatisch aan.** Een component met zowel `x-data="factory()"` ALS `x-init="init()"` triggert dubbele initialisatie. Symptoom bij TipTap: twee editors over elkaar gerenderd, één werkend en één dood. Patroon: alleen `x-data="factory()"`, en defensief `if (this.editor) return;` als eerste regel in de factory's `init()` voor extra zekerheid bij hot-reload.

19. **In Blade-attributen geen geneste apostrofs/escaped quotes mixen.** `:title="__('Statische pagina\'s zoals...')"` triggert ParseError omdat Blade `\'` binnen een dubbele-quote-attribuut niet correct verwerkt. Voor hardcoded NL-tekst: drop de `:`-prefix en gebruik platte string-attribuut (`title="Pagina's"`). Binnen `{{ ... }}`-blokken werkt escaped quoting wél (geen attribuut-context).

20. **TipTap initial content uit hidden field lezen** (`this.$refs.hidden.value`), niet via x-data-argument doorgeven. Bij content met apostrofs/quotes klapt de JS-string-interpolatie in het `x-data`-attribuut anders. Het hidden field is sowieso de bron-van-waarheid voor form-submission, dus we hergebruiken dat mechanisme voor zowel in als uit. Veiliger en simpeler.

21. **HTMLPurifier `simple`-config rewrites externe links automatisch** met `HTML.TargetBlank: true` + `HTML.Nofollow: true`. Geen extra controller-logica nodig om `target="_blank"` of `rel="nofollow noopener"` toe te voegen. Per-test bewezen.

22. **Admin-tests staan in `tests\Feature\` direct**, met naamconventie `{Module}ManagementTest.php` (bv. `PageManagementTest.php`, `FamilyMemberManagementTest.php`). Niet in een `Admin/`-submap. Model-tests staan apart in `tests\Feature\Models\`.

23. **`@php(...)`-shorthand met een toewijzing compileert stuk.** `@php($x = ...)` werd gecompileerd naar `<?php($x = ...)` — zonder spatie na `<?php`, wat ongeldige PHP 
is en verderop een misleidende `syntax error, unexpected token "endif"` op regel 1 geeft. De fout wijst NIET naar de echte regel. Gebruik altijd de blok-vorm voor assignments: `@php $x = ...; @endphp` (puntkomma verplicht). Shorthand alleen voor losse calls.
Diagnose-truc: lint de gecompileerde view met `php -l` op het bestand in `storage\framework\views\*.php` — de eerste regel toont de fout-compilatie direct.

24. **`<x-admin.image-upload>` is nu generiek.** Remove-checkbox heet `remove_{{ $name }}` (was hardcoded `remove_portrait`). Controller/Request luisteren naar `remove_{name}`. Werkt voor portrait (FamilyMember) én hero (Destination), klaar voor Posts.

25. **Generieke media-endpoints met eigenaar-gebaseerde autorisatie.** `MediaController` (upload/reorder/destroy) resolvet het eigenaarsmodel en checkt `authorize('update', $model)`. Geen losse media-permission nodig — autorisatie erft van het bovenliggende model. Werkt voor elk model met een gallery-collectie. Routevolgorde: statische `media/reorder` MOET vóór dynamische `media/{media}` staan.

26. **Model-type whitelist in config/westein.php (`gallery_models`).** NOOIT de rauwe class-string van de client vertrouwen bij generieke media-endpoints. Client stuurt een type-string ('destination'), server mapt die tegen de whitelist naar de echte klasse. Voorkomt upload naar willekeurige modellen.

27. **`<x-admin.gallery-upload>` = herbruikbare multi-image galerij.** SortableJS-reorder (`Media::setNewOrder()` met volledige id-lijst uit DOM-volgorde), AJAX-upload/delete los van form-submit. Factory in resources/js/admin/gallery-upload.js. Hoort op de EDIT-pagina (model moet bestaan); sluit aan op de store→edit-redirect. Props: name (collectie), model, max-mb.

28. **AJAX-endpoints in tests vereisen `Accept: application/json`.** Bij file-upload tests kun je geen `postJson()` gebruiken (die is voor JSON-bodies, niet multipart). Gebruik in plaats daarvan `->withHeaders(['Accept' => 'application/json'])->post(...)`. Zonder de Accept-header probeert Laravel bij een validatiefout een redirect-response te bouwen → cryptische "Call to a member function all() on array". De productie-JS-client stuurt sowieso al JSON Accept; in de test moet je dat expliciet repliceren.

29. **Geneste resource-routes met `scoped()` voorkomen cross-parent-aanroepen.**
    `Route::resource('bestemmingen.locaties', ...)->scoped(['location' => 'slug'])` zorgt dat
    Laravel valideert dat `$location` echt onder `$destination` valt — anders 404.
    Cruciaal voor data-integriteit bij volledig geneste URL's. Test dit expliciet met een
    `assertNotFound()`-scenario.

30. **TipTap v3 tabel-extensies zijn named exports, niet default.** `@tiptap/extension-table`, `@tiptap/extension-table-row`, `@tiptap/extension-table-header`, `@tiptap/extension-table-cell` exporteren géén default — gebruik `import { Table } from '@tiptap/extension-table'`. Eén `import Table from ...` met default-vorm gooit `SyntaxError: ... does not provide an export named 'default'` bij het laden van de bundle, en daarmee stopt de **hele** admin.js vóór de `Alpine.data()`-registraties. Symptoom: ALLE Alpine-componenten op de pagina lijken dood (TipTap typt niet, image-upload sleept niet, dropdowns reageren niet). Eén fout, veel symptomen — check eerst de browserconsole bij dit soort "alles dood"-bevindingen, niet de losse componenten. Afwijking van StarterKit en `@tiptap/core` zelf (die wél een default exporteren), dus makkelijk over het hoofd te zien.
31. **`tagPills` Alpine-factory + hidden komma-string-veld.** Patroon voor multi-value form-input zonder dynamische DOM-array-namen: één hidden field bevat een komma-gescheiden string, de factory beheert de zichtbare pills + autocomplete + keyboard-handling (Enter/komma/Backspace/Pijltjes). Server-side Form Request splitst in `prepareForValidation()` terug naar een array. Robuuster dan `name="tags[]"` met dynamic DOM-elementen, en eenvoudiger te valideren. Herbruikbaar voor andere "vrije lijst"-velden.
32. **`<option x-show>` werkt in moderne browsers.** Een `<select>` met `x-show` op afzonderlijke `<option>`-elementen om client-side te filteren werkt in praktijk prima (getest in deze stap voor locatie-filter binnen-bestemming). Browsers die `display:none` op `<option>` negeren bestaan in theorie, maar in moderne Chrome/Edge/Firefox is dit een geldig en simpel patroon. Server-side validatie (§3.4) blijft de echte waarborg; client-side filter is puur UX-hulp.
33. **`tests/Pest.php` draait alléén `RefreshDatabase` centraal, géén seeders.** Elke testfile zet z'n eigen rollen/permissies op in `beforeEach`. CLAUDE.md zegt "rollen centraal", maar in werkelijkheid herhaalt iedere `*ManagementTest.php` z'n eigen RBAC-setup. Bewust — zo is elke suite zelfvoorzienend en zie je in de file zelf welke rollen/rechten de policy nodig heeft. Tips-categorie en andere test-data worden per test met factories aangemaakt; slug via `config('westein.general_tips_category_slug')` zodat test en Form Request dezelfde bron delen.

34. **`Alpine.raw()` vereist voor ELKE TipTap-aanroep vanuit een Alpine-factory.** Alpine wikkelt de editor in een Vue-reactivity Proxy. ProseMirror doet identiteitschecks zoals `tr.before.eq(state.doc)` die over die Proxy heen falen — symptoom: `RangeError: Applying a mismatched transaction`, en bij `isActive()`-aanroepen via de proxy krijg je intermittent `false` voor nodes die wél actief zijn (zichtbaar wanneer je tussen meerdere images switcht). Geldt zowel voor mutaties (`setImage`, `updateAttributes`, `deleteSelection`) als query-calls (`isActive`, `getAttributes`). Patroon: `const rawEditor = window.Alpine.raw(this.editor); if (!rawEditor || rawEditor.isDestroyed) return; rawEditor.commands.x()` — of `rawEditor.chain().focus().x().run()` voor chains. NIET strikt nodig voor toolbar-buttons binnen het editor-element (die hebben editor-focus en lijken een ander code-pad te raken), wel altijd nodig voor externe triggers (modal, sidebar, callbacks) én voor `syncState()` zodra meerdere instances van een node in het doc kunnen staan. Drie keer geleerd in één avond (insertImage → setImageAlign/deleteImage → syncState); bouwt nu de defaultaanname in voor élke nieuwe TipTap-aanraking.

35. **Wrap externe TipTap-aanroepen in try/finally aan de aanroep-kant.** Een onverwerkte error in `editor.insertImage()` of vergelijkbaar liet de image-picker modal in een half-open state hangen (`open=true`, achtergrond dim, geen manier om te sluiten). Patroon in de store: `try { editor.insertImage(...) } catch (err) { console.error(...) } finally { this.close() }`. Garandeert UI-consistentie ook bij toekomstige TipTap-regressies en maakt het bug-zoeken makkelijker (de modal sluit, en je ziet de fout in de console zonder dat de UI vastzit).

36. **`assertRedirect('/login')` faalt bij `getJson()`/`postJson()` voor unauthenticated requests.** Laravel honoreert de `Accept: application/json`-header die `getJson()` automatisch zet, en schakelt unauthenticated → 302 redirect om naar 401 JSON-response. Voor AJAX-endpoint-tests gebruik `->assertUnauthorized()` in plaats van `->assertRedirect(route('login'))`. Klopt ook semantisch met wat de browser-client krijgt (modal-fetch stuurt sowieso `Accept: application/json`).

37. **Purifier `Attr.AllowedClasses` werkt globaal, niet per-element.** Eén whitelist voor het HELE document, geen per-element scoping. In de rich-config staan vier `img-align-*`-classes; als je in de toekomst `table[class]` zou toevoegen aan `HTML.Allowed`, moet je de bestaande `tiptap-table`-class óók aan deze whitelist toevoegen of 'ie wordt gestript. Voor 4.6 niet acuut (table heeft géén `[class]`), maar bewustzijn nodig bij élke uitbreiding van class-bevattende elementen.

38. **TipTap v3 custom-attribute pattern via `Extension.extend({ addAttributes })`.** Een custom attribute (zoals `align` op Image) wordt opgehangen via `Image.extend({ addAttributes() { return { ...this.parent?.(), align: { default, parseHTML, renderHTML } } } })`. `parseHTML` leest met een regex uit een class-attribuut (`/img-align-(left|center|right|full)/`), `renderHTML` schrijft terug als `{ class: 'img-align-...' }`. Géén inline `style` — Purifier-allowlist kan strikt blijven (`img[class]` plus `Attr.AllowedClasses`-whitelist) zonder `CSS.AllowedProperties` te openen. Patroon herbruikbaar voor toekomstige custom attributes (figure-caption, link-style, embed-config).

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

## Volgende concrete actie — Stap 4.7: Comment-moderatie

Stap 4.6 is afgerond (TipTap image-picker + projectbrede browse + post-eigen upload + 25 nieuwe
Pest-tests). De totale Post-stack staat nu op 61 groene tests.

Stap 4.7 pakt de modereerflow op de `comments`-tabel uit Fase 3: lijst van pending-reacties,
goedkeuren/afkeuren/spam-markeren, optioneel inline-bewerking, RBAC via `comments.moderate`. Eerste
module met een puur state-machine-flow (geen rich text, geen media) — voorzien als kleine stap
(2-3 dagen).

Open vragen voor vooraf:
1. Index-layout — tabel of cards? (Tabel ligt voor de hand: status-kolom + bulk-acties zijn
   tabel-natuurlijk. Cards passen niet bij scannable moderatie-werk.)
2. Bulk-acties — JA/NEE in deze stap, of pas in 4.13 erover na?
3. Antwoord-flow — heeft een admin de mogelijkheid om als beheerder direct te antwoorden (een
   threaded reply), of is dat bewust uit scope tot Fase 5?
4. Spam-detectie — alleen handmatig markeren, of een lichte Honeypot/keyword-check vooraf?
