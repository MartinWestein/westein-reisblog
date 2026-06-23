<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="nl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $newsletter->subject }}</title>
<style>
/*
 * Westein Reis Blog - newsletter base styles.
 * Deze regels worden door Emogrifier inline geplakt voor verzending.
 * Houd 't simpel: geen pseudo-classes, geen media-queries die Outlook breekt,
 * geen modern CSS. Tabellen + inline waar mogelijk.
 */

/* Reset */
body { margin: 0; padding: 0; background-color: #F8F6F2; }
table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
img { border: 0; outline: none; text-decoration: none; display: block; max-width: 100%; height: auto; }
a { color: #41B3A3; text-decoration: underline; }

/* Outer */
.email-wrapper { width: 100%; background-color: #F8F6F2; padding: 24px 0; }
.email-container { width: 600px; background-color: #FFFFFF; margin: 0 auto; }

/* Header */
.email-header { padding: 32px 32px 16px 32px; text-align: center; }
.email-header-image { width: 100%; max-width: 536px; height: auto; }
.email-brand { font-family: Georgia, 'Times New Roman', serif; font-size: 14px; color: #14213D; letter-spacing: 2px; text-transform: uppercase; padding: 24px 32px 0 32px; }

/* Body */
.email-body { padding: 24px 32px 32px 32px; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #14213D; }
.email-body h1 { font-family: Georgia, 'Times New Roman', serif; font-size: 28px; line-height: 1.25; color: #14213D; margin: 0 0 16px 0; font-weight: normal; }
.email-body h2 { font-family: Georgia, 'Times New Roman', serif; font-size: 22px; line-height: 1.3; color: #14213D; margin: 24px 0 12px 0; font-weight: normal; }
.email-body h3 { font-family: Georgia, 'Times New Roman', serif; font-size: 18px; line-height: 1.3; color: #14213D; margin: 20px 0 10px 0; font-weight: normal; }
.email-body p { margin: 0 0 16px 0; }
.email-body ul, .email-body ol { margin: 0 0 16px 0; padding-left: 24px; }
.email-body li { margin-bottom: 6px; }
.email-body blockquote { margin: 16px 0; padding: 8px 16px; border-left: 3px solid #E8A87C; color: #14213D; background-color: #F8F6F2; }
.email-body a { color: #41B3A3; }

/* Divider */
.email-divider { border: 0; border-top: 1px solid #E5E0D6; margin: 24px 0; }

/* Post-item (digest) */
.post-item { padding: 16px 0; }
.post-item-title { font-family: Georgia, 'Times New Roman', serif; font-size: 20px; line-height: 1.3; color: #14213D; margin: 0 0 8px 0; font-weight: normal; }
.post-item-title a { color: #14213D; text-decoration: none; }
.post-item-meta { font-size: 13px; color: #6C7A89; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px 0; }
.post-item-excerpt { margin: 0 0 8px 0; }
.post-item-link { color: #41B3A3; font-weight: bold; text-decoration: none; }

/* CTA */
.email-cta { padding: 16px 0; text-align: left; }
.email-cta-button { display: inline-block; padding: 12px 24px; background-color: #41B3A3; color: #FFFFFF; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; text-decoration: none; border-radius: 3px; }

/* Footer */
.email-footer { padding: 24px 32px 32px 32px; font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #6C7A89; text-align: center; background-color: #F8F6F2; }
.email-footer p { margin: 0 0 8px 0; }
.email-footer a { color: #6C7A89; }
</style>
</head>
<body>
<table role="presentation" class="email-wrapper" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td align="center">

<table role="presentation" class="email-container" cellpadding="0" cellspacing="0" border="0" width="600">

@hasSection('header')
<tr>
<td class="email-header">
@yield('header')
</td>
</tr>
@else
<tr>
<td class="email-brand">{{ config('app.name') }}</td>
</tr>
@endif

<tr>
<td class="email-body">
@yield('body')
</td>
</tr>

</table>

<table role="presentation" class="email-container" cellpadding="0" cellspacing="0" border="0" width="600">
<tr>
<td class="email-footer">
<p>{{ __('Je ontvangt deze e-mail omdat je je hebt aangemeld voor de nieuwsbrief van :app.', ['app' => config('app.name')]) }}</p>
<p><a href="{{ $unsubscribeUrl }}">{{ __('Uitschrijven') }}</a></p>
</td>
</tr>
</table>

</td>
</tr>
</table>
</body>
</html>
