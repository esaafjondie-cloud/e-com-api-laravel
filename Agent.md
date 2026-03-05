# Agent.md - سياق المشروع والتعديلات

> هذا الملف يُحدَّث تلقائياً من قبل AI Agent عند كل جلسة عمل.

---

## معلومات المشروع

- **اسم المشروع:** e-com-api-laravel
- **النوع:** Single-Vendor E-Commerce Backend
- **المستودع:** `c:\Users\DELL\Desktop\e-com-api-laravel`
- **الإطار:** Laravel 12 + FilamentPHP 3.3 + Sanctum

---

## سجل جلسات الـ Agent

### جلسة 1 — 05 مارس 2026

**الهدف:** تحليل المشروع وإعداد التقرير الأولي بالعربية.

**ما تم تنفيذه:**

1. قراءة ملف `project.md` — وصف المشروع الكامل مع مخطط DBML وخطوات التنفيذ.
2. قراءة ملف `undertanding.md` — المتطلبات الوظيفية بالتفصيل للأطراف الثلاثة (Admin, Vendor, API).
3. استكشاف هيكل المجلدات بالكامل:
    - `app/Models/` — 10 نماذج.
    - `app/Http/Controllers/Api/` — 6 وحدات تحكم.
    - `app/Filament/Resources/` — لوحة Admin (UserResource, SystemSettingResource).
    - `app/Filament/Vendor/Resources/` — لوحة Vendor (Category, Product, Order).
    - `app/Http/Resources/` — 3 API Resources.
    - `database/migrations/` — 13 ملف مايغريشن.
    - `routes/api.php` — 18 endpoint معرف.
4. قراءة الملفات المحورية: `OrderController.php`, `User.php`, `Order.php`.
5. إنشاء `UserReport1.md` بالعربية — تقرير تحليلي شامل.
6. إنشاء هذا الملف `Agent.md`.

**مشاكل مكتشفة:**

- 🐛 `app/Models/User.php` — دالة `casts()` تحتوي على `return` مزدوج (سطر 36 و39). يجب إزالة الكتلة الثانية.
- 🐛 `app/Http/Controllers/Api/OrderController.php@show` — لا يتحقق من أن الطلب يعود للمستخدم الحالي (ثغرة IDOR).

**الملفات التي تم إنشاؤها:**

- `UserReport1.md` — تقرير عربي شامل
- `Agent.md` — هذا الملف

---

## سياق المشروع التقني

### قاعدة البيانات

- MySQL، 13 جدول، مع Migrations مكتملة.
- لم يُشغَّل `php artisan migrate` بعد في هذه الجلسة.

### الـ API

- 18 endpoint معرف في `routes/api.php`.
- المسارات المحمية تستخدم `auth:sanctum` middleware.
- يدعم `multipart/form-data` لرفع إيصالات الدفع.

### Filament Panels

- Admin: `/admin` — مقيد بـ `role === 'admin'`.
- Vendor: `/vendor` — مقيد بـ `role === 'vendor'`.
- يستخدم `canAccessPanel()` في `User` model للتحكم.

### ملفات التكوين الحساسة

- `.env` — إعدادات DB, Mail, App URL.
- لا تُرفع للـ git (مُدرج في `.gitignore`).

---

## قائمة المهام المقترحة للجلسات القادمة

- [ ] إصلاح الـ `return` المزدوج في `User.php::casts()`.
- [ ] إضافة التحقق من ملكية الطلب في `OrderController@show`.
- [ ] مراجعة `DatabaseSeeder` وإضافة بيانات تجريبية كاملة.
- [ ] التحقق من تكوين Scribe settings.
- [ ] تشغيل الاختبارات وإصلاح أي أخطاء.

---

_آخر تحديث: 05 مارس 2026 — بواسطة AI Agent_
