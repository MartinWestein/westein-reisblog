<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Toon de "Mijn account"-pagina met drie kaarten:
     * persoonlijke gegevens, wachtwoord, en tweefactor-authenticatie.
     */
    public function show(): View
    {
        return view('account.show', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Werk de persoonlijke gegevens bij (alleen naam — email-change gaat
     * via de admin per F4-U2, en de rol is admin-only).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        auth()->user()->update($validated);

        return redirect()
            ->route('account.show')
            ->with('success', 'Persoonlijke gegevens bijgewerkt.');
    }
}
