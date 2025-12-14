<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected FirebaseService $firebaseService;

    public function __construct(ProductService $productService, FirebaseService $firebaseService)
    {
        $this->productService = $productService;
        $this->firebaseService = $firebaseService;
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
            $data = $request->all();
            if ($request->hasFile('image')) {
                $data['image_url'] = $this->firebaseService->upload($request->file('image'));
            }

            $product = $this->productService->create($data);
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
            $data = $request->all();
            if ($request->hasFile('image')) {
                $data['image_url'] = $this->firebaseService->upload($request->file('image'));
            }

            $product = $this->productService->update($id, $data);
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
