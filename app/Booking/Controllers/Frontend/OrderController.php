<?php

namespace App\Booking\Controllers\Frontend;

use App\Booking\Business;
use App\Domains\Order\Order;
use App\Http\Controllers\Controller;

/**
 * Class OrderController.
 */
class OrderController extends Controller
{
    /**
     * Get all orders and render a table.
     *
     * @param $businessId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function all(Business $businessId)
    {
        $business = $businessId;

        return view('frontend.user.orders.orders', compact(['business']));
    }

    /**
     * edit / an order.
     *
     * @param \App\Domains\Order\Order $order
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($orderId)
    {
        $order = Order::find($orderId);

        return view('frontend.user.orders.edit', compact(['order']));
    }

    /**
     * Return the order widget screen.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Business $business)
    {
        // Eventually, this will be pulled from a wordpress plugin.
        // Might want to add a url here too :-)
        //
        $config = [
            'businessId' => $business->id,
            'token' => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZDY2ODFlZmE0MTM4MDVmYzllMDRhZTY2YjExNzcxNDBjYmFlNWNjYzM0ZTdkNGYzMDM4OWZjMTZiZjJhNDQzNzQzMzA2NjU3NDhmZjQ1YTgiLCJpYXQiOjE2NzYwMTkyOTguNDQxODI2LCJuYmYiOjE2NzYwMTkyOTguNDQxODI4LCJleHAiOjE2OTE2NTc2OTguNDAxMTg4LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.DUA-UFbm5W5qkIWCVcgB4XOwI5TBI_NOucIQ89QELtxt_2k0Q_54UKCVYuOR51dxXeYkcuZQZt6RNX045Ea5rxQ5Xq_DUfWkGnVpbU7-BODXG43LSuCtmwew60KvCpXCHVZtImvIGMN21IWKwrqyzYVwng8mEjpH9xCw9msRy62K0O-5p1fZAKGhCqWltzWvZ2ert9SzpjLcsBCkuscWhBeh1uWyUEMAE345BALkhi2UuJdeV-Tn4Af2H_Ot3aVAJ1kB0gU_J5OxpidQN04l-y-sNt-L9zrqxwSitZSx7S8tpD1wvCKJanb5jUU0tkuODUrJKLtD6j5SflECgMtc4LFJSHt7ywp6evzOyEaJQV7N8vU1-mTAAjymSctyd0ffbP5WTovaFlOsY2SlO_tvi_Dz1zhYu2pgCU4g7xxQUthWSoiN-Mi9EBVVC2uJWIkbZ63URO60eAka-jeyh4Yj6u-Zx8Li4R31WnBF2r_v9Ag5WAx6E1-7VmGZugk3QVham1Fz2eVt0WlInyN-stGfOZCguXnyhJscl20m7NTqtimCCFOc7sseKVnTqT1TyvDrQBpP6I-AHFec9f0XCE3mGPenOs3bj6Azk79IuJi0o61ehYNdGPD6Pl2cpbaPpUk-zdvRRfX0Y6U1y0671FX_Bfpu9elstAi8PJsGYKezcz8",
        ];

        return view('frontend.pages.order', ['js_config' => json_encode($config)]);
    }
}
