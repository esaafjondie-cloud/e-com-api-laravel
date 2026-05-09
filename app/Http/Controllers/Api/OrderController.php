<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * List User Orders
     *
     * Returns a paginated list of orders belonging to the authenticated user.
     *
     * @group Orders
     * @authenticated
     *
     * @response 200 {
     *   "data": [{"id": 1, "status": "unpaid", "total_amount": "195000.00", "items": []}],
     *   "meta": {"current_page": 1, "total": 1}
     * }
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }

    /**
     * Submit Order with Payment Receipt
     *
     * Creates a new order from the authenticated user's cart and uploads the Sham Cash payment receipt image.
     * The cart is automatically emptied after successful order creation.
     * Stock is automatically decreased for each ordered product.
     * Validates that all products in the cart have sufficient stock before creating the order.
     *
     * @group Orders
     * @authenticated
     *
     * @bodyParam shipping_address string required The delivery address. Example: Damascus, Mezzeh, Street 5.
     * @bodyParam shipping_phone string required The contact phone for delivery. Example: +963912345678
     * @bodyParam notes string optional Any special delivery instructions. Example: Call before arriving.
     * @bodyParam payment_receipt_image file required Screenshot of the Sham Cash payment (JPEG/PNG, max 5MB).
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "status": "unpaid",
     *     "total_amount": "195000.00",
     *     "payment_receipt_url": "http://localhost/storage/receipts/abc123.png",
     *     "items": []
     *   }
     * }
     * @response 400 {"message": "السلة فارغة."}
     * @response 422 {"message": "الكمية المطلوبة غير متوفرة.", "errors": {...}}
     */
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();

        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'السلة فارغة.',
            ], 400);
        }

        // Validate stock availability for all items before creating the order
        $stockErrors = [];
        foreach ($cart->items as $item) {
            if (!$item->product->is_active) {
                $stockErrors[] = "المنتج \"{$item->product->name}\" غير متاح حالياً.";
            } elseif (!$item->product->hasStock($item->quantity)) {
                $stockErrors[] = "المنتج \"{$item->product->name}\": الكمية المطلوبة ({$item->quantity}) غير متوفرة. المتاح: {$item->product->stock}";
            }
        }

        if (!empty($stockErrors)) {
            return response()->json([
                'message' => 'الكمية المطلوبة غير متوفرة لبعض المنتجات.',
                'errors' => $stockErrors,
            ], 422);
        }

        return DB::transaction(function () use ($request, $user, $cart) {
            $totalAmount = $cart->items->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $imagePath = $request->file('payment_receipt_image')->store('receipts', 'public');

            $order = Order::create([
                'user_id'               => $user->id,
                'total_amount'          => $totalAmount,
                'shipping_address'      => $request->shipping_address,
                'shipping_phone'        => $request->shipping_phone,
                'notes'                 => $request->notes,
                'status'                => 'unpaid',
                'payment_receipt_image' => $imagePath,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);

                // Decrease product stock
                $item->product->decreaseStock($item->quantity);
            }

            CartItem::where('cart_id', $cart->id)->delete();

            return new OrderResource($order->load('items.product'));
        });
    }

    /**
     * Get Order Details
     *
     * Returns the full details of a specific order belonging to the authenticated user.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required The order ID. Example: 1
     *
     * @response 200 {
     *   "data": {"id": 1, "status": "unpaid", "total_amount": "195000.00", "items": []}
     * }
     * @response 404 {"message": "Resource not found.", "error": "not_found"}
     */
    public function show($id, Request $request)
    {
        $order = Order::with(['items.product', 'user'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return new OrderResource($order);
    }
}
