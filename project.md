# Comprehensive Project Blueprint: Single-Vendor E-Commerce (Laravel 12 & Filament 3.3)

## 1. Project Overview & Tech Stack
**Goal:** Build a backend system for an e-commerce app with specific roles (Admin, Vendor, User) and a manual payment verification workflow (Sham Cash).

### **Technology Stack:**
*   **Framework:** **Laravel 12** (Latest).
*   **Admin/Vendor Panels:** **FilamentPHP v3.3**.
*   **API Auth:** **Laravel Sanctum**.
*   **Database:** MySQL.
*   **API Documentation:** Open API / Swagger (via Scribe or L5-Swagger).
*   **Language:** PHP 8.2+.

---

## 2. Database Schema (DBML)
*Instruction: Implement migrations based on this schema.*

```dbml
// Users & Roles
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

// System Settings (Admin manages Payment QR here)
Table system_settings {
  id integer [primary key, increment]
  key varchar [unique] // e.g., 'sham_cash_qr', 'admin_phone'
  value text // Stores image path or text
  description varchar [nullable]
  created_at timestamp
  updated_at timestamp
}

// Catalog
Table categories {
  id integer [primary key, increment]
  name varchar
  image varchar [nullable]
  is_active boolean [default: true]
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
  external_link varchar [nullable] // Youtube/Telegram link
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table product_images {
  id integer [primary key, increment]
  product_id integer
  image_path varchar
  created_at timestamp
}

// Orders & Payments
Table orders {
  id integer [primary key, increment]
  user_id integer
  total_amount decimal(10, 2)
  shipping_address text
  shipping_phone varchar
  notes text [nullable]
  status enum('unpaid', 'paid', 'shipped', 'delivered', 'shipping_issue') [default: 'unpaid']
  payment_receipt_image varchar [nullable] // Uploaded by User via API
  created_at timestamp
  updated_at timestamp
}

Table order_items {
  id integer [primary key, increment]
  order_id integer
  product_id integer
  quantity integer
  price decimal(10, 2)
  created_at timestamp
}

Table carts {
  id integer [primary key, increment]
  user_id integer
  updated_at timestamp
}

Table cart_items {
  id integer [primary key, increment]
  cart_id integer
  product_id integer
  quantity integer
}

Table favorites {
  user_id integer
  product_id integer
  primary key (user_id, product_id)
}

// Relationships
Ref: products.category_id > categories.id
Ref: product_images.product_id > products.id [delete: cascade]
Ref: carts.user_id - users.id
Ref: cart_items.cart_id > carts.id [delete: cascade]
Ref: cart_items.product_id > products.id
Ref: orders.user_id > users.id
Ref: order_items.order_id > orders.id [delete: cascade]
Ref: order_items.product_id > products.id
```
## 3. Filament Implementation (Multi-Panel Architecture)

**Objective:** Create two distinct dashboards using Filament 3.3 Panels features.

### **3.1. Installation & Panel Setup**
*   **Install:** `composer require filament/filament:"^3.3" -W`
*   **Generate Panels:**
    1.  **Admin Panel:** `php artisan filament:install --panels` (Accept default `admin`).
    2.  **Vendor Panel:** `php artisan make:filament-panel vendor` (Path: `/vendor`).

### **3.2. Admin Panel Configuration (`/admin`)**
*   **Auth:** Restrict access using middleware or `canAccessPanel` method in `User` model (Check if `role === 'admin'`).
*   **Resource 1: UserResource**
    *   Manage Admins, Vendors, and Users.
    *   Actions: Create, Edit, Block (Toggle `is_active`).
*   **Resource 2: SystemSettingResource (Crucial)**
    *   **Goal:** Allow Admin to upload the "Sham Cash" QR Code.
    *   **Form Schema:**
        *   `key`: TextInput (Readonly/Disabled, seeded as 'sham_cash_qr').
        *   `value`: **FileUpload** (Image, Public disk).
        *   `description`: Textarea.
*   **Widgets:**
    *   **StatsOverview:** Total Revenue (Sum of `orders.total_amount` where status='paid'), Total Orders, New Users.
    *   **Chart:** Orders per month (Line chart).

### **3.3. Vendor Panel Configuration (`/vendor`)**
*   **Auth:** Restrict access to `role === 'vendor'`.
*   **Resource 1: CategoryResource**
    *   Standard CRUD (Name, Image, Active Status).
*   **Resource 2: ProductResource**
    *   **Form Schema:**
        *   `name`, `description`, `price`, `stock`.
        *   `main_image`: **FileUpload**.
        *   `product_images`: **Repeater** (or `HasMany` relation) containing `FileUpload`.
        *   `external_link`: TextInput (url validation).
