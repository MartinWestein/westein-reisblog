php<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // RBAC-setup per file (leerpunt #33)
    foreach (['comments.moderate', 'comments.delete'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $editor->syncPermissions(['comments.moderate', 'comments.delete']);

    Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);

    // Test-users met expliciete rol-toekenning (leerpunt #16)
    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');

    $this->auteur = User::factory()->create();
    $this->auteur->assignRole('auteur');

    $this->lid = User::factory()->create();
    $this->lid->assignRole('lid');

    // Comment-author zonder moderate-rol: garandeert dat creating-hook 'pending' blijft
    $this->commenter = User::factory()->create();
    $this->commenter->assignRole('lid');
});

// ────────────────────────────────────────────────────────────────────
// RBAC — index
// ────────────────────────────────────────────────────────────────────

test('editor mag de index zien', function () {
    Comment::factory()->for($this->commenter, 'author')->for(Post::factory()->create())->create();

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index'))
        ->assertOk();
});

test('auteur mag de index niet zien', function () {
    $this->actingAs($this->auteur)
        ->get(route('admin.comments.index'))
        ->assertForbidden();
});

test('lid mag de index niet zien', function () {
    $this->actingAs($this->lid)
        ->get(route('admin.comments.index'))
        ->assertForbidden();
});

test('gast wordt naar login gestuurd', function () {
    $this->get(route('admin.comments.index'))
        ->assertRedirect(route('login'));
});

// ────────────────────────────────────────────────────────────────────
// RBAC — moderate-acties
// ────────────────────────────────────────────────────────────────────

test('auteur mag niet modereren', function () {
    $comment = Comment::factory()->for($this->commenter, 'author')->for(Post::factory()->create())->create();

    $this->actingAs($this->auteur)
        ->patch(route('admin.comments.approve', $comment))
        ->assertForbidden();
});

test('lid mag niet verwijderen', function () {
    $comment = Comment::factory()->for($this->commenter, 'author')->for(Post::factory()->create())->create();

    $this->actingAs($this->lid)
        ->delete(route('admin.comments.destroy', $comment))
        ->assertForbidden();
});

// ────────────────────────────────────────────────────────────────────
// Transitie-logica
// ────────────────────────────────────────────────────────────────────

test('approve zet status op approved en timestamp approved_at', function () {
    $comment = Comment::factory()->for($this->commenter, 'author')->for(Post::factory()->create())->create();

    expect($comment->status)->toBe('pending')
        ->and($comment->approved_at)->toBeNull();

    $this->actingAs($this->editor)
        ->patch(route('admin.comments.approve', $comment))
        ->assertRedirect();

    $comment->refresh();

    expect($comment->status)->toBe('approved')
        ->and($comment->approved_at)->not->toBeNull();
});

test('reject wist approved_at en zet status op rejected', function () {
    // Start vanuit approved — de "ik heb me vergist"-flow
    $comment = Comment::factory()->approved()
        ->for($this->commenter, 'author')
        ->for(Post::factory()->create())
        ->create();

    expect($comment->approved_at)->not->toBeNull();

    $this->actingAs($this->editor)
        ->patch(route('admin.comments.reject', $comment))
        ->assertRedirect();

    $comment->refresh();

    expect($comment->status)->toBe('rejected')
        ->and($comment->approved_at)->toBeNull();
});

test('spam wist approved_at en zet status op spam', function () {
    $comment = Comment::factory()->approved()
        ->for($this->commenter, 'author')
        ->for(Post::factory()->create())
        ->create();

    $this->actingAs($this->editor)
        ->patch(route('admin.comments.spam', $comment))
        ->assertRedirect();

    $comment->refresh();

    expect($comment->status)->toBe('spam')
        ->and($comment->approved_at)->toBeNull();
});

// ────────────────────────────────────────────────────────────────────
// Statusfilter
// ────────────────────────────────────────────────────────────────────

test('index toont standaard alleen pending', function () {
    $post = Post::factory()->create();
    Comment::factory()->for($this->commenter, 'author')->for($post)->create();              // pending
    Comment::factory()->approved()->for($this->commenter, 'author')->for($post)->create();
    Comment::factory()->spam()->for($this->commenter, 'author')->for($post)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index'))
        ->assertViewHas('comments', fn ($p) => $p->total() === 1)
        ->assertViewHas('status', 'pending');
});

test('statusfilter all toont alle reacties', function () {
    $post = Post::factory()->create();
    Comment::factory()->count(2)->for($this->commenter, 'author')->for($post)->create();
    Comment::factory()->approved()->for($this->commenter, 'author')->for($post)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index', ['status' => 'all']))
        ->assertViewHas('comments', fn ($p) => $p->total() === 3);
});

test('onbekende status valt veilig terug op pending', function () {
    $post = Post::factory()->create();
    Comment::factory()->for($this->commenter, 'author')->for($post)->create();
    Comment::factory()->approved()->for($this->commenter, 'author')->for($post)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index', ['status' => 'banaan']))
        ->assertViewHas('status', 'pending')
        ->assertViewHas('comments', fn ($p) => $p->total() === 1);
});

// ────────────────────────────────────────────────────────────────────
// Zoekfilter
// ────────────────────────────────────────────────────────────────────

test('zoekt op body-inhoud', function () {
    $post = Post::factory()->create();
    Comment::factory()->for($this->commenter, 'author')->for($post)->create(['body' => 'Spectaculaire zonsondergang!']);
    Comment::factory()->for($this->commenter, 'author')->for($post)->create(['body' => 'Heerlijke pasta gegeten.']);

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index', ['q' => 'zonsondergang']))
        ->assertViewHas('comments', fn ($p) => $p->total() === 1);
});

test('zoekt op auteur-naam', function () {
    $piet = User::factory()->create(['name' => 'Piet Pluk']);
    $piet->assignRole('lid');
    $klaas = User::factory()->create(['name' => 'Klaas Vaak']);
    $klaas->assignRole('lid');

    $post = Post::factory()->create();
    Comment::factory()->for($piet, 'author')->for($post)->create();
    Comment::factory()->for($klaas, 'author')->for($post)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index', ['q' => 'Pluk']))
        ->assertViewHas('comments', fn ($p) => $p->total() === 1);
});

// ────────────────────────────────────────────────────────────────────
// Verwijderen
// ────────────────────────────────────────────────────────────────────

test('editor verwijdert een reactie hard (geen soft-delete)', function () {
    $comment = Comment::factory()->for($this->commenter, 'author')->for(Post::factory()->create())->create();

    $this->actingAs($this->editor)
        ->delete(route('admin.comments.destroy', $comment))
        ->assertRedirect();

    expect(Comment::find($comment->id))->toBeNull();
});

// ────────────────────────────────────────────────────────────────────
// View-data
// ────────────────────────────────────────────────────────────────────

test('view ontvangt counts per status', function () {
    $post = Post::factory()->create();
    Comment::factory()->count(3)->for($this->commenter, 'author')->for($post)->create();    // pending
    Comment::factory()->approved()->count(2)->for($this->commenter, 'author')->for($post)->create();
    Comment::factory()->spam()->for($this->commenter, 'author')->for($post)->create();

    $this->actingAs($this->editor)
        ->get(route('admin.comments.index'))
        ->assertViewHas('counts', function ($counts) {
            return $counts['pending'] === 3
                && $counts['approved'] === 2
                && $counts['spam'] === 1;
        });
});
