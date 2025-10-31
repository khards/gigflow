<?php

namespace App\Booking;

use App\Booking\Commands\CalendarImport;
use App\Booking\Commands\CalendarSync;
use App\Booking\Commands\CreateBusiness;
use App\Booking\Commands\CreateStaff;
use App\Booking\Commands\ScheduleImport;
use App\Booking\Commands\ShowHoliday;
use App\Booking\Contracts\AvailabilityManager as AvailabilityManagerContract;
use App\Booking\Contracts\BusinessManager as BusinessManagerContract;
use App\Booking\Contracts\OrderManager as OrderManagerContract;
use App\Booking\Contracts\OrderProcessor as OrderProcessorContract;
use App\Booking\Contracts\ProductManager as ProductManagerContract;
use App\Booking\Contracts\ScheduleManager as ScheduleManagerContract;
use App\Booking\OrderProcessor\OrderProcessor;
use App\Booking\Services\AvailabilityManager;
use App\Booking\Services\BusinessManager;
use App\Booking\Services\OrderManager;
use App\Booking\Services\ProductManager;
use App\Booking\Services\ScheduleManager;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Konekt\Address\Contracts\Address as AddressContract;
use Pnlinh\GoogleDistance\Contracts\GoogleDistanceContract;
use Pnlinh\GoogleDistance\GoogleDistance;
use Vanilo\Checkout\Contracts\CheckoutDataFactory;
use Vanilo\Framework\Factories\CheckoutDataFactory as DataFactory;

//Vanilo

/**
 * Class AppServiceProvider.
 */
class BookingServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [

    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [

    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*
            Within the register method, you should only bind things into the service container.
            You should never attempt to register any event listeners, routes, or any other piece of functionality within the register method.
            Otherwise, you may accidentally use a service that is provided by a service provider which has not loaded yet.
         */
        // Console and test migrations & factories
        if (app()->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
            $this->app->make(Factory::class)->load(__DIR__.'/database/factories/');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
         This method is called after all other service providers have been registered,
         meaning you have access to all other services that have been registered by the framework:
         */
        $this->app->bind(AvailabilityManagerContract::class, AvailabilityManager::class);
        $this->app->bind(OrderProcessorContract::class, OrderProcessor::class);
        $this->app->bind(ScheduleManagerContract::class, ScheduleManager::class);
        $this->app->bind(ProductManagerContract::class, ProductManager::class);
        $this->app->bind(BusinessManagerContract::class, BusinessManager::class);
        $this->app->bind(OrderManagerContract::class, OrderManager::class);

        //Vanilo
        $this->app->bind(CheckoutDataFactory::class, DataFactory::class);
        $this->app->concord->registerModel(AddressContract::class, Address::class);
        $this->app->concord->registerModel(\Vanilo\Order\Contracts\Order::class, \App\Domains\Order\Order::class);
        $this->app->concord->registerModel(\Vanilo\Cart\Contracts\Cart::class, \App\Booking\Cart\Cart::class);

        $this->app->concord->registerEnum(\Vanilo\Order\Contracts\OrderStatus::class, \App\Domains\Order\OrderStatus::class);


        // Google maps distance API
        $this->app->bind(GoogleDistanceContract::class, GoogleDistance::class);

        //Morph maps
        Relation::morphMap([
            'user' => \App\Domains\Auth\Models\User::class,
            'booking' => \App\Domains\Booking\Models\Booking::class,
            'holiday' => Holiday::class,
            'business' => \App\Booking\Business::class,
            'product' => Product::class,
        ]);

        // Console commands
        $this->registerCommands();
    }

    /**
     * Register console commands.
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CalendarSync::class,        // CRON - Import ALL holidays and scedules
                CalendarImport::class,      // CLI utility for Importing a users HOLIDAY from calendar
                CreateBusiness::class,      // CLI utility for creating a business
                CreateStaff::class,         // CLI utility for creating staff
                ScheduleImport::class,      // Staff schedule
                ShowHoliday::class,         // CLI utility for showing a staff's holiday
            ]);
        } else {
            $this->commands([
                CalendarSync::class,        // CRON - Import ALL holidays and scedules
                CalendarImport::class,      // CLI UTILITY for A Staff holiday import
                ScheduleImport::class,      // CLI UTILITY for A Staff schedule import
            ]);
        }
    }
}
