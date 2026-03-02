# User Report - E-Commerce API Documentation

## 📋 Table of Contents
1. [Project Overview](#project-overview)
2. [Installation Guide](#installation-guide)
3. [Access Points](#access-points)
4. [Admin Panel Guide](#admin-panel-guide)
5. [Vendor Panel Guide](#vendor-panel-guide)
6. [API Documentation](#api-documentation)
7. [Test Credentials](#test-credentials)
8. [Common Workflows](#common-workflows)
9. [Troubleshooting](#troubleshooting)

---

## Project Overview

This is a **Single-Vendor E-Commerce Backend System** built with:
- **Laravel 12** - Framework
- **FilamentPHP 3.3** - Admin/Vendor Panels
- **Laravel Sanctum** - API Authentication
- **MySQL** - Database
- **Scribe** - API Documentation

### Key Features
- ✅ Role-based access (Admin, Vendor, User)
- ✅ Email verification with OTP
- ✅ Manual payment verification (Sham Cash)
- ✅ Shopping cart & favorites
- ✅ Order management workflow
- ✅ RESTful API with Bearer token auth

---

## Installation Guide

### Prerequisites
- PHP 8.2+
- Composer
- MySQL
- Node.js & NPM

### Setup Steps

1. **Install Dependencies**
   ```bash
   composer install --ignore-platform-reqs
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   - Create MySQL database: `e_com_api`
   - Configure `.env`:
     ```env
     DB_DATABASE=e_com_api
     DB_USERNAME=root
     DB_PASSWORD=
     ```

4. **Run Migrations**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

6. **Generate API Documentation**
   ```bash
   php artisan scribe:generate
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

---

## Access Points

| Panel | URL | Description |
|-------|-----|-------------|
| **Admin Panel** | `http://localhost:8000/admin` | Manage users, settings, view all data |
| **Vendor Panel** | `http://localhost:8000/vendor` | Manage products, categories, process orders |
| **API Documentation** | `http://localhost:8000/docs` | Interactive API docs |
| **API Endpoints** | `http://localhost:8000/api/*` | RESTful API for mobile app |

---

## Admin Panel Guide

### Access
- **URL**: `/admin`
- **Role**: `admin` only

### Features

#### 1. **User Management**
- View all users (Admins, Vendors, Customers)
- Create new users
- Edit user details
- Block/unblock users

#### 2. **System Settings**
- Upload QR Code for Sham Cash payments
- Set admin contact phone
- Manage system-wide configurations

#### 3. **Dashboard Widgets**
- Total revenue (from paid orders)
- Total orders count
- New users count
- Monthly orders chart

### Workflow: Uploading Payment QR Code
1. Go to **System Settings** in admin panel
2. Find the `sham_cash_qr` setting
3. Upload the QR code image
4. Save changes
5. QR code will be available via API: `GET /api/settings`

---

## Vendor Panel Guide

### Access
- **URL**: `/vendor`
- **Role**: `vendor` only

### Features

#### 1. **Category Management**
- Create product categories
- Upload category images
- Activate/deactivate categories

#### 2. **Product Management**
- Create new products
- Upload main image
- Add multiple product images
- Set price, stock, description
- Add external links (YouTube, Telegram)
- Activate/deactivate products

#### 3. **Order Processing**
- View all incoming orders
- See payment receipt images
- Update order status:
  - `unpaid` → `paid` → `shipped` → `delivered`
  - Or mark as `shipping_issue`
- **Note**: Delete action is disabled for orders

### Workflow: Processing an Order
1. Navigate to **Orders** in vendor panel
2. Click on an order to view details
3. Check the payment receipt image
4. Verify payment manually
5. Update status to `paid`
6. Ship products and update to `shipped`
7. Update to `delivered` when completed

---

## API Documentation

### Authentication

All API endpoints (except auth endpoints) require Bearer token authentication.

**Get Token:**
```bash
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}
```

**Use Token:**
```bash
Authorization: Bearer {your_token}
```

### Public Endpoints

#### 1. **Register New User**
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### 2. **Verify Email**
```http
POST /api/verify-email
Content-Type: application/json

{
  "email": "john@example.com",
  "code": "123456"
}
```

#### 3. **Login**
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### 4. **Get System Settings**
```http
GET /api/settings
```
Returns Sham Cash QR code and admin phone.

#### 5. **List Products**
```http
GET /api/products?page=1&per_page=20&category_id=1&search=iphone
```

#### 6. **Get Product Details**
```http
GET /api/products/{id}
```

### Protected Endpoints (Require Authentication)

#### 7. **Get Current User**
```http
GET /api/me
Authorization: Bearer {token}
```

#### 8. **Logout**
```http
POST /api/logout
Authorization: Bearer {token}
```

#### 9. **Create Order**
```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: multipart/form-data

shipping_address: "123 Main St, City"
shipping_phone: "1234567890"
notes: "Please deliver in the evening"
payment_receipt_image: [file - max 5MB]
```

#### 10. **List User Orders**
```http
GET /api/orders?page=1
Authorization: Bearer {token}
```

#### 11. **Get Order Details**
```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

#### 12. **Get Cart**
```http
GET /api/cart
Authorization: Bearer {token}
```

#### 13. **Add to Cart**
```http
POST /api/cart
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

#### 14. **Update Cart Item**
```http
PUT /api/cart/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 3
}
```

#### 15. **Remove from Cart**
```http
DELETE /api/cart/{id}
Authorization: Bearer {token}
```

#### 16. **Get Favorites**
```http
GET /api/favorites
Authorization: Bearer {token}
```

#### 17. **Add to Favorites**
```http
POST /api/favorites
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1
}
```

#### 18. **Remove from Favorites**
```http
DELETE /api/favorites/{productId}
Authorization: Bearer {token}
```

---

## Test Credentials

### Admin User
```
Email: admin@example.com
Password: password
Role: admin
Access: /admin
```

### Vendor User
```
Email: vendor@example.com
Password: password
Role: vendor
Access: /vendor
```

### Regular User
```
Email: user@example.com
Password: password
Role: user
Access: API endpoints
```

---

## Common Workflows

### Workflow 1: User Registration & Login

1. **Register**
   ```bash
   POST /api/register
   ```
   - User receives OTP via email

2. **Verify Email**
   ```bash
   POST /api/verify-email
   ```
   - Enter OTP from email

3. **Login**
   ```bash
   POST /api/login
   ```
   - Receive Bearer token

4. **Access Protected Endpoints**
   - Use token in Authorization header

### Workflow 2: Placing an Order

1. **Browse Products**
   ```bash
   GET /api/products
   ```

2. **Add to Cart**
   ```bash
   POST /api/cart
   { "product_id": 1, "quantity": 2 }
   ```

3. **Get Payment Info**
   ```bash
   GET /api/settings
   ```
   - Get Sham Cash QR code
   - Make payment manually

4. **Create Order**
   ```bash
   POST /api/orders
   ```
   - Upload payment receipt screenshot
   - Cart is automatically emptied

5. **Wait for Vendor Verification**
   - Vendor checks receipt
   - Updates order status

6. **Track Order**
   ```bash
   GET /api/orders/{id}
   ```

### Workflow 3: Vendor Order Processing

1. **Login to Vendor Panel**
   - Navigate to `/vendor`

2. **View Orders**
   - See all incoming orders
   - Each order shows payment receipt

3. **Verify Payment**
   - Check uploaded receipt image
   - Verify against bank records

4. **Update Status**
   - Mark as `paid`
   - Ship and mark as `shipped`
   - Complete and mark as `delivered`

---

## Troubleshooting

### Common Issues

#### 1. **Storage Link Not Working**
```bash
php artisan storage:link
```

#### 2. **API Returns 401 Unauthorized**
- Check token is valid
- Ensure `Authorization: Bearer {token}` header is set
- Token format: `Bearer eyJ0eXAiOiJKV1QiLCJhbG...`

#### 3. **Cannot Upload Files**
- Ensure storage folder is writable
- Check `php.ini` upload_max_filesize settings
- Verify `storage:link` is created

#### 4. **Filament Panel Not Accessible**
- Clear config cache: `php artisan config:clear`
- Check user role matches panel access
- Verify panel providers are registered

#### 5. **Email Not Sending**
- Check `.env` mail settings
- For local testing, use `MAIL_MAILER=log`
- Check logs in `storage/logs/laravel.log`

#### 6. **Migration Errors**
```bash
php artisan migrate:fresh
php artisan db:seed
```

#### 7. **API Docs Not Showing**
```bash
php artisan scribe:generate
```
Then visit `/docs`

---

## Database Schema Overview

### Core Tables
- **users** - Admins, vendors, customers
- **categories** - Product categories
- **products** - Product catalog
- **product_images** - Additional product images
- **orders** - Customer orders
- **order_items** - Order line items
- **carts** - Shopping carts
- **cart_items** - Cart line items
- **favorites** - User favorites
- **verification_codes** - Email verification OTPs
- **system_settings** - App settings (QR code, etc.)

### Relationships
- User → hasOne → Cart
- User → hasMany → Orders
- User → belongsToMany → Products (favorites)
- Category → hasMany → Products
- Product → hasMany → ProductImages
- Order → hasMany → OrderItems
- Cart → hasMany → CartItems

---

## Security Notes

1. **Passwords** - Hashed using bcrypt
2. **API Tokens** - Sanctum Bearer tokens
3. **File Uploads** - Validated for type and size
4. **Role-Based Access** - Panel access restricted by role
5. **Email Verification** - Required for new users
6. **Payment Verification** - Manual vendor approval required

---

## Development Tips

### Generate API Documentation
```bash
php artisan scribe:generate
```

### Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Run Tests
```bash
php artisan test
```

### Monitor Logs
```bash
tail -f storage/logs/laravel.log
```

---

## Support

For issues or questions:
1. Check API documentation at `/docs`
2. Review this user report
3. Check Laravel logs
4. Verify database connections
5. Test with provided credentials

---

**Project Status**: ✅ Complete and Ready for Use

**Last Updated**: March 2, 2026

**Version**: 1.0.0
