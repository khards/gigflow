<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class HomeController.
 */
class HomeController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index()
    {
        return view('frontend.index');
    }
}
