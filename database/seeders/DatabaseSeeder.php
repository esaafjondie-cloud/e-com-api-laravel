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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Users — as per requirement.md
        $admin = User::firstOrCreate(['email' => 'admin@app.com'], [
            'name'              => 'Admin User',
            'phone'             => '+963911111111',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $vendor = User::firstOrCreate(['email' => 'vendor@app.com'], [
            'name'              => 'Vendor User',
            'phone'             => '+963922222222',
            'password'          => Hash::make('password'),
            'role'              => 'vendor',
            'email_verified_at' => now(),
        ]);

        // 5 Regular Users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = User::firstOrCreate(['email' => "user{$i}@app.com"], [
                'name'              => "Test User {$i}",
                'phone'             => "+96393{$i}000{$i}00",
                'password'          => Hash::make('password'),
                'role'              => 'user',
                'email_verified_at' => now(),
            ]);
        }

        // 2. System Settings
        SystemSetting::firstOrCreate(['key' => 'sham_cash_qr'], [
            'value'       => 'qr_codes/shamcash_demo.png',
            'description' => 'Scan to pay via Sham Cash',
        ]);

        SystemSetting::firstOrCreate(['key' => 'contact_phone'], [
            'value'       => '+963999999999',
            'description' => 'Admin contact phone number',
        ]);

        // 3. Categories — 5 categories
        $categoriesData = [
            ['name' => 'Clothing (ألبسة)',                 'image' => 'clothing-cat.png'],
            ['name' => 'Groceries (غذائية)',               'image' => 'food-cat.png'],
            ['name' => 'Housewares (أدوات منزلية)',        'image' => 'housewares-cat.png'],
            ['name' => 'Cleaning Supplies (منظفات)',       'image' => 'cleaning-cat.png'],
            ['name' => 'Miscellaneous (مكياج وإكسسوارات)', 'image' => 'misc-cat.png'],
        ];

        $categoryModels = [];
        foreach ($categoriesData as $cat) {
            $categoryModels[] = Category::firstOrCreate(['name' => $cat['name']], [
                'image'     => "categories/{$cat['image']}",
                'is_active' => true,
            ]);
        }

        // 4. Products — 20 products (4 per category)
        $productsData = [
            $categoryModels[0]->id => [
                ['name' => 'Cotton T-Shirt (تيشيرت قطن)',      'price' => 50000,  'stock' => 100],
                ['name' => 'Jeans Pants (بنطال جينز)',         'price' => 120000, 'stock' => 50],
                ['name' => 'Winter Jacket (جاكيت شتوي)',       'price' => 250000, 'stock' => 30],
                ['name' => 'Sports Shorts (شورت رياضي)',       'price' => 45000,  'stock' => 80],
            ],
            $categoryModels[1]->id => [
                ['name' => 'Rice 5kg (أرز 5 كغ)',             'price' => 75000,  'stock' => 200],
                ['name' => 'Canned Tuna (تونة معلبة)',         'price' => 15000,  'stock' => 500],
                ['name' => 'Olive Oil 1L (زيت زيتون)',         'price' => 95000,  'stock' => 150],
                ['name' => 'Pasta 500g (معكرونة)',             'price' => 18000,  'stock' => 400],
            ],
            $categoryModels[2]->id => [
                ['name' => 'Coffee Maker (آلة قهوة)',          'price' => 450000, 'stock' => 20],
                ['name' => 'Blender (خلاط كهربائي)',           'price' => 300000, 'stock' => 35],
                ['name' => 'Electric Kettle (غلاية كهربائية)', 'price' => 180000, 'stock' => 45],
                ['name' => 'Microwave 20L (مايكرويف)',         'price' => 750000, 'stock' => 15],
            ],
            $categoryModels[3]->id => [
                ['name' => 'Laundry Detergent (مسحوق غسيل)',  'price' => 85000,  'stock' => 150],
                ['name' => 'Dish Soap (سائل جلي)',            'price' => 25000,  'stock' => 300],
                ['name' => 'Floor Cleaner (منظف أرضيات)',     'price' => 40000,  'stock' => 200],
                ['name' => 'Bathroom Cleaner (منظف حمامات)',  'price' => 35000,  'stock' => 180],
            ],
            $categoryModels[4]->id => [
                ['name' => 'Matte Lipstick (أحمر شفاه)',      'price' => 35000,  'stock' => 80],
                ['name' => 'Silver Necklace (قلادة فضة)',     'price' => 150000, 'stock' => 40],
                ['name' => 'Perfume 50ml (عطر)',              'price' => 200000, 'stock' => 60],
                ['name' => 'Makeup Brush Set (فرش مكياج)',    'price' => 55000,  'stock' => 70],
            ],
        ];

        $allProducts = [];
        foreach ($productsData as $categoryId => $products) {
            foreach ($products as $prod) {
                $product = Product::firstOrCreate(['name' => $prod['name']], [
                    'category_id'   => $categoryId,
                    'description'   => "High-quality product: {$prod['name']}. Available in our store.",
                    'price'         => $prod['price'],
                    'stock'         => $prod['stock'],
                    'main_image'    => 'products/sample_product.png',
                    'external_link' => null,
                    'is_active'     => true,
                ]);

                $allProducts[] = $product;

                ProductImage::firstOrCreate([
                    'product_id' => $product->id,
                    'image_path' => 'products/sample_gallery.png',
                ]);
            }
        }

        // 5. Cart for user1
        $user1 = $users[0];
        $cart1 = Cart::firstOrCreate(['user_id' => $user1->id]);
        CartItem::firstOrCreate(['cart_id' => $cart1->id, 'product_id' => $allProducts[0]->id], ['quantity' => 2]);
        CartItem::firstOrCreate(['cart_id' => $cart1->id, 'product_id' => $allProducts[4]->id], ['quantity' => 1]);

        // 6. Favorites
        Favorite::firstOrCreate(['user_id' => $users[0]->id, 'product_id' => $allProducts[1]->id]);
        Favorite::firstOrCreate(['user_id' => $users[0]->id, 'product_id' => $allProducts[8]->id]);
        Favorite::firstOrCreate(['user_id' => $users[1]->id, 'product_id' => $allProducts[4]->id]);
        Favorite::firstOrCreate(['user_id' => $users[2]->id, 'product_id' => $allProducts[16]->id]);

        // 7. Orders
        $order1 = Order::firstOrCreate(['id' => 1], [
            'user_id'               => $users[0]->id,
            'total_amount'          => 195000.00,
            'shipping_address'      => 'Damascus, Mezzeh, Building 12',
            'shipping_phone'        => '+963955555551',
            'notes'                 => 'Please deliver in the morning',
            'status'                => 'unpaid',
            'payment_receipt_image' => 'receipts/sample_receipt.png',
        ]);
        OrderItem::firstOrCreate(['order_id' => $order1->id, 'product_id' => $allProducts[1]->id], ['quantity' => 1, 'price' => 120000.00]);
        OrderItem::firstOrCreate(['order_id' => $order1->id, 'product_id' => $allProducts[4]->id], ['quantity' => 1, 'price' => 75000.00]);

        $order2 = Order::firstOrCreate(['id' => 2], [
            'user_id'               => $users[1]->id,
            'total_amount'          => 450000.00,
            'shipping_address'      => 'Damascus, Shaalan, Apartment 5',
            'shipping_phone'        => '+963944444442',
            'notes'                 => 'Call before arrival',
            'status'                => 'paid',
            'payment_receipt_image' => 'receipts/sample_receipt_2.png',
        ]);
        OrderItem::firstOrCreate(['order_id' => $order2->id, 'product_id' => $allProducts[8]->id], ['quantity' => 1, 'price' => 450000.00]);

        $order3 = Order::firstOrCreate(['id' => 3], [
            'user_id'               => $users[2]->id,
            'total_amount'          => 110000.00,
            'shipping_address'      => 'Aleppo, City Center',
            'shipping_phone'        => '+963933333343',
            'notes'                 => null,
            'status'                => 'shipped',
            'payment_receipt_image' => 'receipts/sample_receipt_3.png',
        ]);
        OrderItem::firstOrCreate(['order_id' => $order3->id, 'product_id' => $allProducts[0]->id], ['quantity' => 2, 'price' => 50000.00]);
        OrderItem::firstOrCreate(['order_id' => $order3->id, 'product_id' => $allProducts[13]->id], ['quantity' => 1, 'price' => 25000.00]);
    }
}
