<?php

namespace App\Domains\Auth\Services;

use App\Booking\Customer;
use App\Domains\Auth\Models\User;
use Exception;
use Illuminate\Database\QueryException;

/**
 * Class CustomerService.
 */
class CustomerService extends UserService
{
    /**
     * CustomerService constructor.
     *
     * @param  Customer  $customer
     */
    public function __construct(Customer $customer)
    {
        $this->model = $customer;
    }

    /**
     * Create a new customer, or update existing customer.
     *
     * @param array $data
     * @return Customer
     * @throws Exception
     */
    public function createUpdate(array $data): mixed
    {
        $crudData = [
            'type' => User::TYPE_CUSTOMER,
            'name' => $data['firstName'].' '.$data['lastName'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ];

        try {
            $customer = $this->createUser($crudData);
        } catch (QueryException $e) {

            // If duplicate customer is detected, then select that customer.
            if ($e->getCode() === '23000') {
                // Disable global scope incase admin (User) not customer makes an order!
                $customer = Customer::withoutGlobalScopes()->withTrashed()->where('email', $crudData['email'])->first();

                if ($customer->trashed()) {
                    $customer->restore();
                    $this->update($customer, $crudData);
                }
            } else {
                throw new \RuntimeException("Unable to create a booking under customer {$crudData['email']}");
            }
        }

        return $customer;
    }
}
