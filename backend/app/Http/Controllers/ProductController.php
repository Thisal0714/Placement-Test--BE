<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        return response()->json(['products' => $products]);
    }

    public function show($id)
    {
        try {
            $product = $this->productService->get($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['product' => $product]);
    }

    public function create(Request $request)
    {
        try {
            $product = $this->productService->create($request->all());
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Could not create product'], 500);
        }

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $product = $this->productService->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Could not update product'], 500);
        }

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    public function delete($id)
    {
        try {
            $this->productService->delete($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not delete product'], 500);
        }

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
