# Backend & Admin Panel Blueprint: Single-Vendor E-Commerce with Manual Payment

## 1. Project Overview
**Goal:** Build a robust Backend API and Admin Panel for a single-vendor e-commerce system.
**Scope:** Laravel API (for mobile app consumption) + FilamentPHP Multi-Panel (for Admin & Vendor management).
**Key Workflow:** 
1.  **Admin** manages overall system, users, and uploads "Sham Cash" QR Code via settings.
2.  **Vendor** manages products, categories, and processes user orders.
3.  **User** (via API) places an order and **uploads a payment receipt image**.
4.  **Vendor / Admin** (via Filament) views the receipt, verifies payment, and updates order status.

## 2. Technology Stack
*   **Framework:** Laravel 12.x
*   **Admin/Vendor Panels:** FilamentPHP v3.3 (`composer require filament/filament:"^3.3" -W`)
*   **Database:** MySQL.
*   **Authentication:** Laravel Sanctum (API), Session (Filament).
*   **Storage:** Local (public disk) or S3.
*   **Notifications:** Firebase Cloud Messaging (Backend logic only).
*   **Mail:** SMTP (for OTP).
*   **API Documentation:** Scribe (Automated Generation).

---markdown

## 3. Database Schema (DBML)
*Instruction: Use this DBML to generate migrations and models exactly as defined.*

```dbml
// 1. Users & Auth
Table users {
  id integer [primary key, increment]
  name varchar
  email varchar [unique]
  phone varchar [unique]
  password varchar
  role enum('admin', 'vendor', 'user') [default: 'user']
  avatar varchar [nullable]
  fcm_token varchar [nullable]
  email_verified_at timestamp [nullable]
  created_at timestamp
  updated_at timestamp
}

Table verification_codes {
  id integer [primary key, increment]
  email varchar
  code varchar
  expires_at timestamp
  created_at timestamp
}

// 2. System Settings (Dynamic Key-Value Store)
Table system_settings {
  id integer[primary key, increment]
  key varchar [unique] // e.g., 'sham_cash_qr', 'contact_phone'
  value text // Stores image path or text data
  description varchar [nullable]
  created_at timestamp
  updated_at timestamp
}

// 3. Catalog
Table categories {
  id integer [primary key, increment]
  name varchar
  image varchar [nullable]
  is_active boolean[default: true]
  created_at timestamp
  updated_at timestamp
}

Table products {
  id integer [primary key, increment]
  category_id integer
  name varchar
  description text
  price decimal(10, 2)
  stock integer [default: 0]
  main_image varchar
  external_link varchar [nullable] // Youtube or Telegram link
  is_active boolean[default: true]
  created_at timestamp
  updated_at timestamp
}

Table product_images {
  id integer [primary key, increment]
  product_id integer
  image_path varchar
  created_at timestamp
}

// 4. Shopping Logic
Table carts {
  id integer[primary key, increment]
  user_id integer
  created_at timestamp
  updated_at timestamp
}

Table cart_items {
  id integer [primary key, increment]
  cart_id integer
  product_id integer
  quantity integer
  created_at timestamp
  updated_at timestamp
}

Table favorites {
  user_id integer
  product_id integer
  created_at timestamp
  primary key (user_id, product_id)
}

// 5. Orders & Payments
Table orders {
  id integer [primary key, increment]
  user_id integer
  total_amount decimal(10, 2)
  shipping_address text
  shipping_phone varchar
  notes text [nullable]
  status enum('unpaid', 'paid', 'shipped', 'delivered', 'shipping_issue')[default: 'unpaid']
  payment_receipt_image varchar [nullable] // CRITICAL: Uploaded by user via API
  created_at timestamp
  updated_at timestamp
}

Table order_items {
  id integer[primary key, increment]
  order_id integer
  product_id integer
  quantity integer
  price decimal(10, 2)
  created_at timestamp
}

// Relations
Ref: products.category_id > categories.id
Ref: product_images.product_id > products.id[delete: cascade]
Ref: carts.user_id - users.id
Ref: cart_items.cart_id > carts.id [delete: cascade]
Ref: cart_items.product_id > products.id
Ref: orders.user_id > users.id
Ref: order_items.order_id > orders.id [delete: cascade]
Ref: order_items.product_id > products.id
```

