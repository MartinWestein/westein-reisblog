<?php

test('de homepage rendert de SEO/OG/Twitter baseline-tags', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<link rel="canonical"', false)
        ->assertSee('property="og:type"', false)
        ->assertSee('property="og:site_name"', false)
        ->assertSee('property="og:image"', false)
        ->assertSee('images/og-default.jpg', false)
        ->assertSee('name="twitter:card" content="summary_large_image"', false);
});
