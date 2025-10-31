<?php

namespace App\Policies;

use App\Booking\Business;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
     * View all businesses products.
     *
     * @param User $user
     * @param Business $business
     * @return bool
     */
    public function viewAllByBusiness(User $user, Business $business)
    {
        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Product  $product
     * @return mixed
     */
    public function view(User $user, Product $product)
    {
        if (! $business = Business::find($product->owner_id)) {
            return false;
        }

        if ($business->users()->where('business_user.user_id', $user->id)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @return mixed
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
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Product  $product
     * @return mixed
     */
    public function update(User $user, Product $product)
    {
        return $this->view($user, $product);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Product  $product
     * @return mixed
     */
    public function delete(User $user, Product $product)
    {
        return $this->view($user, $product);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Product  $product
     * @return mixed
     */
    public function restore(User $user, Product $product)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Domains\Auth\Models\User  $user
     * @param  \App\Booking\Product  $product
     * @return mixed
     */
    public function forceDelete(User $user, Product $product)
    {
        //
    }
}
