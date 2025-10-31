<?php

namespace App\Domains\Checkout\CustomerForm\Exceptions;

class CustomerFormInternalValidationException extends \Exception {

    protected $message = 'A validation error occurred';

    public function __construct(public $data, public string $key)
    {
        parent::__construct();
    }

}
