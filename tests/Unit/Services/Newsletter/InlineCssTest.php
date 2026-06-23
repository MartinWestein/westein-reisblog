<?php

use App\Services\Newsletter\InlineCss;

it('inlinet CSS uit <style>-blok naar style-attributen op elementen', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<style>
p { color: #14213D; font-size: 16px; }
.lead { font-weight: bold; }
</style>
</head>
<body>
<p class="lead">Hallo wereld</p>
</body>
</html>
HTML;

    $result = strtolower(InlineCss::inline($html));

    expect($result)
        ->toContain('style=')
        ->toContain('color: #14213d')
        ->toContain('font-size: 16px')
        ->toContain('font-weight: bold');
});

it('laat HTML zonder <style>-blok ongewijzigd doorlopen', function () {
    $html = '<html><body><p>Hallo</p></body></html>';

    $result = InlineCss::inline($html);

    expect($result)->toContain('<p>Hallo</p>');
});
