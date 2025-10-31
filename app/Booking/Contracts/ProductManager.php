<?php

namespace App\Booking\Contracts;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Business;
use App\Booking\Product;

interface ProductManager
{
    /**
     * Create a product.
     *
     * @param mixed $business
     * @param array $productDetails
     * @return mixed
     */
    public function create($business, array $productDetails): Product;

    /**
     * Read a product.
     *
     * @param $id
     * @return Product
     */
    public function read($id);

    /**
     * Get all products for a business, optionally filter.
     *
     * @param Business $business
     * @param array $filter
     * @return AvailabilityData[]
     */
    public function all($business, $filter = []): array;

    /**
     * Update a product.
     *
     * @param Product $product
     * @param array $productDetails
     * @return Product
     */
    public function update(Product $product, array $productDetails): Product;

    /**
     * Delete a product.
     *
     * @param $product
     * @return mixed
     */
    public function delete($product);

    /**
     * Create a product variation.
     *
     * @param Product $product
     * @return Product
     */
    public function createVariation(Product $product): Product;

    /**
     * Update a products variations.
     *
     * @param Product $product
     * @param array $variations
     * @return void
     */
    public function updateVariations(Product $product, array $variations);
}
