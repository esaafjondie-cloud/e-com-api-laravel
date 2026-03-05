# تقرير تحليل المشروع - نظام التجارة الإلكترونية

**تاريخ التقرير:** 05 مارس 2026  
**المعد:** مهندس البرمجيات (AI Agent)  
**اللغة:** عربي حصراً

---

## 1. نظرة عامة على المشروع

هذا المشروع عبارة عن **نظام خلفي (Backend) لمتجر إلكتروني أحادي البائع** مبني على:

| التقنية         | الإصدار | الغرض                       |
| --------------- | ------- | --------------------------- |
| Laravel         | 12      | إطار العمل الرئيسي          |
| FilamentPHP     | 3.3     | لوحات تحكم Admin و Vendor   |
| Laravel Sanctum | —       | المصادقة عبر API            |
| MySQL           | —       | قاعدة البيانات              |
| Scribe          | —       | توثيق API (OpenAPI/Swagger) |
| PHP             | 8.2+    | لغة البرمجة                 |

---

## 2. المميزات الجوهرية للمشروع

### 2.1 نظام الأدوار (Role-Based Access Control)

يتضمن المشروع ثلاثة أدوار رئيسية:

- **Admin (مدير النظام):** يملك صلاحية على جميع بيانات التطبيق ويدير إعدادات الدفع والمستخدمين.
- **Vendor (البائع):** يدير الفئات والمنتجات ويعالج الطلبات يدوياً.
- **User (المستخدم العادي):** يتفاعل مع النظام عبر الـ API (تطبيق الجوال).

### 2.2 نظام الدفع اليدوي عبر "شام كاش"

الميزة الحاسمة في المشروع هي **آلية التحقق اليدوي من الدفع**:

1. يرفع المدير صورة QR Code الخاصة بـ "شام كاش" في الإعدادات.
2. يجلب التطبيق (API) هذا الكود عند الدفع.
3. يتحول المستخدم للدفع خارجياً ثم يرفع صورة إيصال الدفع عند تقديم الطلب.
4. يراجع البائع صورة الإيصال يدوياً في لوحة التحكم ويحدّث حالة الطلب.

---

## 3. هيكل قاعدة البيانات

### جداول قاعدة البيانات المنفذة (13 جدول):

| الجدول               | الوصف                                                       |
| -------------------- | ----------------------------------------------------------- |
| `users`              | المستخدمون بأدوارهم (admin, vendor, user) مع FCM token      |
| `verification_codes` | رموز OTP للتحقق من البريد الإلكتروني                        |
| `system_settings`    | إعدادات النظام (مفتاح-قيمة) مثل QR Code                     |
| `categories`         | فئات المنتجات مع صورة وحالة تفعيل                           |
| `products`           | المنتجات مع السعر والمخزون والصورة الرئيسية والرابط الخارجي |
| `product_images`     | معرض صور المنتج (علاقة one-to-many)                         |
| `orders`             | الطلبات مع حالة Enum ومسار صورة الإيصال                     |
| `order_items`        | تفاصيل عناصر كل طلب (مع قفل السعر وقت الشراء)               |
| `carts`              | عربات التسوق للمستخدمين                                     |
| `cart_items`         | عناصر عربة التسوق                                           |
| `favorites`          | المفضلة (علاقة many-to-many بين User و Product)             |
| `cache`              | جدول تخزين الـ Cache (Laravel)                              |
| `jobs`               | جدول قوائم الانتظار                                         |

### حالات الطلب (Order State Machine):

```
unpaid → paid → shipped → delivered
                       ↘ shipping_issue
```

---

## 4. هيكل الكود البرمجي

### 4.1 النماذج (Models) - 10 نماذج

```
app/Models/
├── User.php          - يطبق FilamentUser + HasApiTokens + canAccessPanel()
├── Order.php         - علاقات user() و items()
├── OrderItem.php     - تفاصيل الطلب
├── Cart.php          - عربة التسوق
├── CartItem.php      - عناصر العربة
├── Category.php      - الفئات
├── Product.php       - المنتجات مع العلاقات
├── ProductImage.php  - صور المنتجات
├── Favorite.php      - المفضلة
└── SystemSetting.php - إعدادات النظام
```

### 4.2 وحدات تحكم API (API Controllers) - 6 وحدات

```
app/Http/Controllers/Api/
├── AuthController.php      - التسجيل + OTP + تسجيل الدخول + تسجيل الخروج
├── OrderController.php     - إنشاء الطلبات + عرضها (مع Transaction DB)
├── CartController.php      - إدارة عربة التسوق (CRUD)
├── FavoriteController.php  - إدارة المفضلة
├── ProductController.php   - عرض المنتجات مع الفلترة
└── SettingsController.php  - جلب QR Code والإعدادات
```

### 4.3 لوحة Admin Panel (Filament)

```
app/Filament/Resources/
├── UserResource.php          - إدارة المستخدمين والأدوار
└── SystemSettingResource.php - رفع QR Code والإعدادات
```

### 4.4 لوحة Vendor Panel (Filament)

