<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(string $locale, Request $request): RedirectResponse
    {
        if (! in_array($locale, config('app.supported_locales'), true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return back();
    }
}
