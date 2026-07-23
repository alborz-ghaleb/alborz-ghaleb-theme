<?php
/**
 * Search Functions — Fixed
 *
 * search.php و searchform.php به این helperها نیاز دارند؛ نبودن آن‌ها باعث Fatal Error می‌شد.
 *
 * @package Alborz_Ghaleb
 */
if (!defined('ABSPATH')) exit;

/* [REMOVED v3.1.3] تابع fl_search_enqueue() حذف شد چون:
   - hook اش از قبل کامنت شده بود (dead code)
   - enqueue search.css در functions.php به‌صورت مرکزی انجام می‌شود
   با handle 'glass-search'
*/

if (!function_exists('glass_search_current_lang')) {
    function glass_search_current_lang() {
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language('slug');
            if (!empty($lang)) return sanitize_key($lang);
        }
        return strtolower(substr(get_locale(), 0, 2)) ?: 'fa';
    }
}

if (!function_exists('glass_search_dir')) {
    function glass_search_dir($lang = null) {
        $lang = $lang ?: glass_search_current_lang();
        return in_array($lang, ['fa','ar','he','ur'], true) ? 'rtl' : 'ltr';
    }
}

if (!function_exists('glass_search_current_language_data')) {
    function glass_search_current_language_data() {
        $lang = glass_search_current_lang();
        $fallback = [
            'fa' => ['name' => 'فارسی', 'flag' => '🇮🇷'],
            'en' => ['name' => 'English', 'flag' => '🇬🇧'],
            'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
            'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷'],
            'ru' => ['name' => 'Русский', 'flag' => '🇷🇺'],
            'hy' => ['name' => 'Հայերեն', 'flag' => '🇦🇲'],
        ];
        $data = $fallback[$lang] ?? ['name' => strtoupper($lang), 'flag' => '🌐'];

        if (function_exists('pll_the_languages')) {
            $langs = pll_the_languages(['raw'=>1,'hide_if_empty'=>0,'hide_current'=>0]);
            if (is_array($langs)) {
                foreach ($langs as $item) {
                    if (!empty($item['slug']) && $item['slug'] === $lang && !empty($item['name'])) {
                        $data['name'] = $item['name'];
                        break;
                    }
                }
            }
        }

        return ['slug'=>$lang, 'name'=>$data['name'], 'flag'=>$data['flag']];
    }
}

if (!function_exists('glass_search_action_url')) {
    function glass_search_action_url() {
        $lang = glass_search_current_lang();
        if (function_exists('pll_home_url')) {
            $url = pll_home_url($lang);
            if (!empty($url)) return $url;
        }
        return home_url('/');
    }
}

if (!function_exists('glass_search_t')) {
    function glass_search_t($key, $lang = null) {
        $lang = $lang ?: glass_search_current_lang();
        $strings = [
            'fa' => [
                'eyebrow'=>'جستجوی سایت','results_for'=>'نتایج جستجو برای','result_s'=>'%s نتیجه یافت شد','results_p'=>'%s نتیجه یافت شد','view'=>'مشاهده','page_label'=>'صفحه‌بندی نتایج جستجو','no_results'=>'نتیجه‌ای یافت نشد','no_desc'=>'لطفاً عبارت دیگری را امتحان کنید یا از منوهای سایت استفاده کنید.','placeholder'=>'جستجو کنید...','search_btn'=>'جستجو'
            ],
            'en' => [
                'eyebrow'=>'Site Search','results_for'=>'Search results for','result_s'=>'%s result found','results_p'=>'%s results found','view'=>'View','page_label'=>'Search results pagination','no_results'=>'No results found','no_desc'=>'Please try another keyword or browse the site menu.','placeholder'=>'Search...','search_btn'=>'Search'
            ],
            'ar' => [
                'eyebrow'=>'بحث الموقع','results_for'=>'نتائج البحث عن','result_s'=>'تم العثور على %s نتيجة','results_p'=>'تم العثور على %s نتيجة','view'=>'عرض','page_label'=>'ترقيم نتائج البحث','no_results'=>'لم يتم العثور على نتائج','no_desc'=>'يرجى تجربة عبارة أخرى أو استخدام قائمة الموقع.','placeholder'=>'ابحث...','search_btn'=>'بحث'
            ],
            'tr' => [
                'eyebrow'=>'Site Araması','results_for'=>'Arama sonuçları','result_s'=>'%s sonuç bulundu','results_p'=>'%s sonuç bulundu','view'=>'Görüntüle','page_label'=>'Arama sonuçları sayfalama','no_results'=>'Sonuç bulunamadı','no_desc'=>'Lütfen farklı bir kelime deneyin.','placeholder'=>'Ara...','search_btn'=>'Ara'
            ],
            'ru' => [
                'eyebrow'=>'Поиск по сайту','results_for'=>'Результаты поиска для','result_s'=>'Найдено результатов: %s','results_p'=>'Найдено результатов: %s','view'=>'Просмотр','page_label'=>'Пагинация результатов поиска','no_results'=>'Ничего не найдено','no_desc'=>'Попробуйте другой запрос.','placeholder'=>'Поиск...','search_btn'=>'Искать'
            ],
            'hy' => [
                'eyebrow'=>'Կայքի որոնում','results_for'=>'Որոնման արդյունքներ','result_s'=>'Գտնվել է %s արդյունք','results_p'=>'Գտնվել է %s արդյունք','view'=>'Դիտել','page_label'=>'Որոնման արդյունքների էջավորում','no_results'=>'Արդյունքներ չեն գտնվել','no_desc'=>'Փորձեք այլ արտահայտություն։','placeholder'=>'Որոնել...','search_btn'=>'Որոնել'
            ],
        ];
        $set = $strings[$lang] ?? $strings['en'];
        return $set[$key] ?? $key;
    }
}
