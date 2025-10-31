<?php

namespace App\Booking\Commands;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabook:create-staff {business-id : The businesses Id} {name : Name} {email : Email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new member of staff';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $businessId = $this->argument('business-id');
        $business = Business::findOrFail($businessId);
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->secret('Enter a new password');
        $confirmation = $this->secret('Confirm new password');

        if ($password != $confirmation) {
            $this->error("Error passwords don't match");

            return;
        }

        if (empty($password)) {
            $this->error('Empty password');

            return;
        }

        if (empty($name) || empty($email)) {
            $this->error('Invalid arguments');

            return;
        }

        $staff = null;
        DB::transaction(function () use (&$staff, $business, $name, $email, $password) {
            $staff = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
            $business->users()->attach($staff);
        });

        $this->info('User: '.$staff->name.' with Id '.$staff->id.' was created and attached to:');
        $this->info('"'.$business->name.'"'."({$business->id})");
    }
}
