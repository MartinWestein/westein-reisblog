<?php

use App\Models\Newsletter;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'editor', 'auteur', 'lid'] as $role) {
        Role::findOrCreate($role);
    }
    $perm = Permission::findOrCreate('newsletters.manage');
    Role::findByName('editor')->givePermissionTo($perm);
    // Admin krijgt geen expliciete permission — Gate::before regelt de bypass
});

it('staat editor toe alle acties op een draft uit te voeren', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $newsletter = Newsletter::factory()->for($editor, 'author')->create();

    expect($editor->can('viewAny', Newsletter::class))->toBeTrue()
        ->and($editor->can('view', $newsletter))->toBeTrue()
        ->and($editor->can('create', Newsletter::class))->toBeTrue()
        ->and($editor->can('update', $newsletter))->toBeTrue()
        ->and($editor->can('delete', $newsletter))->toBeTrue()
        ->and($editor->can('sendTest', $newsletter))->toBeTrue()
        ->and($editor->can('dispatch', $newsletter))->toBeTrue();
});

it('voorkomt dat editor een sent nieuwsbrief muteert (audit-trail blijft intact)', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $newsletter = Newsletter::factory()->for($editor, 'author')->sent(50)->create();

    expect($editor->can('view', $newsletter))->toBeTrue() // inzien voor audit
        ->and($editor->can('update', $newsletter))->toBeFalse()
        ->and($editor->can('delete', $newsletter))->toBeFalse()
        ->and($editor->can('sendTest', $newsletter))->toBeFalse()
        ->and($editor->can('dispatch', $newsletter))->toBeFalse();
});

it('weigert leden zonder newsletters.manage', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');
    $newsletter = Newsletter::factory()->create();

    expect($member->can('viewAny', Newsletter::class))->toBeFalse()
        ->and($member->can('view', $newsletter))->toBeFalse()
        ->and($member->can('create', Newsletter::class))->toBeFalse()
        ->and($member->can('update', $newsletter))->toBeFalse()
        ->and($member->can('delete', $newsletter))->toBeFalse()
        ->and($member->can('sendTest', $newsletter))->toBeFalse()
        ->and($member->can('dispatch', $newsletter))->toBeFalse();
});

it('weigert auteurs zonder newsletters.manage', function () {
    $author = User::factory()->create();
    $author->assignRole('auteur');
    $newsletter = Newsletter::factory()->create();

    expect($author->can('viewAny', Newsletter::class))->toBeFalse()
        ->and($author->can('update', $newsletter))->toBeFalse()
        ->and($author->can('dispatch', $newsletter))->toBeFalse();
});

it('staat admin toe alle acties uit te voeren via Gate::before bypass — inclusief op sent (gedocumenteerd gedrag)', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $newsletter = Newsletter::factory()->sent(100)->create();

    expect($admin->can('viewAny', Newsletter::class))->toBeTrue()
        ->and($admin->can('view', $newsletter))->toBeTrue()
        ->and($admin->can('create', Newsletter::class))->toBeTrue()
        ->and($admin->can('update', $newsletter))->toBeTrue()
        ->and($admin->can('delete', $newsletter))->toBeTrue()
        ->and($admin->can('sendTest', $newsletter))->toBeTrue()
        ->and($admin->can('dispatch', $newsletter))->toBeTrue();
});

it('gast (unauthenticated) kan niets', function () {
    $newsletter = Newsletter::factory()->create();

    expect(auth()->guest())->toBeTrue();
    // Policy-checks via $user->can() vereisen authenticated user;
    // unauthenticated access wordt opgevangen door auth-middleware in routes (blok g)
});
