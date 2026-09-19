<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Persists the user's language choice in the session.
 *
 * Only locales whitelisted in config/clarif.php are accepted, so the route
 * parameter can never inject an arbitrary value into the session.
 */
class LocaleController extends Controller
{
    /**
     * Store the selected locale and send the user back where they came from.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(
            in_array($locale, config('clarif.supported_locales', ['en', 'es']), true),
            404,
        );

        $request->session()->put('locale', $locale);

        return redirect()->to(url()->previous());
    }
}
