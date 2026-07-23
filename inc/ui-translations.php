<?php
/** UI translations for hardcoded template labels. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function glass_ui_t( string $key ): string {
    $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
    $S = [
        'en'=>[
            'owner_phone'=>'Ad owner phone number','show_owner_phone'=>'Show owner phone number','office_phones'=>'Office phones','online_chat'=>'Start online chat','market'=>'Buy & Sell Market','active'=>'Active','market_desc'=>'Used ads and purchase requests','telegram'=>'Telegram channel','telegram_desc'=>'Price list and stock updates','choose_city'=>'Choose city','city_search'=>'Search city...','city_not_found'=>'No city found.','security_title'=>'Security warning and disclaimer','security_text'=>'Dear user, this ad was submitted by site users. All responsibility for the text, photos and content of this ad belongs to the advertiser, and the site has no responsibility or obligation regarding it.<br><br>Important safety tips before trading:<br>1. Do not pay any advance payment or deposit before physical delivery and inspection.<br>2. Trade in person and inspect the item carefully before receiving it.<br>3. If you see anything suspicious, stop the deal and report the ad.','cancel'=>'Cancel','agree'=>'I agree and confirm','expired_banner'=>'This ad has expired.','sold'=>'✅ This ad has been sold','expired'=>'❌ This ad has expired'],
        'ar'=>[
            'owner_phone'=>'رقم هاتف صاحب الإعلان','show_owner_phone'=>'عرض رقم هاتف المالك','office_phones'=>'هواتف المكتب','online_chat'=>'بدء المحادثة آنلاین','market'=>'سوق البيع والشراء','active'=>'نشط','market_desc'=>'إعلانات مستعملة وطلبات شراء','telegram'=>'قناة تلغرام','telegram_desc'=>'قائمة الأسعار والمخزون','choose_city'=>'اختيار المدينة','city_search'=>'ابحث عن مدينة...','city_not_found'=>'لم يتم العثور على مدينة.','security_title'=>'تنبيه أمني وإخلاء مسؤولية','security_text'=>'عزيزي المستخدم، تم نشر هذا الإعلان بواسطة مستخدمي الموقع. تقع مسؤولية النص والصور ومحتوى الإعلان بالكامل على المعلن، ولا يتحمل الموقع أي مسؤولية.<br><br>نصائح أمان مهمة قبل المعاملة:<br>1. لا تدفع أي عربون قبل استلام وفحص السلعة.<br>2. قم بالمعاملة حضورياً وافحص السلعة جيداً.<br>3. عند وجود أي أمر مشبوه أوقف المعاملة وأبلغ عن الإعلان.','cancel'=>'إلغاء','agree'=>'أوافق وأؤكد','expired_banner'=>'انتهت صلاحية هذا الإعلان.','sold'=>'✅ تم بيع هذا الإعلان','expired'=>'❌ انتهت صلاحية هذا الإعلان'],
        'tr'=>[
            'owner_phone'=>'İlan sahibi telefonu','show_owner_phone'=>'Sahibin telefonunu göster','office_phones'=>'Ofis telefonları','online_chat'=>'Çevrimiçi sohbet başlat','market'=>'Alım satım pazarı','active'=>'Aktif','market_desc'=>'İkinci el ilanlar ve satın alma talepleri','telegram'=>'Telegram kanalı','telegram_desc'=>'Fiyat listesi ve stok bilgileri','choose_city'=>'Şehir seç','city_search'=>'Şehir ara...','city_not_found'=>'Şehir bulunamadı.','security_title'=>'Güvenlik uyarısı ve sorumluluk reddi','security_text'=>'Sayın kullanıcı, bu ilan site kullanıcıları tarafından eklenmiştir. İlan metni, fotoğrafları ve içeriğinin tüm sorumluluğu ilan sahibine aittir; site hiçbir sorumluluk kabul etmez.<br><br>İşlem öncesi önemli güvenlik ipuçları:<br>1. Ürünü teslim alıp kontrol etmeden kapora/ön ödeme yapmayın.<br>2. İşlemi yüz yüze yapın ve ürünü dikkatlice kontrol edin.<br>3. Şüpheli bir durumda işlemi durdurun ve ilanı bildirin.','cancel'=>'İptal','agree'=>'Kabul ediyorum','expired_banner'=>'Bu ilanın süresi doldu.','sold'=>'✅ Bu ilan satıldı','expired'=>'❌ Bu ilanın süresi doldu'],
        'ru'=>[
            'owner_phone'=>'Телефон владельца объявления','show_owner_phone'=>'Показать телефон владельца','office_phones'=>'Офисные телефоны','online_chat'=>'Начать онлайн-чат','market'=>'Рынок купли-продажи','active'=>'Активно','market_desc'=>'Объявления б/у и заявки на покупку','telegram'=>'Канал Telegram','telegram_desc'=>'Прайс-лист и наличие','choose_city'=>'Выбрать город','city_search'=>'Поиск города...','city_not_found'=>'Город не найден.','security_title'=>'Предупреждение безопасности и отказ от ответственности','security_text'=>'Уважаемый пользователь, это объявление размещено пользователями сайта. Вся ответственность за текст, фотографии и содержание объявления лежит на рекламодателе; сайт не несёт ответственности.<br><br>Важные советы перед сделкой:<br>1. Не вносите предоплату до получения и проверки товара.<br>2. Совершайте сделку лично и внимательно проверяйте товар.<br>3. При подозрительных обстоятельствах остановите сделку и сообщите об объявлении.','cancel'=>'Отмена','agree'=>'Согласен и подтверждаю','expired_banner'=>'Срок действия объявления истёк.','sold'=>'✅ Это объявление продано','expired'=>'❌ Срок объявления истёк'],
        'hy'=>[
            'owner_phone'=>'Հայտարարության տիրոջ հեռախոսահամարը','show_owner_phone'=>'Ցույց տալ տիրոջ հեռախոսահամարը','office_phones'=>'Գրասենյակի հեռախոսներ','online_chat'=>'Սկսել առցանց զրույց','market'=>'Գնման և վաճառքի շուկա','active'=>'Ակտիվ','market_desc'=>'Օգտագործված հայտարարություններ և գնման հարցումներ','telegram'=>'Telegram ալիք','telegram_desc'=>'Գնացուցակ և պահեստի առկայություն','choose_city'=>'Ընտրել քաղաք','city_search'=>'Որոնել քաղաք...','city_not_found'=>'Քաղաք չի գտնվել։','security_title'=>'Անվտանգության զգուշացում և պատասխանատվության սահմանափակում','security_text'=>'Հարգելի օգտատեր, այս հայտարարությունը տեղադրվել է կայքի օգտատերերի կողմից։ Հայտարարության տեքստի, լուսանկարների և բովանդակության ամբողջ պատասխանատվությունը կրում է հայտարարատուն, և կայքը պատասխանատվություն չի կրում։<br><br>Կարևոր անվտանգության խորհուրդներ գործարքից առաջ.<br>1. Մի կատարեք կանխավճար մինչև ապրանքի ֆիզիկական ստացումը և ստուգումը։<br>2. Գործարքը կատարեք անձամբ և մանրակրկիտ ստուգեք ապրանքը։<br>3. Կասկածելի դեպքերում դադարեցրեք գործարքը և հաղորդեք հայտարարության մասին։','cancel'=>'Չեղարկել','agree'=>'Համաձայն եմ և հաստատում եմ','expired_banner'=>'Այս հայտարարության ժամկետը լրացել է։','sold'=>'✅ Այս հայտարարությունը վաճառված է','expired'=>'❌ Այս հայտարարության ժամկետը լրացել է'],
        'fa'=>[]
    ];
    $fa = [
        'owner_phone'=>'شماره تماس مالک آگهی','show_owner_phone'=>'نمایش شماره تماس مالک','office_phones'=>'تلفن‌های دفتر','online_chat'=>'شروع گفتگوی آنلاین (Chat)','market'=>'بازار خرید و فروش','active'=>'فعال','market_desc'=>'آگهی‌های دست‌دوم و درخواست خرید','telegram'=>'کانال تلگرام','telegram_desc'=>'لیست قیمت و موجودی انبار','choose_city'=>'انتخاب شهر','city_search'=>'جستجوی شهر...','city_not_found'=>'شهری با این نام پیدا نشد.','security_title'=>'هشدار امنیتی و رفع مسئولیت','security_text'=>'کاربر گرامی، این آگهی توسط کاربران سایت ثبت شده است. تمام مسئولیت متن، عکس‌ها و مندرجات این آگهی کاملاً بر عهده شخص آگهی‌دهنده می‌باشد و سایت هیچ‌گونه مسئولیت یا تعهدی در قبال آن‌ها ندارد.<br><br>نکات امنیتی بسیار مهم قبل از معامله:<br>۱. از پرداخت هرگونه پیش‌پرداخت یا بیعانه قبل از تحویل و تست فیزیکی کالا، جداً خودداری فرمایید.<br>۲. معامله را فقط به صورت حضوری انجام داده و کالا را پس از بررسی دقیق فیزیکی دریافت کنید.<br>۳. در صورت مشاهده هرگونه مورد مشکوک یا خارج از عرف، معامله را متوقف کرده و آگهی را گزارش دهید.','cancel'=>'انصراف','agree'=>'موافق و تأیید می‌کنم','expired_banner'=>'این آگهی منقضی شده است.','sold'=>'✅ این آگهی فروخته شد','expired'=>'❌ این آگهی منقضی شده است'
    ];
    $S['fa']=$fa;
    $short = substr((string)$lang,0,2);
    return $S[$short][$key] ?? $fa[$key] ?? $key;
}

if ( ! function_exists( 'glass_ui_extra_t' ) ) {
    function glass_ui_extra_t( string $key ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        $S = [
            'fa' => [
                'vip_title'=>'برای قیمت تماس بگیرید','vip_subtitle'=>'مشاوره، خرید و فروش تجهیزات قالب‌بندی','instagram'=>'گالری تولیدات','instagram_desc'=>'فیلم و عکس پروژه‌های اجرایی','free_submit'=>'ثبت آگهی رایگان','submit_used_ad'=>'ثبت آگهی دست دوم','have_used_equipment'=>'آیا تجهیزات دست‌دوم برای فروش دارید؟','submit_free_desc'=>'آگهی خود را به صورت رایگان ثبت کنید تا هزاران خریدار آن را ببینند.','price_label'=>'قیمت آگهی','optional'=>'اختیاری','price_placeholder'=>'مثلاً 2500000 — خالی یعنی توافقی','price_hint'=>'اگر قیمت را وارد نکنید، در آگهی «توافقی» نمایش داده می‌شود.','old_price'=>'قیمت قبل تخفیف','discount_percent'=>'درصد تخفیف ویژه','negotiable'=>'توافقی','currency'=>'تومان','feature_confirm'=>'آگهی با پرداخت %s ویژه شود؟'
            ],
            'en' => [
                'vip_title'=>'Contact for price','vip_subtitle'=>'Consulting, buying and selling formwork equipment','instagram'=>'Production gallery','instagram_desc'=>'Photos and videos of completed projects','free_submit'=>'Post a free ad','submit_used_ad'=>'Post a used ad','have_used_equipment'=>'Do you have used equipment for sale?','submit_free_desc'=>'Post your ad for free and reach thousands of buyers.','price_label'=>'Ad price','optional'=>'Optional','price_placeholder'=>'Example: 2500 — leave empty for negotiable','price_hint'=>'If you leave the price empty, the ad will show “Negotiable”.','old_price'=>'Old price','discount_percent'=>'Special discount percent','negotiable'=>'Negotiable','currency'=>'USD','feature_confirm'=>'Feature this ad by paying %s?'
            ],
            'ar' => [
                'vip_title'=>'اتصل لمعرفة السعر','vip_subtitle'=>'استشارات وشراء وبيع معدات القوالب','instagram'=>'معرض الإنتاج','instagram_desc'=>'صور وفيديوهات المشاريع المنفذة','free_submit'=>'انشر إعلاناً مجاناً','submit_used_ad'=>'نشر إعلان مستعمل','have_used_equipment'=>'هل لديك معدات مستعملة للبيع؟','submit_free_desc'=>'انشر إعلانك مجاناً ليشاهده آلاف المشترين.','price_label'=>'سعر الإعلان','optional'=>'اختياري','price_placeholder'=>'مثال: 2500 — اتركه فارغاً للتفاوض','price_hint'=>'إذا تركت السعر فارغاً فسيظهر الإعلان بسعر قابل للتفاوض.','old_price'=>'السعر السابق','discount_percent'=>'نسبة الخصم الخاصة','negotiable'=>'قابل للتفاوض','currency'=>'USD','feature_confirm'=>'تمييز الإعلان بدفع %s؟'
            ],
            'tr' => [
                'vip_title'=>'Fiyat için iletişime geçin','vip_subtitle'=>'Kalıp ekipmanları danışmanlık, alım ve satım','instagram'=>'Üretim galerisi','instagram_desc'=>'Uygulanan projelerin fotoğraf ve videoları','free_submit'=>'Ücretsiz ilan ver','submit_used_ad'=>'İkinci el ilan ver','have_used_equipment'=>'Satılık ikinci el ekipmanınız var mı?','submit_free_desc'=>'İlanınızı ücretsiz yayınlayın ve binlerce alıcıya ulaşın.','price_label'=>'İlan fiyatı','optional'=>'İsteğe bağlı','price_placeholder'=>'Örn: 2500 — pazarlık için boş bırakın','price_hint'=>'Fiyatı boş bırakırsanız ilanda “Pazarlık” görünür.','old_price'=>'Önceki fiyat','discount_percent'=>'Özel indirim yüzdesi','negotiable'=>'Pazarlık','currency'=>'USD','feature_confirm'=>'%s ödeyerek ilanı öne çıkar?'
            ],
            'ru' => [
                'vip_title'=>'Свяжитесь для уточнения цены','vip_subtitle'=>'Консультации, покупка и продажа опалубочного оборудования','instagram'=>'Галерея продукции','instagram_desc'=>'Фото и видео выполненных проектов','free_submit'=>'Разместить бесплатно','submit_used_ad'=>'Разместить б/у объявление','have_used_equipment'=>'Есть б/у оборудование на продажу?','submit_free_desc'=>'Разместите объявление бесплатно и покажите его тысячам покупателей.','price_label'=>'Цена объявления','optional'=>'Необязательно','price_placeholder'=>'Напр.: 2500 — оставьте пустым для договорной цены','price_hint'=>'Если цена не указана, будет показано «Договорная».','old_price'=>'Старая цена','discount_percent'=>'Процент скидки','negotiable'=>'Договорная','currency'=>'USD','feature_confirm'=>'Сделать объявление избранным за %s?'
            ],
            'hy' => [
                'vip_title'=>'Կապ հաստատեք գնի համար','vip_subtitle'=>'Խորհրդատվություն, կաղապարամածի սարքավորումների գնում և վաճառք','instagram'=>'Արտադրանքի պատկերասրահ','instagram_desc'=>'Իրականացված նախագծերի լուսանկարներ և տեսանյութեր','free_submit'=>'Տեղադրել անվճար հայտարարություն','submit_used_ad'=>'Տեղադրել օգտագործված հայտարարություն','have_used_equipment'=>'Ունե՞ք օգտագործված սարքավորում վաճառքի համար։','submit_free_desc'=>'Տեղադրեք ձեր հայտարարությունը անվճար և հասեք հազարավոր գնորդների։','price_label'=>'Հայտարարության գին','optional'=>'Ըստ ցանկության','price_placeholder'=>'Օր.՝ 2500 — թողեք դատարկ՝ պայմանագրայինի համար','price_hint'=>'Եթե գինը դատարկ թողնեք, կհայտնվի «Պայմանագրային»։','old_price'=>'Նախկին գին','discount_percent'=>'Հատուկ զեղչի տոկոս','negotiable'=>'Պայմանագրային','currency'=>'USD','feature_confirm'=>'Դարձնել հատուկ՝ վճարելով %s՞'
            ],
        ];
        return $S[$lang][$key] ?? $S['fa'][$key] ?? $key;
    }
}

if ( ! function_exists( 'glass_ui_money' ) ) {
    function glass_ui_money( int $amount ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        if ( 'fa' === $lang ) {
            return number_format_i18n( $amount ) . ' ' . glass_ui_extra_t( 'currency' );
        }
        return '$' . number_format_i18n( $amount );
    }
}

if ( ! function_exists( 'glass_ui_slider_name' ) ) {
    function glass_ui_slider_name( string $key, string $fallback ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        $S = [
            'fa' => [
                'slider_concrete'=>'قالب بتن','slider_scaffold'=>'داربست مدولار','slider_jack'=>'جک سقفی','slider_machinery'=>'ماشین آلات','slider_equipment'=>'تجهیزات قالب بندی','slider_wallpost'=>'والپست',
            ],
            'en' => [
                'slider_concrete'=>'Concrete Formwork','slider_scaffold'=>'Modular Scaffolding','slider_jack'=>'Ceiling Jacks','slider_machinery'=>'Machinery','slider_equipment'=>'Formwork Equipment','slider_wallpost'=>'Wall Post',
            ],
            'ar' => [
                'slider_concrete'=>'قوالب الخرسانة','slider_scaffold'=>'السقالات المعيارية','slider_jack'=>'دعامات السقف','slider_machinery'=>'الآلات','slider_equipment'=>'معدات القوالب','slider_wallpost'=>'وال بوست',
            ],
            'tr' => [
                'slider_concrete'=>'Beton Kalıbı','slider_scaffold'=>'Modüler İskele','slider_jack'=>'Tavan Krikoları','slider_machinery'=>'Makineler','slider_equipment'=>'Kalıp Ekipmanları','slider_wallpost'=>'Duvar Postu',
            ],
            'ru' => [
                'slider_concrete'=>'Бетонная опалубка','slider_scaffold'=>'Модульные леса','slider_jack'=>'Потолочные стойки','slider_machinery'=>'Оборудование','slider_equipment'=>'Оснастка опалубки','slider_wallpost'=>'Wall Post',
            ],
            'hy' => [
                'slider_concrete'=>'Բետոնի կաղապարամած','slider_scaffold'=>'Մոդուլային փայտամած','slider_jack'=>'Առաստաղային հենակներ','slider_machinery'=>'Մեքենաներ','slider_equipment'=>'Կաղապարամածի սարքավորումներ','slider_wallpost'=>'Wall Post',
            ],
        ];
        return $S[$lang][$key] ?? $S['fa'][$key] ?? $fallback;
    }
}

if ( ! function_exists( 'glass_ui_localized_page_item' ) ) {
    /**
     * Build a slider item whose URL/title follows the translated Polylang page when available.
     */
    function glass_ui_localized_page_item( string $path, string $img, string $name_key, string $fallback_name ): array {
        $path = trim( $path, '/' );
        $url  = home_url( '/' . $path . '/' );
        $name = glass_ui_slider_name( $name_key, $fallback_name );
        $page = get_page_by_path( rawurldecode( $path ) );
        if ( ! $page ) {
            $id = url_to_postid( $url );
            $page = $id ? get_post( $id ) : null;
        }
        if ( $page && ! is_wp_error( $page ) ) {
            $target_id = (int) $page->ID;
            if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_get_post' ) ) {
                $lang = pll_current_language( 'slug' );
                $translated_id = $lang ? pll_get_post( $target_id, $lang ) : 0;
                if ( $translated_id ) {
                    $target_id = (int) $translated_id;
                }
            }
            $permalink = get_permalink( $target_id );
            if ( $permalink ) {
                $url = $permalink;
            }
            $title = get_the_title( $target_id );
            if ( $title ) {
                $name = $title;
            }
        }
        return [ 'url' => $url, 'img' => $img, 'name' => $name ];
    }
}


