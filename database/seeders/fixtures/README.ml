# Fixture-images voor demo-content

Deze directory bevat gecommitte foto's die de `DemoContentSeeder` gebruikt om
destinations en locations van beeld te voorzien. Alle foto's van
[Unsplash](https://unsplash.com); zie [`CREDITS.md`](CREDITS.md) voor
fotograaf-attributies.

## Waarom gecommit

- **Offline-veilig**: `php artisan migrate:fresh --seed` mag geen netwerk-call
  worden. Externe fetch (Unsplash-API, Pexels) verplaatst betrouwbaarheid van
  de seed naar externe beschikbaarheid en API-keys.
- **Reproduceerbaar**: elke `fresh --seed` levert exact dezelfde demo-content.
- **Realistisch**: placeholder-vierkanten met "Location 1" ondermijnen precies
  wat Fase 5 wil valideren (magazine-esthetiek, edge-to-edge fotografie).

Fase 6 kan dit t.z.t. vervangen door eigen reisfoto's — nu is dit puur
dev-fixture voor tijdens de bouw van Fase 5.

## Directorystructuur

```
fixtures/
├── README.md              (dit bestand)
├── CREDITS.md             (fotograaf-attributies)
├── destinations/
│   ├── italie/
│   │   └── hero.jpg
│   ├── schotland/
│   │   └── hero.jpg
│   ├── slovenie/
│   │   └── hero.jpg
│   ├── canarische-eilanden/
│   │   └── hero.jpg
│   ├── duitsland/
│   │   └── hero.jpg
│   └── verenigde-staten/
│       └── hero.jpg
└── locations/
    ├── rome/
    │   ├── gallery-01.jpg
    │   ├── gallery-02.jpg
    │   ├── gallery-03.jpg
    │   └── gallery-04.jpg
    ├── florence/
    │   └── (4 files)
    ├── venetie/
    │   └── (4 files)
    ├── edinburgh/
    │   └── (4 files)
    ├── isle-of-skye/
    │   └── (4 files)
    ├── glencoe/
    │   └── (4 files)
    ├── ljubljana/
    │   └── (4 files)
    ├── bled/
    │   └── (4 files)
    ├── tenerife/
    │   └── (4 files)
    ├── lanzarote/
    │   └── (4 files)
    ├── berlijn/
    │   └── (4 files)
    ├── zwarte-woud/
    │   └── (4 files)
    ├── new-york/
    │   └── (4 files)
    └── miami/
        └── (4 files)
```

Totaal bij compleet: **6 hero + 56 gallery = 62 foto's** (~15-25 MB).

## Naamgeving-conventies

Kritiek voor cross-platform (Windows dev / Linux CI/prod):

- **Alles lowercase**: `hero.jpg`, niet `Hero.JPG` of `Hero.jpg`.
- **Extensie**: `.jpg` (lowercase). Niet `.jpeg`, niet `.JPG`.
- **Gallery-nummers**: twee cijfers, zero-padded: `gallery-01.jpg` t/m
  `gallery-04.jpg`. Niet `gallery-1.jpg`.
- **Slug-mappen**: identiek aan model-slugs uit `DemoContentSeeder`
  (`isle-of-skye`, niet `isleOfSkye`; `zwarte-woud`, niet `zwarte_woud`).

De seeder-hook doet `file_exists()` checks — bij een verkeerde naam wordt de
foto stil overgeslagen zonder foutmelding. Bij afwezigheid van verwachte
foto's: controleer eerst de casing.

## Seeder-gedrag

`DemoContentSeeder::attachDestinationHero()` en `attachLocationGallery()` zijn
idempotent:

- Als `file_exists()` false: skip stil.
- Als de collection al gevuld is: skip stil.
- Attach gebeurt via `->preservingOriginal()` — bronbestand blijft in
  `fixtures/`, wordt niet verplaatst naar `storage/app/public/`. Kritiek: zonder
  deze call is de fixtures-directory na eerste seed leeg.

Dus je kunt op elk moment een subset van de 62 foto's aanwezig hebben — de
seeder werkt gewoon en attacht wat er is.

## Shoppinglijst — volledig overzicht

Ontbrekende foto's op te halen op [Unsplash](https://unsplash.com) → klik
"Download" → kies "Large" (~2400px breed). License vereist geen attributie
maar we noteren fotograaf-namen in `CREDITS.md` voor provenance.

**Formaat**: JPG (Media Library maakt zelf WebP-conversies). Landscape-oriëntatie
werkt het beste in de magazine-layout; portrait mag voor gallery-foto's maar
overweeg spreiding.

### Destinations — 6 hero-foto's

| Bestand | Zoektermen op Unsplash |
|---|---|
| `destinations/italie/hero.jpg` | tuscany landscape, italian countryside, cypress hills |
| `destinations/schotland/hero.jpg` | scottish highlands, isle of skye, glencoe valley |
| `destinations/slovenie/hero.jpg` | lake bled slovenia, triglav, julian alps |
| `destinations/canarische-eilanden/hero.jpg` | teide tenerife, lanzarote volcano, canary islands |
| `destinations/duitsland/hero.jpg` | neuschwanstein, bavaria, black forest |
| `destinations/verenigde-staten/hero.jpg` | grand canyon, new york skyline, yosemite |

### Locations — 4 gallery-foto's elk

#### Italië

**Rome** — `locations/rome/`
- `gallery-01.jpg` — Colosseum
- `gallery-02.jpg` — Vaticaanstad / Sint-Pietersplein
- `gallery-03.jpg` — Trevifontein
- `gallery-04.jpg` — Piazza Navona of Spaanse Trappen

**Florence** — `locations/florence/`
- `gallery-01.jpg` — Duomo (Kathedraal)
- `gallery-02.jpg` — Ponte Vecchio
- `gallery-03.jpg` — Palazzo Vecchio of Uffizi
- `gallery-04.jpg` — Uitzicht vanaf Piazzale Michelangelo

**Venetië** — `locations/venetie/`
- `gallery-01.jpg` — Canale Grande
- `gallery-02.jpg` — San Marco-plein
- `gallery-03.jpg` — Gondel / gondelier
- `gallery-04.jpg` — Rialtobrug

#### Schotland

**Edinburgh** — `locations/edinburgh/`
- `gallery-01.jpg` — Edinburgh Castle
- `gallery-02.jpg` — Royal Mile
- `gallery-03.jpg` — Arthur's Seat (uitzicht of berg)
- `gallery-04.jpg` — Georgische huizen / New Town

**Isle of Skye** — `locations/isle-of-skye/`
- `gallery-01.jpg` — Old Man of Storr
- `gallery-02.jpg` — Fairy Pools
- `gallery-03.jpg` — Quiraing landschap
- `gallery-04.jpg` — Portree-haven met gekleurde huisjes

**Glencoe** — `locations/glencoe/`
- `gallery-01.jpg` — Glencoe-vallei uitzicht
- `gallery-02.jpg` — Three Sisters
- `gallery-03.jpg` — Meer of rivier in Glencoe
- `gallery-04.jpg` — Highland-huisje / camperspot

#### Slovenië

**Ljubljana** — `locations/ljubljana/`
- `gallery-01.jpg` — Ljubljana Castle van boven
- `gallery-02.jpg` — Drakenbrug (Zmajski most)
- `gallery-03.jpg` — Ljubljanica-rivier met terrassen
- `gallery-04.jpg` — Oude stad / straatje

**Bled** — `locations/bled/`
- `gallery-01.jpg` — Meer van Bled met eiland-kerkje (klassiek shot)
- `gallery-02.jpg` — Bled Castle op de rots
- `gallery-03.jpg` — Vissersbootje (pletna)
- `gallery-04.jpg` — Wandelpad rond het meer

#### Canarische Eilanden

**Tenerife** — `locations/tenerife/`
- `gallery-01.jpg` — Teide vulkaan
- `gallery-02.jpg` — Anaga-laurierwoud
- `gallery-03.jpg` — Playa de las Teresitas of zwart strand
- `gallery-04.jpg` — La Laguna oude stad

**Lanzarote** — `locations/lanzarote/`
- `gallery-01.jpg` — Timanfaya nationaal park (vulkanisch)
- `gallery-02.jpg` — Jameos del Agua / lavatunnel
- `gallery-03.jpg` — Wijngaarden in La Geria (halve-maantjes in as)
- `gallery-04.jpg` — Kustlijn met witte huisjes

#### Duitsland

**Berlijn** — `locations/berlijn/`
- `gallery-01.jpg` — Brandenburger Tor
- `gallery-02.jpg` — Reichstag-koepel
- `gallery-03.jpg` — East Side Gallery (muurschilderingen)
- `gallery-04.jpg` — Museumsinsel / Berliner Dom

**Zwarte Woud** — `locations/zwarte-woud/`
- `gallery-01.jpg` — Bosweg / donkere naaldbossen
- `gallery-02.jpg` — Titisee meer
- `gallery-03.jpg` — Triberg-waterval
- `gallery-04.jpg` — Vakwerkhuis / koekoeksklok-dorp

#### Verenigde Staten

**New York** — `locations/new-york/`
- `gallery-01.jpg` — Manhattan skyline vanaf Brooklyn Bridge
- `gallery-02.jpg` — Central Park uit de lucht
- `gallery-03.jpg` — Times Square 's avonds
- `gallery-04.jpg` — Vrijheidsbeeld

**Miami** — `locations/miami/`
- `gallery-01.jpg` — Art Deco district / South Beach kleurige gebouwen
- `gallery-02.jpg` — Strand met palmen
- `gallery-03.jpg` — Wynwood muurschilderingen (street art)
- `gallery-04.jpg` — Ocean Drive / boulevard

## Beslissingen referentie

- **F5-25**: DemoSeeder + demo-images als blokkerende 5.1.a
- **F5-26**: Gecommitte fixture-images (i.p.v. runtime Unsplash-fetch)
- **F5-27**: 6 destinations / 14 locations
- **F5-28**: Minimalistische foto-omvang (1 hero per dest, 4 gallery per loc, 62 totaal)
