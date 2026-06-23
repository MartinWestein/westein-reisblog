<?php

namespace App\Services\Newsletter;

use Pelago\Emogrifier\CssInliner;

class InlineCss
{
    /**
     * Inline alle CSS uit <style>-blokken naar style-attributen op de elementen.
     *
     * Webmail-clients (Outlook, Yahoo, oude Gmail-app) negeren <head>-stylesheets;
     * alle styling moet als inline style="..." worden meegestuurd. Emogrifier
     * leest de <style>-blokken, matched ze tegen de DOM en schrijft inline.
     */
    public static function inline(string $html): string
    {
        return CssInliner::fromHtml($html)
            ->inlineCss()
            ->render();
    }
}