---

## Step 4: Filament Multi-Panel Architecture

**Objective:** Implement two strictly separated dashboards using `php artisan filament:install --panels`. One for `Admin` and one for `Vendor`.

### 4.1. Admin Panel (`/admin`)
**Access:** Users with `role === 'admin'`. Has full systemic control.
*   **Resources:**
    *   `UserResource`: Manage Users and Vendors (Create, Edit, Delete, Block).
    *   `SystemSettingResource`: **Crucial** for managing Financial Info and uploading the "Sham Cash QR Code".
    *   `CategoryResource`: Full management of categories.
    *   `ProductResource`: Full management of all products.
    *   `OrderResource`: Track orders, confirm payments (view receipt image), and update order status.
*   **Relation Managers:**
    *   `ProductImagesRelationManager` (Inside `ProductResource` to manage gallery).
    *   `OrderItemsRelationManager` (Inside `OrderResource` to view purchased items).
*   **Widgets:**
    *   `AdminStatsWidget`: Total Revenue (Financials), Total Users, Total Orders.
    *   `RevenueChartWidget`: Line chart of sales over time.

### 4.2. Vendor Panel (`/vendor`)
**Access:** Users with `role === 'vendor'`. Focuses on operational tasks only.
*   **Resources:**
    *   `CategoryResource`: Manage categories.
    *   `ProductResource`: Manage products, set details, upload `main_image` and external links.
    *   `OrderResource`: Track orders, view `payment_receipt_image` to **Confirm Payment**, and update order `status` (unpaid -> paid -> shipped).
*   **Relation Managers:**
    *   `ProductImagesRelationManager` (Inside `ProductResource` to upload gallery images).
    *   `OrderItemsRelationManager` (Inside `OrderResource` to view items to be packed).
*   **Widgets:**
    *   `VendorStatsWidget`: Pending Orders, Shipped Orders (Count only).
*   **Security Constraints (Policies):** Vendor MUST NOT access `UserResource`, `SystemSettingResource`, or any global financial revenue statistics.

---

## Step 5: Professional API Architecture (Standardization)

**Objective:** Build a clean, secure, and maintainable API using Laravel 12 best practices.

### 5.1. Form Requests (Validation Layer)
*Instruction: Create separate Request classes for validation.*

*   **Create `Auth/RegisterRequest`:**
    *   Rules: `name` (string), `email` (required|email|unique:users), `phone` (required|unique:users), `password` (required|min:6).
*   **Create `Order/StoreOrderRequest` (Crucial):**
    *   **Rules:** `shipping_address` (required|string|max:500), `shipping_phone` (required|string), `payment_receipt_image` (required|file|image|mimes:jpeg,png,jpg|max:5120).
    *   **Note:** Ensure this request handles `multipart/form-data`.

### 5.2. API Resources (Transformation Layer)
*Instruction: Transform models to JSON and handle media URLs.*

*   **Create `ProductResource`:**
    *   Return full URLs: `'main_image' => asset('storage/' . $this->main_image)`.
    *   Include relations: `'category' => new CategoryResource($this->whenLoaded('category'))`.
*   **Create `OrderResource`:**
    *   Format Status: `'status_label' => ucfirst($this->status)`.
    *   **Payment Image:** `'payment_receipt_url' => asset('storage/' . $this->payment_receipt_image)`.

### 5.3. Controller Implementation (Business Logic)
*   **`OrderController@store` Logic:**
    *   Use `DB::transaction` to ensure data integrity.
    *   **Step 1:** Upload the image: `$path = $request->file('payment_receipt_image')->store('receipts', 'public');`
    *   **Step 2:** Calculate Total from Cart.
    *   **Step 3:** Create Order with status `unpaid` and save `$path`.
    *   **Step 4:** Move Cart items to Order Items.
    *   **Step 5:** Empty Cart & Return `new OrderResource($order)`.

---

## Step 6: Automated API Documentation (Swagger/Scribe)

**Objective:** Generate interactive Swagger/OpenAPI documentation for the mobile development team.

