<?php

use App\Models\Category;
use App\Models\Destination;
use App\Models\Location;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    // Permissies die PostPolicy nodig heeft
    $permissions = [
        'posts.viewAny', 'posts.view', 'posts.create',
        'posts.update.own', 'posts.update.any',
        'posts.delete.own', 'posts.delete.any',
        'posts.publish',
    ];
    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    foreach (['admin', 'editor', 'auteur', 'lid'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }

    // Editor: alles inclusief publish
    Role::findByName('editor')->syncPermissions($permissions);

    // Auteur: viewAny/view/create + own + GEEN publish (de hele 4.5-kern)
    Role::findByName('auteur')->syncPermissions([
        'posts.viewAny', 'posts.view', 'posts.create',
        'posts.update.own', 'posts.delete.own',
    ]);

    // Admin via Gate::before, lid krijgt niets

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create();
    $this->author->assignRole('auteur');

    $this->otherAuthor = User::factory()->create();
    $this->otherAuthor->assignRole('auteur');

    $this->member = User::factory()->create();
    $this->member->assignRole('lid');
});

/**
 * Helper: maakt de Tips-categorie met slug uit config — zo deelt de test
 * dezelfde bron als de Form Request (config('westein.general_tips_category_slug')).
 */
function tipsCategory(): Category
{
    return Category::factory()->create([
        'name' => 'Tips',
        'slug' => config('westein.general_tips_category_slug', 'tips'),
        'order' => 2,
    ]);
}

/**
 * Helper: minimale geldige post-payload met sane defaults.
 * Caller kan overschrijven via array_merge.
 */
function postPayload(array $overrides = []): array
{
    $location = Location::factory()->create();

    return array_merge([
        'title' => 'Testpost titel',
        'body' => '<p>Testtekst.</p>',
        'status' => 'draft',
        'destination_id' => $location->destination_id,
        'location_id' => $location->id,
        'categories' => [],
        'tags' => '',
    ], $overrides);
}

/*
|--------------------------------------------------------------------------
| RBAC — Toegang tot de index
|--------------------------------------------------------------------------
*/

it('toont de index voor een admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.posts.index'))
        ->assertOk();
});

it('toont de index voor een editor', function () {
    $this->actingAs($this->editor)
        ->get(route('admin.posts.index'))
        ->assertOk();
});

it('toont de index voor een auteur', function () {
    $this->actingAs($this->author)
        ->get(route('admin.posts.index'))
        ->assertOk();
});

it('weigert leden op de index', function () {
    $this->actingAs($this->member)
        ->get(route('admin.posts.index'))
        ->assertForbidden();
});

