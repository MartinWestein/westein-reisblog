<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Route;
use App\Models\RouteWaypoint;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function () {
    // Permissions + rollen lokaal voor deze suite (Pest.php draait alleen RefreshDatabase).
    Permission::firstOrCreate(['name' => 'content.manage', 'guard_name' => 'web']);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);

    $editor->givePermissionTo('content.manage');

    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->assignRole('admin');

    $this->editor = User::factory()->create(['email_verified_at' => now()]);
    $this->editor->assignRole('editor');

    $this->author = User::factory()->create(['email_verified_at' => now()]);
    $this->author->assignRole('auteur');

    $this->member = User::factory()->create(['email_verified_at' => now()]);
    $this->member->assignRole('lid');

    $this->destination = Destination::factory()->create([
        'name' => 'Italië',
        'slug' => 'italie',
    ]);

    $this->locA = Location::factory()->for($this->destination)->create([
        'name' => 'Rome',
        'latitude' => 41.9028,
        'longitude' => 12.4964,
    ]);
    $this->locB = Location::factory()->for($this->destination)->create([
        'name' => 'Florence',
        'latitude' => 43.7696,
        'longitude' => 11.2558,
    ]);
    $this->locC = Location::factory()->for($this->destination)->create([
        'name' => 'Venetië',
        'latitude' => 45.4408,
        'longitude' => 12.3155,
    ]);

    $this->otherDestination = Destination::factory()->create([
        'name' => 'Schotland',
        'slug' => 'schotland',
    ]);
    $this->locScotland = Location::factory()->for($this->otherDestination)->create([
        'name' => 'Edinburgh',
    ]);
});

// ============================================================
// RBAC
// ============================================================

it('redirects guest to login on routes index', function () {
    get(route('admin.reisroutes.index'))->assertRedirect('/login');
});

it('forbids lid (no admin-role) to access routes admin', function () {
    actingAs($this->member)
        ->get(route('admin.reisroutes.index'))
        ->assertForbidden();
});

it('forbids auteur (no content.manage) to view routes index', function () {
    actingAs($this->author)
        ->get(route('admin.reisroutes.index'))
        ->assertForbidden();
});

it('allows editor to view routes index', function () {
    actingAs($this->editor)
        ->get(route('admin.reisroutes.index'))
        ->assertOk();
});

it('allows admin to view routes index (via Gate::before)', function () {
    actingAs($this->admin)
        ->get(route('admin.reisroutes.index'))
        ->assertOk();
});

it('forbids auteur to create a route', function () {
    actingAs($this->author)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Probeertje',
        ])
        ->assertForbidden();
});

// ============================================================
// Index filters
// ============================================================

it('filters routes by search query on name', function () {
    Route::factory()->for($this->destination)->create(['name' => 'Italiaanse roadtrip']);
    Route::factory()->for($this->destination)->create(['name' => 'Wandelvakantie']);

    actingAs($this->editor)
        ->get(route('admin.reisroutes.index', ['search' => 'roadtrip']))
        ->assertOk()
        ->assertSee('Italiaanse roadtrip')
        ->assertDontSee('Wandelvakantie');
});

it('filters routes by destination slug', function () {
    Route::factory()->for($this->destination)->create(['name' => 'Italiëroute']);
    Route::factory()->for($this->otherDestination)->create(['name' => 'Schotlandroute']);

    actingAs($this->editor)
        ->get(route('admin.reisroutes.index', ['destination' => 'italie']))
        ->assertOk()
        ->assertSee('Italiëroute')
        ->assertDontSee('Schotlandroute');
});

it('filters routes by status published', function () {
    Route::factory()->for($this->destination)->create([
        'name' => 'Gepubliceerde route',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    Route::factory()->for($this->destination)->create([
        'name' => 'Conceptroute',
        'is_published' => false,
    ]);

    actingAs($this->editor)
        ->get(route('admin.reisroutes.index', ['status' => 'published']))
        ->assertOk()
        ->assertSee('Gepubliceerde route')
        ->assertDontSee('Conceptroute');
});

it('filters routes by status draft', function () {
    Route::factory()->for($this->destination)->create([
        'name' => 'Gepubliceerde route',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    Route::factory()->for($this->destination)->create([
        'name' => 'Conceptroute',
        'is_published' => false,
    ]);

    actingAs($this->editor)
        ->get(route('admin.reisroutes.index', ['status' => 'draft']))
        ->assertOk()
        ->assertSee('Conceptroute')
        ->assertDontSee('Gepubliceerde route');
});

// ============================================================
// Store
// ============================================================

it('stores a new route with waypoints in submitted order', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Italiaanse roadtrip 2024',
            'description' => '<p>Een mooie reis.</p>',
            'travel_date' => '2024-07-01',
            'is_published' => '1',
            'waypoints' => json_encode([
                ['location_id' => $this->locC->id, 'notes' => 'Eerst'],
                ['location_id' => $this->locA->id, 'notes' => 'Tweede'],
                ['location_id' => $this->locB->id, 'notes' => 'Derde'],
            ]),
        ])
        ->assertRedirect();

    $route = Route::where('name', 'Italiaanse roadtrip 2024')->firstOrFail();
    expect($route->slug)->toBe('italiaanse-roadtrip-2024');
    expect($route->is_published)->toBeTrue();
    expect($route->published_at)->not->toBeNull();
    expect($route->waypoints)->toHaveCount(3);
    expect($route->waypoints->pluck('location_id')->all())->toBe([
        $this->locC->id, $this->locA->id, $this->locB->id,
    ]);
    expect($route->waypoints->pluck('order')->all())->toBe([0, 1, 2]);
});

it('stores a route without waypoints', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Leeg test',
        ])
        ->assertRedirect();

    $route = Route::where('name', 'Leeg test')->firstOrFail();
    expect($route->waypoints)->toHaveCount(0);
});

