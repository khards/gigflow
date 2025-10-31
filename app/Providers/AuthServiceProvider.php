<?php

namespace App\Providers;

use App\Booking\Availability\Schedule;
use App\Booking\Product;
use App\Domains\Email\Models\MailTemplate;
use App\Domains\Form\Models\Form;
use App\Domains\Order\Order;
use App\Domains\Payment\Models\Transaction;
use App\Policies\FormPolicy;
use App\Policies\MailTemplatePolicy;
use App\Policies\OrderPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SchedulePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

/**
 * Class AuthServiceProvider.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Schedule::class => SchedulePolicy::class,
        Form::class => FormPolicy::class,
        Order::class => OrderPolicy::class,
        Transaction::class => TransactionPolicy::class,
        MailTemplate::class => MailTemplatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user) {
            return $user->hasAllAccess() ? true : null;
        });

        // Learn when to use this instead: https://docs.spatie.be/laravel-permission/v3/basic-usage/super-admin/#gate-after
//        Gate::after(function ($user) {
//            return $user->hasAllAccess();
//        });

        Passport::routes();

        // Add 6 months to expiring Personal Access Tokens.
        // This is used to indefinetly extend the lifetime of the API token used for booking on our site!
        //
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
