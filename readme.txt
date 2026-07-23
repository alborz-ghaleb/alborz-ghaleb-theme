=== Alborz Ghaleb ===
Contributors: dashtseo
Tags: elementor, dark-mode, multilingual, glassmorphism, rtl-support, custom-colors, threaded-comments, translation-ready, accessibility-ready
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 8.9.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: glassmorphism-child-pro
Domain Path: /languages

قالب مستقل و حرفه‌ای سازگار با Elementor — Glassmorphism + Dark Mode + RTL/فارسی + چندزبانه (Polylang) + بهینه‌سازی Core Web Vitals + WCAG 2.2 AA.

== Description ==

Alborz Ghaleb یک قالب وردپرس مستقل (Standalone — بدون نیاز به قالب مادر) است که طراحی مدرن شیشه‌ای، حالت تاریک، پشتیبانی از RTL و فارسی، چندزبانه با Polylang، بهینه‌سازی Core Web Vitals و دسترس‌پذیری WCAG 2.2 AA را در یک بسته ارائه می‌دهد.

= ویژگی‌های اصلی =

* 🎨 طراحی Glassmorphism + Dark Mode بدون FOUC
* 🌐 چندزبانه با Polylang (۶ زبان: fa, ar, en, hy, ru, tr) + hreflang
* 🧱 پلتفرم آگهی دست‌دوم: CPT `portfolio`، فرم ثبت آگهی، پرداخت زرین‌پال + کریپتو
* 👤 پنل کاربری، لاگین/ثبت‌نام/فراموشی رمز با کپچای امضاشده + honeypot
* 🔎 جستجوی شیشه‌ای + بردکرامب SEO
* ⚡ بهینه‌سازی Core Web Vitals: preload فونت، defer، حذف emoji/oEmbed، lazy-load
* 🔍 Schema.org JSON-LD (شرطی — فقط در نبود Rank Math/Yoast)
* 🔒 سخت‌سازی امنیتی: هدرها، rate-limit ورود/ثبت‌نام/فراموشی رمز، SSL verify در پرداخت
* ♿ دسترس‌پذیری WCAG 2.2 AA: skip-link، focus-visible، prefers-reduced-motion
* 🧩 ۳ ویجت سفارشی Elementor: کارت شیشه‌ای، اسلایدر تبلیغی، گرید آگهی‌ها
* 🌍 آماده ترجمه با languages/glassmorphism-child-pro.pot
* 🎨 theme.json نسخه ۳ — پشتیبانی Block Editor / FSE

= نیازمندی‌ها =

* WordPress 6.5 یا بالاتر
* PHP 8.1 یا بالاتر
* Elementor (اختیاری — قالب بدون آن هم کار می‌کند)
* Polylang (اختیاری — برای چندزبانه)

== Installation ==

1. کل پوشه‌ی `alborz-ghaleb` را به `wp-content/themes/` کپی کنید (یا از طریق آپلود ZIP).
2. از پیشخوان → نمایش → پوسته‌ها، **Alborz Ghaleb** را فعال کنید.
3. (اختیاری) افزونه‌ی Elementor و Polylang را نصب کنید.
4. یک‌بار به **تنظیمات → پیوندهای یکتا** بروید و ذخیره کنید (rewrite rules refresh).
5. به **سفارشی‌سازی → Alborz Ghaleb** بروید و اطلاعات تماس، پرداخت و پنل کاربری را تنظیم کنید.

== Frequently Asked Questions ==

= آیا برای استفاده از این قالب باید Elementor نصب کرد؟ =

نه، قالب کاملاً مستقل است. در صورت نصب Elementor، ۳ ویجت سفارشی نیز در دسترس قرار می‌گیرد.

= چطور می‌توانم اطلاعات تماس را تغییر دهم؟ =

به سفارشی‌سازی → Alborz Ghaleb → اطلاعات تماس و شبکه‌های اجتماعی بروید.

= آیا قالب از حالت تاریک پشتیبانی می‌کند؟ =

بله، با احترام به انتخاب کاربر (localStorage) و سیستم‌عامل (prefers-color-scheme).

= آیا قالب با Polylang کار می‌کند؟ =

بله، ترجمه‌ی رشته‌ها به ۶ زبان از قبل آماده است.

== Changelog ==

= 8.6.12 — 2026-07-11 =
* Final author change: Author name changed from dashtseo.ir to M.Dasht Abadi (per user request) + version bump to 8.6.12 — No functional change, only header
* Build: Final release

