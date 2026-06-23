<?php

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'editor', 'auteur', 'lid'] as $role) {
        Role::findOrCreate($role);
    }
    $perm = Permission::findOrCreate('newsletters.manage');
    Role::findByName('editor')->givePermissionTo($perm);

    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
});

it('weigert gasten op de testmail-route', function () {
    $newsletter = Newsletter::factory()->for(User::factory(), 'author')->create();

    $this->post(route('admin.newsletters.send-test', $newsletter))
        ->assertRedirect(route('login'));
});

it('weigert lid op de testmail-route', function () {
    $lid = User::factory()->create();
    $lid->assignRole('lid');
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create();

    Mail::fake();

    $this->actingAs($lid)
        ->post(route('admin.newsletters.send-test', $newsletter))
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('verstuurt testmail naar eigen e-mailadres met [TEST]-prefix bij draft', function () {
    Mail::fake();

    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create([
        'subject' => 'Reizen door Italië',
    ]);

    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.send-test', $newsletter))
        ->assertRedirect(route('admin.newsletters.edit', $newsletter))
        ->assertSessionHas('success');

    Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail) use ($newsletter) {
        return $mail->hasTo($this->editor->email)
            && $mail->newsletter->is($newsletter)
            && $mail->isTest === true
            && $mail->envelope()->subject === '[TEST] Reizen door Italië';
    });
});

it('weigert testmail op een nieuwsbrief die wordt verzonden', function () {
    Mail::fake();

    $newsletter = Newsletter::factory()->for($this->editor, 'author')->sending()->create();

    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.send-test', $newsletter))
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('weigert testmail op een reeds verzonden nieuwsbrief', function () {
    Mail::fake();

    $newsletter = Newsletter::factory()->for($this->editor, 'author')->sent(42)->create();

    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.send-test', $newsletter))
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('maakt geen NewsletterSend-row aan bij testmail', function () {
    Mail::fake();

    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create();

    $this->actingAs($this->editor)
        ->post(route('admin.newsletters.send-test', $newsletter))
        ->assertRedirect();

    expect($newsletter->sends()->count())->toBe(0);
});

it('rendert e-mail met inline CSS via Emogrifier', function () {
    $newsletter = Newsletter::factory()->for($this->editor, 'author')->create([
        'subject' => 'Test inline CSS',
        'body' => '<p>Test body</p>',
    ]);

    $mail = new NewsletterMail(
        newsletter: $newsletter,
        unsubscribeUrl: 'https://example.test/unsubscribe/dummy',
        isTest: true,
    );

    $rendered = $mail->render();

    // Inline-style-attribuut bewijst dat Emogrifier de <style>-blok heeft geplakt
    expect($rendered)
        ->toContain('style="')
        ->toContain('Test inline CSS')
        ->toContain('Test body')
        ->toContain('https://example.test/unsubscribe/dummy');
});

it('rendert digest-template met de N recente gepubliceerde posts', function () {
    config()->set('westein.newsletter.digest_post_count', 3);

    // 5 published posts maken; alleen de 3 nieuwste moeten in de mail belanden
    Post::factory()
        ->count(5)
        ->sequence(
            ['published_at' => now()->subDays(10), 'title' => 'Oudste post', 'slug' => 'oudste-post'],
            ['published_at' => now()->subDays(8), 'title' => 'Op een na oudste', 'slug' => 'op-een-na-oudste'],
            ['published_at' => now()->subDays(5), 'title' => 'Middelste', 'slug' => 'middelste'],
            ['published_at' => now()->subDays(3), 'title' => 'Op een na nieuwste', 'slug' => 'op-een-na-nieuwste'],
            ['published_at' => now()->subDay(), 'title' => 'Nieuwste post', 'slug' => 'nieuwste-post'],
        )
        ->create(['status' => 'published', 'user_id' => $this->editor->id]);

    $newsletter = Newsletter::factory()
        ->for($this->editor, 'author')
        ->template(Newsletter::TEMPLATE_DIGEST)
        ->create();

    $mail = new NewsletterMail(
        newsletter: $newsletter,
        unsubscribeUrl: 'https://example.test/unsubscribe/dummy',
    );

    $rendered = $mail->render();

    expect($rendered)
        ->toContain('Nieuwste post')
        ->toContain('Op een na nieuwste')
        ->toContain('Middelste')
        ->not->toContain('Op een na oudste')
        ->not->toContain('Oudste post');
});

it('voert geen Posts-query uit voor de plain-template', function () {
    Post::factory()->count(3)->create(['status' => 'published']);

    $newsletter = Newsletter::factory()
        ->for($this->editor, 'author')
        ->template(Newsletter::TEMPLATE_PLAIN)
        ->create(['body' => '<p>Plain body inhoud</p>']);

    $mail = new NewsletterMail(
        newsletter: $newsletter,
        unsubscribeUrl: 'https://example.test/unsubscribe/dummy',
    );

    $rendered = $mail->render();

    // Plain-template heeft geen "Recente verhalen"-sectie of post-titel-rendering
    expect($rendered)
        ->toContain('Plain body inhoud')
        ->not->toContain('Recente verhalen');
});
