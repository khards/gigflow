<?php

namespace App\Domains\Address\Services;

use App\Booking\Address;
use App\Services\BaseService;
use Konekt\Address\Models\AddressType;

class AddressService extends BaseService
{
    /**
     * AddressService constructor.
     * @param Address $address
     */
    public function __construct(Address $address)
    {
        $this->model = $address;
    }

    /**
     * Create an address.
     *
     * @param array $details
     * @return Address
     */
    public function create(array $details): Address
    {
        return Address::create([
            'type' => $details['address_type'] ?? AddressType::BILLING(),
            'country_id' => $details['country_id'] ?? 'GB',
            'name' => $details['address_name'],
            'address' => $details['address'],
            'city' => $details['city'],
            'postalcode' => $details['postalcode'],
        ]);
    }
}