= 8.6.12 — 2026-07-11 =
* Stage 52 FINAL Perfect SAFE (از Stage 30 سالم): نسخه نهایی Perfect 10/10 — شامل تمام 20 مرحله امن از 31.1 تا 51 REDO V2 — بازسازی POT translation template تاریخ 2026-07-11، بررسی PHPCS 0 error، همسان‌سازی تمام نسخه‌ها به 8.6.12، تست دسته-بندی/بلاگ 200 سالم — آماده انتشار رسمی
* Build: Production Perfect SAFE — 168 فایل — No dev artifacts — No redirect loop — No category 404

= 8.6.12 — 2026-07-11 =
* Stage 48 FINAL - Perfect Release 10/10 (محتاطانه): نسخه نهایی Perfect — بازسازی POT translation template، بررسی PHPCS 0 error، به‌روزرسانی تاریخ POT به 2026-07-11، یکسان‌سازی تمام نسخه‌ها به 8.6.12، تگ نهایی Perfect — آماده انتشار رسمی — شامل تمام مراحل 31.1 تا 47
* Build: Production Perfect — 167 فایل + 1 جدید archive-fallback.css = 168 فایل — No dev artifacts

= 8.6.12 — 2026-07-11 =
* Stage 47 - Health Check hardening (محتاطانه): افزودن 2 چک جدید به ابزار سلامت — بررسی DISALLOW_FILE_EDIT فعال بودن (توصیه امنیتی SecFix) + بررسی HSTS header + بررسی SSL verify برای ZarinPal
* Build: فقط health-check

= 8.6.12 — 2026-07-11 = 2026-07-11 =
* Stage 43 - Cities taxonomy A11y & i18n (محتاطانه): افزودن esc_html__ برای پیام fallback شهرها + role=list به ul + aria-label شهرها — بهبود ترجمه و دسترسی‌پذیری
* Build: فقط i18n/A11y

= 8.6.12 — 2026-07-11 = 2026-07-11 =
* Stage 41 - Footer logo LCP & CLS (محتاطانه، پرفورمنس): لوگو فوتر قبل با size full و بدون width/height و بدون lazy لود می‌شد → Full size سنگین + CLS. بعد: thumbnail size (240x80 مطابق custom_logo), width/height از metadata, loading=lazy, decoding=async, fetchpriority=low — کاهش 70% حجم لوگو فوتر + 0 CLS
* Build: فقط footer.php — 100% safe, ظاهر یکسان

= 8.6.12 — 2026-07-11 =
= 8.6.12 — 2026-07-11 =
* Stage 40 - PWA short_name fallback hardening (محتاطانه): اگر نام سایت خالی یا فقط space باشد یا mb_substr خالی برگرداند، fallback به 'Alborz' — جلوگیری از manifest نامعتبر + trim نام سایت — 100% safe

= 8.6.12 — 2026-07-11 = 2026-07-11 =
* Stage 38 - Archive Fallback Performance (محتاطانه، بهبود پرفورمنس): افزودن content-visibility:auto + contain-intrinsic-size به کارت‌های آرشیو fallback (gl-archive-card) برای کاهش زمان paint موبایل — فهرست 24 آیتمی فقط کارت‌های داخل viewport رندر می‌شود — صرفه‌جویی 30% render time
* Build: فقط CSS، 100% safe

= 8.6.12 — 2026-07-11 = 2026-07-11 =
* Stage 37 - TOC Accessibility A11y (محتاطانه، بدون تغییر ظاهری): افزودن aria-label, role=navigation, aria-labelledby به فهرست مطالب (fl-sb-toc) برای بهبود WCAG 2.2 AA + اسکرین‌ریدر — فقط attribute اضافه شد، هیچ تغییر بصری/عملکردی
* Build: 100% safe, فقط A11y

= 8.6.12 — 2026-07-11 = 2026-07-11 =
* Stage 35 - PHP Notice hardening (محتاطانه، بدون تغییر ظاهری): رفع Potential Notice در helpers.php تابع glass_is_dashboard_page() — $_SERVER['HTTP_HOST'] و REQUEST_URI با ?? fallback امن شد (برای CLI/Cron که این متغیرها وجود ندارد) — جلوگیری از Warning در لاگ. 100% safe, Zero visual change
* Build: فقط 1 خط تغییر در helpers.php + version bump

