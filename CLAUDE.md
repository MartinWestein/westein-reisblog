# CLAUDE.md — Westein Reis Blog

Briefing voor Claude bij elke sessie. Lees dit eerst.

**Laatst bijgewerkt:** 21 juni 2026 — Fase 4 in uitvoering (Stap 4.9 afgerond)
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
30. **State-machine modules gebruiken verb-routes, niet één PATCH met status-veld.** Bij moderatie/workflow-acties (Comments: approve/reject/spam — straks waarschijnlijk Subscribers: confirm/unsubscribe) is de actie *expliciet*: de knop zegt "Goedkeuren", de route is `comments.approve`, de test is `patch(route('comments.approve', $c))`. Status valt niet te tamperen vanuit de client, logs zijn leesbaar, geen Form Request nodig voor een veld dat sowieso server-side vastligt. De transitie-regel zelf (welke timestamps wel/niet wissen) blijft gecentraliseerd in een model-method (`Comment::moderate(string $status)`), zodat de routevorm en de business-rule onafhankelijk kunnen evolueren. Edit-forms met meerdere velden tegelijk (Posts, Pages) houden hun status gewoon in de PATCH — andere context, andere oplossing.
31. **Routes publicatie-model = `is_published` boolean + `published_at` timestamp.** Bewuste keuze tegen een full enum à la Posts/Pages. Routes-content is niet tijdgevoelig zoals een blogpost, maar wél vooraf-planbaar (roadtrip 2027 voorbouwen zonder dat 'ie publiek staat). `scopePublished()` checkt is_published=true AND (published_at IS NULL OR published_at <= now()) — toekomstige datum = automatisch concept-onzichtbaar zonder migratie wanneer we later expliciete scheduling-UI willen.
32. **Routes admin-URL = platte `/admin/reisroutes/{route:slug}`**, niet genest onder Destination. Spatie Sluggable-default = globally unique slug, dus geen `scoped()`-binding nodig zoals bij Locations. Past bij de publieke URL `/reisroutes/{slug}` uit het masterplan. Vanaf een Destination-edit doorlinken kan via `?destination={slug}`-filter op de index.
33. **Routes hero = optioneel met fallback-keten via `Route::displayHeroUrl()`.** Probeert eigen `hero`-collectie → eerste-waypoint-`gallery`-foto → null. Caller toont placeholder bij null. Eén plek voor één ding (geen if-else in views). Sluit aan op het Destination-hero-patroon maar voegt de waypoint-fallback toe.
34. **Routes description = TipTap simple + Purifier simple.** Consistent met Pages. Bewuste afwijking van plain-textarea-overweging: extra implementatiekosten zijn drie regels (component bestaat), maar route-beschrijvingen profiteren van koppen/lijsten/links naar specifieke posts. KISS-textarea-met-later-upgrade is ontraden: migratie van plain→rich is duurder dan nu meteen rich kiezen.
35. **Routes waypoint-serialisatie = JSON in één hidden field.** Bron-van-waarheid is Alpine-state; `serialize()` schrijft een JSON-string in een hidden `<input name="waypoints">`. Form Request decodeert in `prepareForValidation()` naar een array waarop standaard array-validatie loopt. Robuuster dan dynamische `waypoints[N][location_id]`-arrays met DOM-rename bij reorder. Patroon bewezen via tagPills (Stap 4.5).
36. **Routes waypoint-sync = delete-then-recreate.** Eenvoudig, schoon, geen FK-/media-/comment-afhankelijkheden op waypoint-IDs. Mocht 't ooit relevant worden (waypoint-foto's bv.), refactor naar upsert. Combineert prima met het revisit-scenario (zie beslissing 39).
37. **Routes index-thumbnail = server-side SVG-polylijn (`<x-admin.route-thumb>`).** Lat/lng-bounds → genormaliseerde viewBox → inline SVG. Geen Leaflet-init per rij, geen tile-fetches. Upgrade-pad naar mini-Leaflet bij rij blijft open voor stap 9.X als de SVG visueel te karig blijkt — geen migratie nodig om te switchen.
38. **Routes Form Request = abstract base `RouteRequest` (Posts-patroon).** Aanvankelijk leunde ik naar Pages-patroon (twee onafhankelijke Requests). Tijdens uitwerken bleek dat vier stukken logica gedeeld moeten worden: `prepareForValidation()` (waypoints-JSON-decode), `withValidator()` (§3.4-equivalent: waypoints binnen bestemming), `publicationData()` (publication-state-derivatie), `messages()`. Drempel uit Posts-beslissing #25 wordt gehaald — abstract base levert hier echte DRY-winst. Verschil tussen Store en Update zit alleen in `authorize()` en `slugRules()`.
39. **Routes location-revisits = toegestaan.** Fase 3 voegde een `unique(route_id, location_id)`-constraint toe op `route_waypoints`, vermoedelijk by-default zonder bewuste afweging. In 4.8 gedropt via migratie omdat fly-in/fly-out roadtrips (Rome → Florence → Venetië → Rome) een echt use case zijn voor familiereizen. Geen vervangende index nodig (FK's dekken query-performance). Leaflet rendert revisit-routes prima als polylijn-loops.
40. **`Paginator::useBootstrapFive()` project-breed sinds Stap 4.9.** Latente bug sinds Fase 1 die nooit opviel omdat geen index >25 items had. Toegevoegd aan `AppServiceProvider::boot()` naast `Gate::before`. JSON-vertaling van `Showing/to/of/results` via `lang/nl.json` (HTML-entities voor `«`/`»` — leerpunt #49).

41. **Subscribers status afgeleid uit timestamps, geen status-kolom.** `pending|active|unsubscribed` via `Subscriber::status()`-methode op basis van `confirmed_at`/`unsubscribed_at`. Eén bron-van-waarheid, geen kolom-vs-timestamp-inconsistentie. Status-scopes (`pending()`, `active()`, `unsubscribed()`) leveren queries; constants `STATUS_PENDING|ACTIVE|UNSUBSCRIBED` voor matchen in code en views.

42. **Subscribers double-opt-in altijd, ook bij admin-add.** Geen "vertrouwd-shortcut" bij single create. AVG-zuiver. CSV-import landt expliciet op pending **zonder** automatische mail-dispatch — admin verstuurt later via per-rij of bulk-actie. Bewuste scheiding tussen import en mail-flow voorkomt 200-mail-tsunami's bij grote imports en geeft admin controle over timing.

43. **Uitgeschreven abonnees bij re-import silent gehonoreerd (geen reactivate).** Telt apart in aggregaat ("X eerder uitgeschreven") voor transparantie maar reset `unsubscribed_at` niet. AVG-conform: opt-out is een definitieve keuze tenzij de abonnee zich actief opnieuw aanmeldt via het publieke formulier.

44. **CSV-import + foutrapport-CSV via League\Csv.** Aggregaat-flash met vier tellers ('X nieuw · Y al bekend · Z eerder uitgeschreven · N ongeldig'). Foutregels in tijdelijke CSV op `local`-disk onder `imports/subscriber-errors/{ulid}.csv`, ULID-token in flash. Generieke flash-partial-uitbreiding: `flash_action_url` + `flash_action_label` keys voor herbruikbare 'Download foutrapport'-knop in alle toekomstige import/export-tools. Auto-purge na 24u uitgesteld naar Fase 6 (cron-config).

45. **Geen `dns`-rule op Subscriber email-validatie.** Bewust `email:rfc` zonder `dns` in zowel `StoreSubscriberRequest` als `UpdateSubscriberRequest` (consistent met de import-flow uit beslissing 42). Drie redenen: DNS-check is langzaam (50-200ms per request), flaky bij offline ontwikkeling en in test-environments, en de bounce van een confirmation-mail vangt non-existing domains sowieso af — als de mail niet aankomt blijft de abonnee gewoon pending.

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
- **`<x-admin.comment-actions>`** (Stap 4.7) — contextuele actie-knoppen voor een reactie (goedkeuren/afkeuren/spam/verwijderen). Knoppen verschijnen alleen wanneer de transitie zinvol is. Bootstrap-outline-knoppen, delete via bestaande `<x-admin.delete-button>` achter `@can('delete', ...)`.
- **`<x-admin.comment-status-badge>`** (Stap 4.7) — Bootstrap `badge text-bg-*` met label per status (pending=warning, approved=success, rejected=secondary, spam=danger). Volgt het inline-pattern van Pages, geen losse SCSS.
- **`App\Models\Concerns\HasAvatarFallback`** (Stap 4.7) — trait met `initials()` + `accentColor()`. Geëxtraheerd uit FamilyMember, ook gebruikt op User. Per model blijft een lokale `avatarUrl()` (verschillende collecties/conversies — `portrait/webp-300` op FamilyMember, `avatar/thumb` op User).


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
| **4.7**   | Comment-moderatie (state-machine, verb-route, avatar-refactor)                          | ✅ afgerond |
| **4.8**   | Routes + Waypoints CRUD                                                                 | ✅ afgerond |
| **4.9**   | Subscribers + import/export                                                             | ✅ afgerond |
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
- **Comment-moderatie** (`/admin/reacties`) — eerste state-machine-module: pending → approved/rejected/spam, plus hard-delete. Verb-routes (approve/reject/spam) i.p.v. één PATCH met status-veld; transitie-regel (`approved_at` zetten bij approved, wissen bij elke andere status) gecentraliseerd in `Comment::moderate(string $status)`. Tabel-index met statusfilter-dropdown inclusief tellers per status ("Te modereren (19)"), default-filter = pending wachtrij, server-side zoek op body + auteur, sorteerbare datumkolom, inline body-expand voor moderatiewerk. Avatar per regel via `<x-admin.avatar-initials>` op `size=32`. Contextuele actie-knoppen via `<x-admin.comment-actions>` (geen "goedkeuren" op iets dat al approved is, geen "afkeuren" buiten pending, etc.). `CommentPolicy` op `comments.moderate` (Admin+Editor) en `comments.delete` (idem). Geen bulk-acties (uitgesteld naar 4.13), geen admin-reply (Fase 5 frontend), spam = handmatig markeren. 16 Pest-tests. Aanvullend: `HasAvatarFallback`-trait geëxtraheerd uit FamilyMember en gedeeld met User, `<x-admin.avatar-initials>` generiek gemaakt (prop `:member` → `:subject`), beide modellen kregen een lokale `avatarUrl()`-methode die de juiste collectie+conversie kent.  
- **`<x-admin.route-thumb>`** (Stap 4.8) — inline-SVG route-mini-kaart. Neemt een collectie waypoints (met geladen `->location`), berekent bounds uit lat/lng, projecteert naar genormaliseerde SVG-coordinaten met aspect-preserving fit, rendert polylijn + dots. Geen JS, geen tiles, geen externe afhankelijkheden. Props: `waypoints`, `width=80`, `height=56`. Herbruikbaar overal waar je een "route-vorm hint" wil tonen (admin-index, straks publieke detail-cards in Fase 5).
- **`routeWaypoints` Alpine-factory** (Stap 4.8) — beheert een dynamische lijst van waypoints met `location_id` + `notes`, SortableJS-binding voor drag-reorder, live-filter op bestemming, JSON-serialisatie naar hidden field. Patroon: DOM-revert in SortableJS `onEnd` gevolgd door Alpine-array-mutation + force-notify via spread → voorkomt desync tussen DOM en model. Cross-component coördinatie met de Leaflet-preview-modal: `openMapPreview()` luistert op `shown.bs.modal` om kaart te instantiëren, `hidden.bs.modal` om op te ruimen.
- **`RouteController` + `RoutePolicy` + abstract `RouteRequest` met Store/Update-subklassen** (Stap 4.8) — eerste module die de Posts-Form-Request-architectuur uitbreidt voor een ander datadomein. `syncWaypoints()` delete-then-recreate, geen upsert. `handleHero()` consistent met Destination-patroon.
- **Leaflet-integratie in admin** (Stap 4.8) — `import L from 'leaflet'` + `import 'leaflet/dist/leaflet.css'` in `resources/js/admin.js`, plus standaard marker-icon-fix (`L.Icon.Default.mergeOptions({...})` met expliciete PNG-imports). `window.L = L` voor Alpine-factory-toegang. Klaar voor hergebruik in Fase 5 op publieke route-detail- en fotogalerij-pagina's.
- **Subscribers CRUD + CSV import/export** (`/admin/abonnees`) — eerste module met SMTP-mail-dispatch én eerste CSV-import-flow. Tabel-index met collapsible row-details (chevron-toggle via Alpine voor confirmation-token + timestamps), drie statusbadges (Wacht op bevestiging / Actief / Uitgeschreven) afgeleid uit timestamps, server-side filter/sort/paginate. Single create via `<x-admin.form-layout>` met dispatch van `SubscriberConfirmationMail` (Markdown, queued, double-opt-in) via `SendConfirmationMailAction`. Per-rij en bulk-acties ("Stuur naar alle X pending") als verb-routes, gerouteerd vóór `Route::resource()`. CSV-import via `league/csv` + `ImportSubscribersAction` met aggregaat-flash ('3 nieuw · 1 al bekend · 2 eerder uitgeschreven · 0 ongeldig') en download-knop voor foutrapport-CSV op `local`-disk via ULID-token. Generieke flash-partial-uitbreiding (`flash_action_url` + `flash_action_label`) voor herbruikbare download-knoppen in toekomstige import/export-tools. CSV-export respecteert status- en zoekfilter, kolom-shape identiek aan import-template zodat round-trip werkt. **Geen soft-delete** (AVG: opt-out is definitief). 37 Pest-tests (20 management + 17 import/export). Totaal nu **361 groene tests**.

Subzij-effecten in deze stap die het hele project raken:
- `Paginator::useBootstrapFive()` toegevoegd aan AppServiceProvider — eerste keer dat een index >25 rijen had, gat sinds Fase 1
- `lang/nl.json` aangemaakt voor JSON-translations (Showing/to/of/results — via HTML-entities voor `«`/`»`)
- Eerste `app/Mail/`-class in het project: `SubscriberConfirmationMail` met Markdown-template (`emails/subscribers/confirmation.blade.php`)
- Eerste werkende `queue:work` in dev — onthulde dat oude Spatie image-conversion jobs in de queue stonden (Fase 6 dev-config-pass)

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

39. **PowerShell parsert de regel vóór 't commando draait — speciale tekens in paden of argumenten zijn een mijnenveld.** `}` is een scriptblock-token (`Get-Item .\status}` faalt met ParserError); `{` `[` `]` `;` `&` `|` `$` zijn ook gevoelig. Defensieve standaard:
    - **Voor paden:** altijd `-LiteralPath '...'` op `Get-Item`/`Get-Content`/`Remove-Item`. Geen wildcard-/token-interpretatie.
    - **Voor PHP-snippets via `php artisan tinker --execute`:** dubbele buitenquotes + enkele PHP-binnenquotes + her-query inline (geen `$`-variabelen, want PowerShell expand die in dubbele quotes). `\"`-escapen is onbetrouwbaar, leidt vaak tot stil gestripte quotes en `Undefined constant`-fouten.
    - **Voor het schrijven van een compleet bestand vanuit een chat:** `@'...'@ | Set-Content path -Encoding UTF8`. De `-Encoding UTF8` is verplicht — zonder die flag krijg je UTF-16 of UTF-8-met-BOM en (zoals leerpunt #13) crasht PHP op de BOM.
    - **Voor multi-statement of variabele-zwaar werk:** een wegwerp `.php`-bestand. Niet vechten met `--execute` of here-string-piping naar Psy (Psy knipt op lege regels). Vervangt en breidt leerpunt #15 uit.

40. **`->for($model, 'relation')` met expliciete relatienaam voor élke niet-default factory-relatie.** Laravel leidt de relatienaam standaard af uit de modelnaam (`->for($user)` → `user()`). Werkt voor straight-named relaties, faalt stil met `Call to undefined method ...::user()` zodra een model die relatie heeft hernoemd. In dit project: Comment heeft `author()` (sinds Fase 3), Post heeft `author()` (sinds Stap 4.2). Eerder genoteerd in #2, maar dat leerpunt was Post-specifiek geformuleerd. Bredere regel: bij élk model met een hernoemde `belongsTo` mét een eigen FK-kolom (`user_id` → `author()`), is `->for($user, 'author')` verplicht in factories. Test-error is duidelijk wanneer je 'm kent, mysterieus wanneer je 'm niet kent (suite van 16 tests valt en masse op de eerste factory-call).

41. **`Set-Content` met here-string is de enige betrouwbare manier om vanuit een chat een PHP-bestand in PowerShell neer te zetten.** `(Get-Content … -Raw) -replace … | Set-Content` heeft een hoog risico op vermangelde escaping zodra de inhoud quotes bevat — bewezen toen `'->for($this->commenter, ''author'')'` per ongeluk een dwalende `'` invoegde tussen `factory()` en `->for`. Stel je bouwt of vervangt een PHP-bestand: schrijf 'm in z'n geheel via `@'...'@ | Set-Content path -Encoding UTF8` en sla het bestand-search-replace-traject over. Voor kleine, lokale wijzigingen in bestaande bestanden: gewone editor-actie of een gerichte tool, geen one-liner regex-vervanging.

42. **Pages-index gebruikt straight Bootstrap, geen `.admin-*` custom class-stelsel.** Toen ik de Route-index begon te bouwen, gokte ik op `.admin-table`, `.admin-filters`, `.admin-empty-state`, `.admin-pagination`, `.admin-page-header` als bestaande klassen — al die scss-namen bestaan niet. Het echte patroon: `<x-admin.page-header title="..." subtitle="...">` met `<x-slot:actions>`-slot voor de create-knop, `<x-admin.card>` als wrapper rond zowel filter-rij als tabel, en straight Bootstrap-utilities erbinnen (`d-flex gap-2 align-items-center flex-wrap` voor filter, `table table-hover align-middle mb-0` voor tabel, `text-end` voor actie-kolom, `text-muted small` voor meta-info). Alleen `.admin-breadcrumbs__current` is custom-styled. Cementeert leerpunt #9: grep een bestaande module van hetzelfde index-type vóór je een nieuwe view begint. Specifiek voor tabel-modules: leen van `pages/index.blade.php`, niet uit CLAUDE.md-vermeldingen.

43. **Alpine factory: gedestructureerde constructor-argumenten komen NIET automatisch op `this`.** Een factory zoals `function routeWaypoints({ initialWaypoints, locations, initialDestinationId })` heeft `locations` als variabele binnen de functie-scope, maar tenzij je 'm expliciet in `return { ... }` opneemt, bestaat 'ie niet op de Alpine-state. Symptoom: `this.locations` is `undefined`, getter-methods stil-falen (geen exception), UI rendert nul opties of leeg-string. Patroon: ELK genoemd argument moet ook in de return staan, ook al hernoem je 'm niet. Vorige factories werkten omdat ze single-argument waren of toevallig matchten met de juiste eerste-veld-naam. Bij meerdere argumenten wordt 't bug-gevoelig.

44. **Check component-prop-namen door de component-bron op te zoeken, niet door te gokken uit CLAUDE.md-vermeldingen.** Twee discoveries in stap 4.8: `<x-admin.sort-link>` heet de prop `sort` (de kolom-id), niet `column`; en `<x-admin.delete-button>` heeft géén `:confirm`-prop — de confirm-flow zit ingebakken in de component (`x-data="{ confirming: false }"`-toggle). Vorige modules werkten omdat ze de juiste prop-namen toevallig raakten. Patroon: `Get-Content -LiteralPath resources\views\components\admin\{naam}.blade.php` als reflex zodra je 'n component gebruikt waarvan je de signature niet recent gezien hebt.

45. **Check Fase 3-`unique`-constraints tegen actueel module-gebruik vóór je 'n CRUD opent.** `route_waypoints.unique(route_id, location_id)` werd in Fase 3 toegevoegd, vermoedelijk by-default zonder afweging tegen het werkelijke domein. Bleek pas tijdens 4.8 te conflicteren met fly-in/fly-out roadtrips (start+eindig op zelfde locatie). Brede les: bij elke module-implementatie checken welke constraints uit voorgaande fasen het gebruik beperken, en bij twijfel liever droppen — Eloquent dwingt identiteit-via-PK al af. Vergelijkbare risico's verwacht ik bij Subscribers (`unique(email)` — wel terecht) en Newsletter-Sends (`unique(newsletter_id, subscriber_id)` — wel terecht omdat herverzending via een aparte status moet, niet via een tweede row).

46. **Leaflet-integratie in Vite vereist twee fixes.** Eén: marker-iconen breken zonder expliciete PNG-imports, fix is `delete L.Icon.Default.prototype._getIconUrl;` + `L.Icon.Default.mergeOptions({ iconRetinaUrl, iconUrl, shadowUrl })` met de drie PNG's via Vite-imports. Twee: Leaflet rendert verkeerd (leeg/grijs canvas) als 't in een hidden container instantieert — `L.map()` aanroepen in een Bootstrap-modal die nog niet zichtbaar is geeft een dood canvas. Fix: luister op `shown.bs.modal`-event vóór `L.map()`, en cleanup op `hidden.bs.modal` via `.remove()`. Patroon herbruikbaar voor publieke fullscreen-kaart-modals in Fase 5.

47. **SortableJS + Alpine sync-pattern: revert DOM, re-render uit model.** SortableJS muteert DOM direct bij drag-end, wat desync veroorzaakt met de Alpine `x-for`-render (Alpine ziet de DOM-mutatie als out-of-band, snapt 'm niet, en bij volgende update herschikt 'ie alles fout). Patroon in `onEnd`-callback: eerst de DOM-mutatie terugdraaien door het item op `event.oldIndex` terug te plaatsen, DAN de Alpine-array herordenen via splice → triggert `x-for` herrender vanaf het model, beide weer in sync. Force-notify met `this.array = [...this.array]` voor zekerheid bij oude Alpine-versies die nested mutations soms missen. Werkt voor élke SortableJS-Alpine-combinatie (toekomstige Categories-orderbar, Pages-orderbar, Family-Members-orderbar).

48. **Framework-defaults uit eerdere fasen falen stil tot een nieuwe module ze triggert.** Paginatie >1 pagina toonde pas in Stap 4.9 (Abonnees was eerste 25+ rij-module) dat `Paginator::useBootstrapFive()` ontbrak — zat al sinds Fase 1 als latente bug. Eerste keer `queue:work` draaien toonde dat 2 weken aan Spatie image-conversion jobs in de queue stonden te wachten. Patroon-zwager van leerpunt #45 (route_waypoints unique constraint). Bij elke nieuwe module: niet alleen module-specifieke gaten checken, ook of de nieuwe schaal/data-volume framework-defaults eindelijk onthult. Mogelijke kandidaten voor toekomstige modules: cache-invalidatie op grote response-cache, soft-delete >30 dagen scenario, e-mail attachment-size in Mailable, fulltext-search bij grote post-aantallen.

49. **Non-ASCII in PowerShell here-strings = mojibake door console-codepage.** `«`/`»`/`é`/`'` direct plakken in een PowerShell here-string wordt door de Windows-console gemangeld vóór de data in een variabele belandt — `WriteAllText` schrijft daarna keurig UTF-8 van al-corrupte bytes. Drie werkende routes: (a) gebruik HTML-entities (`&laquo;`/`&raquo;`) — sowieso wenselijk voor lang-bestanden zodat ze ASCII-only blijven; (b) edit direct in VS Code waar encoding-controle wel werkbaar is; (c) JSON-files lossen 't sowieso op (JSON-delimiters zijn ASCII). Voor langere PHP/Blade-bestanden die in chats geconstrueerd worden: bouw 't bestand direct in VS Code op via copy-paste, niet via PowerShell here-string — die struikelt regelmatig over speciale tekens of subtiele whitespace en cap't bestanden mid-file (zoals gebeurde bij `SubscriberManagementTest.php` op regel 222). Patroon-uitbreiding van leerpunt #39.

50. **Cryptische "Call to a member function all() on array" treedt op bij URL-mismatch in `assertRedirect()`, niet alleen bij ontbrekende Accept-header (#28).** Wanneer een Form Request validatie faalt EN de actual redirect-URL niet matched met de URL in `assertRedirect(...)`, probeert Laravel intern een nuttige foutmelding op te bouwen waarbij 't struikelt over de session-error-bag-structuur. Diagnose-aanpak: `$response->dumpSession()->dump()` toont onmiddellijk of validatie faalt + waar de redirect heen ging. Zonder die dump interpreteer je 't symptoom als infrastructuur-issue terwijl 't gewoon een validatie-error is. Specifiek triggert dit bij `email:rfc,dns`-validatie op domeinen zonder MX-records — in tests is DNS-resolving niet altijd beschikbaar, dus tests met handgemaakte e-mails moeten test-safe domains gebruiken (`example.com` via RFC2606), of de Form Request laat `dns` weg (zie beslissing 45).

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

## Volgende concrete actie — Stap 4.10: Newsletter compose & dispatch

Stap 4.9 is afgerond (Subscribers CRUD + CSV import/export, 37 nieuwe Pest-tests, project-brede paginator-fix). Subscriber-lijst is nu gevuld → Newsletter heeft een doelgroep om naar te dispatchen.

Stap 4.10 pakt de `newsletters`-tabel uit Fase 3: TipTap-simple compose-editor (zelfde profiel als Pages, beslissing 5), drie vaste templates (`announcement`, `digest`, `plain` — beslissing 8), Emogrifier voor inline CSS, batch-50 queued dispatch via Laravel queue. Newsletter-Send-status per Subscriber zodat 'n nieuwsbrief twee keer naar dezelfde persoon dispatchen niet mogelijk is (de `unique(newsletter_id, subscriber_id)`-constraint op `newsletter_sends` uit Fase 3 is hier wél terecht — zie leerpunt #45/#48 over wanneer constraints uit eerdere fasen wel/niet kloppen).

Open vragen voor vooraf:

1. **Compose-editor scope** — TipTap simple is de bedoeling (consistent met Pages). Maar Newsletters hebben mogelijk image-embedding nodig (header-banner, foto van laatste reis). Wel image-picker zoals Posts (Stap 4.6), niet, of alleen via een aparte 'header_image'-veld op het Newsletter-model?
2. **Templates** — drie templates uit beslissing 8. Hoe configureerbaar moet de admin ze maken? Hardcoded Blade-files (KISS), of database-driven met preview? Lean: hardcoded Blade voor v1, later DB-driven als er behoefte komt.
3. **Test-modus** — kan de admin een test-mail naar zichzelf sturen vóór de echte dispatch? Of komt dat in v2?
4. **Audit-trail** — `newsletter_sends`-tabel houdt timestamps bij per dispatch. Toon de admin een 'aankomstrapport' (X verzonden, Y gebounced, Z geopend)? Of alleen verzonden-status? Lean: alleen verzonden voor v1, opens/clicks vereisen tracking-pixels die we sowieso uitstellen.
5. **Pre-dispatch confirmation** — vraagt de UI om bevestiging vóór 'n nieuwsbrief naar 200+ abonnees gaat? Lean: ja, modale bevestiging met aantal recipients.
