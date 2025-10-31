<?php

namespace App\Booking\Commands;

use App\Booking\Contracts\ScheduleManager;
use App\Booking\Holiday;
use App\Domains\Auth\Models\User;
use Illuminate\Console\Command;

class ShowHoliday extends Command
{
    protected $signature = 'larabook:show-holiday {user-id : The user Id}';
    protected $description = 'Show a users holiday';
    private ScheduleManager $scheduleManager;

    public function __construct(ScheduleManager $scheduleManager)
    {
        $this->scheduleManager = $scheduleManager;
        parent::__construct();
    }

    public function handle()
    {
        $userId = $this->argument('user-id');
        $user = User::findOrFail($userId);
        $this->info("Users name = {$user->name}");
        $this->info("Users email = {$user->email}");

        foreach($this->scheduleManager->getHoliday($user) as $holiday) {
            $this->info("Holiday title: {$holiday->booked_by->title}");
            $this->info("Holiday description: {$holiday->booked_by->description}");
            $this->info("Holiday start: {$holiday->start}");
            $this->info("Holiday end: {$holiday->end}");
        }
    }
}
