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
     * Returns a paginated list of active products. Can be filtered by category, search term,
     * price range, stock availability, and sorted by various fields.
     *
     * @group Products
     * @unauthenticated
     *
     * @queryParam category_id integer optional Filter products by category ID. Example: 1
     * @queryParam search string optional Search for products by name or description. Example: phone
     * @queryParam min_price numeric optional Minimum price filter. Example: 1000
     * @queryParam max_price numeric optional Maximum price filter. Example: 50000
     * @queryParam in_stock boolean optional Filter only products that are in stock. Example: true
     * @queryParam sort_by string optional Sort field: price, name, created_at. Default: created_at. Example: price
     * @queryParam sort_order string optional Sort direction: asc, desc. Default: desc. Example: asc
     *
     * @apiResourceCollection App\Http\Resources\ProductResource
     * @apiResourceModel App\Models\Product
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])->where('is_active', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock === 'true') {
            $query->inStock();
        }

        // Sorting
        $sortBy = in_array($request->sort_by, ['price', 'name', 'created_at']) ? $request->sort_by : 'created_at';
        $sortOrder = in_array($request->sort_order, ['asc', 'desc']) ? $request->sort_order : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(20);

        return response()->json([
            'data' => ProductResource::collection($products),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]
        ]);
    }

    /**
     * Get Product Details
     *
     * Returns detailed information about a specific product including its sizes, images, and category.
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
