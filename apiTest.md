# دليل اختبار الـ API باستخدام Postman

الـ API الخاصة بالمشروع موثقة وجاهزة للتوافق مع أداة Scribe، وتم إضافة الـ Docblocks اللازمة لجميع الـ Controllers (مثل `Cart`, `Favorite`, `Product`, `Order`, `Auth`, `Settings`) والـ Requests لضمان استخراج التوثيق بشكل سليم.

فيما يلي دليل خطوة بخطوة لاختبار الـ API عبر مساراتها (Endpoints) المختلفة داخل Postman.

## إعدادات عامة في Postman
1. **الرابط الأساسي (Base URL):** `http://localhost:8000/api` (استبدله برابط خادمك إذا كان مختلفاً).
2. **الترويسات (Headers):** من المهم جداً إضافة الترويسة التالية لجميع الطلبات لضمان استلام الاستجابة بصيغة JSON:
   - `Accept`: `application/json`
3. **المصادقة (Authorization):** بالنسبة للطلبات التي تتطلب تسجيل دخول، يجب وضع الرمز المميز (Token) الذي تحصل عليه من عملية تسجيل الدخول:
   - اذهب إلى تبويب **Authorization** في الطلب.
   - اختر النوع **Bearer Token**.
   - الصق رمز الـ Token في حقل الـ Token.

---

## 1. المصادقة (Authentication)

### إنشاء حساب جديد (Register)
- **المسار:** `POST /register`
- **Body (raw JSON):**
  ```json
  {
    "name": "Ahmad Ali",
    "email": "user@test.com",
    "phone": "+963912345678",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```

### تأكيد البريد الإلكتروني (Verify Email)
- **المسار:** `POST /verify-email`
- **Body (raw JSON):**
  ```json
  {
    "email": "user@test.com",
    "code": "A1B2C3" // الكود المرسل عبر الإيميل
  }
  ```

### تسجيل الدخول (Login)
- **المسار:** `POST /login`
- **Body (raw JSON):**
  ```json
  {
    "email": "user@test.com",
    "password": "password123"
  }
  ```
> **ملاحظة هامة:** بعد نجاح تسجيل الدخول، انسخ قيمة الـ `token` من الاستجابة لاستخدامها في الخطوات القادمة التي تتطلب مصادقة (Bearer Token).

### تسجيل الخروج (Logout)
- **المسار:** `POST /logout`
- **التفويض:** Bearer Token مطلوب.

### الملف الشخصي (Me)
- **المسار:** `GET /user` (أو `GET /me` حسب إعداد ملف api.php)
- **التفويض:** Bearer Token مطلوب.

---

## 2. المنتجات (Products)

### جلب قائمة المنتجات (List Products)
- **المسار:** `GET /products`
- يمكن إضافة معايير بحث وتصفية عبر الـ Params:
  - `category_id`: رقم (مثال `1`)
  - `search`: نص (مثال `phone`)

### تفاصيل المنتج (Product Details)
- **المسار:** `GET /products/{id}`

---

## 3. سلة المشتريات (Cart) - تتطلب Bearer Token

### عرض السلة
- **المسار:** `GET /cart`

### إضافة منتج للسلة
- **المسار:** `POST /cart`
- **Body (raw JSON):**
  ```json
  {
    "product_id": 1,
    "quantity": 2
  }
  ```

### تعديل كمية منتج في السلة
- **المسار:** `PUT /cart/{id}` (حيث `id` هو مُعرِّف عنصر السلة `cart_item_id`)
- **Body (raw JSON):**
  ```json
  {
    "quantity": 3
  }
  ```

### حذف منتج من السلة
- **المسار:** `DELETE /cart/{id}`

---

## 4. المفضلة (Favorites) - تتطلب Bearer Token

### عرض المفضلة
- **المسار:** `GET /favorites`

### إضافة للمفضلة
- **المسار:** `POST /favorites`
- **Body (raw JSON):**
  ```json
  {
    "product_id": 1
  }
  ```

### إزالة من المفضلة
- **المسار:** `DELETE /favorites/{productId}` (حيث رقم المتغير هو المُعرف للمنتج `product_id`)

---

## 5. الطلبات (Orders) - تتطلب Bearer Token

### عرض قائمة طلباتي
- **المسار:** `GET /orders`

### إنشاء طلب جديد (Store Order)
- **المسار:** `POST /orders`
- **Body:** هنا **لا يجب** استخدام JSON لأننا سنرفع صورة. افتح تبويب الـ **Body** واختر **form-data**:
  - `shipping_address` (Text): `Damascus, Mezzeh`
  - `shipping_phone` (Text): `+963912345678`
  - `notes` (Text): `رجاء الاتصال قبل التوصيل` (اختياري)
  - `payment_receipt_image` (File): قم باختيار ملف صورة للإشعار المالي (Sham Cash).

> **تنويه:** يجب أن لا تكون سلة المشتريات فارغة عند تنفيذ هذا الطلب. بعد إتمام إنشاء الطلب سيتم إفراغ السلة تلقائياً.

### تفاصيل طلب معين
- **المسار:** `GET /orders/{id}`

---

## 6. إعدادات النظام (Settings)

### جلب الإعدادات (مثل رقم التواصل والـ QR Code)
- **المسار:** `GET /settings`
