<?php

namespace App\Domains\Checkout\CustomerForm;

use App\Domains\Form\Models\Form;
use Exception;
use Illuminate\Support\Collection;

class Customer
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Parse customer details from user submitted form details
     * See config: checkout.customer_details_form.
     *
     * @param array $userSubmittedForms
     * @return Collection
     * @throws Exception
     * @noinspection PhpUndefinedMethodInspection
     */
    public function parse(array $userSubmittedForms): Collection
    {
        $requiredFormName = config('checkout.customer_details_form.name');

        //Get the form id since we don't know which it is due to not having the form name.
        $formId = Form::whereIn('id', array_keys($userSubmittedForms))->where('name', $requiredFormName)->pluck('id')->first();
        if (! isset($userSubmittedForms[$formId])) {
            throw new Exception('No user form supplied during booking.');
        }

        $requiredFormItems = config('checkout.customer_details_form.items');

        return $this->parser->parseRequiredFormItems($userSubmittedForms[$formId], $requiredFormItems);
    }
}
