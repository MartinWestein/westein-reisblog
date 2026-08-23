<?php

use App\Mail\ContactMail;
use App\Models\Page;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    config(['honeypot.enabled' => false]);
});

it('toont de contactpagina met de Page-intro en het formulier', function () {
    Page::factory()->create([
        'title' => 'Contact',
        'slug' => 'contact',
        'body' => '<p>Zo bereik je ons.</p>',
        'published_at' => now()->subDay(),
    ]);

    get('/contact')
        ->assertOk()
        ->assertSee('Contact')
        ->assertSee('Zo bereik je ons', false)
        ->assertSee('name="message"', false);
});

it('verstuurt een geldig contactbericht en mailt naar de admin', function () {
    Mail::fake();

    post('/contact', [
        'name' => 'Test Bezoeker',
        'email' => 'bezoeker@example.com',
        'subject' => 'Een vraag over jullie reis',
        'message' => 'Dit is een testbericht met genoeg tekens.',
    ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('contact_success');

    Mail::assertQueued(ContactMail::class, function ($mail) {
        return $mail->hasTo(config('westein.contact.recipient'))
            && $mail->senderEmail === 'bezoeker@example.com';
    });
});

it('valideert de verplichte velden', function () {
    Mail::fake();

    post('/contact', [
        'name' => '',
        'email' => 'geen-geldig-email',
        'subject' => '',
        'message' => '',
    ])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

    Mail::assertNothingQueued();
});
