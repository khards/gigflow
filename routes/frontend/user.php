<?php

use App\Booking\Controllers\Frontend\BusinessController;
use App\Booking\Controllers\Frontend\CalendarController;
use App\Booking\Controllers\Frontend\FormController;
use App\Booking\Controllers\Frontend\OrderController;
use App\Booking\Controllers\Frontend\ProductController;
use App\Domains\Messages\Http\Controllers\MessageTemplateController;
use App\Domains\Schedules\Controllers\ScheduleController;
use App\Http\Controllers\Frontend\User\AccountController;
use App\Http\Controllers\Frontend\User\DashboardController;
use App\Http\Controllers\Frontend\User\ProfileController;
use Tabuna\Breadcrumbs\Trail;

//Booking system
// Livewire forms

/*
 * These frontend controllers require the user to be logged in
 * All route names are prefixed with 'frontend.'
 * These routes can not be hit if the user has not confirmed their email
 */
Route::group(['as' => 'user.', 'middleware' => ['auth', 'password.expires', config('boilerplate.access.middleware.verified')]], function () {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('is_user')
        ->name('dashboard')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('frontend.index')
                ->push(__('Dashboard'), route('frontend.user.dashboard'));
        });

    Route::get('account', [AccountController::class, 'index'])
        ->name('account')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('frontend.index')
                ->push(__('My Account'), route('frontend.user.account'));
        });

    Route::patch('profile/update', [ProfileController::class, 'update'])->name('profile.update');

    //Booking system - User setting routes
    Route::patch('calendar/update', [CalendarController::class, 'update'])->name('calendar.update');
    Route::patch('schedule/update', [CalendarController::class, 'update'])->name('schedule.update');

    //Booking system business - routes
    Route::get('business', [BusinessController::class, 'index'])->name('business.index');
    Route::get('business/view/{businessId}', [BusinessController::class, 'view'])->name('business.view');
    Route::patch('business/{business}/profile/update', [BusinessController::class, 'update'])->name('business.profile.update');
    Route::patch('business/{business}/profile/update/payments', [BusinessController::class, 'updatePayments'])->name('business.profile.update.payments');

    // Message templates - Livewire
    Route::get('message-template/{messageTemplateId}/preview', [MessageTemplateController::class, 'preview'])->name('message-template.preview');
    Route::get('message-template/{messageTemplateId}', [MessageTemplateController::class, 'edit'])->name('message-template.edit');
    Route::get('business/{business}/message-templates', [MessageTemplateController::class, 'index'])->name('message-template.view');

    // Forms - Livewire
    Route::get('form/{formId}', [FormController::class, 'edit'])->name('form.edit');
    Route::post('form/clone/{formId}', [FormController::class, 'clone'])->name('form.clone');
    Route::get('business/{businessId}/forms', [FormController::class, 'index'])->name('forms.view');

    // Products - VueJs
    Route::get('product/{productId}', [ProductController::class, 'edit'])->name('product.edit');
    Route::post('product/{productId}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('product/{productId}', [ProductController::class, 'delete'])->name('product.delete');
    Route::post('product/create/{businessId}', [ProductController::class, 'create'])->name('product.create');
    Route::get('business/{businessId}/products', [ProductController::class, 'all'])->name('products.view');

    // Orders & Bookings
    Route::get('business/{businessId}/orders', [OrderController::class, 'all'])->name('orders.view');
    Route::get('order/edit/{orderId}', [OrderController::class, 'edit'])->name('order.edit');

    //Product variations
    Route::post('product/create/variation/{product}', [ProductController::class, 'createVariation'])->name('product.variation.create');

    //Schedules
    Route::get('schedule/{scheduleId}', [ScheduleController::class, 'edit'])->name('schedule.edit');
    Route::post('schedule/{scheduleId}', [ScheduleController::class, 'update'])->name('schedule.update');
//    Route::delete('schedule/{scheduleId}', [ScheduleController::class, 'delete'])->name('schedule.delete');
    Route::post('schedule/create/{businessId}', [ScheduleController::class, 'create'])->name('schedule.create');
    Route::get('business/{businessId}/schedules', [ScheduleController::class, 'all'])->name('schedules.view');

    //Booking system - Calendar
    Route::get('account/calendar', [AccountController::class, 'calendar'])
        ->name('calendar')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('frontend.index')
                ->push(__('Calendar'), route('frontend.user.calendar'));
        });

    //My calendar - full calendar json events
    Route::get('calendar/mycalendar', [CalendarController::class, 'fullCalendar'])->name('calendar.mycalendar');

    //My calendar,
    Route::get('calendar/sync', [CalendarController::class, 'sync'])->name('calendar.sync');
});
