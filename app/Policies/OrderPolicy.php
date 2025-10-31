<?php

namespace App\Policies;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Order\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasAllAccess() ? true : false;
    }

    /**
     * View all orders for a businesses - used by the orders table.
     *
     * @param User $user
     * @param Business $business
     * @return bool
     */
    public function index(User $user, Business $business)
    {
        //Double check user belongs to business
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param \App\Domains\Order\Order $order
     * @return bool
     */
    public function view(User $user, Order $order)
    {
        // Get the order's business
        if (! $business = Business::find($order->business_id)) {
            return false;
        }

        //Check the passed user belongs to that business.
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param Business $business
     * @return bool
     */
    public function create(User $user, Business $business)
    {
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param \App\Domains\Order\Order $order
     * @return bool
     */
    public function update(User $user, Order $order)
    {
        return $this->view($user, $order);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param \App\Domains\Order\Order $order
     * @return bool
     */
    public function delete(User $user, Order $order)
    {
        return $this->view($user, $order);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Order $order
     * @return false
     */
    public function restore(User $user, Order $order)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Order $order
     * @return false
     */
    public function forceDelete(User $user, Order $order)
    {
        return false;
    }
}
