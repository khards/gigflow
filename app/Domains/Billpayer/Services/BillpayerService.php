<?php

namespace App\Domains\Billpayer\Services;

use App\Booking\Address;
use App\Domains\Billpayer\Models\Billpayer;
use App\Services\BaseService;

class BillpayerService extends BaseService
{
    /**
     * BillpayerService constructor.
     * @param Billpayer $billpayer
     */
    public function __construct(Billpayer $billpayer)
    {
        $this->model = $billpayer;
    }

    /**
     * Create a new billpayer.
     *
     * @param Address $address
     * @param array $billpayerDetails
     * @return Billpayer
     */
    public function create(Address $address, array $billpayerDetails): Billpayer
    {
        $billPayer = Billpayer::make([
            'email' => $billpayerDetails['email'],
            'phone' => $billpayerDetails['phone'],
            'firstname' => $billpayerDetails['firstName'],
            'lastname' => $billpayerDetails['lastName'],
        ]);
        $billPayer->address()->associate($address);
        $billPayer->save();

        return $billPayer;
    }
}