it('requires destination and name', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [])
        ->assertSessionHasErrors(['destination_id', 'name']);
});

it('rejects waypoints from outside the chosen destination (§3.4)', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Verkeerde route',
            'waypoints' => json_encode([
                ['location_id' => $this->locScotland->id, 'notes' => ''],
            ]),
        ])
        ->assertSessionHasErrors(['waypoints']);

    expect(Route::where('name', 'Verkeerde route')->exists())->toBeFalse();
});

it('sanitizes description via Purifier simple profile', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'XSS test',
            'description' => '<p>Veilig</p><script>alert("xss")</script><strong>vet</strong>',
        ])
        ->assertRedirect();

    $description = Route::where('name', 'XSS test')->firstOrFail()->description;
    expect($description)->toContain('<p>Veilig</p>');
    expect($description)->toContain('<strong>vet</strong>');
    expect($description)->not->toContain('<script>');
});

it('allows revisits — same location twice in waypoints', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Rondreis',
            'waypoints' => json_encode([
                ['location_id' => $this->locA->id, 'notes' => 'Start'],
                ['location_id' => $this->locB->id, 'notes' => 'Middel'],
                ['location_id' => $this->locA->id, 'notes' => 'Einde'],
            ]),
        ])
        ->assertRedirect();

    $route = Route::where('name', 'Rondreis')->firstOrFail();
    expect($route->waypoints)->toHaveCount(3);
    expect($route->waypoints->pluck('location_id')->all())->toBe([
        $this->locA->id, $this->locB->id, $this->locA->id,
    ]);
});

// ============================================================
// Update
// ============================================================

it('updates a route and replaces waypoints via delete-then-recreate', function () {
    $route = Route::factory()->for($this->destination)->create(['name' => 'Origineel']);
    RouteWaypoint::create(['route_id' => $route->id, 'location_id' => $this->locA->id, 'order' => 0]);
    RouteWaypoint::create(['route_id' => $route->id, 'location_id' => $this->locB->id, 'order' => 1]);

    actingAs($this->editor)
        ->patch(route('admin.reisroutes.update', $route), [
            'destination_id' => $this->destination->id,
            'name' => 'Bijgewerkt',
            'waypoints' => json_encode([
                ['location_id' => $this->locC->id, 'notes' => 'enige'],
            ]),
        ])
        ->assertRedirect();

    $route->refresh();
    expect($route->name)->toBe('Bijgewerkt');
    expect($route->waypoints)->toHaveCount(1);
    expect($route->waypoints[0]->location_id)->toBe($this->locC->id);
});

it('keeps slug locked on update even when tampered (Pages-patroon)', function () {
    $route = Route::factory()->for($this->destination)->create([
        'name' => 'Origineel',
        'slug' => 'origineel',
    ]);

    actingAs($this->editor)
        ->patch(route('admin.reisroutes.update', $route), [
            'destination_id' => $this->destination->id,
            'name' => 'Nieuwe naam',
            'slug' => 'gehackte-slug',
        ])
        ->assertRedirect();

    expect($route->refresh()->slug)->toBe('origineel');
});

it('preserves published_at on unpublish (history)', function () {
    $publishedAt = now()->subDays(3);
    $route = Route::factory()->for($this->destination)->create([
        'name' => 'Was gepubliceerd',
        'is_published' => true,
        'published_at' => $publishedAt,
    ]);

    actingAs($this->editor)
        ->patch(route('admin.reisroutes.update', $route), [
            'destination_id' => $this->destination->id,
            'name' => 'Was gepubliceerd',
            // geen is_published in payload = $this->boolean() returnt false
        ])
        ->assertRedirect();

    $route->refresh();
    expect($route->is_published)->toBeFalse();
    expect($route->published_at)->not->toBeNull();
    expect($route->published_at->toDateString())->toBe($publishedAt->toDateString());
});

// ============================================================
// Delete
// ============================================================

