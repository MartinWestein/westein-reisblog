# Fase 5 — Ontwikkeling openbare pagina's

**Schoon bouwplan, herhaalbaar van scratch**
Versie: 1.0 — opgesteld na afronding op 23 augustus 2026

> Dit document beschrijft hoe Fase 5 uiteindelijk gebouwd is, in zeven stappen
> (5.0 t/m 5.6) met hun sub-blokken, zonder de zijwegen en debug-sessies
> onderweg. Per stap: doel, beslissingen (F5-x-refs), key infrastructuur, en
> test-coverage. Voor implementatie-details staat de code in de commits; voor
> conventies en landmines zie `CLAUDE.md`.
>
> **Voorwaarde:** Fase 4 is afgerond en gemerged naar `main`. Het volledige
> admin-paneel bestaat (CRUD op alle content, engagement en beheer). Suite
> draait groen op 526 tests. Alle content-modellen, factories en seeders zijn
> er; de datalaag voor subscribers (double-opt-in-tokens) en de vier
> `Actions\Subscribers\*` bestaan al uit Fase 4.
>
> **Design:** Voorstel B "Modern magazine" (Fase 1). Achtergrond `#F8F6F2`,
> tekst `#14213D`, headings Playfair Display, body Inter, accenten perzik
> `#E8A87C` / salie-groen `#41B3A3` / rosé `#C38D9E`.

---

## Inhoudsopgave