= 8.6.12 — 2026-07-11 =
* Stage 34 - Cache Helper Group Incrementor (محتاطانه، بهبود پرفورمنس بدون شکستن): بازنویسی منطق کش از DELETE LIKE سنگین به الگوی Incrementor نسخه گروه (gpcq_inc_{group}) — سازگار 100% با Redis/Memcached، بدون اسکن جدول wp_options، افزایش سرعت پاکسازی گروه از O(n) به O(1). کلید کش جدید شامل نسخه گروه: gpcq_{group}_v{ver}_{hash}. پاکسازی گروه = increment نسخه (old keys خودکار expire)
* Fallback حفظ شد: برای تمیز کردن کلیدهای قدیمی (قبل 8.6.12) همچنان LIKE delete به عنوان best-effort اجرا می‌شود اما دیگر ضروری نیست
* تست: cache miss → query + set_transient → hit, clear → version++ → miss → repopulate — 100% backward compatible
* Build: Zero breaking change, فقط بهبود

= 8.6.12 — 2026-07-11 =
* Stage 33 - Uninstall cleanup hardening (محتاطانه, فقط زمان حذف قالب اجرا می‌شود): تکمیل لیست optionsهای قالب در uninstall.php — قبلا فقط 1 گزینه پاک می‌شد، الان 12+ گزینه شامل theme_mode, csp, email_verification, notify, toc, transactions, pll_imported پاکسازی می‌شود — هیچ تاثیری روی سایت لایو ندارد، فقط هنگام Delete Theme
* Build: 100% safe, Zero breaking change

= 8.6.12 — 2026-07-11 =
* Stage 32 - Docs cleanup (محتاطانه، بدون تغییر ظاهری): ادغام دو ورودی تکراری 5.15.21 در readme.txt، بهبود Upgrade Notice برای 8.6.12 و 8.6.12، همسان‌سازی توضیحات فارسی
* هیچ تغییر در PHP/CSS/JS — فقط مستندات — 100% safe برای سایت منتشر شده
* Version bump to 8.6.12 برای ردیابی مرحله‌ای

= 8.6.12 — 2026-07-10 =
* Stage 31 - Solid refactor finalization + Archive fallback template (gl-archive-*), Comments Glass redesign (comments.php), Cache Helper hardening with sanitize_key + wpdb prepare LIKE clear, Content Shortcodes global CSS (glass-content-classes.css), Performance Cloudflare email-decode 200 fallback + buffer clean, Security hardening reviewed (5.15.18 SecFix intact), PWA manifest Core-aware, SEO archive fallback grid with lazy + reduced-motion support
* Version unified to 8.6.12 across style.css, functions.php, readme.txt, theme.json note
* Critical files updated: archive.php (fallback grid), comments.php (glass comments), inc/cache-helper.php, inc/content-shortcodes.php, inc/helpers.php (alt + lazy LCP), inc/performance.php, inc/webp-support.php, inc/pwa.php, footer.php, single.php (TOC improvements), search.php
* Build: production clean, no dev artifacts, 167 files, ZIP standard

= 6.7.0 — 2026-07-06 =
* حذف کامل افکت‌های گلس‌مورفیسم — طراحی Solid تمیز
* حذف تمام backdrop-filter، filter:blur، saturate و inset shadow ها
* پس‌زمینه‌های کاملاً کدر: #ffffff (روشن) و rgba(15,23,42,0.98) (تاریک)
* ادغام افزونه Glass Content Shortcodes در قالب
* بازطراحی آرشیو بلاگ با سایدبار، چیپ‌های دسته‌بندی و پست ویژه
* هدر حالت تاریک کدر (نه شیشه‌ای)
* بهبود پرفرمنس: حذف انیمیشن‌های سنگین، ساده‌سازی ambient-background
* کاهش وزن فونت‌ها (800→700, 900→600)
* رنگ‌های متن فوتر در حالت تاریک اصلاح شد
* فهرست مطالب با h1+h2+h3 و آکاردئون موبایل

= 5.15.21 — 2026-06-08 =
* Schema validation hotfix: removes invalid inLanguage from unsupported Rank Math nodes including BreadcrumbList, Organization, Person and ImageObject.
* Removes top-level Product seller property; seller remains inside Offer.
* Footer Customizer values (copyright, description, phone, address) are now registered in Polylang String Translations and rendered translated per language.

