<?php

namespace App\Domains\Form\Contracts;

use stdClass;
use Vanilo\Order\Models\Order;

interface FormResponseManager
{
    /**
     * Store cached forms into database against an order ID.
     *
     * @param string $sessionId
     * @param Order  $order
     *
     * @return null
     */
    public function storeCachedForms(string $sessionId, Order $order): void;

    /**
     * Submit form.
     *
     * Saves into cache under sessionId, returns array of validation errors
     *
     * @param int $pageId
     * @param array|\stdClass $formData
     * @param string $sessionId
     * @return array
     */
    public function submit(int $pageId, array|stdClass $formData, string $sessionId);

    /**
     * @param int $formId
     * @param string $sessionId
     *
     * @return mixed
     */
    public function getCachedForm(int $formId, string $sessionId);

    /**
     * @param string $sessionId
     *
     * @return mixed
     */
    public function getCachedForms(string $sessionId);

    /**
     * Calculate if there's a response that is mapped to a skip-to form.
     * If so then return that form ID.
     * If no skip or invalid form ID then return null.
     *
     * @param array|stdClass $formData
     * @param                $formId
     *
     * @return mixed
     */
    public function skipToForm(array|stdClass $formData, $formId);

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
    public function validateForm(array|stdClass $formData);

    /**
     * Check response data integrity.
     * Can be bypassed by changing the submitted forms options.
     *
     * @TODO - grab form from db and validate userData against that.
     *
     * @param array|\stdClass $formData
     * @return void
     */
    public function validateIntegrity(array|stdClass $formData);
}
