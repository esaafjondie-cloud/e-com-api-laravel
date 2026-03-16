<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List Products
     *
     * Returns a paginated list of active products. Can be filtered by category or search term.
     *
     * @group Products
     * @unauthenticated
     *
     * @queryParam category_id integer optional Filter products by category ID. Example: 1
     * @queryParam search string optional Search for products by name. Example: phone
     *
     * @apiResourceCollection App\Http\Resources\ProductResource
     * @apiResourceModel App\Models\Product
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20);

        return ProductResource::collection($products);
    }

    /**
     * Get Product Details
     *
     * Returns detailed information about a specific product.
     *
     * @group Products
     * @unauthenticated
     *
     * @urlParam id integer required The ID of the product. Example: 1
     *
     * @apiResource App\Http\Resources\ProductResource
     * @apiResourceModel App\Models\Product
     */
    public function show($id)
    {
        $product = Product::with(['category', 'images'])->findOrFail($id);

        return new ProductResource($product);
    }
}
