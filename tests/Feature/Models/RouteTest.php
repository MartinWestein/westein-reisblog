<?php

use App\Models\Destination;
use App\Models\Location;
use App\Models\Route;
use App\Models\RouteWaypoint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

test('route genereert slug uit name', function () {
    $route = Route::create([
        'name' => 'Italië roadtrip 2024',
        'destination_id' => Destination::factory()->create()->id,
    ]);

    expect($route->slug)->toBe('italie-roadtrip-2024');
});

test('route slug wijzigt niet bij name-update', function () {
    $route = Route::create([
        'name' => 'Originele naam',
        'destination_id' => Destination::factory()->create()->id,
    ]);
    $original = $route->slug;

    $route->update(['name' => 'Andere naam']);

    expect($route->fresh()->slug)->toBe($original);
});

test('route slug krijgt suffix bij naam-botsing (Sluggable lost dubbele af)', function () {
    Route::create([
        'name' => 'Dezelfde naam',
        'destination_id' => Destination::factory()->create()->id,
    ]);

    $second = Route::create([
        'name' => 'Dezelfde naam',
        'destination_id' => Destination::factory()->create()->id,
    ]);

    expect($second->slug)->not->toBe('dezelfde-naam')
        ->and($second->slug)->toStartWith('dezelfde-naam-');
});

test('route slug unique-constraint blokkeert duplicate inserts op DB-niveau', function () {
    Route::create([
        'name' => 'Eerste route',
        'destination_id' => Destination::factory()->create()->id,
    ]);

    // Eloquent omzeilen: directe INSERT die HasSlug niet aanraakt
    expect(fn () => DB::table('routes')->insert([
        'name' => 'Tweede route',
        'slug' => 'eerste-route',
        'destination_id' => Destination::factory()->create()->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('route locations zijn geordend via pivot order', function () {
    $route = Route::factory()->create();
    $loc1 = Location::factory()->create(['name' => 'Eerst']);
    $loc2 = Location::factory()->create(['name' => 'Tweede']);
    $loc3 = Location::factory()->create(['name' => 'Derde']);

    // Bewust niet op volgorde van id attachen
    $route->locations()->attach([
        $loc3->id => ['order' => 3],
        $loc1->id => ['order' => 1],
        $loc2->id => ['order' => 2],
    ]);

    $names = $route->locations->pluck('name')->all();

    expect($names)->toBe(['Eerst', 'Tweede', 'Derde']);
});

test('waypoint cascade-verwijdert bij route delete', function () {
    $route = Route::factory()->create();
    $loc = Location::factory()->create();
    $route->locations()->attach($loc->id, ['order' => 1]);

    expect($route->waypoints)->toHaveCount(1);

    $route->forceDelete();

    expect(RouteWaypoint::where('route_id', $route->id)->count())->toBe(0);
});

it('staat dezelfde location twee keer toe in dezelfde route (revisits)', function () {
    $route = Route::factory()->create();
    $loc = Location::factory()->create();

    $route->locations()->attach($loc->id, ['order' => 1]);
    $route->locations()->attach($loc->id, ['order' => 2]);

    expect($route->locations()->count())->toBe(2)
        ->and($route->locations()->get()->pluck('id')->all())->toBe([$loc->id, $loc->id]);
});