it('stuurt gasten naar login', function () {
    $this->get(route('admin.posts.index'))
        ->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| RBAC — own/any-matrix op update + destroy
|--------------------------------------------------------------------------
*/

it('staat een auteur toe een eigen post te bewerken', function () {
    $post = Post::factory()->create(['user_id' => $this->author->id]);

    $this->actingAs($this->author)
        ->get(route('admin.posts.edit', $post))
        ->assertOk();
});

it('weigert een auteur bij andermans post (edit)', function () {
    $post = Post::factory()->create(['user_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->get(route('admin.posts.edit', $post))
        ->assertForbidden();
});

it('weigert een auteur bij andermans post (update)', function () {
    $post = Post::factory()->create(['user_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->put(route('admin.posts.update', $post), postPayload(['title' => 'Geprobeerd']))
        ->assertForbidden();

    expect($post->fresh()->title)->not->toBe('Geprobeerd');
});

it('weigert een auteur bij andermans post (destroy)', function () {
    $post = Post::factory()->create(['user_id' => $this->otherAuthor->id]);

    $this->actingAs($this->author)
        ->delete(route('admin.posts.destroy', $post))
        ->assertForbidden();

    expect(Post::find($post->id))->not->toBeNull();
});

it('staat een editor toe andermans post te bewerken', function () {
    $post = Post::factory()->create(['user_id' => $this->author->id]);

    $this->actingAs($this->editor)
        ->put(route('admin.posts.update', $post), postPayload([
            'title' => 'Door editor aangepast',
            'destination_id' => $post->destination_id,
            'location_id' => $post->location_id,
        ]))
        ->assertRedirect(route('admin.posts.index'));

    expect($post->fresh()->title)->toBe('Door editor aangepast');
});

/*
|--------------------------------------------------------------------------
| CRUD — happy paths
|--------------------------------------------------------------------------
*/

it('maakt een post aan en redirect naar edit', function () {
    $location = Location::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Onze eerste dag in Rome',
            'body' => '<p>Wat een stad.</p>',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
            'categories' => [$category->id],
            'tags' => 'italië,roadtrip',
        ]);

    $post = Post::where('title', 'Onze eerste dag in Rome')->first();

    expect($post)->not->toBeNull()
        ->and($post->slug)->toBe('onze-eerste-dag-in-rome')
        ->and($post->status)->toBe('draft')
        ->and($post->published_at)->toBeNull()
        ->and($post->user_id)->toBe($this->admin->id)
        ->and($post->categories)->toHaveCount(1)
        ->and($post->tags->pluck('name')->all())->toEqualCanonicalizing(['italië', 'roadtrip']);

    // store → edit (Stap 4.5-beslissing)
    $response->assertRedirect(route('admin.posts.edit', $post));
});

it('werkt een post bij en re-synct relaties', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    // Eerst aankoppelen, dan zien of update ze vervangt
    $post->categories()->sync([$cat1->id]);
    $post->syncTagsByName(['oud']);

    $this->actingAs($this->admin)
        ->put(route('admin.posts.update', $post), postPayload([
            'title' => 'Bijgewerkt',
            'destination_id' => $post->destination_id,
            'location_id' => $post->location_id,
            'categories' => [$cat2->id],
            'tags' => 'nieuw,fris',
        ]))
        ->assertRedirect(route('admin.posts.index'));

    $fresh = $post->fresh();
    expect($fresh->title)->toBe('Bijgewerkt')
        ->and($fresh->categories->pluck('id')->all())->toBe([$cat2->id])
        ->and($fresh->tags->pluck('name')->all())->toEqualCanonicalizing(['nieuw', 'fris']);
});

it('verwijdert een post via soft delete', function () {
    $post = Post::factory()->create(['user_id' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.posts.destroy', $post))
        ->assertRedirect(route('admin.posts.index'));

    expect(Post::find($post->id))->toBeNull()
        ->and(Post::withTrashed()->find($post->id))->not->toBeNull()
        ->and(Post::withTrashed()->find($post->id)->deleted_at)->not->toBeNull();
});

it('slaat een featured image op bij store', function () {
    Storage::fake('public');
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Met foto',
            'body' => '<p>Tekst.</p>',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
            'featured' => UploadedFile::fake()->image('hero.jpg', 800, 600),
        ]);

    $post = Post::where('title', 'Met foto')->first();
    expect($post->getFirstMedia('featured'))->not->toBeNull();
});

/*
|--------------------------------------------------------------------------
| §3.4 — bestemming / locatie / Tips
|--------------------------------------------------------------------------
*/

it('weigert een post zonder bestemming en zonder Tips-categorie', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'destination_id' => null,
            'location_id' => null,
            'categories' => [],
        ]))
        ->assertSessionHasErrors('destination_id');
});

it('staat een Tips-post zonder bestemming toe (permissief, masterplan)', function () {
    $tips = tipsCategory();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'title' => 'Algemene tip',
            'destination_id' => null,
            'location_id' => null,
            'categories' => [$tips->id],
        ]))
        ->assertSessionHasNoErrors();

    expect(Post::where('title', 'Algemene tip')->exists())->toBeTrue();
});

