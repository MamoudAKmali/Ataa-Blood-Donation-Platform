<div align="center">

<img src="https://img.shields.io/badge/عطاء-منصة_التبرع_بالدم-dc2626?style=for-the-badge&logo=heart&logoColor=white" alt="Ataa Platform"/>

# 🩸 منصة عطاء | Ataa Blood Donation Platform

**منصة مجتمعية ذكية تربط المتبرعين بالمستشفيات لإنقاذ الأرواح**

*A smart community platform connecting blood donors with hospitals to save lives*

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![XAMPP](https://img.shields.io/badge/Server-XAMPP-FB7A24?style=flat-square&logo=apache&logoColor=white)](https://www.apachefriends.org/)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Author](https://img.shields.io/badge/Author-Mamoud%20AKmali-blue?style=flat-square&logo=github)](https://github.com/MamoudAKmali)
[![Arabic RTL](https://img.shields.io/badge/Language-Arabic_RTL-blue?style=flat-square)](https://ar.wikipedia.org/wiki/)

</div>

---

## 📋 جدول المحتويات | Table of Contents

- [نظرة عامة | Overview](#-نظرة-عامة--overview)
- [المميزات | Features](#-المميزات--features)
- [هيكل المشروع | Project Structure](#-هيكل-المشروع--project-structure)
- [قاعدة البيانات | Database Schema](#-قاعدة-البيانات--database-schema)
- [الأدوار | User Roles](#-الأدوار--user-roles)
- [متطلبات التشغيل | Requirements](#-متطلبات-التشغيل--requirements)
- [التثبيت | Installation](#-التثبيت--installation)
- [إعداد قاعدة البيانات | Database Setup](#-إعداد-قاعدة-البيانات--database-setup)
- [حسابات الاختبار | Default Accounts](#-حسابات-الاختبار--default-accounts)
- [لقطات الشاشة | Screenshots](#-لقطات-الشاشة--screenshots)
- [الرفع على الإنترنت | Deployment](#-الرفع-على-الإنترنت--deployment)
- [المساهمة | Contributing](#-المساهمة--contributing)
- [المؤلف | Author](#-المؤلف--author)

---

## 🌐 نظرة عامة | Overview

**منصة عطاء** هي تطبيق ويب متكامل مبني بـ PHP و MySQL، يهدف إلى تسهيل عملية التبرع بالدم في المجتمع العربي من خلال:

- **ربط ذكي** بين المتبرعين والمستشفيات وبنوك الدم بناءً على الفصيلة والموقع الجغرافي.
- **نظام نداءات طارئة** يعرض الحالات الحرجة في الوقت الفعلي.
- **تقييم طبي** للتحقق من أهلية المتبرع قبل الاستجابة.
- **لوحات تحكم** مخصصة لكل دور: المتبرع، المستشفى، والمدير.

> **Ataa Platform** is a full-stack PHP/MySQL web application designed to streamline blood donation in the Arabic-speaking community, featuring role-based dashboards, real-time urgent requests, geolocation-based donor matching, and a medical screening system.

---

## ✨ المميزات | Features

### 👤 للمتبرع | For Donors
- ✅ تسجيل حساب مع تحديد فصيلة الدم والجنس والموقع
- ✅ استعراض النداءات الطارئة وتفاصيل كل طلب
- ✅ حجز موعد تبرع في أقرب مستشفى
- ✅ متابعة سجل التبرعات ونقاط المكافآت
- ✅ استكمال التقييم الصحي (أسئلة الفرز الطبي)
- ✅ تحديث الملف الشخصي وكلمة المرور

### 🏥 للمستشفيات وبنوك الدم | For Hospitals & Blood Banks
- ✅ لوحة تحكم خاصة لإنشاء طلبات دم عاجلة
- ✅ إدارة مخزون الدم بحسب كل فصيلة
- ✅ متابعة حالة الطلبات (مفتوح / مكتمل / مغلق)
- ✅ الاطلاع على تفاصيل المتبرعين المستجيبين
- ✅ إعدادات المنشأة والتحقق من الحساب

### 🛡️ للمدير | For Admins
- ✅ لوحة تحكم إدارية شاملة مع إحصائيات النظام
- ✅ إدارة المستخدمين (عرض / تعديل / حذف)
- ✅ إدارة المنشآت الطبية والتحقق منها
- ✅ إدارة أسئلة الفرز الطبي (تفعيل / تعطيل / إضافة)
- ✅ مراقبة جميع طلبات الدم في النظام
- ✅ سجل نشاط حي لآخر العمليات

### 🗺️ مميزات عامة | General Features
- ✅ خريطة تفاعلية لعرض المستشفيات والمتبرعين
- ✅ عرض النداءات الطارئة الحالية على الصفحة الرئيسية
- ✅ تصميم عربي بالكامل يدعم RTL
- ✅ واجهة متجاوبة مع جميع أحجام الشاشات
- ✅ نظام نقاط ومكافآت للمتبرعين

---

## 📁 هيكل المشروع | Project Structure

```
Blood_Donations/
│
├── 📄 index.php                   # الصفحة الرئيسية (Hero + النداءات الطارئة)
├── 📄 hospitals.php               # قائمة المستشفيات وبنوك الدم
├── 📄 map.php                     # الخريطة التفاعلية
├── 📄 request.php                 # صفحة تفاصيل طلب الدم
├── 📄 about.php                   # صفحة من نحن
├── 📄 contact.php                 # صفحة التواصل
├── 📄 privacy.php                 # سياسة الخصوصية
├── 📄 terms.php                   # الشروط والأحكام
├── 📄 medical_setup.php           # إعداد التقييم الطبي
├── 📄 ataa_db.sql                 # ملف قاعدة البيانات الكاملة
│
├── 📂 auth/                       # المصادقة والتسجيل
│   ├── login.php                  # تسجيل الدخول
│   ├── register.php               # إنشاء حساب جديد
│   └── logout.php                 # تسجيل الخروج
│
├── 📂 dashboards/                 # لوحات التحكم
│   ├── 📂 admin/                  # لوحة المدير
│   │   ├── index.php              # الإحصائيات العامة
│   │   ├── users.php              # إدارة المستخدمين
│   │   ├── facilities.php         # إدارة المنشآت الطبية
│   │   ├── requests.php           # إدارة طلبات الدم
│   │   └── screening_questions.php # إدارة أسئلة الفرز
│   │
│   ├── 📂 donor/                  # لوحة المتبرع
│   │   ├── index.php              # الرئيسية والطلبات المتاحة
│   │   ├── profile.php            # الملف الشخصي
│   │   ├── history.php            # سجل التبرعات
│   │   ├── urgent.php             # النداءات الطارئة
│   │   └── donate_action.php      # معالجة طلب التبرع
│   │
│   └── 📂 hospital/              # لوحة المستشفى
│       ├── index.php              # لوحة التحكم الرئيسية
│       ├── create_request.php     # إنشاء طلب دم جديد
│       ├── request_details.php    # تفاصيل طلب محدد
│       ├── inventory.php          # إدارة مخزون الدم
│       ├── settings.php           # إعدادات المنشأة
│       └── complete_request.php   # إغلاق / إكمال طلب
│
├── 📂 includes/                   # المكونات المشتركة
│   ├── db_connect.php             # الاتصال بقاعدة البيانات + BASE_URL
│   ├── header.php                 # رأس الصفحة العامة
│   ├── footer.php                 # تذييل الصفحة العامة
│   ├── auth_check.php             # التحقق من الجلسة
│   ├── functions.php              # دوال مساعدة
│   ├── dashboard_header.php       # رأس لوحة المتبرع
│   ├── donor_header.php           # رأس خاص بالمتبرع
│   ├── donor_footer.php           # تذييل خاص بالمتبرع
│   ├── admin_head.php             # رأس لوحة الإدارة
│   ├── admin_sidebar.php          # الشريط الجانبي للإدارة
│   ├── admin_topbar.php           # الشريط العلوي للإدارة
│   ├── admin_foot.php             # تذييل لوحة الإدارة
│   ├── hospital_auth.php          # التحقق من صلاحيات المستشفى
│   └── dashboard_footer.php       # تذييل لوحة التحكم
│
└── 📂 assets/                     # الأصول الثابتة
    ├── 📂 css/                    # ملفات التنسيق
    ├── 📂 js/                     # ملفات JavaScript
    └── 📂 img/                    # الصور والأيقونات
```

---

## 🗄️ قاعدة البيانات | Database Schema

اسم قاعدة البيانات: **`ataa_db`**

```
┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│     users        │     │  blood_requests   │     │    facilities    │
├──────────────────┤     ├──────────────────┤     ├──────────────────┤
│ id (PK)          │──┐  │ id (PK)          │  ┌──│ id (PK)          │
│ name             │  │  │ requester_id(FK) │──┘  │ name             │
│ email (UNIQUE)   │  └──│ facility_id (FK) │─────│ type             │
│ password_hash    │     │ patient_name     │     │ email (UNIQUE)   │
│ phone            │     │ blood_type       │     │ password_hash    │
│ gender           │     │ units_needed     │     │ phone            │
│ role             │     │ units_secured    │     │ address          │
│ blood_type       │     │ urgency_level    │     │ latitude         │
│ latitude         │     │ status           │     │ longitude        │
│ longitude        │     │ created_at       │     │ is_verified      │
│ points           │     └──────────────────┘     │ created_at       │
│ created_at       │                              └──────────────────┘
└──────────────────┘
         │
         │ ┌──────────────────┐     ┌──────────────────────┐
         │ │    donations     │     │  medical_assessments  │
         │ ├──────────────────┤     ├──────────────────────┤
         └─│ donor_id (FK)    │  ┌──│ donor_id (FK)         │
           │ facility_id (FK) │  │  │ weight                │
           │ request_id (FK)  │  │  │ has_chronic_diseases  │
           │ appointment_date │  │  │ last_travel_date      │
           │ status           │  │  │ last_donation_date    │
           │ units_donated    │  │  │ is_eligible           │
           │ points_earned    │  │  │ notes                 │
           │ created_at       │  │  │ created_at            │
           └──────────────────┘  │  └──────────────────────┘
                                 │
         ┌───────────────────────┘
         │  ┌────────────────────┐     ┌──────────────────────┐
         │  │     inventory      │     │  screening_questions  │
         │  ├────────────────────┤     ├──────────────────────┤
         └──│ facility_id (FK)   │     │ id (PK)              │
            │ blood_type         │     │ question_text         │
            │ units_available    │     │ rejection_reason      │
            │ last_updated       │     │ is_active             │
            └────────────────────┘     │ created_at            │
                                       └──────────────────────┘
```

### الجداول | Tables Summary

| الجدول | الوصف | الحقول الرئيسية |
|--------|-------|-----------------|
| `users` | المتبرعون والمرضى والمدراء | role, blood_type, points, latitude/longitude |
| `facilities` | المستشفيات وبنوك الدم | type, is_verified, latitude/longitude |
| `blood_requests` | طلبات الدم الطارئة | blood_type, urgency_level, units_needed/secured |
| `donations` | سجل التبرعات | appointment_date, status, points_earned |
| `inventory` | مخزون الدم في كل منشأة | blood_type, units_available |
| `medical_assessments` | التقييمات الطبية للمتبرعين | weight, is_eligible, last_donation_date |
| `screening_questions` | أسئلة الفرز الطبي | question_text, rejection_reason, is_active |

---

## 👥 الأدوار | User Roles

| الدور | الوصف | الصلاحيات |
|-------|-------|-----------|
| `donor` | المتبرع بالدم | استعراض الطلبات، حجز تبرع، متابعة السجل |
| `admin` | مدير النظام | إدارة كاملة للمنصة والمستخدمين |
| `facility` | مستشفى / بنك دم | إنشاء طلبات، إدارة مخزون الدم |

---

## 🛠️ متطلبات التشغيل | Requirements

| المكون | الإصدار المطلوب |
|--------|----------------|
| **PHP** | 7.4 أو أحدث |
| **MySQL / MariaDB** | 5.7+ / 10.4+ |
| **Apache** | 2.4+ |
| **XAMPP** (موصى به) | أي إصدار حديث |
| **متصفح** | Chrome / Firefox / Edge (حديث) |

---

## ⚙️ التثبيت | Installation

### 1. استنساخ المستودع | Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/Blood_Donations.git
```

### 2. نقل الملفات | Move Files to Server

**XAMPP على Windows:**
```
C:\xampp\htdocs\Blood_Donations\
```

**XAMPP على Linux/Mac:**
```
/opt/lampp/htdocs/Blood_Donations/
```

### 3. تشغيل الخادم | Start the Server

افتح **XAMPP Control Panel** وشغّل:
- ✅ **Apache**
- ✅ **MySQL**

---

## 🗄️ إعداد قاعدة البيانات | Database Setup

### الطريقة 1: عبر phpMyAdmin (موصى به)

1. افتح المتصفح وانتقل إلى: `http://localhost/phpmyadmin`
2. اضغط على **"New"** أو **"قاعدة بيانات جديدة"**
3. أدخل اسم القاعدة: **`ataa_db`**
4. اختر **`utf8mb4_general_ci`** كـ Collation
5. اضغط **"Create"**
6. انقر على **"Import"** من القائمة العلوية
7. اختر ملف **`ataa_db.sql`** من مجلد المشروع
8. اضغط **"Go"** ✅

### الطريقة 2: عبر سطر الأوامر

```bash
mysql -u root -p
CREATE DATABASE ataa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ataa_db;
SOURCE /path/to/Blood_Donations/ataa_db.sql;
```

### إعداد الاتصال | Connection Config

افتح `includes/db_connect.php` وتأكد من الإعدادات:

```php
// Local (XAMPP)
$host     = 'localhost';
$dbname   = 'ataa_db';
$username = 'root';
$password = '';        // كلمة مرور XAMPP الافتراضية فارغة
```

---

## 🚀 تشغيل التطبيق | Run the App

بعد إكمال الإعداد، افتح المتصفح وانتقل إلى:

```
http://localhost/Blood_Donations/
```

---

## 🔑 حسابات الاختبار | Default Accounts

> ⚠️ **تحذير:** قم بتغيير هذه الكلمات السرية فوراً في بيئة الإنتاج!

| الدور | البريد الإلكتروني | كلمة المرور |
|-------|-------------------|------------|
| 🛡️ **Admin** | `admin@ataa.com` | `admin123` |
| 👤 **Donor** | `salman@gmail.com` | (مشفّرة - أنشئ حساباً جديداً) |

> لإنشاء حساب جديد، اذهب إلى: `http://localhost/Blood_Donations/auth/register.php`

---

## 🩸 فصائل الدم المدعومة | Supported Blood Types

| فصيلة الدم | الرمز |
|------------|-------|
| A موجب | `A+` |
| A سالب | `A-` |
| B موجب | `B+` |
| B سالب | `B-` |
| AB موجب | `AB+` |
| AB سالب | `AB-` |
| O موجب | `O+` |
| O سالب | `O-` |

---

## 📸 لقطات الشاشة | Screenshots

> سيتم إضافة لقطات الشاشة قريباً.

| الصفحة | الوصف |
|--------|-------|
| الصفحة الرئيسية | Hero Section + النداءات الطارئة + الإحصائيات |
| لوحة المتبرع | طلبات الدم المتاحة + سجل التبرعات |
| لوحة المستشفى | إنشاء طلبات + إدارة المخزون |
| لوحة المدير | إحصائيات كاملة + إدارة النظام |
| الخريطة التفاعلية | عرض المستشفيات والمتبرعين على الخريطة |

---

## 🌍 الرفع على الإنترنت | Deployment (InfinityFree / Shared Hosting)

المشروع مُعدّ مسبقاً للنشر على **InfinityFree**. فقط قم بتعديل `includes/db_connect.php`:

```php
// InfinityFree Production
$host     = 'sql106.infinityfree.com';
$dbname   = 'if0_41762104_ataa_db';
$username = 'if0_41762104';
$password = 'YOUR_PASSWORD';
```

**خطوات الرفع:**
1. ارفع جميع ملفات المشروع عبر **FTP** (باستخدام FileZilla)
2. أنشئ قاعدة البيانات من لوحة تحكم الاستضافة
3. استورد ملف `ataa_db.sql`
4. عدّل إعدادات الاتصال في `db_connect.php`

---

## 🔒 الأمان | Security Notes

- ✅ كلمات المرور مشفّرة باستخدام `password_hash()` مع `PASSWORD_DEFAULT` (bcrypt)
- ✅ جميع استعلامات قاعدة البيانات تستخدم **PDO Prepared Statements** لمنع SQL Injection
- ✅ جميع مدخلات المستخدم محمية بـ `htmlspecialchars()` لمنع XSS
- ✅ التحقق من الجلسة والأدوار في كل صفحة محمية
- ⚠️ يُنصح بإضافة HTTPS في بيئة الإنتاج
- ⚠️ يُنصح بإزالة بيانات الاختبار قبل النشر الرسمي

---

## 🤝 المساهمة | Contributing

نرحب بمساهماتك! اتبع الخطوات التالية:

1. **Fork** هذا المستودع
2. أنشئ فرعاً جديداً: `git checkout -b feature/اسم-الميزة`
3. أضف تعديلاتك وارفعها: `git commit -m "feat: إضافة ميزة كذا"`
4. ادفع إلى الفرع: `git push origin feature/اسم-الميزة`
5. افتح **Pull Request** ✅

---

## 📄 الرخصة | License

هذا المشروع مرخّص تحت رخصة **MIT** — راجع ملف [LICENSE](LICENSE) لمزيد من التفاصيل.

---

## 👤 المؤلف | Author

- **Mamoud AKmali**
- **GitHub:** [@MamoudAKmali](https://github.com/MamoudAKmali)
- **Email:** MamoudAKmali@users.noreply.github.com

---

<div align="center">

صُنع بـ ❤️ بواسطة **Mamoud AKmali** لإنقاذ الأرواح | Made with ❤️ by **Mamoud AKmali** to save lives

**منصة عطاء** — *قطرة من دمك.. تنبض بها حياة*

</div>
