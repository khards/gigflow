<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index()
    {
        return view('frontend.user.dashboard');
    }
}
