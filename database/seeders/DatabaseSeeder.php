<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Favorite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $vendor = User::firstOrCreate(['email' => 'vendor@example.com'], [
            'name' => 'Vendor User',
            'phone' => '0987654321',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        $user1 = User::firstOrCreate(['email' => 'user@example.com'], [
            'name' => 'Test User',
            'phone' => '5555555555',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user2 = User::firstOrCreate(['email' => 'user2@example.com'], [
            'name' => 'Ahmad User',
            'phone' => '0933333333',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // 2. System Settings
        SystemSetting::firstOrCreate(['key' => 'sham_cash_qr'], [
            'value' => 'qr_codes/shamcash_demo.png',
            'description' => 'Payment QR Code for Sham Cash',
        ]);

        SystemSetting::firstOrCreate(['key' => 'admin_phone'], [
            'value' => '+963900000000',
            'description' => 'Admin contact phone number',
        ]);

        // 3. Categories (ألبسة، غذائية، أدوات منزلية، منظفات، متفرقات)
        $categories = [
            'Clothing (ألبسة)' => 'clothing-cat.png',
            'Groceries (غذائية)' => 'food-cat.png',
            'Housewares (أدوات منزلية)' => 'housewares-cat.png',
            'Cleaning Supplies (منظفات)' => 'cleaning-cat.png',
            'Miscellaneous (مكياج وإكسسوارات)' => 'misc-cat.png',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $image) {
            $categoryModels[] = Category::firstOrCreate(['name' => $name], [
                'image' => "categories/{$image}",
                'is_active' => true,
            ]);
        }

        // 4. Products for each category
        $productsData = [
            $categoryModels[0]->id => [
                ['name' => 'T-Shirt Cotton (تيشيرت قطن)', 'price' => 50000, 'stock' => 100],
                ['name' => 'Jeans Pants (بنطال جينز)', 'price' => 120000, 'stock' => 50],
            ],
            $categoryModels[1]->id => [
                ['name' => 'Rice 5kg (أرز 5 كغ)', 'price' => 75000, 'stock' => 200],
                ['name' => 'Canned Tuna (تونة معلبة)', 'price' => 15000, 'stock' => 500],
            ],
            $categoryModels[2]->id => [
                ['name' => 'Coffee Maker (آلة صنع القهوة)', 'price' => 450000, 'stock' => 20],
                ['name' => 'Blender (خلاط كهربائي)', 'price' => 300000, 'stock' => 35],
            ],
            $categoryModels[3]->id => [
                ['name' => 'Laundry Detergent (مسحوق غسيل)', 'price' => 85000, 'stock' => 150],
                ['name' => 'Dish Soap (سائل جلي)', 'price' => 25000, 'stock' => 300],
            ],
            $categoryModels[4]->id => [
                ['name' => 'Matte Lipstick (أحمر شفاه مات)', 'price' => 35000, 'stock' => 80],
                ['name' => 'Silver Necklace (قلادة فضة)', 'price' => 150000, 'stock' => 40],
            ],
        ];

        $allProducts = [];
        foreach ($productsData as $categoryId => $products) {
            foreach ($products as $index => $prod) {
                $product = Product::firstOrCreate(['name' => $prod['name']], [
                    'category_id' => $categoryId,
                    'description' => 'This is a description for ' . $prod['name'],
                    'price' => $prod['price'],
                    'stock' => $prod['stock'],
                    'main_image' => 'products/sample_product.png',
                    'external_link' => 'https://youtube.com',
                    'is_active' => true,
                ]);

                $allProducts[] = $product;

                // Add sample product image
                ProductImage::firstOrCreate([
                    'product_id' => $product->id,    
                    'image_path' => 'products/sample_product_gallery.png',
                ]);
            }
        }

        // 5. Carts for Users
        $cart1 = Cart::firstOrCreate(['user_id' => $user1->id]);
        CartItem::firstOrCreate([
            'cart_id' => $cart1->id,
            'product_id' => $allProducts[0]->id,
        ], ['quantity' => 2]);

        // 6. Favorites
        Favorite::firstOrCreate([
            'user_id' => $user1->id,
            'product_id' => $allProducts[1]->id,
        ]);
        Favorite::firstOrCreate([
            'user_id' => $user2->id,
            'product_id' => $allProducts[4]->id,
        ]);

        // 7. Orders & Invoices
        $order1 = Order::firstOrCreate(['id' => 1], [
            'user_id' => $user1->id,
            'total_amount' => 195000.00,
            'shipping_address' => 'Damascus, Mezzeh',
            'shipping_phone' => '0955555555',
            'notes' => 'Deliver in morning',
            'status' => 'unpaid',
            'payment_receipt_image' => 'receipts/sample_receipt.png',
        ]);

        OrderItem::firstOrCreate(['order_id' => $order1->id, 'product_id' => $allProducts[1]->id], [
            'quantity' => 1,
            'price' => 120000.00,
        ]);
        OrderItem::firstOrCreate(['order_id' => $order1->id, 'product_id' => $allProducts[2]->id], [
            'quantity' => 1,
            'price' => 75000.00,
        ]);

        // Second Order (Paid Status)
        $order2 = Order::firstOrCreate(['id' => 2], [
            'user_id' => $user2->id,
            'total_amount' => 300000.00,
            'shipping_address' => 'Damascus, Shaalan',
            'shipping_phone' => '0933333333',
            'notes' => 'Call before arrival',
            'status' => 'paid',
            'payment_receipt_image' => 'receipts/sample_receipt_2.png',
        ]);

        OrderItem::firstOrCreate(['order_id' => $order2->id, 'product_id' => $allProducts[5]->id], [
            'quantity' => 1,
            'price' => 300000.00,
        ]);

    }
}
