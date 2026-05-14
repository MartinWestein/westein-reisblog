<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('comment van admin wordt direct approved via hook', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $post = Post::factory()->create();

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $admin->id,
        'body' => 'Admin-reactie',
    ]);

    expect($comment->status)->toBe('approved')
        ->and($comment->approved_at)->not->toBeNull();
});

test('comment van editor wordt direct approved via hook', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $post = Post::factory()->create();

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $editor->id,
        'body' => 'Editor-reactie',
    ]);

    expect($comment->status)->toBe('approved');
});

test('comment van auteur wordt pending via hook', function () {
    $author = User::factory()->create();
    $author->assignRole('auteur');

    $post = Post::factory()->create();

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'Auteur-reactie',
    ]);

    expect($comment->status)->toBe('pending')
        ->and($comment->approved_at)->toBeNull();
});

test('comment van lid wordt pending via hook', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');

    $post = Post::factory()->create();

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $member->id,
        'body' => 'Lid-reactie',
    ]);

    expect($comment->status)->toBe('pending');
});

test('expliciete status overschrijft hook', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $comment = Comment::create([
        'post_id' => Post::factory()->create()->id,
        'user_id' => $admin->id,
        'body' => 'Admin maar handmatig op spam',
        'status' => 'spam',
    ]);

    expect($comment->status)->toBe('spam');
});

test('reply op top-level comment is toegestaan', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $post = Post::factory()->create();

    $parent = Comment::create([
        'post_id' => $post->id,
        'user_id' => $admin->id,
        'body' => 'Top-level',
    ]);

    $reply = Comment::create([
        'post_id' => $post->id,
        'user_id' => $admin->id,
        'parent_id' => $parent->id,
        'body' => 'Reply',
    ]);

    expect($reply->parent_id)->toBe($parent->id);
});

test('reply op reply gooit ValidationException (max 1 niveau diep)', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $post = Post::factory()->create();

    $parent = Comment::create([
        'post_id' => $post->id,
        'user_id' => $admin->id,
        'body' => 'Top-level',
    ]);

    $reply = Comment::create([
        'post_id' => $post->id,
        'user_id' => $admin->id,
        'parent_id' => $parent->id,
        'body' => 'Reply',
    ]);

    expect(fn () => Comment::create([
        'post_id' => $post->id,
        'user_id' => $admin->id,
        'parent_id' => $reply->id,
        'body' => 'Reply op reply',
    ]))->toThrow(ValidationException::class);
});

test('approved scope filtert op status approved', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $member = User::factory()->create()->assignRole('lid');
    $post = Post::factory()->create();

    Comment::create(['post_id' => $post->id, 'user_id' => $admin->id, 'body' => 'a']);
    Comment::create(['post_id' => $post->id, 'user_id' => $member->id, 'body' => 'b']);

    expect(Comment::approved()->count())->toBe(1)
        ->and(Comment::pending()->count())->toBe(1);
});

test('topLevel scope filtert op parent_id is null', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $post = Post::factory()->create();

    $parent = Comment::create(['post_id' => $post->id, 'user_id' => $admin->id, 'body' => 'parent']);
    Comment::create(['post_id' => $post->id, 'user_id' => $admin->id, 'parent_id' => $parent->id, 'body' => 'reply']);

    expect(Comment::topLevel()->count())->toBe(1);
});

test('comment cascade-verwijdert bij post delete', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $post = Post::factory()->create();

    Comment::create(['post_id' => $post->id, 'user_id' => $admin->id, 'body' => 'x']);

    expect(Comment::count())->toBe(1);

    $post->delete();

    expect(Comment::count())->toBe(0);
});

test('replies cascaden mee als parent comment wordt verwijderd', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $post = Post::factory()->create();

    $parent = Comment::create(['post_id' => $post->id, 'user_id' => $admin->id, 'body' => 'parent']);
    Comment::create(['post_id' => $post->id, 'user_id' => $admin->id, 'parent_id' => $parent->id, 'body' => 'reply']);

    expect(Comment::count())->toBe(2);

    $parent->delete();

    expect(Comment::count())->toBe(0);
});