1. [Doelstelling Fase 5](#doelstelling-fase-5)
2. [Fase 5 op één pagina](#fase-5-op-één-pagina)
3. [Stap 5.0 — Fundament: layout, navigatie, account, homepage](#stap-50--fundament-layout-navigatie-account-homepage)
4. [Stap 5.1 — Bestemmingen + locaties](#stap-51--bestemmingen--locaties)
5. [Stap 5.2 — Posts + comments + blog-index + reistips](#stap-52--posts--comments--blog-index--reistips)
6. [Stap 5.3 — Reisroutes + fotogalerij](#stap-53--reisroutes--fotogalerij)
7. [Stap 5.4 — Auteurs + statische pagina's + contact](#stap-54--auteurs--statische-paginas--contact)
8. [Stap 5.5 — Newsletter + publieke unsubscribe](#stap-55--newsletter--publieke-unsubscribe)
9. [Stap 5.6 — Eindcheck (deze oplevering)](#stap-56--eindcheck-deze-oplevering)
10. [Fase 5 — leerpunten voor Fase 6](#fase-5--leerpunten-voor-fase-6)

---

## Doelstelling Fase 5

De volledige publieke voorkant van de reisblog, server-side gerenderd met Blade
+ Bootstrap + Alpine, geoptimaliseerd voor SEO en leesbaarheid voor een
multi-generatie publiek. Elke content-soort uit het admin-paneel krijgt zijn
publieke tegenhanger: homepage, bestemmingen, locaties, verhalen, reistips,
reisroutes met Leaflet-kaarten, fotogalerij met lightbox, auteurspagina's,
statische pagina's, contact en nieuwsbrief-aanmelding met double-opt-in. Alles
onder de "Modern magazine"-designtaal. Geen SPA. Suite gegroeid van 526 → 693.

## Fase 5 op één pagina

| Stap         | Inhoud                                                                             | Suite     |
| ------------ | ---------------------------------------------------------------------------------- | --------- |
| **5.0.a**    | Publieke layout + site-nav + blog-nav + footer                                     | 526 → 526 |
| **5.0.b**    | `/mijn-account` met geïntegreerde 2FA                                              | 526 → 542 |
| **5.0.c**    | Homepage + welcome-vervanging + ExampleTest opruimen                               | 542 → 553 |
| **5.0.d**    | Sessies-invalidatie (F4-U18) bij email-change door admin                           | 553 → 553 |
| **5.1.a**    | DemoContentSeeder verrijken + fixture-images + is_featured data-laag               | 553 → 553 |
| **5.1.b**    | is_featured admin-toggle UX (Destination + Route + Post)                           | 553 → 571 |
| **5.1.c**    | `/bestemmingen` publieke index                                                     | 571 → 577 |
| **5.1.d**    | `/bestemmingen/{destination}` detail                                               | 577 → 584 |
| **5.1.e-i**  | `/bestemmingen/{destination}/{location}` detail (statisch + breadcrumb)            | 584 → 593 |
| **5.1.e-ii** | Leaflet-kaart op location-detail                                                   | 593 → 595 |
| **5.2.0**    | Blocker-chore: `scopePublished()` + post-content + reistips-seeding                | 595 → 595 |
| **5.2.a**    | Blog-index `/verhalen` + post-detail-fundament + `url()`-helper                    | 595 → 614 |
| **5.2.b**    | Post-detail afmaken (hero/breadcrumb/SEO) + comments (threaded + honeypot)         | 614 → 633 |
| **5.2.c**    | Reistips-view `/reistips` + cross-linking destination → tips                       | 633 → 644 |
| **5.3.0**    | Blocker-chore: routes publiceren                                                   | 644 → 644 |
| **5.3.a**    | `/reisroutes`-index + kale route-detail                                            | 644 → 655 |
| **5.3.b**    | Route-detail compleet: Leaflet-polylijn + waypoint-links + verhalen-strook         | 655 → 659 |
| **5.3.c**    | Fotogalerij `/fotos` + progressive filters + Alpine-lightbox                       | 659 → 665 |
| **5.4.0**    | Blocker-chore: content (bio's + page-body's) + seeder-consolidatie                 | 665 → 665 |
| **5.4.a**    | Auteurs `/auteurs/{slug}` + Over ons `/over-ons`                                   | 665 → 673 |
| **5.4.b-i**  | Statische pagina's via catch-all `/{page:slug}`                                    | 673 → 679 |
| **5.4.b-ii** | Contactformulier `/contact` (honeypot + throttle, mail-only)                       | 679 → 682 |
| **5.5.a**    | Nieuwsbrief-aanmelding + double-opt-in-bevestiging (`/nieuwsbrief`)                | 682 → 690 |
| **5.5.b**    | Publieke unsubscribe `/nieuwsbrief/uitschrijven/{token}` (sluit F4-N11)            | 690 → 693 |
| **5.6**      | Eindcheck + `fase-5-bouwplan.md`                                                   | 693       |

**Designtaal (Fase 1, Voorstel B):** Modern magazine — edge-to-edge fotografie,
Playfair-koppen, Inter-body, zandbeige achtergrond, perzik-accent.

**Kernprincipes:** server-side Blade (geen SPA), herbruikbare Blade-componenten,
`$model->url()`-helpers voor canonieke URL's, publieke controllers los van de
`Admin\`-namespace, honeypot + throttle op alle open POST-formulieren, scoped
flash (de publieke layout heeft geen globale flash-partial).

---

## Stap 5.0 — Fundament: layout, navigatie, account, homepage

**Doel:** De publieke schil neerzetten waar alle latere pagina's in landen: een
gedeelde layout, twee-lagen-navigatie (ml-westein site-nav boven, blog-nav
eronder), footer, een `/mijn-account`-pagina met 2FA, en een echte homepage die
`welcome.blade.php` vervangt.

**Sub-blok-opdeling (F5-2):** a (layout+nav+footer), b (/mijn-account), c
(homepage + welcome-cleanup), d (F4-U18 sessies-invalidatie).

**Beslissingen die hier landen:**
- F5-1 (Fase 5 opgedeeld in 7 stappen; cookie-banner/Analytics/SEO/sitemap/RSS → Fase 6)
- F5-3 t/m F5-11 (navigatie-structuur: hoofdmenu-items, site-nav als partial met hardcoded items, A-hybrid SCSS-strategie, logo via absolute URL, twee-lagen-scheiding, blog-nav macro + kleur, auth-state dropdown, `/dashboard` verwijderd → iedereen na login naar `/`)
- F5-12 t/m F5-15 (`/mijn-account`: naam editable, email/rol read-only, wachtwoord + 2FA geïntegreerd, één lange pagina met drie kaarten, 2FA-state automatisch uit user-model)
- F5-16 t/m F5-20 (styling: perzik-accent uit Fase-1-tokens, Bootstrap `$primary` blijft blauw, Fase-1 dead code weg, footer drie-koloms)
- F5-21 t/m F5-23 (homepage: hero + featured destination + laatste posts + featured routes + CTA-strook; "featured" zonder flag via `latest()`)
- F5-24 (sessies-invalidatie bij email-change via `DB::table('sessions')->where('user_id')->delete()`, vereist `SESSION_DRIVER=database`)

**Key infrastructuur:**
- `resources/views/layouts/public.blade.php` — publieke layout met title/meta-conventie (`@section('title')`/`@section('meta_description')` + fallbacks), stacks voor head/modals/scripts
- `partials/site-nav.blade.php` (gedeelde ml-westein-nav, CSS-vars gescoped naar `.main-nav`), `partials/blog-nav.blade.php` (dark navy, tekst-brand + profiel-dropdown), `partials/footer.blade.php` (drie kolommen)
- `HomeController` + `home.blade.php` (vervangt `welcome.blade.php`)
- `AccountController` (`show` + `updateProfile` — alleen `name`; wachtwoord via Fortify; 2FA-kaart-state uit `two_factor_secret`/`two_factor_confirmed_at`)
- SCSS-partials in `resources/scss/public/`: `_layout`, `_site-nav`, `_blog-nav`, `_footer`, `_account`, `_home`. Utility-classes `.section-label`, `.section-title`, `.btn-accent`, `.post-card`, `.route-card`.

**Landmines:** `.navbar-expand-lg` verplicht bij `.collapse.navbar-collapse`; `welcome.blade.php` met inline Tailwind faalde zonder `npm run dev` (opgelost door eigen homepage); Fortify's `updateProfileInformation` accepteert standaard email (eigen `AccountController::updateProfile` die alleen `name` valideert); Fortify wachtwoord-errors in aparte error-bag `updatePassword`.

**Tests:** 526 → 553.

---

## Stap 5.1 — Bestemmingen + locaties

**Doel:** De bestemmingen-hiërarchie publiek maken: index, destination-detail, en
location-detail met bento-fotogalerij en Leaflet-kaart. Voorafgegaan door een
data-blokker (seeder verrijken + fixture-foto's) en een `is_featured`-datalaag.

**Sub-blok-opdeling (F5-29):** 5.1.a (data + fixtures + is_featured data-laag),
5.1.b (is_featured admin-toggle UX per model), 5.1.c (index), 5.1.d
(destination-detail), 5.1.e-i (location-detail statisch), 5.1.e-ii (Leaflet).

**Beslissingen die hier landen:**
- F5-25 t/m F5-32 (data-blokker: gecommitte Pexels-fixtures i.p.v. runtime-fetch, 6 destinations / 14 locations, 62 foto's, `is_featured` op Destination/Route/Post — Location bewust uitgesloten, meerdere featured toegestaan, 30 posts / 6 routes in seeder)
- F5-33/F5-34 (is_featured admin-toggle per model; badge-conventie `.badge.bg-warning.text-dark` + ster-icoon, "Uitgelicht")
- F5-35 t/m F5-39 (index: uniforme 3-koloms grid, ster-badge + perzik-outline op featured, kaart-inhoud, sortering `is_featured` dan `created_at`, geen country-meta, één sub-blok)
- F5-40 t/m F5-48 (destination-detail: edge-to-edge 2:1 hero, location-tegels foto-first, 3-koloms locations-grid, terug-CTA, SEO-metadata-conventie, descriptions verrijkt)
- F5-49 t/m F5-57 (location-detail statisch: aparte `LocationController` met `scopeBindings()`, edge-to-edge hero uit `gallery[0]`, bento-masonry-gallery 1-groot-3-klein, breadcrumb-component site-breed + retrofit destination-detail, SEO-title met em-dash-uitzondering)
- F5-58 t/m F5-65 (Leaflet: vanilla-JS-module `leaflet-location.js` met statische import + DOM-guard, default-marker + Vite-PNG-fix, OSM-tiles, permanente tooltip, scroll-wheel-zoom uit, test-strategy = coördinaten in de DOM)

**Key infrastructuur:**
- Publieke `DestinationController` (`index` + `show`) + `LocationController` (`show`, `scopeBindings()`)
- `<x-public.breadcrumb :items>` (site-brede conventie), `.destination-card`, `.location-card`, bento-`.location-gallery`
- `resources/js/leaflet-location.js` (herbruikbaar publiek Leaflet-init-patroon)
- Media-URL fallback-keten `large ?: medium ?: original` voor edge-to-edge hero's

**Landmines:** Faker-locale localiseert alleen data-methodes, niet `paragraph()`/`sentence()` (Lorem-valkuil → descriptions handmatig verrijkt); fixture-media-attach vereist `->preservingOriginal()` (anders verhuist het bronbestand); `firstOrCreate` is idempotent (nieuwe seeder-waarden vereisen `migrate:fresh --seed`); Vite-PNG-marker-fix nodig bij élke `import 'leaflet'`; Herd PHP CLI `memory_limit` 128M te laag voor GD-seeding; Blade-echo's op aparte regels breken `assertSee`-substrings.

**Tests:** 553 → 595.

---

## Stap 5.2 — Posts + comments + blog-index + reistips

**Doel:** Het hart van de blog: publieke blog-index, volledige post-detail met
TipTap-rendering + hero + gerelateerde posts, een threaded comments-systeem voor
ingelogde lezers, en een aparte reistips-categorie-view.

**Sub-blok-opdeling (F5-66, herzien F5-73):** 5.2.0 (blocker-chore), 5.2.a
(blog-index + post-detail-fundament + `url()`), 5.2.b (post-detail afmaken +
comments, twee commits), 5.2.c (reistips-view + cross-linking).

**Beslissingen die hier landen:**
- F5-67 t/m F5-69 (blocker-chore: `scopePublished()` dubbele check, content-verrijking van 30 posts, 5 reistips geseed — 3 bestemming-gebonden + 2 algemeen)
- F5-70 t/m F5-79 (blog-index op `/verhalen`; `url()`-model-methode met drie takken; canonieke tip-URL categorie-leidend; contentmodel destination-paraplu → verhalen aan location; `<x-public.post-card>`; index chronologisch zonder featured-voorrang; published-enforcement via controller-`abort_if`; één publieke `PostController` met `index`/`show`/`showTip`; "Verhalen"-nav-item)
- F5-80 t/m F5-90 (post-detail: edge-to-edge hero + `large`-conversie, breadcrumb spiegelt canonieke URL, SEO via override-kolommen, body-rendering via purify-at-save, gerelateerde posts per-type; comments: 1-niveau-threading, eigen pending zichtbaar, uitgelogd inlog-oproep, oudste-eerst, write-path `POST /reacties/{post:slug}` met honeypot)
- F5-91 t/m F5-96 (reistips-view: `indexTips()` op de publieke PostController, hergebruik post-card + post-grid, één chronologisch grid, tips geweerd uit `/verhalen`, nav + breadcrumb levend, cross-linking destination-detail → tips)
- F5-97 (hero-verfijning: `max-height: 62vh` op de drie detail-hero's)

**Key infrastructuur:**
- `$post->url()` + `$post->isPublished()` (model-methodes, delen waarheid met `scopePublished()`)
- Publieke `PostController` (`index`/`show`/`showTip`/`indexTips` + private `renderDetail`/`visibleComments`/`relatedPosts`)
- `CommentController@store` + `StoreCommentRequest` (honeypot, error-bag `comment`)
- `<x-public.post-card>`, `<x-public.comment>`, `<x-public.comment-form>`
- Routes: `posts.index` (`/verhalen`), `posts.show` (3-segment, `scopeBindings()`), `reistips.index`/`reistips.show`, `comments.store`

**Landmines:** FormRequest onder verkeerde namespace/map → `ReflectionException` (PSR-4 map ≠ namespace); honeypot staat in tests default AAN (per-file uitzetten); publieke layout rendert geen globale flash (scoped `comment_success`); `/verhalen` en `/reistips` wederzijds exclusief gefilterd (`whereDoesntHave`/`whereHas`).

**Tests:** 595 → 644.

---

## Stap 5.3 — Reisroutes + fotogalerij

**Doel:** Publieke reisroutes met Leaflet-polylijn over genummerde waypoints, en
een fotogalerij met progressive filters + Alpine-lightbox.

**Sub-blok-opdeling (F5-98):** 5.3.0 (publish-chore), 5.3.a (index + kale
detail), 5.3.b (detail compleet met Leaflet), 5.3.c (fotogalerij + lightbox).

**Beslissingen die hier landen:**
- F5-99 (blocker-chore: alle 6 seeder-routes publiceren; `published_at=travel_date`)
- F5-100 t/m F5-103 (index + kale detail samen; sortering `is_featured` dan `travel_date` + ster-badge; `<x-public.route-card>` geëxtraheerd; Route.hero-conversies gealigneerd naar thumb/medium/large + `isPublished()`)
- F5-104/F5-105 (route-detail: `leaflet-route.js` met genummerde divIcon-markers + polylijn + `fitBounds`, waypoint-links met pivot-notes; "Verhalen van deze reis"-strook)
- F5-106/F5-107 (fotogalerij: eigen Alpine-lightbox `photo-lightbox.js` als progressive enhancement; progressive bestemming/locatie-pills via querystring; bron = location-`gallery`-collecties)

**Key infrastructuur:**
- Publieke `RouteController` (`index`/`show` + `relatedPosts`) + `PhotoController` (`index`)
- `<x-public.route-card>`, `resources/js/leaflet-route.js`, `resources/js/photo-lightbox.js`
- Routes: `reisroutes.index`/`reisroutes.show`, `fotos.index`

**Landmines:** `route(...)` vereist de slug-kolom in de eager-load-select; een Leaflet-container heeft een expliciete CSS-hoogte nodig; `@json($x)` in een HTML-attribuut is veilig (HEX-flags); media in tests via `Storage::fake('public')` + `UploadedFile::fake()->image()`.

**Tests:** 644 → 665.

---

## Stap 5.4 — Auteurs + statische pagina's + contact

**Doel:** Auteurspagina's per familielid, een "Over ons"-pagina, generieke
statische pagina's via een catch-all, en een open contactformulier. Contact is
bewust vanuit 5.5 naar voren gehaald.

**Sub-blok-opdeling (F5-108):** 5.4.0 (blocker-chore), 5.4.a (auteurs + Over
ons), 5.4.b-i (statische pagina's via catch-all), 5.4.b-ii (contactformulier).

**Beslissingen die hier landen:**
- F5-109/F5-110 (auteurs op FamilyMember met `user_id`-brug; Over ons eigen route die de `over-ons`-Page als bewerkbare intro leest + FamilyMembers-grid)
- F5-111 (statische pagina's via single-segment GET-catch-all `/{page:slug}` met negatieve-lookahead-constraint op `reserved_slugs`; correctie op de oude "catch-all laatste"-aanname omdat `admin.php` ná `web.php` laadt)
- F5-112/F5-113 (contact naar voren gehaald; open form, honeypot + `throttle:6,1`, `ContactMail` queued reply-to afzender, mail-only geen DB-opslag, scoped `contact_success`-flash)
- F5-114 (portretten: initialen-fallback in dev, geen stockfoto's voor echte personen)
- F5-115 (auteur-pagina toont volledige gepagineerde verhalenlijst)
- F5-116 (blocker-chore: bio's + page-body's van Lorem naar echt NL; dubbele FamilyMember-seeder geconsolideerd 8 → 4)

**Key infrastructuur:**
- `AuthorController` (`overview` + `show`), `PageController` (`show` via catch-all), `ContactController` (`show` + `send`)
- `<x-public.avatar>`, `Page::isPublished()`, `ContactMail` + `StoreContactRequest`
- Routes: `about` (`/over-ons`), `authors.show`, `pages.show` (catch-all, allerlaatste), `contact` + `contact.send`
- `reserved_slugs` uitgebreid met `verhalen`, `over-ons`, `contact`, `mijn-account`

**Landmines:** twee seeders die op verschillende keys `firstOrCreate`'en stapelen stil (dubbele records); een catch-all in `web.php` is NIET globaal laatste (`admin.php` laadt erna); `Route::fallback()` matcht élk pad voor GET (breekt 404-semantiek voor onbekende POSTs) → gekozen patroon is single-segment GET met reserved-slug-lookahead; `Mail::to()->send()` queue't automatisch bij `ShouldQueue` (`assertQueued`).

**Tests:** 665 → 682.

---

## Stap 5.5 — Newsletter + publieke unsubscribe

**Doel:** De publieke nieuwsbrief-kant: aanmelding met double-opt-in en een
publieke unsubscribe-route. Contact zat al in 5.4, dus 5.5 is puur nieuwsbrief.
Leunt volledig op de bestaande Fase-4-datalaag (`Subscriber` + tokens +
status-afleiding) en de vier `Actions\Subscribers\*` — geen model-, migratie-
of action-werk.

**Sub-blok-opdeling (F5-117):** 5.5.a (aanmelding + double-opt-in-bevestiging),
5.5.b (unsubscribe). Géén 5.5.0 content-chore (geen publiek-zichtbare
seeder-data; alleen inline UI-copy).

**Beslissingen die hier landen:**
- F5-117 (sub-blok-opdeling; geen chore; geen model-/migratie-/action-werk)
- F5-118 (eigen `/nieuwsbrief`-pagina spiegelt `/contact`; footer-link, geen hoofdnav-item; routes vóór de catch-all; `nieuwsbrief` was al reserved)
- F5-119 (publieke `SubscribeRequest` is unique-loos — `Rule::unique` weglaten tegen e-mail-enumeratie; `email:rfc` zonder dns)
- F5-120 (anti-enumeratie: altijd dezelfde generieke "check je inbox"-melding; `SubscribeAction` zet uitgeschreven adres bij publieke zelf-heraanmelding terug naar pending + verplichte herbevestiging — géén silent reactivate, F4-17-conform)
- F5-121 (eigen resultaatpagina's voor confirm + unsubscribe; confirm one-shot → neutrale melding bij tweede klik; unsubscribe idempotent → zelfde pagina; neutrale unsubscribe-tak biedt contact-link)
- F5-122 (spam/rate-limiting spiegelt contact: `throttle:6,1` + `ProtectAgainstSpam`; scoped `newsletter_success`-flash)
- F5-123 (sluit F4-N11: `/nieuwsbrief/uitschrijven/{token}` live, testmail-placeholder landt netjes; impliciet ook de confirm-placeholder uit `SubscriberConfirmationMail` die de admin al verstuurde)

**Key infrastructuur:**
- Publieke `NewsletterSubscriptionController` (`show`/`store`/`confirm`/`unsubscribe`)
- `App\Http\Requests\SubscribeRequest` (publiek, unique-loos)
- Views: `newsletter/show` (form + intro + scoped flash), `newsletter/confirmed`, `newsletter/unsubscribed` (beide met neutrale null-tak); SCSS `_newsletter.scss`
- Routes: `newsletter.show` (GET `/nieuwsbrief`), `newsletter.subscribe` (POST, throttle + honeypot), `newsletter.confirm`, `newsletter.unsubscribe`
- Footer: Nieuwsbrief-link in de Info-kolom

**Landmines:** `SubscriberConfirmationMail` bouwde de confirm- én unsubscribe-URL al hard op (twee levende placeholders vóór 5.5); `ConfirmSubscriptionAction` wist het token one-shot (tweede klik = onbekend token); `UnsubscribeAction` wist het token nooit (echt idempotent); Windows git auto-gc lockt tijdens `git commit` (commit slaagt, gc-opruiming faalt — `git config gc.auto 0` of `.git` uit Defender).

**Tests:** 682 → 693.

---

## Stap 5.6 — Eindcheck (deze oplevering)

**Doel:** Fase 5 formeel afsluiten. Publieke site verifiëren op consistentie en
dode links, dit bouwplan schrijven, en CLAUDE.md's roadmap op ✅ zetten. Bewust
géén cleanups: de opgespaarde loose-ends (flash-key-inconsistentie in
admin-controllers, lege `resources/views/public/`-dir, Tailwind uit
`package.json`, Sass-`@use`-migratie, import-conventie-inconsistentie) schuiven
door naar Fase 6, waar ze thuishoren (admin-scope + build/tooling). Prioriteit
ligt op naar-live-gaan, niet op polijsten.

**Uitvoering:**
1. Eindcheck-verificatiepass: `route:list` volledig, nav/footer-links, `reserved_slugs` compleet, alle publieke pagina's laden, contact- en nieuwsbrief-mail komen aan (queue-worker). Alleen zero-risk fixes meepakken.
2. `fase-5-bouwplan.md` opgeleverd (dit document).
3. CLAUDE.md: roadmap Fase 5 op ✅, 5.6-regel toegevoegd; cleanup-triage vastgelegd.
4. Docs-commit + push.

**Definition of Done Fase 5:**
- [x] Alle zeven stappen (5.0 t/m 5.6) opgeleverd
- [x] Beslissingen F5-1 t/m F5-123 chronologisch vastgelegd in CLAUDE.md
- [x] Suite 693 groen (1750 assertions), van 526 baseline aan begin Fase 5
- [x] Alle publieke content-soorten hebben hun voorkant; F4-N11 gesloten
- [x] `fase-5-bouwplan.md` opgeleverd
- [ ] Cleanups getrieerd → Fase 6

---

## Fase 5 — leerpunten voor Fase 6

### Werkstijl-patronen die zichzelf hebben bewezen

1. **Blocker-chore als eigen sub-blok (5.x.0).** Vier keer (5.1.a, 5.2.0, 5.3.0, 5.4.0) bleek data/content-voorbereiding een blokker vóór de views gebouwd konden worden. Apart committen houdt data- en code-wijzigingen schoon gescheiden.
2. **State-check als allereerste stap van elke sessie.** `git log`/`git status`/`php artisan test` + module-specifieke inspectie, vóór ontwerp-vragen. Voorkwam meermaals bouwen op verkeerde aannames — o.a. de ontdekking in 5.5 dat de complete datalaag + Actions al bestonden.
3. **Design-vragen één voor één, met stated lean + rationale.** Sequentiële F5-prefixen, vastgelegd in CLAUDE.md aan sessie-einde.
4. **"Elk sub-blok een compleet-werkend, groen geheel."** Zelfs bij splitsen (index + kale detail samen, F5-73/F5-100) blijft `main` altijd demo-baar.
5. **Bestaand patroon lenen vóór nieuw verzinnen.** De contact-write-path (5.4.b-ii) werd de leen-basis voor de nieuwsbrief-aanmelding (5.5); `<x-public.post-card>` en `<x-public.breadcrumb>` werden site-breed hergebruikt.

### Architectuur-patronen die zichzelf hebben bewezen

1. **`$model->url()` + `$model->isPublished()`-helpers** die de waarheid delen met de scopes — canonieke URL's en 404-checks divergeren niet.
2. **Publieke controllers los van `Admin\`-namespace**, zelfde modellen.
3. **Scoped flash** (`contact_success`, `newsletter_success`, `comment_success`) omdat de publieke layout geen globale flash-partial heeft.
4. **Honeypot + `throttle:6,1` op elk open POST-formulier** (comments, contact, nieuwsbrief).
5. **Vanilla-JS Leaflet-modules met DOM-guard + statische import** (location- en route-kaart) — geen Alpine-in-modal-complexiteit nodig voor read-only kaarten.
6. **Catch-all met reserved-slug-lookahead** voor generieke Pages, robuust tegen de laadvolgorde van `web.php`/`admin.php`.

### Openstaande cleanups voor Fase 6

- **Flash-key-inconsistentie** in admin-controllers (Destination/Location/Comment gebruiken `->with('status', ...)` terwijl de partial alleen `success/error/info/warning` rendert → stille onzichtbare meldingen). Kleine maar echte bug; hoogste prioriteit van de cleanups.
- Lege `resources/views/public/`-dir (Fase-1-scaffolding-restant).
- Tailwind 4.0 uit `package.json` (Laravel-11-scaffold-restant; project gebruikt Bootstrap).
- Sass-`@use`-migratie (honderden deprecation-warnings; Bootstrap 5.3 SCSS niet forward-compatible).
- Import-conventie-inconsistentie (underscore-prefix in sommige `@import 'public/...'`).

### Wat Fase 6 verder brengt (masterplan §5 + §8)

Spatie SEO meta-tags + Open Graph + JSON-LD, sitemap + `robots.txt`, RSS-feed,
response-cache met model-event-invalidatie, WebP-optimalisatie, cookie-banner +
analytics-afweging, Lighthouse + WCAG-check, en de **productie-deploy** naar NL
shared hosting (prod-`.env`, `migrate --force`, config/route/view-cache,
`storage:link`, queue-worker via cron, HTTPS, backups). De hero-intro-teksten
(F5-22-placeholder) en de `/verhalen`-/`/reistips`-intro's verfijnt Martin nog.

---

*Einde Fase 5 bouwplan — versie 1.0*
