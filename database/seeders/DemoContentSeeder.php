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
            ['name' => 'Italië', 'slug' => 'italie', 'country_code' => 'IT'],
            ['name' => 'Schotland', 'slug' => 'schotland', 'country_code' => 'GB'],
            ['name' => 'Slovenië', 'slug' => 'slovenie', 'country_code' => 'SI'],
            ['name' => 'Canarische Eilanden', 'slug' => 'canarische-eilanden', 'country_code' => 'ES'],
            ['name' => 'Duitsland', 'slug' => 'duitsland', 'country_code' => 'DE'],
            ['name' => 'Verenigde Staten', 'slug' => 'verenigde-staten', 'country_code' => 'US'],
        ];
        $destinations = collect();
        foreach ($destSpecs as $spec) {
            $destinations->push(
                Destination::firstOrCreate(
                    ['slug' => $spec['slug']],
                    [
                        'name' => $spec['name'],
                        'description' => "Familievakanties in {$spec['name']}.",
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
            ['dest' => 0, 'name' => 'Rome', 'slug' => 'rome', 'lat' => 41.9028, 'lng' => 12.4964],
            ['dest' => 0, 'name' => 'Florence', 'slug' => 'florence', 'lat' => 43.7696, 'lng' => 11.2558],
            ['dest' => 0, 'name' => 'Venetië', 'slug' => 'venetie', 'lat' => 45.4408, 'lng' => 12.3155],
            // Schotland
            ['dest' => 1, 'name' => 'Edinburgh', 'slug' => 'edinburgh', 'lat' => 55.9533, 'lng' => -3.1883],
            ['dest' => 1, 'name' => 'Isle of Skye', 'slug' => 'isle-of-skye', 'lat' => 57.2730, 'lng' => -6.2150],
            ['dest' => 1, 'name' => 'Glencoe', 'slug' => 'glencoe', 'lat' => 56.6864, 'lng' => -5.1027],
            // Slovenië
            ['dest' => 2, 'name' => 'Ljubljana', 'slug' => 'ljubljana', 'lat' => 46.0569, 'lng' => 14.5058],
            ['dest' => 2, 'name' => 'Bled', 'slug' => 'bled', 'lat' => 46.3683, 'lng' => 14.1146],
            // Canarische Eilanden
            ['dest' => 3, 'name' => 'Tenerife', 'slug' => 'tenerife', 'lat' => 28.2916, 'lng' => -16.6291],
            ['dest' => 3, 'name' => 'Lanzarote', 'slug' => 'lanzarote', 'lat' => 29.0469, 'lng' => -13.5900],
            // Duitsland
            ['dest' => 4, 'name' => 'Berlijn', 'slug' => 'berlijn', 'lat' => 52.5200, 'lng' => 13.4050],
            ['dest' => 4, 'name' => 'Zwarte Woud', 'slug' => 'zwarte-woud', 'lat' => 48.0000, 'lng' => 8.2000],
            // Verenigde Staten
            ['dest' => 5, 'name' => 'New York', 'slug' => 'new-york', 'lat' => 40.7128, 'lng' => -74.0060],
            ['dest' => 5, 'name' => 'Miami', 'slug' => 'miami', 'lat' => 25.7617, 'lng' => -80.1918],
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
                        'description' => "Bezoek aan {$spec['name']}.",
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

        // -----------------------------------------------------------------
        // POSTS — 30 stuks, gemixt over locations + auteurs + categorieën
        // -----------------------------------------------------------------
        $categories = Category::all();
        $tagPool = ['camper', 'kindvriendelijk', 'wandelen', 'eten', 'cultuur', 'natuur'];

        // Zorg dat de tags bestaan (Tag-model lowercased via mutator)
        foreach ($tagPool as $tagName) {
            Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName]);
        }
        $tags = Tag::whereIn('slug', collect($tagPool)->map(fn ($t) => Str::slug($t))->all())->get();

        if (Post::count() === 0) {
            $titles = [
                // Bestaande 18
                'Onze eerste dag in Rome', 'Pasta-paradise in Florence', 'Gondelvaart met de kinderen',
                'Edinburgh: kastelen en koek', 'Wandelen op Skye', 'Highland-camperen in Glencoe',
                'Ljubljana per fiets', 'Het meer van Bled bij zonsopkomst', 'Wat we leerden in Italië',
                'Pakken voor een gezinscamperreis', 'Schotland in een week — kan dat?',
                'Eten met kinderen onderweg', 'Beste fotospots in Bled', 'Vroeg opstaan loont',
                'Onze 10 lessen van deze roadtrip', 'Wat we anders hadden gedaan',
                'Boekentips voor onderweg', 'Veilig kamperen met kleine kinderen',
                // Nieuwe 12 — spread over de 3 nieuwe destinations
                'Op de Teide vulkaan in Tenerife', 'Vulkanische landschappen op Lanzarote',
                'Wijngaarden in de as: La Geria', 'Camperspots op de Canarische Eilanden',
                'Berlijn in twee dagen met kinderen', 'Wandelen door het Zwarte Woud',
                'Titisee: rustpunt in het bos', 'Wat je moet weten over Berlijn',
                'New York met kids: onze survivalgids', 'Miami art deco: kleur op South Beach',
                'Onze eerste transatlantische vlucht', 'Familiereis naar de VS: onze kosten',
            ];

            foreach ($titles as $i => $title) {
                $location = $locations->random();
                $author = $authors->random();

                $post = Post::create([
                    'user_id' => $author->id,
                    'destination_id' => $location->destination_id,
                    'location_id' => $location->id,
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'excerpt' => "Korte intro voor: {$title}.",
                    'body' => '<p>'.fake()->paragraphs(4, true).'</p>',
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 180)),
                ]);

                // 1-2 categorieën
                $post->categories()->sync($categories->random(rand(1, 2))->pluck('id'));

                // 0-3 tags via HasTags trait
                $post->syncTagsByName($tags->random(rand(0, 3))->pluck('name')->all());
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