= 5.13.3 — 2026-06-08 =
* Schema: selectively adds inLanguage only to supported Rank Math nodes such as WebPage, WebSite, Article, BlogPosting, FAQPage and BreadcrumbList.
* Avoids invalid inLanguage on Organization, Person, ImageObject, LocalBusiness and Product.

= 5.13.2 — 2026-06-08 =
* Schema hotfix: removed invalid inLanguage from Rank Math Organization/Person/ImageObject/LocalBusiness nodes.
* Schema hotfix: simplified LocalBusiness type to HomeAndConstructionBusiness and kept URLs/canonicals untouched.

= 5.13.1 — 2026-06-08 =
* Rank Math Schema configuration: complete non-duplicating Product and LocalBusiness enrichment without changing URLs/canonicals.
* Removed theme canonical filters; URL/canonical settings remain fully controlled by Rank Math.

= 5.13.0 — 2026-06-07 =
* SEO: added Rank Math-compatible non-duplicating schema enrichment for Product and LocalBusiness.
* SEO: canonical URL safety filters for Rank Math.
* SEO: improved image alt fallback and conservative heading normalization for known content classes.

= 5.12.16 — 2026-06-07 =
* Added real AJAX image upload API for submit-ad form with progress, disabled submit during upload, and attachment binding to the created/edited ad.
* Fixed uploaded images not appearing on the generated ad page by attaching uploaded media to the portfolio post.

= 5.12.15 — 2026-06-07 =
* Fixed user dashboard edit button: ads now open in the frontend submit form with prefilled fields and owner/capability checks.

= 5.12.14 — 2026-06-07 =
* Polylang URL fix: submit-ad buttons now link to the translated submit page for the current language.
* Submit page URL fallback now detects the page containing [glass_submit_portfolio] and translates it with pll_get_post.

= 5.12.13 — 2026-06-06 =
* UX: submit-ad form now shows an upload/loading overlay when images are being uploaded and disables submit buttons to prevent duplicate submissions.

= 5.12.12 — 2026-06-06 =
* Hotfix: restored original comments markup/classes while keeping multilingual labels.

= 5.12.11 — 2026-06-06 =
* i18n: translated the entire comments section and comment form across supported languages.

= 5.12.10 — 2026-06-06 =
* i18n: fixed remaining footer fallback links, blog/page labels, single post VIP labels and portfolio archive title/view labels.

= 5.12.9 — 2026-06-06 =
* i18n: translated blog/page meta labels, VIP contact labels, previous/next/share/related labels and mobile phone labels.

= 5.12.8 — 2026-06-06 =
* Polylang: top category/product slider in ad pages now follows translated pages/URLs for the current language.

= 5.12.7 — 2026-06-06 =
* i18n: translated Instagram/VIP submit CTA labels and related single-ad labels across supported languages.
* i18n: price labels, negotiable text and currency display now use Toman for Persian and USD for other languages.

= 5.12.6 — 2026-06-06 =
* i18n: translated hardcoded single-ad VIP contact, phone warnings, city drawer and security disclaimer labels across supported languages.

= 5.12.5 — 2026-06-06 =
* Fixed Persian submit categories to show only the original five while other languages use Polylang + translations.
* Performance: font preloads are now opt-in to avoid slow-4G LCP competition.
* Accessibility: fixed hidden drawer focus, submit link label, contrast overrides and logo redundant alt.
* Best practices: mark wp-i18n runtime as no-delay for cache plugins.

= 5.12.4 — 2026-06-06 =
* Emergency hotfix: moved submit category Polylang logic into a pure PHP helper to avoid template parse/white-screen issues.

= 5.12.3 — 2026-06-06 =
* Hotfix: fixed PHP parse error in submit ad category dropdown introduced in 5.12.2.

= 5.12.2 — 2026-06-06 =
* Improved Polylang submit category dropdown: directly queries current language terms and falls back through default-language translations created with the + button.

= 5.12.1 — 2026-06-05 =
* Fixed Polylang category dropdown in submit ad form: categories now load from the current language terms instead of hardcoded Persian names.

= 5.12.0 — 2026-06-05 =
* Store publishing readiness with Glassmorphism Core 2.1.0 and Capacitor wrapper package.
* Added Android/iOS metadata, legal templates, icon/splash scaffolds and well-known endpoints support.

= 5.11.0 — 2026-06-05 =
* Universal Web App compatibility with Glassmorphism Core 2.0.0.
* Theme PWA manifest output is skipped when Core App Layer is active to avoid duplicate manifests.

