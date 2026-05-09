<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get User Cart
     *
     * Retrieves the current user's shopping cart along with its items and associated products.
     *
     * @group Cart
     * @authenticated
     *
     * @response 200 {
     *   "id": 1,
     *   "user_id": 1,
     *   "items": [
     *     {
     *       "id": 1,
     *       "quantity": 2,
     *       "product": {
     *         "id": 1,
     *         "name": "Sample Product",
     *         "price": "100.00"
     *       }
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $cart = Cart::with('items.product')->firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        return response()->json(['data' => $cart]);
    }

    /**
     * Add Item to Cart
     *
     * Adds a specific product to the user's cart or updates its quantity if it already exists.
     * Validates that the requested quantity does not exceed available stock.
     *
     * @group Cart
     * @authenticated
     *
     * @bodyParam product_id integer required The ID of the product. Example: 1
     * @bodyParam quantity integer required The quantity to add. Example: 2
     *
     * @response 200 {
     *   "message": "تمت إضافة المنتج إلى السلة بنجاح.",
     *   "data": {"id": 1, "quantity": 2, "product": {"id": 1, "name": "Sample Product"}}
     * }
     * @response 400 {"message": "المنتج غير متاح حالياً."}
     * @response 422 {"message": "الكمية المطلوبة غير متوفرة. الكمية المتاحة: 3"}
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Check if product is active
        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active) {
            return response()->json([
                'message' => 'المنتج غير متاح حالياً.',
            ], 400);
        }

        // Check stock availability
        if (!$product->hasStock($request->quantity)) {
            return response()->json([
                'message' => "الكمية المطلوبة غير متوفرة. الكمية المتاحة: {$product->stock}",
            ], 422);
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        // Check if item already in cart and validate total quantity
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        $totalQuantity = $request->quantity;
        if ($existingItem) {
            $totalQuantity = $request->quantity; // Replace quantity (not add)
        }

        if (!$product->hasStock($totalQuantity)) {
            return response()->json([
                'message' => "الكمية المطلوبة غير متوفرة. الكمية المتاحة: {$product->stock}",
            ], 422);
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json([
            'message' => 'تمت إضافة المنتج إلى السلة بنجاح.',
            'data' => $cartItem->load('product'),
        ]);
    }

    /**
     * Update Cart Item Quantity
     *
     * Updates the quantity of a specific item in the cart.
     * Validates that the new quantity does not exceed available stock.
     *
     * @group Cart
     * @authenticated
     *
     * @urlParam id integer required The ID of the cart item. Example: 1
     * @bodyParam quantity integer required The new quantity. Example: 3
     *
     * @response 200 {
     *   "message": "تم تحديث السلة بنجاح.",
     *   "data": {"id": 1, "quantity": 3, "product": {"id": 1, "name": "Sample Product"}}
     * }
     * @response 422 {"message": "الكمية المطلوبة غير متوفرة. الكمية المتاحة: 3"}
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::with('product')->findOrFail($id);

        // Check stock availability
        if (!$cartItem->product->hasStock($request->quantity)) {
            return response()->json([
                'message' => "الكمية المطلوبة غير متوفرة. الكمية المتاحة: {$cartItem->product->stock}",
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'تم تحديث السلة بنجاح.',
            'data' => $cartItem->load('product'),
        ]);
    }

    /**
     * Remove Item from Cart
     *
     * Removes a specific item from the user's cart.
     *
     * @group Cart
     * @authenticated
     *
     * @urlParam id integer required The ID of the cart item. Example: 1
     *
     * @response 200 {
     *   "message": "تم حذف المنتج من السلة بنجاح."
     * }
     */
    public function destroy($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'message' => 'تم حذف المنتج من السلة بنجاح.',
        ]);
    }
}
