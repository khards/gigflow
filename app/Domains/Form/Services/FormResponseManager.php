<?php

namespace App\Domains\Form\Services;

use App\Booking\Business;
use App\Domains\Form\Contracts\FormResponseManager as FormResponseManagerContract;
use App\Domains\Form\Models\Form;
use App\Domains\Form\Models\FormResponses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Vanilo\Order\Models\Order;

class FormResponseManager implements FormResponseManagerContract
{
    /**
     * Submit form.
     *
     * Saves into cache under sessionId, returns array of validation errors
     *
     * @param int $formId
     * @param array|\stdClass $formData
     * @param string $sessionId
     * @return array
     */
    public function submit(int $formId, array|\stdClass $formData, string $sessionId)
    {
        // Validate form
        $integrityErrors = $this->validateIntegrity($formData);
        $formErrors = $this->validateForm($formData);
        $allErrors = array_merge($integrityErrors, $formErrors);

        // Cache submitted forms without any validation errors.
        if (count($allErrors) === 0) {
            $this->cacheSubmittedForm($sessionId, $formData, $formId);
        }

        return $allErrors;
    }

    /**
     * Get cached form. Returns DB model if not cached.
     *
     * @param int $formId
     * @param string $sessionId
     * @return null|Form
     */
    public function getCachedForm(int $formId, string $sessionId)
    {
        $cacheKey = $sessionId.'_form';
        if ($formData = Cache::get($cacheKey)) {
            if (isset($formData[$formId])) {
                return $formData[$formId];
            }
        }

        $form = Form::find($formId);
        if ($form->count()) {
            return $form?->data;
        }

        return null;
    }

    /**
     * Get cached form. Returns DB model if not cached.
     *
     * @param string $sessionId
     * @return null|Form
     */
    public function getCachedForms(string $sessionId)
    {
        $cacheKey = $sessionId.'_form';

        return Cache::get($cacheKey, null);
    }

    /**
     * Save form data cached against session into database against order ID.
     *
     * @param string $sessionId
     * @param Order  $order
     *
     * @return mixed
     */
    public function storeCachedForms(string $sessionId, Order $order): void
    {

        // Get cached form responses.
        $cacheKey = $sessionId.'_form';
        if ($formData = Cache::get($cacheKey)) {
            foreach ($formData as $formId => $formData) {
                FormResponses::create([
                    'form_id' => $formId,
                    'order_id' => $order->id,
                    'form' => $formData,
                ]);
            }
        }
    }

    /**
     * Calculate if there's a response that is mapped to a skip-to form.
     * If so then return that form ID.
     * If no skip or invalid form ID then return null.
     */
    public function skipToForm(array|\stdClass $formData, $formId)
    {
        $userData = [];
        $supportedSkipTypes = ['select', 'radio-group', 'autocomplete', 'checkbox-group'];
        foreach ($formData as $item) {
            if (! is_object($item)) {
                $item = (object) $item;
            }

            if (! property_exists($item, 'type')) {
                continue;
            }

            if (! property_exists($item, 'name')) {
                continue;
            }

            if (! in_array($item->type, $supportedSkipTypes)) {
                continue;
            }

            if (! property_exists($item, 'userData')) {
                continue;
            }

            if (! is_array($item->userData)) {
                continue;
            }

            $userData[$item->name] = $item->userData;
        }

        if (count($userData) === 0) {
            return null;
        }

        if (! $form = Form::find($formId)) {
            return null;
        }

        if (! $action = (object)$form->action->toArray()) {
            Log::debug('1.0.2.1 NO ACTION');

            return null;
        }

        // Skip to next form based on a response..
        if (! property_exists($action, 'logic_question_name')) {
            Log::debug('2.1.0.0 logic_question_name does not exist on form action, aborting');

            return null;
        }

        $formId = null;
        foreach ($userData as $name => $responses) {
            if ($action->logic_question_name !== $name) {
                continue;
            }
            foreach ($responses as $response) {
                $formId = $action->logic[$response] ?? null;
                if ($formId) {
                    break;
                }
            }
        }

        if ($formId) {
            // Check exists in db.
            if (Form::where('id', '=', $formId)->count() > 0) {
                return $formId;
            }
        }

        return null;
    }

