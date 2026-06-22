<?php

use App\Models\Newsletter;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('maakt een nieuwsbrief aan met draft status en plain template als default', function () {
    $newsletter = Newsletter::factory()->for($this->user, 'author')->create();

    expect($newsletter->status)->toBe(Newsletter::STATUS_DRAFT)
        ->and($newsletter->template)->toBe(Newsletter::TEMPLATE_PLAIN)
        ->and($newsletter->isDraft())->toBeTrue()
        ->and($newsletter->isEditable())->toBeTrue()
        ->and($newsletter->isSent())->toBeFalse()
        ->and($newsletter->author->id)->toBe($this->user->id);
});

it('sent-state markeert een nieuwsbrief als verzonden met recipients_count', function () {
    $newsletter = Newsletter::factory()->for($this->user, 'author')->sent(247)->create();

    expect($newsletter->isSent())->toBeTrue()
        ->and($newsletter->sent_at)->not->toBeNull()
        ->and($newsletter->recipients_count)->toBe(247)
        ->and($newsletter->isEditable())->toBeFalse();
});

it('exposeert precies drie geldige templates', function () {
    expect(Newsletter::TEMPLATES)->toBe([
        Newsletter::TEMPLATE_ANNOUNCEMENT,
        Newsletter::TEMPLATE_DIGEST,
        Newsletter::TEMPLATE_PLAIN,
    ]);
});

it('registreert een single-file header media collection', function () {
    $newsletter = Newsletter::factory()->for($this->user, 'author')->create();

    $header = $newsletter->getRegisteredMediaCollections()->firstWhere('name', 'header');

    expect($header)->not->toBeNull()
        ->and($header->singleFile)->toBeTrue();
});