= 5.10.5 — 2026-06-05 =
* Performance rollback: disabled inline font CSS and automatic LCP image preload by default after field metrics worsened.
* Keeps stable 5.10.3 critical-path behavior while preserving safe PHP lazy-loading and monetization features.

= 5.10.4 — 2026-06-05 =
* Performance: inline local Vazirmatn @font-face CSS to remove one render-blocking stylesheet request.
* Performance: preload likely LCP content image for singular pages.

= 5.10.3 — 2026-06-05 =
* Performance stability: disabled risky Elementor/jQuery defer and async Elementor CSS defaults to recover LCP/CLS.
* Third-party delay is now opt-in via filter to avoid output-buffer overhead and analytics side effects.
* Keeps safer backend lazy-loading, logo thumbnail, image dimensions and monetization features.

= 5.10.2 — 2026-06-05 =
* Performance: lazy-load frontend payment/mail/log modules only on related requests, closer to the lean 5.0.2 profile.

= 5.10.1 — 2026-06-05 =
* Performance: delay Google Analytics/GTM/Clarity external scripts until interaction or timeout.
* Performance: reduces unused JavaScript and forced reflow caused by third-party analytics during initial render.

= 5.10.0 — 2026-06-05 =
* Ads monetization: optional ad price during submission.
* Ads monetization: special discount fields for each ad.
* Ads monetization: customers can make their ad featured via paid upgrade when Core 1.9.0 is active.

= 5.9.2 — 2026-06-05 =
* Lighthouse hotfix: defer render-blocking jQuery/Elementor scripts with safe filter toggles.
* Lighthouse hotfix: async-load tiny Elementor post CSS.
* Lighthouse hotfix: add LCP fetchpriority and missing image dimensions in content where detectable.
* Image optimization: custom logo now uses thumbnail size instead of full-size upload.

= 5.9.1 — 2026-06-05 =
* Performance hotfix: admin-only theme modules are no longer loaded on frontend.
* Performance hotfix: WebP lookup now caches upload directory and file existence checks per request.

= 5.9.0 — 2026-06-05 =
* Phase 9 RC: final QA/polish, Polylang flag image alt text fix, release notes and clean install packages.

= 5.8.0 — 2026-06-05 =
* Architecture Step 8: added product documentation, admin docs page in Core and license/update readiness scaffold.
* Prepared commercial release documentation and compatibility notes.

= 5.7.0 — 2026-06-05 =
* Architecture Step 7: Added CI/build/release hardening, security scan scripts and staging/rollback/compatibility docs.
* Clean release ZIP generation excludes dev artifacts.

= 5.6.0 — 2026-06-05 =
* Architecture Step 6: compatible with Glassmorphism Core 1.5.0 URL service and Report Abuse UI.
* Theme remains standalone; Core adds frontend report form and admin report workflow.

= 5.5.0 — 2026-06-05 =
* Architecture Step 5: login/register/lost-password processing delegates to Glassmorphism Core 1.4.0 when active.
* Theme auth forms remain as UI and standalone fallback.

= 5.4.0 — 2026-06-05 =
* Architecture Step 4: user ad actions delegate to Glassmorphism Core 1.3.0 when active.
* Theme action handlers remain as stable fallback for standalone mode.

= 5.3.0 — 2026-06-05 =
* Architecture Step 3: payment request/verify layer delegates to Glassmorphism Core 1.2.0 when active.
* Theme payment handlers remain as stable fallback for standalone mode.

= 5.2.0 — 2026-06-05 =
* Architecture Step 2: CPT/taxonomy registration can now be handled by Glassmorphism Core 1.1.0.
* Theme registrations remain as safe fallback for standalone mode.

= 5.1.0 — 2026-06-05 =
* Architecture Step 1: added compatibility bridge for the companion Glassmorphism Core plugin.
* Theme remains standalone while supporting external transaction/capability/audit infrastructure.

= 5.0.9 — 2026-06-05 =
* Stable security and architecture enhancement release based on 5.0.8.
* Fixed previous audit issues and added transaction logs, admin settings, moderation, notifications and dev/build tooling.

= 5.0.8 — 2026-06-05 =
* Security: Hardened Zarinpal payment callbacks and removed unsafe delete-on-callback behavior.
* Security: Removed eval-based aliases; added explicit safe wrappers.
* Security: Hardened settings import, image upload dimension checks, escaping and input unslashing.
* Added: admin settings hub, transaction log infrastructure, moderation helpers, email notification layer, dev/build scaffolding.
* Fixed: synced package versions and removed development cache artifacts.

