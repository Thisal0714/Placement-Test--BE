<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Return all products.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function list()
    {
        return Product::all();
    }

    /**
     * Get a product by id or throw ModelNotFoundException.
     */
    public function get(string $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * Create a new product after validating input.
     *
     * @param array $data
     * @return Product
     * @throws ValidationException
     * @throws QueryException
     */
    public function create(array $data): Product
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Product::create($validator->validated());
    }

    /**
     * Update an existing product.
     *
     * @param string $id
     * @param array $data
     * @return Product
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws QueryException
     */
    public function update(string $id, array $data): Product
    {
        $product = $this->get($id);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $product->update($validator->validated());

        return $product;
    }

    /**
     * Delete a product by id.
     *
     * @param string $id
     * @return void
     * @throws ModelNotFoundException
     */
    public function delete(string $id): void
    {
        $product = $this->get($id);
        $product->delete();
    }
}
