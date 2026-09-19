<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Renders the standalone "What is SARIF?" explainer page.
 */
class AboutController extends Controller
{
    /**
     * Show the SARIF explainer.
     */
    public function __invoke(): View
    {
        return view('about');
    }
}