if ( ! function_exists( 'glass_ui_blog_t' ) ) {
    function glass_ui_blog_t( string $key ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        $S = [
            'fa'=>['read_min'=>'%s دقیقه مطالعه','views'=>'%s بازدید','share'=>'اشتراک:','prev'=>'قبلی','next'=>'بعدی','related'=>'مطالب مرتبط','last_updated'=>'آخرین به‌روزرسانی:','published'=>'منتشر شده:','no_image'=>'بدون تصویر','online_chat'=>'شروع گفتگوی آنلاین','market'=>'بازار خرید و فروش','telegram'=>'کانال تلگرام','telegram_desc'=>'لیست قیمت و موجودی انبار','instagram'=>'گالری تولیدات','instagram_desc'=>'فیلم و عکس پروژه‌های اجرایی','toc'=>'فهرست مطالب'],
            'en'=>['read_min'=>'%s min read','views'=>'%s views','share'=>'Share:','prev'=>'Previous','next'=>'Next','related'=>'Related posts','last_updated'=>'Last updated:','published'=>'Published:','no_image'=>'No image','online_chat'=>'Start online chat','market'=>'Buy & Sell Market','telegram'=>'Telegram channel','telegram_desc'=>'Price list and stock updates','instagram'=>'Production gallery','instagram_desc'=>'Photos and videos of completed projects','toc'=>'Table of Contents'],
            'ar'=>['read_min'=>'%s دقيقة قراءة','views'=>'%s مشاهدة','share'=>'مشاركة:','prev'=>'السابق','next'=>'التالي','related'=>'مقالات ذات صلة','last_updated'=>'آخر تحديث:','published'=>'تاريخ النشر:','no_image'=>'بدون صورة','online_chat'=>'بدء المحادثة آنلاین','market'=>'سوق البيع والشراء','telegram'=>'قناة تلغرام','telegram_desc'=>'قائمة الأسعار والمخزون','instagram'=>'معرض الإنتاج','instagram_desc'=>'صور وفيديوهات المشاريع المنفذة','toc'=>'فهرست المحتويات'],
            'tr'=>['read_min'=>'%s dk okuma','views'=>'%s görüntüleme','share'=>'Paylaş:','prev'=>'Önceki','next'=>'Sonraki','related'=>'İlgili yazılar','last_updated'=>'Son güncelleme:','published'=>'Yayınlandı:','no_image'=>'Görsel yok','online_chat'=>'Çevrimiçi sohbet başlat','market'=>'Alım satım pazarı','telegram'=>'Telegram kanalı','telegram_desc'=>'Fiyat listesi ve stok bilgileri','instagram'=>'Üretim galerisi','instagram_desc'=>'Uygulanan projelerin fotoğraf ve videoları','toc'=>'İçindekiler'],
            'ru'=>['read_min'=>'%s мин чтения','views'=>'%s просмотров','share'=>'Поделиться:','prev'=>'Предыдущая','next'=>'Следующая','related'=>'Похожие материалы','last_updated'=>'Последнее обновление:','published'=>'Опубликовано:','no_image'=>'Нет изображения','online_chat'=>'Начать онлайн-чат','market'=>'Рынок купли-продажи','telegram'=>'Канал Telegram','telegram_desc'=>'Прайс-лист и наличие','instagram'=>'Галерея продукции','instagram_desc'=>'Фото и видео выполненных проектов','toc'=>'Содержание'],
            'hy'=>['read_min'=>'%s րոպե ընթերցում','views'=>'%s դիտում','share'=>'Կիսվել՝','prev'=>'Նախորդ','next'=>'Հաջորդ','related'=>'Առնչվող նյութեր','last_updated'=>'Վերջին թարմացում՝','published'=>'Հրապարակվել է՝','no_image'=>'Պատկեր չկա','online_chat'=>'Սկսել առցանց զրույց','market'=>'Գնման և վաճառքի շուկա','telegram'=>'Telegram ալիք','telegram_desc'=>'Գնացուցակ և պահեստի առկայություն','instagram'=>'Արտադրանքի պատկերասրահ','instagram_desc'=>'Իրականացված նախագծերի լուսանկարներ և տեսանյութեր','toc'=>'Բdelays'],
        ];
        return $S[$lang][$key] ?? $S['fa'][$key] ?? $key;
    }
}


