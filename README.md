# Alborz Ghaleb (Standalone)

قالب **مستقل** و حرفه‌ای (Production-grade) سازگار با **Elementor** — بدون هیچ نیاز به قالب مادر.
نسخه‌ی Standalone حاصل از بازنویسی `Alborz Ghaleb`.

**نسخه:** 8.9.2 · **والد:** ندارد (Standalone) · **PHP:** 8.1+ · **WordPress:** 6.5+
**Text Domain:** `glassmorphism-child-pro` · **نویسنده:** dashtseo.ir

> ℹ️ **Text Domain** عمداً روی `glassmorphism-child-pro` نگه داشته شده تا فایل‌های ترجمه‌ی
> موجود (`languages/*.mo/.po`) بدون نیاز به بازترجمه همچنان کار کنند.

---

## ✨ ویژگی‌ها

- 🎨 طراحی **Glassmorphism** + **Dark Mode** بدون FOUC
- 🌐 چندزبانه (۶ زبان) با **Polylang** + `hreflang`
- 🧱 پلتفرم آگهی دست‌دوم: CPT `portfolio`، فرم ثبت آگهی، پرداخت زرین‌پال + کریپتو
- 👤 پنل کاربری، لاگین/ثبت‌نام/فراموشی رمز با کپچای امضاشده
- 🔎 جستجوی شیشه‌ای + بردکرامب SEO
- ⚡ بهینه‌سازی Core Web Vitals: preload فونت، defer، حذف emoji/oEmbed، lazy-load
- 🔍 **Schema.org JSON-LD** (Organization, WebSite, Breadcrumb, Article/Product) — **فقط در نبود Rank Math/Yoast** تا تکراری نشود
- 🔒 سخت‌سازی امنیتی: هدرها، محدودسازی ورود، غیرفعال‌سازی ویرایش فایل، xmlrpc
- ♿ دسترس‌پذیری WCAG 2.2 AA: skip-link، focus-visible، `prefers-reduced-motion`
- 🧩 ۲ ویجت سفارشی Elementor: **کارت شیشه‌ای** و **گرید آگهی‌ها**
- 🌍 آماده‌ی ترجمه با `languages/glassmorphism-child-pro.pot`

---

## 📦 نصب

1. کل پوشه‌ی `alborz-ghaleb` را به `wp-content/themes/` کپی کنید.
2. نیازی به نصب هیچ قالب مادری **نیست** — این قالب کاملاً مستقل است.
3. از پیشخوان → نمایش → پوسته‌ها، **Alborz Ghaleb** را فعال کنید.
4. (اختیاری) افزونه‌ی **Elementor** را برای ویرایش بصری صفحات نصب کنید.
4. یک‌بار به **تنظیمات → پیوندهای یکتا** بروید و ذخیره کنید (به‌روزرسانی rewrite rules).

---

## 🗂️ ساختار

```
alborz-ghaleb/
├── style.css                 # هدر + متغیرهای CSS + reset (ظاهر بدون تغییر)
├── rtl.css                   # اصلاحات RTL
├── functions.php             # bootstrapper
├── inc/                      # ماژول‌های زیرساخت + widgets + legacy
├── template-parts/           # اجزای قالب (skip-link، content-none)
├── assets/{css,js,fonts}/    # دارایی‌ها و فونت‌های self-hosted
└── languages/                # .pot/.po/.mo
```

جزئیات تغییرات در فایل `CHANGELOG.md`.

---

## 🛠️ برای توسعه‌دهندگان (Hookها)

| فیلتر | کاربرد |
|------|--------|
| `glass_pro/font_url` | تغییر آدرس CSS فونت |
| `glass_pro/enable_persian_numbers` | فعال/غیرفعال تبدیل اعداد فارسی |
| `glass_pro/schema/organization` | بازنویسی Schema سازمان |
| `glass_pro/login_max_attempts` | سقف تلاش‌های ورود (پیش‌فرض ۸) |

---

## 🔁 مهاجرت از نسخه‌ی قبل

به فایل `CHANGELOG.md` مراجعه کنید. خلاصه: همه‌ی URLها، CPTها، slugها، shortcodeها و ظاهر **حفظ شده‌اند**؛ فقط زیرساخت تمیز و بهینه شده است.