*   **Resource 3: OrderResource (The Core Workflow)**
    *   **Table:** List orders assigned to the system (or filtered if multi-vendor logic applies later).
    *   **Infolist / View:**
        *   **Section:** "Payment Verification".
        *   **Entry:** **ImageEntry** for `payment_receipt_image` (To view the user's screenshot).
    *   **Actions:**
        *   **Edit Status:** Select (`unpaid`, `paid`, `shipped`, `delivered`, `shipping_issue`).
        *   *Restriction:* Disable "Delete" action.
*   **Widgets:**
    *   **Stats:** Pending Orders Count (No financial totals allowed).

---

## 4. API Architecture (Laravel 12 & Sanctum)

**Objective:** Build a RESTful API with versioning, strictly using Resources and Requests.

### **4.1. Authentication**
*   **Package:** Laravel Sanctum (Ensure it's installed/configured).
*   **Routes:**
    *   `POST /api/register`: Creates user, generates OTP, Sends Email.
    *   `POST /api/verify-email`: Validates OTP code.
    *   `POST /api/login`: Returns `Bearer` token.

### **4.2. Core Functionality**
*   **Settings Controller:**
    *   `GET /api/settings`: Returns the `sham_cash_qr` image URL from `system_settings` table.
*   **Product Controller:**
    *   `GET /api/products`: Returns `ProductResource::collection`.
    *   `GET /api/products/{id}`: Returns single `ProductResource` (includes `images` relationship).

### **4.3. Order Transaction (Logic)**
*   **Endpoint:** `POST /api/orders`
*   **Form Request:** `StoreOrderRequest`
    *   `shipping_address`: required|string.
    *   `payment_receipt_image`: **required|image|max:5120** (5MB).
*   **Controller Logic (`OrderController@store`):**
    1.  **Validation:** handled by Request class.
    2.  **Upload:** Store `payment_receipt_image` to `public/receipts`.
    3.  **Calculation:** Calculate total from User's Cart.
    4.  **Creation:** Create Order (`status: unpaid`, `payment_receipt_image: $path`).
    5.  **Migration:** Move `CartItems` -> `OrderItems`.
    6.  **Cleanup:** Empty Cart.
    7.  **Response:** Return `OrderResource` (201 Created).

---

## 5. API Documentation (Swagger/OpenAPI)

**Objective:** Generate a `swagger.json` file for the Mobile Team using **Scribe**.

### **5.1. Setup**
*   **Install:** `composer require --dev knuckleswtf/scribe`
*   **Config:** `php artisan vendor:publish --tag=scribe-config`
    *   Set `type` => `laravel`.
    *   Enable `openapi` => `true`.
    *   Set `auth.enabled` => `true` (Bearer).

### **5.2. Annotations (DocBlocks)**
*   **Add to Controllers:**
    ```php
    /**
     * Submit Order
     * 
     * Uploads the Sham Cash receipt and creates the order.
     * 
     * @group Orders
     * @authenticated
     * @bodyParam shipping_address string required Address.
     * @bodyParam payment_receipt_image file required The screenshot image.
     */
    public function store(StoreOrderRequest $request) { ... }
    ```

### **5.3. Generation**
*   Run: `php artisan scribe:generate`.
*   Output: Provides a webpage at `/docs` and an `openapi.yaml` file for Swagger UI.

---

## 6. Implementation Steps for AI Agent

**Step 1: Environment & Database**
*   Initialize Laravel 12 project.
*   Setup `.env` (DB, Mail, App URL).
*   Create Migrations (Users, SystemSettings, Categories, Products, Orders, Carts).
*   Run `php artisan migrate`.

**Step 2: Filament Panels Setup**
*   Run `php artisan filament:install --panels`.
*   Create `VendorPanelProvider`.
*   **Admin Panel:** Generate `UserResource`, `SystemSettingResource`.
*   **Vendor Panel:** Generate `CategoryResource`, `ProductResource`, `OrderResource`.
*   *Task:* Ensure `OrderResource` has `ImageEntry` for the receipt.

**Step 3: API Foundation**
*   Create `StoreOrderRequest` (Validation).
*   Create `OrderResource`, `ProductResource` (Transformation).
*   Create `AuthController`, `ProductController`, `OrderController`.
*   Define Routes in `api.php`.

**Step 4: Documentation**
*   Install Scribe.
*   Annotate `OrderController` specifically for File Uploads.
*   Generate Docs.

**Step 5: Final Check**
*   Verify `storage:link` is run.
*   Verify Admin can upload QR Code.
*   Verify API can fetch QR Code.
*   Verify Vendor can see uploaded receipt in Order details.