it('soft-deletes a route', function () {
    $route = Route::factory()->for($this->destination)->create(['name' => 'Te verwijderen']);

    actingAs($this->editor)
        ->delete(route('admin.reisroutes.destroy', $route))
        ->assertRedirect();

    expect(Route::find($route->id))->toBeNull();
    expect(Route::withTrashed()->find($route->id)->deleted_at)->not->toBeNull();
});

// ============================================================
// Published scope
// ============================================================

it('published scope excludes drafts', function () {
    Route::factory()->for($this->destination)->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    Route::factory()->for($this->destination)->create([
        'is_published' => false,
    ]);

    expect(Route::published()->count())->toBe(1);
});

it('published scope excludes future-scheduled', function () {
    Route::factory()->for($this->destination)->create([
        'is_published' => true,
        'published_at' => now()->addWeek(),
    ]);

    expect(Route::published()->count())->toBe(0);
});

// ============================================================
// Hero-fallback
// ============================================================

it('displayHeroUrl returns null without hero and without waypoints', function () {
    $route = Route::factory()->for($this->destination)->create();

    expect($route->displayHeroUrl())->toBeNull();
});

it('displayHeroUrl returns null when waypoint location has no gallery', function () {
    $route = Route::factory()->for($this->destination)->create();
    RouteWaypoint::create(['route_id' => $route->id, 'location_id' => $this->locA->id, 'order' => 0]);

    expect($route->fresh()->displayHeroUrl())->toBeNull();
});

it('displayHeroUrl returns own hero when present', function () {
    Storage::fake('public');
    $route = Route::factory()->for($this->destination)->create();
    $route->addMedia(UploadedFile::fake()->image('hero.jpg', 1200, 675))
        ->toMediaCollection('hero');

    expect($route->fresh()->displayHeroUrl())->not->toBeNull();
});

it('displayHeroUrl falls back to first waypoint gallery photo', function () {
    Storage::fake('public');
    $route = Route::factory()->for($this->destination)->create();
    RouteWaypoint::create(['route_id' => $route->id, 'location_id' => $this->locA->id, 'order' => 0]);

    $this->locA->addMedia(UploadedFile::fake()->image('rome.jpg', 1200, 675))
        ->toMediaCollection('gallery');

    expect($route->fresh()->displayHeroUrl())->not->toBeNull();
});

// ============================================================
// Uitlichten — is_featured toggle (5.1.b-ii)
// ============================================================

it('maakt een route aan met is_featured aangevinkt', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Beste route',
            'is_featured' => '1',
        ])
        ->assertRedirect();

    expect(Route::firstWhere('name', 'Beste route')->is_featured)->toBeTrue();
});

it('slaat is_featured standaard op als false bij aanmaken zonder toggle', function () {
    actingAs($this->editor)
        ->post(route('admin.reisroutes.store'), [
            'destination_id' => $this->destination->id,
            'name' => 'Gewone route',
        ])
        ->assertRedirect();

    expect(Route::firstWhere('name', 'Gewone route')->is_featured)->toBeFalse();
});

it('zet is_featured aan via update op een route', function () {
    $route = Route::factory()->for($this->destination)->create(['is_featured' => false]);

    actingAs($this->editor)
        ->put(route('admin.reisroutes.update', $route), [
            'destination_id' => $this->destination->id,
            'name' => $route->name,
            'is_featured' => '1',
        ]);

    expect($route->fresh()->is_featured)->toBeTrue();
});

it('zet is_featured weer uit via update wanneer checkbox niet aangevinkt is (route)', function () {
    $route = Route::factory()->for($this->destination)->create(['is_featured' => true]);

    actingAs($this->editor)
        ->put(route('admin.reisroutes.update', $route), [
            'destination_id' => $this->destination->id,
            'name' => $route->name,
            // is_featured bewust weggelaten — simuleert een uitgevinkte checkbox
        ]);

    expect($route->fresh()->is_featured)->toBeFalse();
});

it('toont een uitgelicht-badge op de routes-index bij is_featured=true', function () {
    Route::factory()->for($this->destination)->create(['name' => 'Beste route', 'is_featured' => true]);
    Route::factory()->for($this->destination)->create(['name' => 'Middelmatig', 'is_featured' => false]);

    $response = actingAs($this->admin)
        ->get(route('admin.reisroutes.index'))
        ->assertOk()
        ->assertSee('Beste route')
        ->assertSee('Middelmatig');

    // Badge komt precies één keer voor — alleen bij de featured route
    expect(substr_count($response->getContent(), 'Uitgelicht'))->toBe(1);
});

it('scopeFeatured pickt alleen routes met is_featured=true', function () {
    Route::factory()->for($this->destination)->create(['name' => 'A', 'is_featured' => true]);
    Route::factory()->for($this->destination)->create(['name' => 'B', 'is_featured' => false]);
    Route::factory()->for($this->destination)->create(['name' => 'C', 'is_featured' => true]);

    $featured = Route::featured()->pluck('name')->toArray();

    expect($featured)->toHaveCount(2)
        ->and($featured)->toContain('A', 'C')
        ->and($featured)->not->toContain('B');
});
