<?php

namespace App\Booking\Commands;

use App\Booking\Business;
use Illuminate\Console\Command;

class CreateBusiness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabook:create-business {name : The businesses name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new business';

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
        $name = $this->argument('name');

        $business = Business::create([
            'name' => $name,
        ]);

        $this->info('Business: '.$business->name.' with Id '.$business->id.' was created');
    }
}
