<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * @return View
     */
    public function index()
    {
        return view('backend.dashboard');
    }
}
