# Project Analysis & Functional Requirements (For AI Agent Context)

## 1. Core Business Logic & Objective
**System Type:** Single-Vendor E-Commerce Backend (Laravel 12 API + Filament 3.3 Admin/Vendor Panels).
**Unique Selling Proposition (USP) / Core Workflow:** 
The system relies on a **Manual Payment Verification Flow (Sham Cash)** rather than an automated payment gateway. 
*Workflow:* 
1. The Admin defines a payment QR Code in the system settings.
2. The Mobile App (User) fetches this QR Code via API during checkout.
3. The User pays externally using the "Sham Cash" app, takes a screenshot of the receipt, and submits the order via API along with the image.
4. The Vendor reviews the uploaded receipt in their Filament panel and manually transitions the order status.

---

## 2. Detailed Functional Requirements by Actor

### Actor 1: The Super Admin (Filament `/admin` Panel)
The Admin is the business owner who oversees the platform but does not manage day-to-day product shipping.
*   **FR-A1 (System Settings):** Must be able to upload and update the "Sham Cash QR Code" and "Payment Phone Number" dynamically. These are stored in a key-value `system_settings` table.
*   **FR-A2 (Financial Oversight):** Must have a dashboard displaying global metrics: Total Store Revenue, Total Orders, and User Growth.
*   **FR-A3 (Access Control):** Has full CRUD access to Users, Vendors, and global settings.

### Actor 2: The Vendor (Filament `/vendor` Panel)
The Vendor is the operational manager. They handle cataloging and order fulfillment.
*   **FR-V1 (Catalog Management):** Can create Categories and Products. Products must support a main image, a gallery of multiple images, and an optional external link (e.g., YouTube review or Telegram channel).
*   **FR-V2 (Order Processing - CRITICAL):** 
    *   Must be able to view incoming orders.
    *   Must have a clear UI component (`ImageEntry` in Filament) to view the `payment_receipt_image` uploaded by the user.
    *   Must be able to update the `status` of the order.
    *   *Constraint:* Cannot delete orders (append-only/update-only for data integrity).
*   **FR-V3 (Financial Isolation):** *Security Rule:* The Vendor dashboard MUST NOT display the total historical revenue of the entire store. They only see counts of pending/completed orders.

### Actor 3: The API / System (Serving the Mobile App)
The API acts as the bridge for the Flutter mobile application.
*   **FR-S1 (Authentication & Security):** 
    *   Uses Laravel Sanctum for token-based auth.
    *   Requires Email OTP verification (via SMTP) upon registration before allowing full access.
*   **FR-S2 (Shopping Cart):** Maintains a persistent cart for the user in the database (`carts` and `cart_items` tables).
*   **FR-S3 (Checkout Transaction - CRITICAL):**
    *   The `POST /api/orders` endpoint must accept `multipart/form-data`.
    *   It must safely upload the `payment_receipt_image` to the public storage.
    *   It must calculate the total price based on the current cart items.
    *   It must lock the price of the items at the time of purchase by migrating them to `order_items`.
    *   It must clear the user's cart upon successful order creation.
*   **FR-S4 (Dynamic Content):** The API must expose an endpoint (`GET /api/settings`) to serve the Sham Cash QR code URL to the app.

---

## 3. Order State Machine (Lifecycle)
The system must enforce the following order statuses (`status` enum in `orders` table):
1.  **`unpaid`**: The default status when an API order is created. (Means the order is logged, and the receipt is attached, pending Vendor review).
2.  **`paid`**: Vendor manually transitions to this after verifying the `payment_receipt_image` is valid.
3.  **`shipped`**: Vendor marks the order as handed over to the delivery company.
4.  **`delivered`**: Final successful state.
5.  **`shipping_issue`**: Exception state if delivery fails.

---

## 4. Technical Constraints & Directives for AI Agent
1.  **Framework Versions:** Strictly use features compatible with **Laravel 12** and **FilamentPHP 3.3**.
2.  **Multi-Panel Architecture:** Utilize Filament's native multi-panel support (`php artisan filament:install --panels`). Do not build custom middleware for panel routing if Filament handles it natively via `PanelProvider`.
3.  **API Standards:** 
    *   Always use `FormRequest` classes for validation.
    *   Always return data wrapped in `JsonResource` (API Resources).
    *   Append full absolute URLs to any media/image fields in the API response using `asset('storage/...')`.
4.  **Documentation:** Generate OpenAPI/Swagger documentation using `knuckleswtf/scribe` with accurate DocBlock annotations reflecting the `multipart/form-data` requirement for the order endpoint.