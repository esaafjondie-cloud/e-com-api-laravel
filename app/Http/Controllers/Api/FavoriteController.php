<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * List Favorite Products
     *
     * Returns a paginated list of the user's favorite products.
     *
     * @group Favorites
     * @authenticated
     *
     * @response 200 {
     *   "data": [{"id": 1, "name": "Sample Product", "price": "100.00"}],
     *   "meta": {"current_page": 1, "total": 1}
     * }
     */
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with('category')
            ->paginate(20);

        return response()->json($favorites);
    }

    /**
     * Add to Favorites
     *
     * Adds a specific product to the user's favorite list.
     *
     * @group Favorites
     * @authenticated
     *
     * @bodyParam product_id integer required The ID of the product. Example: 1
     *
     * @response 200 {
     *   "message": "Product added to favorites successfully.",
     *   "favorite": {"id": 1, "user_id": 1, "product_id": 1}
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $favorite = Favorite::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]
        );

        return response()->json([
            'message' => 'Product added to favorites successfully.',
            'favorite' => $favorite,
        ]);
    }

    /**
     * Remove from Favorites
     *
     * Removes a specific product from the user's favorite list.
     *
     * @group Favorites
     * @authenticated
     *
     * @urlParam productId integer required The ID of the product. Example: 1
     *
     * @response 200 {
     *   "message": "Product removed from favorites successfully."
     * }
     */
    public function destroy(Request $request, $productId)
    {
        Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'message' => 'Product removed from favorites successfully.',
        ]);
    }
}
