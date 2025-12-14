<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $products = $this->productService->list();
        return $this->successResponse(['products' => $products]);
    }

    public function show($id)
    {
        try {
            $product = $this->productService->get($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        }

        return $this->successResponse(['product' => $product]);
    }

    public function create(Request $request)
    {
        try {
            $product = $this->productService->create($request->all());
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return $this->errorResponse('Could not create product', null, 500);
        }

        return $this->successResponse(['product' => $product], 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $product = $this->productService->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return $this->errorResponse('Could not update product', null, 500);
        }

        return $this->successResponse(['product' => $product], 'Product updated successfully');
    }

    public function delete($id)
    {
        try {
            $this->productService->delete($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Could not delete product', null, 500);
        }

        return $this->successResponse(null, 'Product deleted');
    }
}
