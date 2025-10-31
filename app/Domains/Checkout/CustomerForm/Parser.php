<?php

namespace App\Domains\Checkout\CustomerForm;

use App\Domains\Checkout\CustomerForm\Exceptions\CustomerFormInternalValidationException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class Parser
{
    /**
     * @param array $userSubmittedFormData
     * @param array $required_form_items
     * @return Collection
     * @throws Exception
     */
    public function parseRequiredFormItems(array $userSubmittedFormData, array $required_form_items): Collection
    {
        $details = [];
        $searchable = collect(json_decode(json_encode($userSubmittedFormData), true));

        foreach ($required_form_items as $key => $formItem) {

            $index = $formItem['form_item_name'];
            $validation = Arr::get($formItem, 'validation');

            $formDataItem = $searchable->where('name', $index)->first();
            if (! $formDataItem) {
                throw new CustomerFormInternalValidationException("Required item {$index} missing during booking.", );
            }
            if (! isset($formDataItem['userData'][0]) || $formDataItem['userData'][0] === '') {
                throw new CustomerFormInternalValidationException("Required form item \"{$index}\" is missing during booking.", $index);
            }

            // Validate item
            if($validation) {
                $validator = Validator::make([
                    $key => $formDataItem['userData'][0]
                ], [
                    $key => $validation,
                ], [
                    $key => "Invalid {$key}",
                ]);
                if ($validator->fails()) {
                    throw new CustomerFormInternalValidationException($validator->getMessageBag()->first(), $key);
                }
            }

            // Store item
            $details[$key] = $formDataItem['userData'][0];
        }

        return collect($details);
    }
}