    /**
     * Validate form.
     *
     * @TODO -form needs to come from DB as this can be bypassed.
     *
     * Validates required fields.
     *
     * Returns empty array if no errors
     * Returns array of name => error if errors are detected.
     *
     * Currently only supports required fields.
     *
     * @param array|\stdClass $formData
     * @return array
     */
    public function validateForm(array|\stdClass $formData)
    {
        $errors = [];
        foreach ($formData as $item) {
            if (! is_object($item)) {
                $item = (object) $item;
            }

            // Must be required.
            if (! property_exists($item, 'required')) {
                continue;
            }

            // Must have a name
            if (! property_exists($item, 'name')) {
                continue;
            }

            // No empty names allowed.
            if (! $item->name) {
                continue;
            }

            // If not required, then jog on.
            if (! $item->required) {
                continue;
            }

            // Item is required, so must have userData else is an error.
            if (! property_exists($item, 'userData') || ! is_array($item->userData)) {
                $errors[$item->name] = 'required';
                continue;
            }

            // If no responses when required, then error.
            if (0 === count($item->userData)) {
                $errors[$item->name] = 'required';
                continue;
            }

            if ($item->type === 'text' && empty($item->userData[0])) {
                $errors[$item->name] = 'required';
                continue;
            }
        }

        return $errors;
    }

    /**
     * Check response data integrity.
     * Can be bypassed by changing the submitted forms options.
     *
     * @TODO - grab form from db and validate userData against that.
     *
     * @param array|\stdClass $formData
     * @return array
     */
    public function validateIntegrity($formData)
    {
        $supportedValidationTypes = ['select', 'radio-group', 'autocomplete', 'checkbox-group'];

        $errors = [];
        foreach ($formData as $formItem) {

            // The form structure can vary between an array of arrays and an array of std objects with keys.
            if (! is_object($formItem)) {
                $formItem = (object) $formItem;
            }

            // If no user data, continue
            if (! property_exists($formItem, 'userData') || ! is_array($formItem->userData)) {
                continue;
            }

            // If no, or unsupported type, then carry on.
            if (! property_exists($formItem, 'type') || ! in_array($formItem->type, $supportedValidationTypes)) {
                continue;
            }

            // Search for response in values.
            $validResponseFound = $this->isValidResponseFound($formItem);
            $invalidEmptyResponseFound = $this->invalidEmptyResponse($formItem);

            if (! $validResponseFound) {
                $errors[$formItem->name] = 'invalid';
            }

            if ($invalidEmptyResponseFound) {
                $errors[$formItem->name] = 'empty';
            }
        }

        return $errors;
    }

    /**
     * @param string $sessionId
     *
     * @return array
     */
    private function getFormFromCache(string $sessionId): array|null
    {
        $cacheKey = $sessionId.'_form';

        return Cache::get($cacheKey);
    }

    /**
     * @param array $allFormData
     * @param string $sessionId
     */
    private function cacheFormData(array $allFormData, string $sessionId)
    {
        $cacheKey = $sessionId.'_form';
        $seconds = 60 * 60 * 12; /* hours */
        Cache::put($cacheKey, $allFormData, $seconds);
    }

    /**
     * Cache the submitted form data.
     *
     * @param string          $sessionId
     * @param array|\stdClass $formData
     * @param int             $formId
     */
    private function cacheSubmittedForm(string $sessionId, array|\stdClass $formData, int $formId): void
    {
        // Get existing forms from cache
        $allFormData = $this->getFormFromCache($sessionId);

        // Append to cached forms data
        $allFormData[$formId] = $formData;

        // Save form into cache
        $this->cacheFormData($allFormData, $sessionId);
    }

    /**
     * @param mixed $formItem
     *
     * @return bool
     */
    private function invalidEmptyResponse(mixed $formItem): bool
    {
        if (! property_exists($formItem, 'required')) {
            return false;
        }

        foreach ($formItem->userData as $userFormResponse) {
            if (empty($userFormResponse) && $formItem->required) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $formItem
     *
     * @return bool
     */
    private function isValidResponseFound(mixed $formItem): bool
    {
        $required = true;
        if (! property_exists($formItem, 'required') || ! $formItem->required) {
            $required = false;
        }

        $response_found = false;
        foreach ($formItem->userData as $userFormResponse) {

            // Allow empty response when not reuqired and emptr.
            if (! $required && empty($userFormResponse)) {
                $response_found = true;
                break;
            }

            $response_found = false;
            foreach ($formItem->values as $value) {
                if (! is_object($value)) {
                    $value = (object) $value;
                }

                if ($value->value === $userFormResponse) {
                    $response_found = true;
                    break 2;
                }
            }
        }

        return $response_found;
    }
}
