<?php

use App\Models\FamilyMember;
use App\Models\User;

test('family_member kan zonder user_id (los profiel)', function () {
    $fm = FamilyMember::create([
        'name' => 'Sophie',
        'bio' => 'Dochter van de familie.',
    ]);

    expect($fm->user_id)->toBeNull()
        ->and($fm->slug)->toBe('sophie');
});

test('family_member kan gekoppeld aan user (hybride)', function () {
    $user = User::factory()->create();
    $fm = FamilyMember::create([
        'user_id' => $user->id,
        'name' => 'Jan',
        'bio' => 'Vader.',
    ]);

    expect($fm->user->id)->toBe($user->id);
});

test('user delete zet family_member.user_id op null', function () {
    $user = User::factory()->create();
    $fm = FamilyMember::create([
        'user_id' => $user->id,
        'name' => 'Jan',
    ]);

    $user->delete();

    expect($fm->fresh()->user_id)->toBeNull();
});
