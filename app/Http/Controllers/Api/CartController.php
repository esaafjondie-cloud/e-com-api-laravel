<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
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

        return response()->json($cart);
    }

    /**
     * Add Item to Cart
     *
     * Adds a specific product to the user's cart or updates its quantity if it already exists.
     *
     * @group Cart
     * @authenticated
     *
     * @bodyParam product_id integer required The ID of the product. Example: 1
     * @bodyParam quantity integer required The quantity to add. Example: 2
     *
     * @response 200 {
     *   "message": "Item added to cart successfully.",
     *   "cart_item": {
     *     "id": 1,
     *     "quantity": 2,
     *     "product": {"id": 1, "name": "Sample Product"}
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

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
            'message' => 'Item added to cart successfully.',
            'cart_item' => $cartItem->load('product'),
        ]);
    }

    /**
     * Update Cart Item Quantity
     *
     * Updates the quantity of a specific item in the cart.
     *
     * @group Cart
     * @authenticated
     *
     * @urlParam id integer required The ID of the cart item. Example: 1
     * @bodyParam quantity integer required The new quantity. Example: 3
     *
     * @response 200 {
     *   "message": "Cart item updated successfully.",
     *   "cart_item": {
     *     "id": 1,
     *     "quantity": 3,
     *     "product": {"id": 1, "name": "Sample Product"}
     *   }
     * }
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::findOrFail($id);
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart item updated successfully.',
            'cart_item' => $cartItem->load('product'),
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
     *   "message": "Item removed from cart successfully."
     * }
     */
    public function destroy($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart successfully.',
        ]);
    }
}