it('staat een Tips-post met bestemming ook toe (permissief)', function () {
    $tips = tipsCategory();
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'title' => 'Tip met bestemming',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
            'categories' => [$tips->id],
        ]))
        ->assertSessionHasNoErrors();
});

it('weigert een locatie buiten de gekozen bestemming', function () {
    $destA = Destination::factory()->create();
    $destB = Destination::factory()->create();
    $locInB = Location::factory()->create(['destination_id' => $destB->id]);

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'destination_id' => $destA->id,
            'location_id' => $locInB->id,
        ]))
        ->assertSessionHasErrors('location_id');
});

it('weigert een locatie zonder bestemming', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'destination_id' => null,
            'location_id' => $location->id,
        ]))
        ->assertSessionHasErrors('destination_id');
});

it('staat een post zonder locatie maar mét bestemming toe', function () {
    $destination = Destination::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'title' => 'Bestemming-only',
            'destination_id' => $destination->id,
            'location_id' => null,
        ]))
        ->assertSessionHasNoErrors();

    expect(Post::where('title', 'Bestemming-only')->first()?->location_id)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Status-vork — alleen wie posts.publish heeft mag publiceren/inplannen
|--------------------------------------------------------------------------
*/

it('weigert een auteur die direct wil publiceren', function () {
    $this->actingAs($this->author)
        ->post(route('admin.posts.store'), postPayload([
            'status' => 'published',
        ]))
        ->assertSessionHasErrors('status');
});

it('weigert een auteur die wil inplannen', function () {
    $this->actingAs($this->author)
        ->post(route('admin.posts.store'), postPayload([
            'status' => 'scheduled',
            'published_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ]))
        ->assertSessionHasErrors('status');
});

it('staat een auteur toe een concept op te slaan', function () {
    $this->actingAs($this->author)
        ->post(route('admin.posts.store'), postPayload([
            'title' => 'Auteur concept',
            'status' => 'draft',
        ]))
        ->assertSessionHasNoErrors();

    $post = Post::where('title', 'Auteur concept')->first();
    expect($post->user_id)->toBe($this->author->id)
        ->and($post->status)->toBe('draft');
});

it('staat een editor toe direct te publiceren met published_at = nu', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.posts.store'), postPayload([
            'title' => 'Direct gepubliceerd',
            'status' => 'published',
        ]))
        ->assertSessionHasNoErrors();

    $post = Post::where('title', 'Direct gepubliceerd')->first();
    expect($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull()
        ->and($post->published_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('plant een post in voor een toekomstige datum', function () {
    $future = now()->addDays(7);

    $this->actingAs($this->editor)
        ->post(route('admin.posts.store'), postPayload([
            'title' => 'Geplande post',
            'status' => 'scheduled',
            'published_at' => $future->format('Y-m-d\TH:i'),
        ]))
        ->assertSessionHasNoErrors();

    $post = Post::where('title', 'Geplande post')->first();
    expect($post->status)->toBe('scheduled')
        ->and($post->published_at->isFuture())->toBeTrue();
});

it('weigert een geplande post met datum in het verleden', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.posts.store'), postPayload([
            'status' => 'scheduled',
            'published_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]))
        ->assertSessionHasErrors('published_at');
});

it('weigert een geplande post zonder datum', function () {
    $this->actingAs($this->editor)
        ->post(route('admin.posts.store'), postPayload([
            'status' => 'scheduled',
            'published_at' => null,
        ]))
        ->assertSessionHasErrors('published_at');
});

/*
|--------------------------------------------------------------------------
| Slug-locking bij update (tamper-bescherming)
|--------------------------------------------------------------------------
*/

it('negeert een meegestuurde slug bij update', function () {
    $post = Post::factory()->create([
        'user_id' => $this->admin->id,
        'slug' => 'beschermde-slug',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.posts.update', $post), postPayload([
            'title' => 'Nieuwe titel',
            'destination_id' => $post->destination_id,
            'location_id' => $post->location_id,
            'slug' => 'gekaapt',
        ]));

    expect($post->fresh()->slug)->toBe('beschermde-slug');
});

/*
|--------------------------------------------------------------------------
| Sanitization — HTMLPurifier 'rich'-profiel
|--------------------------------------------------------------------------
*/

it('verwijdert script-tags uit de body bij store', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'XSS test',
            'body' => '<p>Veilig.</p><script>alert("XSS")</script>',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
        ]);

    $body = Post::where('title', 'XSS test')->first()->body;

    expect($body)
        ->not->toContain('<script')
        ->not->toContain('alert(')
        ->toContain('Veilig.');
});

