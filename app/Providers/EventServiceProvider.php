<?php

namespace App\Providers;

use App\Booking\Requests\Frontend\Business\UpdatePayment;
use App\Domains\Auth\Listeners\RoleEventListener;
use App\Domains\Auth\Listeners\UserEventListener;
use App\Domains\Order\Events\OrderReady;
use App\Domains\Payment\Events\PaymentTransaction;
use App\Domains\Payment\Listeners\Payment\BookStock;
use App\Domains\Email\Jobs\Orders\SendConfirmation;
use App\Domains\Payment\Listeners\Payment\CreateReceiptEmailJob;
use App\Domains\Payment\Listeners\Payment\UpdateStatus;
use App\Domains\Payment\Models\Transaction;
use App\Domains\Payment\Observers\TransactionObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PaymentTransaction::class => [
            BookStock::class,
            UpdateStatus::class,
            CreateReceiptEmailJob::class,
        ],
        OrderReady::class => [
            // - Now a job that needs to be dispatched
            // - An event sending an email is blocking and would take too long
            // SendConfirmation::class
        ]
    ];

    /**
     * Class event subscribers.
     *
     * @var array
     */
    protected $subscribe = [
        RoleEventListener::class,
        UserEventListener::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Transaction::observe(TransactionObserver::class);
    }
}
