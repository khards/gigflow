<?php

namespace App\Providers;

use App\Booking\Cart\CartManager;
use App\Domains\Form\Contracts\FormManager as FormManagerContract;
use App\Domains\Form\Contracts\FormResponseManager as FormResponseManagerContract;
use App\Domains\Form\Services\FormManager;
use App\Domains\Form\Services\FormResponseManager;
use App\Domains\Payment\Contracts\PaymentService as PaymentServiceContract;
use App\Domains\Payment\Contracts\TransactionService as TransactionServiceContract;
use App\Domains\Payment\Services\PaymentService;
use App\Domains\Payment\Services\TransactionService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Vanilo\Cart\Contracts\CartManager as CartManagerContract;
use Vanilo\Order\Contracts\Billpayer;

/**
 * Class AppServiceProvider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(FormManagerContract::class, FormManager::class);
        $this->app->bind(FormResponseManagerContract::class, FormResponseManager::class);
        $this->app->bind(CartManagerContract::class, CartManager::class);
        $this->app->bind(PaymentServiceContract::class, PaymentService::class);
        $this->app->bind(TransactionServiceContract::class, TransactionService::class);

        // Vanillo module binding
        $this->app->concord->registerModel(Billpayer::class, \App\Domains\BillPayer\Models\Billpayer::class);

        Paginator::useBootstrap();

        // Add toRawSql() to Query Builder.
        //
        \Illuminate\Database\Query\Builder::macro('toRawSql', function(){
            return array_reduce($this->getBindings(), function($sql, $binding){
                return preg_replace('/\?/', is_numeric($binding) ? $binding : "'".$binding."'" , $sql, 1);
            }, $this->toSql());
        });

        // Add an alias in Eloquent Builder.
        //
        \Illuminate\Database\Eloquent\Builder::macro('toRawSql', function(){
            return ($this->getQuery()->toRawSql());
        });

    }
}
