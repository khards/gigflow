<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Class LocaleController.
 */
class LocaleController extends Controller
{
    /**
     * @param $locale
     *
     * @return RedirectResponse
     */
    public function change($locale)
    {
        app()->setLocale($locale);

        session()->put('locale', $locale);

        return redirect()->back();
    }
}