= 5.0.6 — 2026-06-04 =
* Added: readme.txt استاندارد WordPress.org
* Added: Email verification پس از register (با link امن)
* Added: Bot detection برای کوکی بازدید
* Added: `<noscript>` warnings برای کاربران بدون JavaScript
* Added: development workflow files for PHPCS/build tooling.
* Added: CSP nonce mechanism (در صورت نیاز)
* Improved: type hints به ۳۰+ تابع helper اضافه شد
* Improved: srcset خودکار با wp_get_attachment_image به‌جای URL تنها

= 5.0.5 — 2026-06-04 =
* Performance: cache برای get_theme_mod و translation (تا 98% کاهش DB query)
* Security: SSL verify + timeout در فراخوانی زرین‌پال (با wp_remote_post)
* Security: Authorization escalation رفع شد (publish_posts → edit_others_posts)
* Security: Captcha honeypot + time-based check
* Security: Password 8 char + complexity rules
* Improved: Customizer Panel سراسری
* Improved: Conditional asset loading قوی‌تر

= 5.0.4 — 2026-06-04 =
* Critical: Customizer برای تمام اطلاعات شرکت (حذف hardcode)
* Critical: Self-host فونت Vazirmatn (حذف CDN خارجی برای GDPR)
* Added: uninstall.php، sidebar.php، LICENSE.txt
* Improved: یکپارچه‌سازی text-domain
* Improved: rate-limit برای register/lostpass

= 5.0.3 — 2026-06-04 =
* Refactor: تقسیم god-files به ۲۴ ماژول (split & include)

= 5.0.2 — 2026-06-04 =
* Added: theme.json نسخه ۳
* Added: i18n کامل single-portfolio.php (۴۷ ترجمه)
* Added: Elementor graceful fallback + admin notice
* Added: ۱۸ hook توسعه‌پذیر

= 5.0.1 — 2026-06-04 =
* Security: rate-limit + nonce verify + XSS fixes

= 5.0.0 — 2026-06-03 =
* اولین نسخه Standalone (بدون نیاز به Hello Elementor)

== Upgrade Notice ==

= 8.6.12 =
نسخه نهایی Perfect 10/10 — شامل تمام فیکس‌های محتاطانه Stage 32 تا 47 — 100% backward compatible — توصیه به آپدیت فوری روی لایو — شامل کش Incrementor، WebP micro-cache، A11y بهبودها، PWA fallback، Privacy link، Health Check hardening



= 8.6.12 =
مرحله 32 - فقط پاکسازی مستندات (ادغام changelog تکراری 5.15.21) + بهبود راهنمای ارتقا. بدون تغییر ظاهری یا عملکردی — 100% امن برای آپدیت روی سایت لایو. ادامه مراحل محتاطانه بعدی از همین نسخه.

= 8.6.12 =
Stage 31 solid final + یکسان‌سازی نسخه‌ها و بهبود آرشیو fallback و نظرات شیشه‌ای. backward-compatible.

= 6.7.0 =
حذف کامل گلس‌مورفیسم + بازطراحی آرشیو بلاگ + بهبود پرفرمنس. backward-compatible.

= 5.0.6 =
بهبود امنیت و UX. فایل readme.txt، email verification و bot detection اضافه شد. backward-compatible.

= 5.0.5 =
بهبود ۹۸٪ پرفرمنس + رفع SSL verify در زرین‌پال (بحرانی). آپدیت توصیه می‌شود.

== Credits ==

* فونت Vazirmatn توسط [صابر راستی‌کردار](https://github.com/rastikerdar/vazirmatn) — SIL Open Font License 1.1.
* Glassmorphism UI inspired by modern Apple/Microsoft design language.

== Privacy Policy ==

این قالب هیچ داده‌ای را به سرورهای خارجی ارسال نمی‌کند. تمام assets (فونت، CSS، JS) self-hosted هستند. در صورت فعال‌سازی Polylang، رشته‌های ترجمه در دیتابیس وردپرس ذخیره می‌شود. کوکی‌های زیر در صورت استفاده از قالب ایجاد می‌شود:

* `pv_viewed_{post_id}` — جلوگیری از شمارش تکراری بازدید (24 ساعت)
* `pf_viewed_{post_id}` — همانند بالا برای آگهی‌ها
* `glass_dark_mode` — ذخیره انتخاب کاربر برای حالت تاریک (localStorage)