```
app/Filament/Vendor/Resources/
├── CategoryResource.php - إدارة الفئات
├── ProductResource.php  - إدارة المنتجات مع معرض الصور
└── OrderResource.php    - معالجة الطلبات + عرض إيصال الدفع
```

### 4.5 API Resources (JSON Transformation) - 3 موارد

```
app/Http/Resources/
├── UserResource.php    - بيانات المستخدم في API
├── OrderResource.php   - بيانات الطلب مع العناصر
└── ProductResource.php - بيانات المنتج مع الصور
```

---

## 5. نقاط نهاية الـ API (API Endpoints)

### المسارات العامة (بدون مصادقة):

| الطريقة | المسار               | الوصف                          |
| ------- | -------------------- | ------------------------------ |
| POST    | `/api/register`      | تسجيل مستخدم جديد + OTP        |
| POST    | `/api/verify-email`  | التحقق من OTP                  |
| POST    | `/api/login`         | تسجيل الدخول + Bearer Token    |
| GET     | `/api/settings`      | جلب QR Code والإعدادات         |
| GET     | `/api/products`      | قائمة المنتجات مع فلترة وترقيم |
| GET     | `/api/products/{id}` | تفاصيل منتج واحد               |

### المسارات المحمية (تتطلب Bearer Token):

| الطريقة | المسار                       | الوصف                                |
| ------- | ---------------------------- | ------------------------------------ |
| GET     | `/api/me`                    | بيانات المستخدم الحالي               |
| POST    | `/api/logout`                | تسجيل الخروج                         |
| GET     | `/api/orders`                | قائمة طلبات المستخدم                 |
| POST    | `/api/orders`                | إنشاء طلب جديد (multipart/form-data) |
| GET     | `/api/orders/{id}`           | تفاصيل طلب                           |
| GET     | `/api/cart`                  | عرض العربة                           |
| POST    | `/api/cart`                  | إضافة عنصر للعربة                    |
| PUT     | `/api/cart/{id}`             | تعديل كمية العنصر                    |
| DELETE  | `/api/cart/{id}`             | حذف عنصر من العربة                   |
| GET     | `/api/favorites`             | قائمة المفضلة                        |
| POST    | `/api/favorites`             | إضافة للمفضلة                        |
| DELETE  | `/api/favorites/{productId}` | حذف من المفضلة                       |

---

## 6. منطق تنفيذ الطلب (Order Transaction Logic)

المنفذ في `OrderController@store` باستخدام `DB::transaction()`:

1. **التحقق:** يتحقق من خلو العربة.
2. **الحساب:** يحسب الإجمالي من عناصر العربة × السعر.
3. **الرفع:** يرفع صورة الإيصال إلى `public/receipts`.
4. **الإنشاء:** يُنشئ سجل الطلب بحالة `unpaid`.
5. **النقل:** ينقل عناصر العربة إلى `order_items` (مع قفل السعر الحالي).
6. **التنظيف:** يُفرغ عربة التسوق بعد الطلب.
7. **الاستجابة:** يُعيد `OrderResource` بكود HTTP 201.

---

## 7. ملاحظات تقنية وأمنية

### نقاط القوة:

- ✅ استخدام `DB::transaction()` في عملية الطلب لضمان تكامل البيانات.
- ✅ تطبيق `canAccessPanel()` في نموذج User للتحكم في وصول الـ Filament Panels.
- ✅ استخدام `FormRequest` لكل التحققات.
- ✅ تقييد حذف الطلبات من لوحة البائع.
- ✅ عزل البائع مالياً (لا يرى الإيرادات الكلية).
- ✅ قفل سعر المنتج وقت الشراء في `order_items.price`.

### مشاكل مكتشفة في الكود:

- ⚠️ **خطأ في `User.php`:** الدالة `casts()` تحتوي على `return` مزدوج في نفس الدالة (السطران 36-43). يجب حذف الكتلة المكررة.
- ⚠️ **دالة `show` في `OrderController`:** لا تتحقق من أن الطلب يعود للمستخدم الحالي (ثغرة IDOR محتملة للـ API). يجب إضافة `where('user_id', auth()->id())`.

---

## 8. ملخص حالة المشروع

| المكون                | الحالة                                                 |
| --------------------- | ------------------------------------------------------ |
| المايغريشنز (13 ملف)  | ✅ منجز                                                |
| النماذج (10 ملفات)    | ✅ منجز مع ملاحظة خطأ طفيف                             |
| API Controllers (6)   | ✅ منجز                                                |
| Filament Admin Panel  | ✅ منجز                                                |
| Filament Vendor Panel | ✅ منجز                                                |
| API Routes            | ✅ منجز                                                |
| API Resources         | ✅ منجز                                                |
| توثيق Scribe/Swagger  | ✅ مُعد ويمكن التوليد بـ `php artisan scribe:generate` |
| نظام Seeders          | ⚠️ يحتاج مراجعة                                        |

---

_تم إعداد هذا التقرير بواسطة نظام AI Agent بتاريخ 05 مارس 2026_