if ( ! function_exists( 'glass_ui_contact_t' ) ) {
    function glass_ui_contact_t( string $key ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        $S = [
            'fa'=>['mobile_phones'=>'شماره‌های همراه','office_phones'=>'تلفن‌های دفتر','sales'=>'فروش','support'=>'پشتیبانی'],
            'en'=>['mobile_phones'=>'Mobile numbers','office_phones'=>'Office phones','sales'=>'Sales','support'=>'Support'],
            'ar'=>['mobile_phones'=>'أرقام الجوال','office_phones'=>'هواتف المكتب','sales'=>'المبيعات','support'=>'الدعم'],
            'tr'=>['mobile_phones'=>'Cep telefonları','office_phones'=>'Ofis telefonları','sales'=>'Satış','support'=>'Destek'],
            'ru'=>['mobile_phones'=>'Мобильные телефоны','office_phones'=>'Офисные телефоны','sales'=>'Продажи','support'=>'Поддержка'],
            'hy'=>['mobile_phones'=>'Բջջային համարներ','office_phones'=>'Գրասենյակի հեռախոսներ','sales'=>'Վաճառք','support'=>'Աջակցություն'],
        ];
        return $S[$lang][$key] ?? $S['fa'][$key] ?? $key;
    }
}

if ( ! function_exists( 'glass_ui_misc_t' ) ) {
    function glass_ui_misc_t( string $key ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        $S = [
            'fa'=>['used_ads'=>'دست دوم','used_ads_in'=>'دست دوم در %s','view'=>'مشاهده','view_ad'=>'مشاهده آگهی','read_article'=>'مطالعه مقاله','read_more'=>'بیشتر بخوانید','page'=>'صفحه','article'=>'مقاله'],
            'en'=>['used_ads'=>'Used Ads','used_ads_in'=>'Used ads in %s','view'=>'View','view_ad'=>'View ad','read_article'=>'Read article','read_more'=>'Read more','page'=>'Page','article'=>'Article'],
            'ar'=>['used_ads'=>'مستعمل','used_ads_in'=>'مستعمل في %s','view'=>'عرض','view_ad'=>'عرض الإعلان','read_article'=>'قراءة المقال','read_more'=>'اقرأ المزيد','page'=>'صفحة','article'=>'مقالة'],
            'tr'=>['used_ads'=>'İkinci El','used_ads_in'=>'%s ikinci el ilanları','view'=>'Görüntüle','view_ad'=>'İlanı görüntüle','read_article'=>'Makaleyi oku','read_more'=>'Daha fazla oku','page'=>'Sayfa','article'=>'Makale'],
            'ru'=>['used_ads'=>'Б/у объявления','used_ads_in'=>'Б/у объявления в %s','view'=>'Просмотр','view_ad'=>'Посмотреть объявление','read_article'=>'Читать статью','read_more'=>'Подробнее','page'=>'Страница','article'=>'Статья'],
            'hy'=>['used_ads'=>'Օգտագործված հայտարարություններ','used_ads_in'=>'Օգտագործված հայտարարություններ՝ %s','view'=>'Դիտել','view_ad'=>'Դիտել հայտարարությունը','read_article'=>'Կարդալ հոդվածը','read_more'=>'Կարդալ ավելին','page'=>'Էջ','article'=>'Հոդված'],
        ];
        return $S[$lang][$key] ?? $S['fa'][$key] ?? $key;
    }
}
