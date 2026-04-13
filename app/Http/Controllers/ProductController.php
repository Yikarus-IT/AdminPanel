<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $view = $request->string('view')->toString();

        $query = Product::query();

        if ($view === 'deleted') {
            $query->onlyTrashed();
        }

        return response()->json([
            'data' => $query
                ->with('category')
                ->latest()
                ->get()
                ->map(fn (Product $product): array => $this->serializeProduct($product)),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $this->serializeProduct($product->load('category')),
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $this->serializeProduct($product->fresh()->load('category')),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product archived successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProduct(Product $product): array
    {
        return [
            ...$product->attributesToArray(),
            'category' => $product->category?->name,
        ];
    }
}
