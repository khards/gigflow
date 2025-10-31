<?php

namespace Database\Factories;

use App\Domains\Auth\Models\User;

if (! class_exists('Database\Factories\CustomerFactory')) {
    class CustomerFactory extends \Database\Factories\UserFactory
    {
        /**
         * The name of the factory's corresponding model.
         *
         * @var string
         */
        protected $model = \App\Booking\Customer::class;

        public function definition()
        {
            $userDefinition = parent::definition();

            $userDefinition['type'] = User::TYPE_CUSTOMER;

            return $userDefinition;
        }
    }
}
