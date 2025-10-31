<?php

namespace App\Domains\Form\Contracts;

use App\Booking\Business;
use App\Domains\Form\Models\Form;
use Illuminate\Support\Collection;

interface FormManager
{
    /**
     * Create a form.
     *
     * @param mixed $business
     * @param array $formDetails
     * @return Form
     */
    public function create($business, array $formDetails): Form;

    /**
     * Read a form.
     *
     * @param $id
     * @return Form
     */
    public function read($id): Form;

    /**
     * Get all forms for a business, optionally filter.
     *
     * @param Business $business
     * @param array $filter
     * @return Collection
     */
    public function all($business, $filter = []): Collection;

    /**
     * Update a form.
     *
     * @param Form $form
     * @param array $formDetails
     * @return Form
     */
    public function update(Form $form, array $formDetails): Form;

    /**
     * Delete a form.
     *
     * @param Form|int $form
     * @return void
     */
    public function delete($form): void;

    /**
     * Clone a form.
     *
     * @param Form|int $form
     * @return Form
     */
    public function clone($form): Form;

    /**
     * @param int $businessId
     * @param string $cartId
     * @param array|\stdClass $formData
     * @param array $prodcutIds
     * @param array $visitedFormIds
     * @return int
     */
    public function getNextFormId(int $businessId, string $cartId, array|\stdClass $formData, array $productIds, array $visitedFormIds): ?int;
}