### 6.1. Setup Scribe
1.  **Install:** `composer require --dev knuckleswtf/scribe`.
2.  **Publish Config:** `php artisan vendor:publish --tag=scribe-config`.
3.  **Config (`config/scribe.php`):**
    *   Set `type` to `'static'` or `'laravel'`.
    *   Enable `openapi` generation (to output swagger file).
    *   Enable `auth` (Bearer Token).

### 6.2. Aggressive Annotation Strategy
*Instruction: Add DocBlocks to Controllers, FormRequests, and API Resources so Scribe generates highly accurate OpenAPI schemas.*

*   **Controllers (`OrderController@store`):**
    ```php
    /**
     * Submit Order with Receipt
     * 
     * Creates a new order from the user's cart and uploads the Sham Cash receipt.
     * 
     * @group Orders
     * @authenticated
     */
    public function store(StoreOrderRequest $request) { ... }
    ```
*   **Form Requests (`StoreOrderRequest`):** (Scribe reads validation rules, but adding descriptions helps).
    ```php
    /**
     * @bodyParam shipping_address string required The delivery address. Example: Damascus, Street 1.
     * @bodyParam payment_receipt_image file required The screenshot of the payment via Sham Cash.
     */
    ```
*   **API Resources:** Add `@mixin` or define the expected JSON structure in DocBlocks for better schema generation.

### 6.3. Generation
*   Run `php artisan scribe:generate`.
*   Verify docs at `http://your-app.test/docs`.

---

## Step 7: Configuration, Error Handling & Seeding

### 7.1. Route Organization (`routes/api.php`)
*   **Public Routes:** `register`, `login`, `categories`, `products`, `settings` (Returns Sham Cash QR Code).
*   **Protected Routes (`auth:sanctum`):** `cart/*`, `favorites/*`, `orders/*`, `profile`.

### 7.2. Laravel 12 Exception Handling (JSON)
*   Ensure `bootstrap/app.php` is configured via `->withExceptions(function (Exceptions $exceptions) { ... })` to force JSON responses for all API endpoints.
*   Ensure Validation Errors (422) return a structured object for the Flutter form to parse easily.
*   Handle `ModelNotFoundException` to return a clean `404 Not Found` JSON response.

### 7.3. Database Seeding (Testing Data)
*Instruction: Create robust Seeders to allow immediate testing of the Mobile App and Admin Panels.*

1.  **`UserSeeder`:**
    *   Create 1 Admin (`role: admin`, email: `admin@app.com`, password: `password`).
    *   Create 1 Vendor (`role: vendor`, email: `vendor@app.com`, password: `password`).
    *   Create 5 Users (`role: user`, password: `password`).
2.  **`SystemSettingSeeder`:**
    *   Create record for `key: 'sham_cash_qr'`, `value: 'path/to/dummy_qr.png'`, `description: 'Scan to pay'`.
    *   Create record for `key: 'contact_phone'`, `value: '+963999999999'`.
3.  **`CategorySeeder` & `ProductSeeder`:**
    *   Generate 5 Categories with dummy images.
    *   Generate 20 Products linked to categories with dummy `main_image` and varying stock/price.
4.  **Run Strategy:** Call all seeders inside `DatabaseSeeder` and run `php artisan migrate:fresh --seed`.

---

## Step 8: Execution Guidelines for AI Agent

**Agent Directive:** Execute the project strictly in the following phases to ensure dependencies are met:

*   **Phase 1 (Foundation):** Setup Laravel 12, configure `.env`, create Models, Migrations (from DBML in Step 3), and execute Seeders (Step 7).
*   **Phase 2 (Filament Panels):** Install Filament 3.3, generate both `admin` and `vendor` panels. Build Resources, RelationManagers, and Widgets for each panel. **Critically:** Implement Laravel Policies to ensure the Vendor cannot access Admin resources or global financial widgets.
*   **Phase 3 (API Layer):** Build API Routes, FormRequests, and API Resources. Implement the Controllers. Pay special attention to `OrderController@store` ensuring it handles `multipart/form-data` for the `payment_receipt_image` securely using DB Transactions.
*   **Phase 4 (Documentation):** Install Scribe, comprehensively annotate the API controllers and FormRequests (especially the file upload requirements), and run `php artisan scribe:generate` to produce the Swagger/OpenAPI spec.
---