it('behoudt tabel-markup uit de rich editor', function () {
    $location = Location::factory()->create();
    $table = '<table><thead><tr><th>Kop</th></tr></thead>'
        .'<tbody><tr><td>Cel</td></tr></tbody></table>';

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Met tabel',
            'body' => "<p>Intro.</p>{$table}",
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
        ]);

    $body = Post::where('title', 'Met tabel')->first()->body;

    expect($body)
        ->toContain('<table')
        ->toContain('<th>Kop</th>')
        ->toContain('<td>Cel</td>');
});

it('behoudt img-tags met alignment-class (stap 4.6)', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Img behoud',
            'body' => '<p>Voor.</p>'
                .'<img class="img-align-left" src="https://example.com/foto.jpg" alt="Een foto">'
                .'<p>Na.</p>',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
        ]);

    $body = Post::where('title', 'Img behoud')->first()->body;

    expect($body)
        ->toContain('<img')
        ->toContain('src="https://example.com/foto.jpg"')
        ->toContain('alt="Een foto"')
        ->toContain('class="img-align-left"');
});

it('strip onbekende classes op img-tags', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Class-strip',
            'body' => '<img class="img-align-full evil-tracker-class" '
                .'src="https://example.com/x.jpg" alt="">',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
        ]);

    $body = Post::where('title', 'Class-strip')->first()->body;

    expect($body)
        ->toContain('img-align-full')
        ->not->toContain('evil-tracker-class');
});

it('strip img-tags met javascript: scheme', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'JS-scheme',
            'body' => '<img src="javascript:alert(1)" alt="">',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
        ]);

    expect(Post::where('title', 'JS-scheme')->first()->body)
        ->not->toContain('javascript:');
});

it('strip data:-URI src op img-tags', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Data-URI',
            'body' => '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA" alt="">',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
        ]);

    expect(Post::where('title', 'Data-URI')->first()->body)
        ->not->toContain('data:image');
});
/*
|--------------------------------------------------------------------------
| Tag-sync — komma-string + dedupe + hergebruik
|--------------------------------------------------------------------------
*/

it('splitst de komma-string en lowercaset tags', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Tag-test',
            'body' => '<p>Tekst.</p>',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
            'tags' => 'Italië, Roadtrip , CAMPER',
        ]);

    $post = Post::where('title', 'Tag-test')->first();

    expect($post->tags->pluck('name')->all())
        ->toEqualCanonicalizing(['italië', 'roadtrip', 'camper']);
});

it('hergebruikt bestaande tags zonder duplicaten aan te maken', function () {
    Tag::create(['name' => 'roadtrip', 'slug' => 'roadtrip']);
    $countBefore = Tag::count();

    $location = Location::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Hergebruik-test',
            'body' => '<p>Tekst.</p>',
            'status' => 'draft',
            'destination_id' => $location->destination_id,
            'location_id' => $location->id,
            'tags' => 'roadtrip,nieuw',
        ]);

    expect(Tag::count())->toBe($countBefore + 1)                                   // alleen 'nieuw' erbij
        ->and(Post::where('title', 'Hergebruik-test')->first()->tags)->toHaveCount(2);
});
