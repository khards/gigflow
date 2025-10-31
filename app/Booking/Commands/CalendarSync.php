<?php
/**
 * Sync all calendar's
 *      import holidays
 *      import schedules
 *      import special prices
 */

namespace App\Booking\Commands;

use App\Booking\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CalendarSync extends Command
{
    protected $signature = 'larabook:calendar-sync';
    protected $description = 'Sync all calendars. To be called from a cron every x mins.';

    public function handle()
    {
        $this->processAllBusinesses();

        $this->clearOldCarts();
    }

    protected function processAllBusinesses(): void
    {
        foreach (Business::all()->chunk(10) as $businessChunk) {
            $businessChunk->each(function ($business) {
                $this->processBusiness($business);
            });
        }
    }

    protected function processBusiness($business): void
    {
        $this->info("Processing Business {$business->id}: " . $business->name);

        foreach ($business->users()->get()->chunk(10) as $userChunk) {
            $userChunk->each(function ($user) {
                $this->processUser($user);
            });
        }
    }

    protected function processUser($user): void
    {
        $this->info("Processing user {$user->id}: " . $user->email);

        if ($user->booking_settings->get('calendar_schedule.url')) {
            $this->callScheduleImport($user);
        }

        if ($user->booking_settings->get('calendar.url')) {
            $this->callCalendarHolidayImport($user);
        }
    }

    protected function callScheduleImport($user): void
    {
        $exitCode = Artisan::call('larabook:schedule-import', [
            'userid' => $user->id,
            'icsurl' => $user->booking_settings->get('calendar_schedule.url'),
            //            //{--tag=[holiday]}';
        ]);

        if (!$exitCode)
            $this->info("Schedule Import Success");
        else
            $this->info("Schedule Import Failed code = '{$exitCode}'");
    }

    // Holiday
    protected function callCalendarHolidayImport($user): void
    {
        $exitCode = Artisan::call('larabook:calendar-import', [
            'userid' => $user->id,
            'icsurl' => $user->booking_settings->get('calendar.url'),
            //            //{--tag=[holiday]}';
        ]);

        if (!$exitCode)
            $this->info("Calendar Import Success");
        else
            $this->info("Calendar Import Failed code = '{$exitCode}'");
    }

    /**
     * Clear / delete old carts and cart items over 7 days old.
     *
     * @return void
     */
    private function clearOldCarts(): void
    {
        $oldCarts = \App\Booking\Cart\Cart::where('updated_at', '<=', \Carbon\Carbon::now()->subDays(7))
            ->get();

        foreach ($oldCarts as $cart) {
            $cart->clear();
            $cart->delete();
        }
    }
}
