<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class AccountController.
 */
class AccountController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index()
    {
        return view('frontend.user.account');
    }

    /**
     * @return Factory|View
     */
    public function calendar()
    {
        return view('frontend.user.calendar');
    }
}
