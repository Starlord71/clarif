<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Renders the standalone "Help" page explaining how the app works.
 */
class HelpController extends Controller
{
    /**
     * Show the help guide.
     */
    public function __invoke(): View
    {
        return view('help');
    }
}
