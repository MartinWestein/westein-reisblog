<?php

use function Pest\Laravel\get;

it('zet de basis security-headers op publieke responses', function () {
    $response = get('/');

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
});

it('stuurt geen CSP en geen HSTS in de testomgeving', function () {
    // CSP vuurt alleen in productie, HSTS alleen op https-requests.
    $response = get('/');

    expect($response->headers->has('Content-Security-Policy'))->toBeFalse();
    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});
