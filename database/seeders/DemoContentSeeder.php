<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Destination;
use App\Models\FamilyMember;
use App\Models\Location;
use App\Models\Newsletter;
use App\Models\NewsletterSend;
use App\Models\Page;
use App\Models\Post;
use App\Models\Route;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------------------------------------------------
        // USERS — admin + editor + 2 auteurs + 5 leden (idempotent)
        // -----------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.test'],
            ['name' => 'Demo Admin', 'password' => bcrypt('password'), 'email_verified_at' => now()],
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $editor = User::firstOrCreate(
            ['email' => 'editor@demo.test'],
            ['name' => 'Demo Editor', 'password' => bcrypt('password'), 'email_verified_at' => now()],
        );
        if (! $editor->hasRole('editor')) {
            $editor->assignRole('editor');
        }

        $author1 = User::firstOrCreate(
            ['email' => 'jan@demo.test'],
            ['name' => 'Jan Westein', 'password' => bcrypt('password'), 'email_verified_at' => now()],
        );
        if (! $author1->hasRole('auteur')) {
            $author1->assignRole('auteur');
        }

        $author2 = User::firstOrCreate(
            ['email' => 'marieke@demo.test'],
            ['name' => 'Marieke Westein', 'password' => bcrypt('password'), 'email_verified_at' => now()],
        );
        if (! $author2->hasRole('auteur')) {
            $author2->assignRole('auteur');
        }

        $members = collect();
        for ($i = 1; $i <= 5; $i++) {
            $m = User::firstOrCreate(
                ['email' => "lid{$i}@demo.test"],
                ['name' => "Demo Lid {$i}", 'password' => bcrypt('password'), 'email_verified_at' => now()],
            );
            if (! $m->hasRole('lid')) {
                $m->assignRole('lid');
            }
            $members->push($m);
        }

        $authors = collect([$author1, $author2]);

        // -----------------------------------------------------------------
        // DESTINATIONS — 6 stuks (idempotent via slug), country_code correct gezet
        // -----------------------------------------------------------------
        $destSpecs = [
            [
                'name' => 'Italië',
                'slug' => 'italie',
                'country_code' => 'IT',
                'description' => 'Onze grote zomerreis: drie weken door Toscane, Lazio en Veneto. Van glooiende wijnvelden tot de kanalen van Venetië, met tussenstops die we niet meer vergeten.',
            ],
            [
                'name' => 'Schotland',
                'slug' => 'schotland',
                'country_code' => 'GB',
                'description' => 'Tien dagen door de Highlands, tussen mistige lochs en historische kastelen. Voor ons de mooiste combinatie van ruige natuur en gastvrije pubs.',
            ],
            [
                'name' => 'Slovenië',
                'slug' => 'slovenie',
                'country_code' => 'SI',
                'description' => 'Een compact land met een verrassende variatie: van de meren van Bled en Bohinj tot de Julische Alpen. Een week vol wandelingen en heldere bergstreken.',
            ],
            [
                'name' => 'Canarische Eilanden',
                'slug' => 'canarische-eilanden',
                'country_code' => 'ES',
                'description' => 'Twee eilanden op één reis: het groene Tenerife en het vulkanische Lanzarote. Genieten van winterzon en compleet verschillende landschappen.',
            ],
            [
                'name' => 'Duitsland',
                'slug' => 'duitsland',
                'country_code' => 'DE',
                'description' => 'Van de bruisende straten van Berlijn naar de stille dennenbossen van het Zwarte Woud. Een camperroute die stad en natuur in één reis verbindt.',
            ],
            [
                'name' => 'Verenigde Staten',
                'slug' => 'verenigde-staten',
                'country_code' => 'US',
                'description' => 'Tien dagen langs de oostkust, van New York naar Miami. Skyline-verkenningen, road-trip-motels en het Miami-strand als eindpunt van onze zomerreis.',
            ],
        ];
        $destinations = collect();
        foreach ($destSpecs as $spec) {
            $destinations->push(
                Destination::firstOrCreate(
                    ['slug' => $spec['slug']],
                    [
                        'name' => $spec['name'],
                        'description' => $spec['description'],
                        'country_code' => $spec['country_code'],
                    ],
                ),
            );
        }

        // -----------------------------------------------------------------
        // LOCATIONS — 14 stuks, verdeeld over de 6 destinations
        // country_code geërfd van destination voor consistentie
        // -----------------------------------------------------------------
        $locSpecs = [
            // Italië
            ['dest' => 0, 'name' => 'Rome', 'slug' => 'rome', 'lat' => 41.9028, 'lng' => 12.4964, 'description' => 'De eeuwige stad met het Colosseum, de Sint-Pieter en de pleinen waar we iedere avond ijs aten.'],
            ['dest' => 0, 'name' => 'Florence', 'slug' => 'florence', 'lat' => 43.7696, 'lng' => 11.2558, 'description' => 'Toscaanse renaissancestad met de rode koepel van de Duomo en gelato-winkels op elke hoek.'],
            ['dest' => 0, 'name' => 'Venetië', 'slug' => 'venetie', 'lat' => 45.4408, 'lng' => 12.3155, 'description' => 'Een stad die op water is gebouwd, waar we een gondeltocht maakten door de kanalen.'],
            // Schotland
            ['dest' => 1, 'name' => 'Edinburgh', 'slug' => 'edinburgh', 'lat' => 55.9533, 'lng' => -3.1883, 'description' => 'Hoofdstad van Schotland, met een kasteel hoog op de rots en de Royal Mile eronder.'],
            ['dest' => 1, 'name' => 'Isle of Skye', 'slug' => 'isle-of-skye', 'lat' => 57.2730, 'lng' => -6.2150, 'description' => 'Ruig eiland aan de westkust, bekend om de Fairy Pools en dramatische kliffen.'],
            ['dest' => 1, 'name' => 'Glencoe', 'slug' => 'glencoe', 'lat' => 56.6864, 'lng' => -5.1027, 'description' => 'Diep dal in de Highlands, ideaal voor lange wandelingen en stille camperspots.'],
            // Slovenië
            ['dest' => 2, 'name' => 'Ljubljana', 'slug' => 'ljubljana', 'lat' => 46.0569, 'lng' => 14.5058, 'description' => 'Compacte hoofdstad met een kasteel op de heuvel en een gezellige rivier-boulevard.'],
            ['dest' => 2, 'name' => 'Bled', 'slug' => 'bled', 'lat' => 46.3683, 'lng' => 14.1146, 'description' => 'Bergmeer met een kerkje op een eilandje en een kasteel hoog op de klif erboven.'],
            // Canarische Eilanden
            ['dest' => 3, 'name' => 'Tenerife', 'slug' => 'tenerife', 'lat' => 28.2916, 'lng' => -16.6291, 'description' => 'Grootste van de Canarische Eilanden, met vulkaan de Teide en een groen noorden.'],
            ['dest' => 3, 'name' => 'Lanzarote', 'slug' => 'lanzarote', 'lat' => 29.0469, 'lng' => -13.5900, 'description' => 'Vulkanisch eiland waar Timanfaya en de wijngaarden van La Geria je bijblijven.'],
            // Duitsland
            ['dest' => 4, 'name' => 'Berlijn', 'slug' => 'berlijn', 'lat' => 52.5200, 'lng' => 13.4050, 'description' => 'Bruisende Duitse hoofdstad met museumeiland, muurresten en veel groene parken.'],
            ['dest' => 4, 'name' => 'Zwarte Woud', 'slug' => 'zwarte-woud', 'lat' => 48.0000, 'lng' => 8.2000, 'description' => 'Dennenbossen in Zuid-Duitsland, met de Titisee en veel wandelroutes tussen de heuvels.'],
            // Verenigde Staten
            ['dest' => 5, 'name' => 'New York', 'slug' => 'new-york', 'lat' => 40.7128, 'lng' => -74.0060, 'description' => 'Manhattan, Central Park en het Vrijheidsbeeld — onze eerste kennismaking met de VS.'],
            ['dest' => 5, 'name' => 'Miami', 'slug' => 'miami', 'lat' => 25.7617, 'lng' => -80.1918, 'description' => 'Zonnig eindstation aan de Atlantische kust, met art deco en South Beach.'],
        ];
        $locations = collect();
        foreach ($locSpecs as $spec) {
            $destination = $destinations[$spec['dest']];
            $locations->push(
                Location::firstOrCreate(
                    ['slug' => $spec['slug']],
                    [
                        'destination_id' => $destination->id,
                        'name' => $spec['name'],
                        'latitude' => $spec['lat'],
                        'longitude' => $spec['lng'],
                        'country_code' => $destination->country_code,
                        'description' => $spec['description'],
                    ],
                ),
            );
        }

        // -----------------------------------------------------------------
        // MEDIA — attach fixture-images aan destinations en locations
        // Idempotent: gebeurt niet dubbel bij re-seed.
        // preservingOriginal() zorgt dat fixtures na eerste seed niet verhuizen.
        // -----------------------------------------------------------------
        foreach ($destinations as $destination) {
            $this->attachDestinationHero($destination);
        }

        foreach ($locations as $location) {
            $this->attachLocationGallery($location);
        }

        // POSTS — 30 stuks, expliciet gekoppeld aan location + categorie + content (F5-68).
        // Elke titel hangt aan de inhoudelijk juiste location; categorie past bij het onderwerp.
        // 'body_full' = volledig uitgeschreven NL-body (7 posts, incl. de 3 featured).
        // Waar 'body_full' ontbreekt valt de loop terug op een korte-maar-echte NL-body.
        // -----------------------------------------------------------------
        $categories = Category::all();
        $tagPool = ['camper', 'kindvriendelijk', 'wandelen', 'eten', 'cultuur', 'natuur'];

        // Zorg dat de tags bestaan (Tag-model lowercased via mutator)
        foreach ($tagPool as $tagName) {
            Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName]);
        }
        $tags = Tag::whereIn('slug', collect($tagPool)->map(fn ($t) => Str::slug($t))->all())->get();

        if (Post::count() === 0) {
            $locBySlug = $locations->keyBy('slug');
            $catByName = $categories->keyBy('name');

            // Elke spec: title, location-slug (of null), category-naam, excerpt, en optioneel body_full.
            $postSpecs = [
                // --- Italië ---
                [
                    'title' => 'Onze eerste dag in Rome',
                    'location' => 'rome',
                    'category' => 'Verslag',
                    'excerpt' => 'Jetlag, gelato en het Colosseum in de avondzon — hoe onze eerste dag in Rome begon met chaos en eindigde in verwondering.',
                    'body_full' => '<p>We waren om zeven uur \'s ochtends geland en hadden ons voorgenomen om rustig aan te doen. Dat plan hield precies tot het moment dat de kinderen door het raam van ons appartement de koepel van een kerk zagen en per se naar buiten wilden. Rome laat je niet uitrusten.</p>'
                        .'<p>De ochtend brachten we door in de buurt rond Campo de\' Fiori, waar de markt net werd afgebroken en de geur van perziken en basilicum nog in de lucht hing. We kochten brood, tomaten en een handvol kersen, en aten die op een muurtje terwijl scooters langs raasden. De kinderen telden er meer dan honderd voordat ze de tel kwijtraakten.</p>'
                        .'<h2>Het Colosseum in de avond</h2>'
                        .'<p>We hadden expres kaartjes voor het einde van de dag geboekt, en dat was de beste beslissing van de reis. De drukte was weggeëbd, de stenen kleurden oranje in de laagstaande zon, en voor het eerst stonden we allemaal even stil. Zelfs de jongste, die de hele dag had geklaagd over zijn schoenen, zei niets meer.</p>'
                        .'<p>Terug in het appartement vielen ze binnen tien minuten in slaap. Wij zaten nog even op het balkon met een glas wijn en de kaart van morgen. Dag één zat erop, en Rome had gewonnen.</p>',
                ],
                [
                    'title' => 'Pasta-paradise in Florence',
                    'location' => 'florence',
                    'category' => 'Eten',
                    'excerpt' => 'Van handgemaakte pici tot de beste bistecca van Toscane: een dag lang eten door Florence met twee hongerige kinderen op sleeptouw.',
                    'body_full' => '<p>Florence is een stad om in te eten, en dat namen we serieus. We begonnen bij een kleine trattoria achter de Mercato Centrale, waar een oudere vrouw achter een houten tafel met haar handen pici rolde — dikke, ongelijke spaghetti die je nergens beter krijgt dan hier.</p>'
                        .'<h2>De markt als speeltuin</h2>'
                        .'<p>De overdekte markt bleek een uitstekende plek om kinderen te laten kiezen. Ze mochten allebei één ding aanwijzen: het werd pecorino met honing en een puntzak geroosterde kastanjes. We aten staand tussen de kramen en niemand keek ervan op.</p>'
                        .'<p>\'s Avonds waagden we ons aan de beroemde bistecca alla fiorentina, een steak zo groot dat het hele gezin er samen van at. De kinderen waren vooral onder de indruk van de omvang. Wij vooral van de rekening, maar geen spijt.</p>',
                ],
                [
                    'title' => 'Gondelvaart met de kinderen',
                    'location' => 'venetie',
                    'category' => 'Activiteit',
                    'excerpt' => 'Een gondel is een cliché — en precies daarom deden we het toch. Over smalle kanalen, lage bruggen en de blik van twee kinderen die even helemaal stil waren.',
                ],
                [
                    'title' => 'Wat we leerden in Italië',
                    'location' => 'rome',
                    'category' => 'Verslag',
                    'excerpt' => 'Drie weken, drie steden en een camper vol herinneringen. Onze grootste lessen na een zomer door Toscane, Lazio en Veneto.',
                ],
                [
                    'title' => 'Onze 10 lessen van deze roadtrip',
                    'location' => 'florence',
                    'category' => 'Verslag',
                    'excerpt' => 'Van te vol geplande dagen tot de magie van niets doen: tien dingen die deze Italiaanse roadtrip ons heeft geleerd.',
                ],
                [
                    'title' => 'Wat we anders hadden gedaan',
                    'location' => 'venetie',
                    'category' => 'Verslag',
                    'excerpt' => 'Achteraf is alles makkelijker. De keuzes waar we op terugkijken en de dingen die we een volgende keer zeker anders aanpakken.',
                ],
                // --- Schotland ---
                [
                    'title' => 'Edinburgh: kastelen en koek',
                    'location' => 'edinburgh',
                    'category' => 'Verslag',
                    'excerpt' => 'Regen, een kasteel op een rots en de beste shortbread die we ooit aten. Onze eerste dagen in de Schotse hoofdstad.',
                ],
                [
                    'title' => 'Wandelen op Skye',
                    'location' => 'isle-of-skye',
                    'category' => 'Activiteit',
                    'excerpt' => 'De Fairy Pools, een stevige wind en modder tot aan de enkels. Wandelen op Isle of Skye is ruig, nat en onvergetelijk.',
                ],
                [
                    'title' => 'Highland-camperen in Glencoe',
                    'location' => 'glencoe',
                    'category' => 'Verslag',
                    'excerpt' => 'Een stille camperplek diep in het dal, omringd door bergen en zonder een streepje bereik. Onze mooiste nacht van de hele reis.',
                    'body_full' => '<p>Er zijn plekken waar je stopt omdat de kaart zegt dat het mooi is, en er zijn plekken waar je stopt omdat je gewoon niet verder kunt kijken zonder te stoppen. Glencoe was het tweede soort. We reden het dal in en werden om de paar honderd meter stiller.</p>'
                        .'<h2>Geen bereik, geen zorgen</h2>'
                        .'<p>Onze camperplek was niet meer dan een verhard stuk naast een beek. Geen voorzieningen, geen bereik, geen buren. De kinderen bouwden een dam van keien terwijl wij koffie zetten op het gasstel en naar de wolken keken die tegen de bergtoppen bleven hangen.</p>'
                        .'<p>\'s Avonds werd het koud, veel kolder dan we in augustus hadden verwacht. We kropen met z\'n allen onder de dekens in het dak-bed en luisterden naar de regen op het canvas. Ergens in de nacht hield het op, en toen ik even naar buiten keek stond het dal vol sterren.</p>'
                        .'<p>De volgende ochtend wilde niemand weg. Dat is het beste teken dat een plek klopt.</p>',
                ],
                [
                    'title' => 'Schotland in een week — kan dat?',
                    'location' => 'edinburgh',
                    'category' => 'Verslag',
                    'excerpt' => 'Highlands, kastelen én de hoofdstad in zeven dagen: ambitieus, maar haalbaar. Zo verdeelden we onze week door Schotland.',
                ],
                // --- Slovenië ---
                [
                    'title' => 'Ljubljana per fiets',
                    'location' => 'ljubljana',
                    'category' => 'Activiteit',
                    'excerpt' => 'Een compacte hoofdstad is een fietsstad. Over de bruggen van Plečnik, langs de rivier en met een ijsje op het kasteelplein.',
                ],
                [
                    'title' => 'Het meer van Bled bij zonsopkomst',
                    'location' => 'bled',
                    'category' => 'Verslag',
                    'excerpt' => 'Om vijf uur uit bed voor een leeg meer, een kerkje in de mist en een spiegelgladde waterspiegel. Waarom vroeg opstaan in Bled de moeite waard is.',
                    'body_full' => '<p>Iedereen fotografeert het meer van Bled, en dus wilden wij het anders. De enige manier om dat te doen, bleek: er eerder zijn dan iedereen. De wekker ging om half vijf.</p>'
                        .'<h2>Een leeg meer</h2>'
                        .'<p>We liepen in het donker naar de oever en waren volledig alleen. Het water lag doodstil, het kerkje op het eilandje dreef in een dunne mist, en de eerste zonnestralen kleurden de bergtoppen roze terwijl het meer zelf nog in schaduw lag. Geen boot, geen geluid, alleen wat eenden die traag hun kringen trokken.</p>'
                        .'<p>De kinderen hadden we thuisgelaten bij oma, die met ons meereisde — dit was er een voor de volwassenen. We zeiden een half uur lang bijna niets. Toen de eerste toeristenbus arriveerde, pakten we onze spullen en liepen terug voor het ontbijt, met het gevoel dat we iets hadden gezien dat de rest van de dag zou missen.</p>',
                ],
                [
                    'title' => 'Beste fotospots in Bled',
                    'location' => 'bled',
                    'category' => 'Tips',
                    'excerpt' => 'Van het uitzichtpunt bij Mala Osojnica tot de steiger aan de oostkant: de plekken waar het meer van Bled zich op zijn mooist laat vastleggen.',
                ],
                // --- Canarische Eilanden ---
                [
                    'title' => 'Op de Teide vulkaan in Tenerife',
                    'location' => 'tenerife',
                    'category' => 'Activiteit',
                    'excerpt' => 'Boven de wolken op de hoogste berg van Spanje: met de kabelbaan de Teide op, tussen lavavelden en een uitzicht dat niet lijkt te kloppen.',
                    'body_full' => '<p>Vanaf de kust leek de Teide een verre, blauwe schaduw. Pas toen we de bergweg op reden, besefte we hoe hoog we gingen: de begroeiing verdween, de lucht werd ijler en het landschap veranderde in iets dat meer op Mars leek dan op een vakantie-eiland.</p>'
                        .'<h2>Met de kabelbaan omhoog</h2>'
                        .'<p>De kabelbaan brengt je in acht minuten naar bijna 3.500 meter. De kinderen drukten hun neus tegen het glas terwijl de lavavelden onder ons wegzakten. Boven aangekomen was het koud en waaide het hard, maar het uitzicht — een zee van wolken met andere eilandtoppen die er als eilandjes bovenuit staken — maakte alles goed.</p>'
                        .'<p>We hadden geen vergunning voor het laatste stuk naar de echte top, dus daar bleven we onder. Eerlijk gezegd was dat ver genoeg. De hoogte deed zich voelen, en een van de kinderen werd wat duizelig. Rustig aan naar beneden, en op zeeniveau een dubbele portie patat als beloning.</p>',
                ],
                [
                    'title' => 'Vulkanische landschappen op Lanzarote',
                    'location' => 'lanzarote',
                    'category' => 'Verslag',
                    'excerpt' => 'Zwarte aarde tot aan de horizon, kraters en kronkelwegen: rijden door Timanfaya voelt als een tocht over een andere planeet.',
                ],
                [
                    'title' => 'Wijngaarden in de as: La Geria',
                    'location' => 'lanzarote',
                    'category' => 'Eten',
                    'excerpt' => 'Wijnstokken in trechters van vulkanische as, elk beschermd door een muurtje van steen. Hoe Lanzarote wijn maakt op een plek waar niets zou moeten groeien.',
                ],
                [
                    'title' => 'Camperspots op de Canarische Eilanden',
                    'location' => 'tenerife',
                    'category' => 'Tips',
                    'excerpt' => 'Waar je met de camper mag staan, waar je beter wegblijft en hoe je op de eilanden aan water en stroom komt. Onze praktische bevindingen.',
                ],
                // --- Duitsland ---
                [
                    'title' => 'Berlijn in twee dagen met kinderen',
                    'location' => 'berlijn',
                    'category' => 'Activiteit',
                    'excerpt' => 'Muurresten, museumeiland en een middag in het park: hoe je Berlijn in twee dagen behapbaar houdt voor kleine benen.',
                    'body_full' => '<p>Twee dagen is kort voor een stad als Berlijn, en met kinderen erbij moet je keuzes maken. Wij besloten om niet te proberen alles te zien, maar om per dag één groot ding en veel ruimte voor spelen te plannen.</p>'
                        .'<h2>Dag één: geschiedenis in stukjes</h2>'
                        .'<p>We liepen langs de East Side Gallery, waar de kinderen de geschilderde muurdelen vooral als een lange buitenexpositie zagen. Dat het ooit een echte grens was, kwam later pas binnen, toen we bij een overgebleven stuk muur stonden en uitlegden wat het betekende. Daarna: een uur ravotten in een speeltuin, want de aandacht was op.</p>'
                        .'<h2>Dag twee: museum en park</h2>'
                        .'<p>Het Naturkundemuseum, met zijn enorme dinoskeletten, was een schot in de roos. \'s Middags weken we uit naar het Tiergarten, waar we een boot huurden en de kinderen zelf mochten roeien — met wisselend succes. Berlijn bleek voor ons vooral een stad die je in porties moet eten.</p>',
                ],
                [
                    'title' => 'Wandelen door het Zwarte Woud',
                    'location' => 'zwarte-woud',
                    'category' => 'Activiteit',
                    'excerpt' => 'Dennengeur, zachte bospaden en een picknick tussen de heuvels. Onze mooiste wandelingen in het zuiden van Duitsland.',
                ],
                [
                    'title' => 'Titisee: rustpunt in het bos',
                    'location' => 'zwarte-woud',
                    'category' => 'Verslag',
                    'excerpt' => 'Na dagen op de weg was het bergmeer Titisee precies wat we nodig hadden: pootjebaden, een bootje en niks moeten.',
                ],
                [
                    'title' => 'Wat je moet weten over Berlijn',
                    'location' => 'berlijn',
                    'category' => 'Tips',
                    'excerpt' => 'Openbaar vervoer, kaartjes en de handigste buurten om te overnachten: praktische tips voordat je met het gezin naar Berlijn afreist.',
                ],
                // --- Verenigde Staten ---
                [
                    'title' => 'New York met kids: onze survivalgids',
                    'location' => 'new-york',
                    'category' => 'Verslag',
                    'excerpt' => 'Wolkenkrabbers, gele taxi\'s en veel te veel indrukken: hoe we New York met twee kinderen overleefden — en er zelfs van genoten.',
                    'body_full' => '<p>New York met kinderen klinkt als een test van je uithoudingsvermogen, en dat is het ook. Maar het is ook de stad waar hun ogen het grootst werden van de hele reis. We hadden onszelf één regel opgelegd: elke dag één hoogtepunt, en verder meebewegen met hun tempo.</p>'
                        .'<h2>Omhoog kijken</h2>'
                        .'<p>De eerste ochtend liepen we gewoon door Midtown, en de kinderen deden niets anders dan omhoog kijken. Central Park werd hun favoriet: ruimte om te rennen, eekhoorns om achterna te zitten, en een rots om vanaf te springen. De stad eromheen verdween voor hen even helemaal.</p>'
                        .'<h2>Praktische overwinningen</h2>'
                        .'<p>We ontdekten dat de metro met een kinderwagen een uitdaging is, maar dat de veerboot naar Staten Island gratis is en langs het Vrijheidsbeeld vaart — het beste uitzicht van de reis voor precies nul dollar. \'s Avonds waren we gesloopt, maar het soort gesloopt waar je later met een glimlach aan terugdenkt.</p>',
                ],
                [
                    'title' => 'Miami art deco: kleur op South Beach',
                    'location' => 'miami',
                    'category' => 'Activiteit',
                    'excerpt' => 'Pastelkleurige gevels, palmbomen en de warme Atlantische branding: een wandeling door de art-decowijk van Miami Beach.',
                ],
                [
                    'title' => 'Onze eerste transatlantische vlucht',
                    'location' => 'new-york',
                    'category' => 'Verslag',
                    'excerpt' => 'Acht uur in een vliegtuig met twee kinderen: onze voorbereiding, wat werkte en wat we compleet verkeerd hadden ingeschat.',
                ],
                [
                    'title' => 'Familiereis naar de VS: onze kosten',
                    'location' => 'miami',
                    'category' => 'Verslag',
                    'excerpt' => 'Vluchten, huurauto, hotels en eten: een eerlijk overzicht van wat een reis van tien dagen naar de Amerikaanse oostkust ons kostte.',
                ],
                // --- Algemene how-to posts (blijven in de 30, hangen aan een plausibele plek) ---
                [
                    'title' => 'Pakken voor een gezinscamperreis',
                    'location' => 'glencoe',
                    'category' => 'Tips',
                    'excerpt' => 'Te veel meenemen is de klassieke fout. Onze uitgeklede paklijst voor een lange camperreis met kinderen, na jaren van vallen en opstaan.',
                ],
                [
                    'title' => 'Eten met kinderen onderweg',
                    'location' => 'florence',
                    'category' => 'Eten',
                    'excerpt' => 'Kieskeurige eters in een vreemd land: hoe we onderweg toch elke dag iets op tafel kregen waar iedereen blij van werd.',
                ],
                [
                    'title' => 'Vroeg opstaan loont',
                    'location' => 'bled',
                    'category' => 'Tips',
                    'excerpt' => 'De mooiste plekken zijn het mooist als niemand er is. Waarom we onderweg steeds vaker voor dag en dauw ons bed uit kropen.',
                ],
                [
                    'title' => 'Boekentips voor onderweg',
                    'location' => 'edinburgh',
                    'category' => 'Tips',
                    'excerpt' => 'Voorleesboeken tegen de verveling en reisverhalen voor de grote mensen: wat er bij ons in de camper meeging en het meest gelezen werd.',
                ],
                [
                    'title' => 'Veilig kamperen met kleine kinderen',
                    'location' => 'glencoe',
                    'category' => 'Tips',
                    'excerpt' => 'Van rondslingerende tentharingen tot water in de buurt: waar we op letten om het kamperen met jonge kinderen veilig én ontspannen te houden.',
                ],
            ];

            foreach ($postSpecs as $i => $spec) {
                $location = $locBySlug->get($spec['location']);

                $body = $spec['body_full']
                    ?? '<p>'.e($spec['excerpt']).'</p>'
                        .'<p>Dit verslag werken we binnenkort verder uit met de volledige verhalen, foto\'s en praktische details van deze etappe van onze reis.</p>';

                $post = Post::create([
                    'user_id' => $authors->random()->id,
                    'destination_id' => $location?->destination_id,
                    'location_id' => $location?->id,
                    'title' => $spec['title'],
                    'slug' => Str::slug($spec['title']),
                    'excerpt' => $spec['excerpt'],
                    'body' => $body,
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 180)),
                ]);

                $category = $catByName->get($spec['category']);
                if ($category) {
                    $post->categories()->sync([$category->id]);
                }

                $post->syncTagsByName($tags->random(rand(0, 3))->pluck('name')->all());
            }

            // -----------------------------------------------------------------
            // REISTIPS — 5 losse tip-posts bovenop de 30 (F5-69).
            // 3 bestemming-gebonden (1 met location), 2 algemeen (destination + location null).
            // Categorie altijd 'Tips'; dit is de content voor /reistips (5.2.c).
            // -----------------------------------------------------------------
            $tipSpecs = [
                [
                    'title' => 'Camperen in Schotland met kinderen',
                    'destination' => 'schotland',
                    'location' => 'glencoe',
                    'excerpt' => 'Wild kamperen, wisselend weer en muggen: alles wat we leerden over kamperen met kinderen in de Schotse Highlands.',
                    'body' => '<p>Schotland en kamperen horen bij elkaar, maar met kinderen komt er wel wat bij kijken. Het weer draait in een uur van zon naar stortbui, en de beruchte midges — kleine steekmuggen — kunnen een avond flink verpesten als je onvoorbereid bent.</p>'
                        .'<h2>Wild kamperen mag, met respect</h2>'
                        .'<p>In Schotland is wildkamperen wettelijk toegestaan onder de Scottish Outdoor Access Code. Dat geeft enorme vrijheid, maar vraagt om verantwoordelijkheid: laat geen sporen achter, blijf uit de buurt van woningen en dek open vuur af. Wij kozen steevast plekken bij een beek, zodat de kinderen konden spelen terwijl wij de boel opzetten.</p>'
                        .'<h2>Tegen de muggen</h2>'
                        .'<p>Neem een goede anti-mug met DEET, plan je kampplek op een plek met wat wind, en zet de tent niet vlak naast stilstaand water. Een muggennet voor over de kinderwagen bleek goud waard. Met die voorbereiding werd het kamperen precies wat we hoopten: ruig, vrij en onvergetelijk.</p>',
                ],
                [
                    'title' => 'Waar parkeer je je camper op Lanzarote',
                    'destination' => 'canarische-eilanden',
                    'location' => null,
                    'excerpt' => 'Officiële plekken, verboden zones en handige aanrijpunten: onze praktische gids voor overnachten met de camper op Lanzarote.',
                    'body' => '<p>Lanzarote is prachtig met de camper, maar de regels rond overnachten zijn strenger dan veel mensen denken. Vrij staan wordt op veel plekken actief ontmoedigd, zeker binnen de natuurparken. Een beetje voorbereiding voorkomt een nachtelijke wegstuur-actie.</p>'
                        .'<h2>Gebruik de officiële plekken</h2>'
                        .'<p>Er zijn een paar aangewezen camperplaatsen op het eiland waar je legaal kunt staan, met water en voorzieningen. Ze zijn beperkt in aantal, dus kom op tijd aan, vooral in het hoogseizoen. Reserveer waar dat kan.</p>'
                        .'<p>Blijf weg van Timanfaya en de kust binnen de beschermde zones — daar wordt gecontroleerd en beboet. Onze vuistregel: overdag rijden en verkennen, en tegen de avond op tijd naar een vaste plek. Zo hou je het ontspannen.</p>',
                ],
                [
                    'title' => 'Berlijn met het openbaar vervoer',
                    'destination' => 'duitsland',
                    'location' => null,
                    'excerpt' => 'Eén kaartje voor het hele netwerk, kinderen vaak gratis: hoe je Berlijn slim en goedkoop doorkruist met U-Bahn, S-Bahn en tram.',
                    'body' => '<p>Berlijn heeft een van de beste openbaarvervoersnetwerken van Europa, en voor een gezin is dat goud waard. U-Bahn, S-Bahn, tram en bus vallen onder één tariefsysteem, dus met het juiste kaartje reis je moeiteloos over.</p>'
                        .'<h2>Het juiste kaartje</h2>'
                        .'<p>Voor de meeste bezoeken volstaat een dagkaart voor tariefzone AB, die het hele centrum en de belangrijkste bezienswaardigheden dekt. Kinderen onder de zes reizen gratis, en er zijn voordelige groepskaarten voor gezinnen. Koop kaartjes bij de automaat of in de app, en vergeet niet ze te stempelen waar dat nodig is.</p>'
                        .'<p>Met een beetje planning hoef je in Berlijn nauwelijks te lopen tussen de highlights — ideaal als de benen van de kleinsten het na een uur al opgeven.</p>',
                ],
                [
                    'title' => 'Reizen met kleine kinderen: onze basisregels',
                    'destination' => null,
                    'location' => null,
                    'excerpt' => 'Na jaren onderweg met jonge kinderen kwamen we tot een handvol regels die elke reis rustiger maken. Onze belangrijkste lessen op een rij.',
                    'body' => '<p>Reizen met kleine kinderen is geen kwestie van geluk, maar van ritme. Na een paar reizen ontdekten we dat een handvol simpele regels het verschil maakt tussen een uitputtingsslag en een fijne vakantie.</p>'
                        .'<h2>Plan de helft</h2>'
                        .'<p>Onze belangrijkste les: plan hooguit de helft van wat je zou willen. Kinderen hebben lege tijd nodig — om te spelen, te treuzelen, of gewoon niks te doen. Een dag met één hoogtepunt en veel ruimte eromheen werkt bijna altijd beter dan een dag vol geplande activiteiten.</p>'
                        .'<h2>Eten en slapen eerst</h2>'
                        .'<p>Honger en vermoeidheid zijn de twee grootste bronnen van ellende onderweg. Wij hadden altijd een noodrantsoen bij de hand en hielden het slaapritme zo veel mogelijk vast, ook op reis. Saai misschien, maar het redde talloze middagen.</p>'
                        .'<p>En de laatste: laat het los als een dag mislukt. Die zijn er. Morgen is weer een nieuwe kans.</p>',
                ],
                [
                    'title' => 'Een lange autorit overleven met kinderen',
                    'destination' => null,
                    'location' => null,
                    'excerpt' => 'Van slimme stops tot het eeuwige "zijn we er al bijna": onze beproefde aanpak om urenlange autoritten met kinderen draaglijk te houden.',
                    'body' => '<p>Een lange autorit is voor veel gezinnen het minst geliefde deel van de reis. Toch hoeft het geen ramp te zijn. Met de juiste aanpak worden de uren op de weg soms zelfs een van de leukere herinneringen.</p>'
                        .'<h2>Stop op tijd, niet als het moet</h2>'
                        .'<p>Wacht niet tot de sfeer omslaat. Wij plannen elke twee uur een stop van een kwartier, ook als iedereen nog vrolijk is. Even rennen op een parkeerplaats, een appel eten, en weer verder. Voorkomen is beter dan sussen.</p>'
                        .'<h2>Verveling is toegestaan</h2>'
                        .'<p>We geven bewust niet de hele rit schermen. Een zakje met kleine verrassingen, een luisterboek dat we samen volgen, en het klassieke spel van nummerborden zoeken doen wonderen. En ja, "zijn we er al bijna" hoort er gewoon bij — dat hebben we losgelaten.</p>',
                ],
            ];

            foreach ($tipSpecs as $spec) {
                $destination = $spec['destination']
                    ? Destination::where('slug', $spec['destination'])->first()
                    : null;
                $location = $spec['location']
                    ? $locBySlug->get($spec['location'])
                    : null;

                $post = Post::create([
                    'user_id' => $authors->random()->id,
                    'destination_id' => $location?->destination_id ?? $destination?->id,
                    'location_id' => $location?->id,
                    'title' => $spec['title'],
                    'slug' => Str::slug($spec['title']),
                    'excerpt' => $spec['excerpt'],
                    'body' => $spec['body'],
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 180)),
                ]);

                $tipsCategory = $catByName->get('Tips');
                if ($tipsCategory) {
                    $post->categories()->sync([$tipsCategory->id]);
                }

                $post->syncTagsByName($tags->random(rand(0, 2))->pluck('name')->all());
            }
        }

        $posts = Post::all();

        // -----------------------------------------------------------------
        // COMMENTS — 25 stuks, mix van rollen + statussen + 1 niveau replies
        // -----------------------------------------------------------------
        if (Comment::count() === 0) {
            $allUsers = $authors->merge($members)->push($admin)->push($editor);

            // 20 top-level comments
            for ($i = 0; $i < 20; $i++) {
                $user = $allUsers->random();
                $post = $posts->random();

                // Voor admin/editor: hook zet 'approved'. Voor anderen: hook zet 'pending'.
                // We laten de hook z'n werk doen door status NIET expliciet te zetten,
                // behalve op een paar om diversiteit te krijgen.
                $explicitStatus = null;
                if ($i % 7 === 0) {
                    $explicitStatus = 'rejected';
                } elseif ($i % 11 === 0) {
                    $explicitStatus = 'spam';
                }

                $attrs = [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'body' => fake()->sentence(rand(8, 20)),
                ];
                if ($explicitStatus) {
                    $attrs['status'] = $explicitStatus;
                }

                Comment::create($attrs);
            }

            // 5 replies (top-level comments krijgen er soms eentje)
            $topLevels = Comment::whereNull('parent_id')->where('status', 'approved')->get();
            if ($topLevels->isNotEmpty()) {
                for ($i = 0; $i < 5; $i++) {
                    $parent = $topLevels->random();
                    Comment::create([
                        'post_id' => $parent->post_id,
                        'user_id' => $allUsers->random()->id,
                        'parent_id' => $parent->id,
                        'body' => fake()->sentence(rand(5, 15)),
                    ]);
                }
            }
        }

        // -----------------------------------------------------------------
        // ROUTES — 6 stuks, één per destination, waypoints in volgorde
        // -----------------------------------------------------------------
        if (Route::count() === 0) {
            // Italië — Rome/Florence/Venetië (index 0/1/2)
            $italyRoute = Route::create([
                'destination_id' => $destinations[0]->id,
                'name' => 'Italië roadtrip 2024',
                'slug' => 'italie-roadtrip-2024',
                'description' => 'Drie weken door Toscane, Lazio en Veneto.',
                'travel_date' => '2024-07-15',
                'is_published' => true,
                'published_at' => '2024-07-15',
            ]);
            $italyRoute->locations()->attach([
                $locations[0]->id => ['order' => 1, 'notes' => 'Start in Rome'],
                $locations[1]->id => ['order' => 2, 'notes' => 'Door naar Florence'],
                $locations[2]->id => ['order' => 3, 'notes' => 'Eindigen in Venetië'],
            ]);

            // Schotland — Edinburgh/Skye/Glencoe (index 3/4/5)
            $scotRoute = Route::create([
                'destination_id' => $destinations[1]->id,
                'name' => 'Highlands tour 2023',
                'slug' => 'highlands-tour-2023',
                'description' => 'Tien dagen door de Schotse Highlands.',
                'travel_date' => '2023-08-10',
                'is_published' => true,
                'published_at' => '2023-08-10',
            ]);
            $scotRoute->locations()->attach([
                $locations[3]->id => ['order' => 1, 'notes' => 'Start in Edinburgh'],
                $locations[5]->id => ['order' => 2, 'notes' => 'Naar Glencoe'],
                $locations[4]->id => ['order' => 3, 'notes' => 'Eindigen op Skye'],
            ]);

            // Slovenië — Ljubljana/Bled (index 6/7)
            $sloRoute = Route::create([
                'destination_id' => $destinations[2]->id,
                'name' => 'Slovenië meren-tour 2024',
                'slug' => 'slovenie-meren-tour-2024',
                'description' => 'Een week rond de mooiste meren van de Julische Alpen.',
                'travel_date' => '2024-06-10',
                'is_published' => true,
                'published_at' => '2024-06-10',
            ]);
            $sloRoute->locations()->attach([
                $locations[6]->id => ['order' => 1, 'notes' => 'Aankomst in Ljubljana'],
                $locations[7]->id => ['order' => 2, 'notes' => 'Doorreis naar Bled'],
            ]);

            // Canarische Eilanden — Tenerife/Lanzarote (index 8/9)
            $canaryRoute = Route::create([
                'destination_id' => $destinations[3]->id,
                'name' => 'Canarische eilandhoppen 2024',
                'slug' => 'canarische-eilandhoppen-2024',
                'description' => 'Twee eilanden vergelijken: Tenerife en Lanzarote.',
                'travel_date' => '2024-02-15',
                'is_published' => true,
                'published_at' => '2024-02-15',
            ]);
            $canaryRoute->locations()->attach([
                $locations[8]->id => ['order' => 1, 'notes' => 'Vlucht naar Tenerife'],
                $locations[9]->id => ['order' => 2, 'notes' => 'Ferry naar Lanzarote'],
            ]);

            // Duitsland — Berlijn/Zwarte Woud (index 10/11)
            $duiRoute = Route::create([
                'destination_id' => $destinations[4]->id,
                'name' => 'Duitsland camperreis 2022',
                'slug' => 'duitsland-camperreis-2022',
                'description' => 'Van hoofdstad naar de dennenbossen met de camper.',
                'travel_date' => '2022-08-05',
                'is_published' => true,
                'published_at' => '2022-08-05',
            ]);
            $duiRoute->locations()->attach([
                $locations[10]->id => ['order' => 1, 'notes' => 'Start in Berlijn'],
                $locations[11]->id => ['order' => 2, 'notes' => 'Doorreis naar Zwarte Woud'],
            ]);

            // Verenigde Staten — New York/Miami (index 12/13)
            $usaRoute = Route::create([
                'destination_id' => $destinations[5]->id,
                'name' => 'Amerikaanse oostkust 2019',
                'slug' => 'amerikaanse-oostkust-2019',
                'description' => 'Van New York naar Miami — tien dagen langs de oostkust.',
                'travel_date' => '2019-07-20',
                'is_published' => true,
                'published_at' => '2019-07-20',
            ]);
            $usaRoute->locations()->attach([
                $locations[12]->id => ['order' => 1, 'notes' => 'Landen in New York'],
                $locations[13]->id => ['order' => 2, 'notes' => 'Vlucht door naar Miami'],
            ]);
        }

        // -----------------------------------------------------------------
        // IS_FEATURED — markering voor prominente weergave op homepage/index (F5-31)
        // Meerdere records mogen tegelijk featured zijn; controllers picken via
        // ->featured()->latest() zodat de meest recent gewijzigde wint.
        // -----------------------------------------------------------------
        Destination::where('slug', 'italie')->update(['is_featured' => true]);

        Route::where('slug', 'italie-roadtrip-2024')->update(['is_featured' => true]);

        Post::whereIn('title', [
            'Onze eerste dag in Rome',
            'Highland-camperen in Glencoe',
            'New York met kids: onze survivalgids',
        ])->update(['is_featured' => true]);

        // -----------------------------------------------------------------
        // SUBSCRIBERS — 30 stuks (20 confirmed, 7 pending, 3 unsubscribed)
        // -----------------------------------------------------------------
        if (Subscriber::count() === 0) {
            Subscriber::factory()->count(20)->confirmed()->create();
            Subscriber::factory()->count(7)->pending()->create();
            Subscriber::factory()->count(3)->unsubscribed()->create();
        }

        // -----------------------------------------------------------------
        // NEWSLETTERS — 2 stuks: 1 sent met sends, 1 draft
        // -----------------------------------------------------------------
        if (Newsletter::count() === 0) {
            $activeSubs = Subscriber::active()->get();

            $sent = Newsletter::factory()
                ->for($editor, 'author')
                ->sent($activeSubs->count())
                ->create([
                    'subject' => 'Onze zomerverhalen — augustus 2025',
                    'body' => '<p>Beste lezers, de eerste verslagen van onze zomerreis staan online...</p>',
                ]);

            foreach ($activeSubs as $sub) {
                NewsletterSend::factory()->create([
                    'newsletter_id' => $sent->id,
                    'subscriber_id' => $sub->id,
                ]);
            }

            Newsletter::factory()
                ->for($admin, 'author')
                ->create([
                    'subject' => 'Volgende reis — werk in uitvoering',
                    'body' => '<p>Concept voor de aankomende reis-aankondiging.</p>',
                    'status' => 'draft',
                ]);
        }

        // -----------------------------------------------------------------
        // PAGES — 3 stuks (Over ons, Privacy, Contact)
        // -----------------------------------------------------------------
        $pageSpecs = [
            ['slug' => 'over-ons', 'title' => 'Over ons', 'order' => 1, 'excerpt' => 'Maak kennis met de familie Westein.'],
            ['slug' => 'privacy', 'title' => 'Privacyverklaring', 'order' => 2, 'excerpt' => 'Hoe we omgaan met je gegevens.'],
            ['slug' => 'contact', 'title' => 'Contact', 'order' => 3, 'excerpt' => 'Hoe je ons bereikt.'],
        ];
        foreach ($pageSpecs as $spec) {
            Page::firstOrCreate(
                ['slug' => $spec['slug']],
                [
                    'title' => $spec['title'],
                    'excerpt' => $spec['excerpt'],
                    'body' => '<p>'.fake()->paragraphs(3, true).'</p>',
                    'published_at' => now()->subDays(30),
                    'order' => $spec['order'],
                ],
            );
        }

        // -----------------------------------------------------------------
        // FAMILY MEMBERS — 4 stuks, 2 gekoppeld aan User
        // -----------------------------------------------------------------
        $familySpecs = [
            ['name' => 'Jan', 'slug' => 'jan', 'role' => 'Vader & reisplanner', 'order' => 1, 'user' => $author1],
            ['name' => 'Marieke', 'slug' => 'marieke', 'role' => 'Moeder & fotograaf', 'order' => 2, 'user' => $author2],
            ['name' => 'Sophie', 'slug' => 'sophie', 'role' => 'Dochter', 'order' => 3, 'user' => null],
            ['name' => 'Tim', 'slug' => 'tim', 'role' => 'Zoon', 'order' => 4, 'user' => null],
        ];
        foreach ($familySpecs as $spec) {
            FamilyMember::firstOrCreate(
                ['slug' => $spec['slug']],
                [
                    'user_id' => $spec['user']?->id,
                    'name' => $spec['name'],
                    'role' => $spec['role'],
                    'bio' => fake()->paragraph(2),
                    'order' => $spec['order'],
                ],
            );
        }
    }

    protected function attachDestinationHero(Destination $destination): void
    {
        $path = database_path("seeders/fixtures/destinations/{$destination->slug}/hero.jpg");

        if (! file_exists($path)) {
            return;
        }

        if ($destination->getFirstMedia('hero') !== null) {
            return;
        }

        $destination->addMedia($path)
            ->preservingOriginal()
            ->toMediaCollection('hero');
    }

    protected function attachLocationGallery(Location $location): void
    {
        $dir = database_path("seeders/fixtures/locations/{$location->slug}");

        if (! is_dir($dir)) {
            return;
        }

        if ($location->getMedia('gallery')->isNotEmpty()) {
            return;
        }

        for ($i = 1; $i <= 4; $i++) {
            $filename = 'gallery-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'.jpg';
            $path = $dir.DIRECTORY_SEPARATOR.$filename;

            if (file_exists($path)) {
                $location->addMedia($path)
                    ->preservingOriginal()
                    ->toMediaCollection('gallery');
            }
        }
    }
}
