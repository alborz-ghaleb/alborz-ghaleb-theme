<?php
/**
 * ═══════════════════════════════════
 *  توابع بخش دیدگاه - Alborz Ghaleb
 * ═══════════════════════════════════
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! function_exists( 'glass_comment_t' ) ) {
    function glass_comment_t( string $key ): string {
        $lang = function_exists('glass_current_lang') ? glass_current_lang() : ( function_exists('pll_current_language') ? pll_current_language('slug') : substr(get_locale(),0,2) );
        $lang = substr( (string) $lang, 0, 2 );
        $S = [
            'fa'=>['author'=>'نویسنده','pending'=>'دیدگاه شما در انتظار تأیید مدیر است.','reply'=>'پاسخ','edit'=>'ویرایش','name'=>'نام','name_ph'=>'نام شما','email'=>'ایمیل','email_ph'=>'ایمیل شما','cookies'=>'ذخیره نام و ایمیل من برای دیدگاه‌های بعدی','title_reply'=>'دیدگاه خود را بنویسید','title_reply_to'=>'پاسخ به %s','cancel'=>'انصراف','submit'=>'ارسال دیدگاه','notes'=>'آدرس ایمیل شما منتشر نمی‌شود. فیلدهای ضروری با <span class="required">*</span> مشخص شده‌اند.','comment_label'=>'متن دیدگاه','comment_ph'=>'نظر، تجربه یا سوال خود را بنویسید...','comments_kicker'=>'دیدگاه‌ها و نظرات','comments_title'=>'نظر شما برای ما ارزشمنده','comments_desc'=>'تجربه و دیدگاه خود را با ما و دیگر کاربران به اشتراک بگذارید','comments_count'=>'%s دیدگاه','prev'=>'قبلی','next'=>'بعدی','comments_empty'=>'هنوز دیدگاهی ثبت نشده. اولین نفر باشید!','comments_closed'=>'امکان ثبت دیدگاه جدید وجود ندارد.'],
            'en'=>['author'=>'Author','pending'=>'Your comment is awaiting moderation.','reply'=>'Reply','edit'=>'Edit','name'=>'Name','name_ph'=>'Your name','email'=>'Email','email_ph'=>'Your email','cookies'=>'Save my name and email for next comments','title_reply'=>'Write your comment','title_reply_to'=>'Reply to %s','cancel'=>'Cancel','submit'=>'Post comment','notes'=>'Your email address will not be published. Required fields are marked <span class="required">*</span>.','comment_label'=>'Comment text','comment_ph'=>'Write your comment, experience or question...','comments_kicker'=>'Comments and opinions','comments_title'=>'Your opinion matters to us','comments_desc'=>'Share your experience and thoughts with us and other users','comments_count'=>'%s comments','prev'=>'Previous','next'=>'Next','comments_empty'=>'No comments yet. Be the first!','comments_closed'=>'New comments are closed.'],
            'ar'=>['author'=>'الكاتب','pending'=>'تعليقك بانتظار موافقة المدير.','reply'=>'رد','edit'=>'تعديل','name'=>'الاسم','name_ph'=>'اسمك','email'=>'البريد الإلكتروني','email_ph'=>'بريدك الإلكتروني','cookies'=>'احفظ اسمي وبريدي للتعليقات القادمة','title_reply'=>'اكتب تعليقك','title_reply_to'=>'الرد على %s','cancel'=>'إلغاء','submit'=>'إرسال التعليق','notes'=>'لن يتم نشر بريدك الإلكتروني. الحقول المطلوبة مميزة بـ <span class="required">*</span>.','comment_label'=>'نص التعليق','comment_ph'=>'اكتب تعليقك أو تجربتك أو سؤالك...','comments_kicker'=>'التعليقات والآراء','comments_title'=>'رأيك يهمنا','comments_desc'=>'شارك تجربتك ورأيك معنا ومع المستخدمين الآخرين','comments_count'=>'%s تعليق','prev'=>'السابق','next'=>'التالي','comments_empty'=>'لا توجد تعليقات بعد. كن الأول!','comments_closed'=>'إضافة تعليقات جديدة غير متاحة.'],
            'tr'=>['author'=>'Yazar','pending'=>'Yorumunuz yönetici onayı bekliyor.','reply'=>'Yanıtla','edit'=>'Düzenle','name'=>'Ad','name_ph'=>'Adınız','email'=>'E-posta','email_ph'=>'E-postanız','cookies'=>'Bir sonraki yorumlar için adımı ve e-postamı kaydet','title_reply'=>'Yorumunuzu yazın','title_reply_to'=>'%s için yanıt','cancel'=>'İptal','submit'=>'Yorumu gönder','notes'=>'E-posta adresiniz yayınlanmayacak. Gerekli alanlar <span class="required">*</span> ile işaretlenmiştir.','comment_label'=>'Yorum metni','comment_ph'=>'Yorumunuzu, deneyiminizi veya sorunuzu yazın...','comments_kicker'=>'Yorumlar ve görüşler','comments_title'=>'Görüşünüz bizim için değerli','comments_desc'=>'Deneyiminizi ve düşüncelerinizi bizimle ve diğer kullanıcılarla paylaşın','comments_count'=>'%s yorum','prev'=>'Önceki','next'=>'Sonraki','comments_empty'=>'Henüz yorum yok. İlk siz olun!','comments_closed'=>'Yeni yorum eklenemiyor.'],
            'ru'=>['author'=>'Автор','pending'=>'Ваш комментарий ожидает одобрения администратора.','reply'=>'Ответить','edit'=>'Редактировать','name'=>'Имя','name_ph'=>'Ваше имя','email'=>'Email','email_ph'=>'Ваш email','cookies'=>'Сохранить моё имя и email для следующих комментариев','title_reply'=>'Напишите комментарий','title_reply_to'=>'Ответить %s','cancel'=>'Отмена','submit'=>'Отправить комментарий','notes'=>'Ваш email не будет опубликован. Обязательные поля отмечены <span class="required">*</span>.','comment_label'=>'Текст комментария','comment_ph'=>'Напишите комментарий, опыт или вопрос...','comments_kicker'=>'Комментарии и мнения','comments_title'=>'Ваше мнение важно для нас','comments_desc'=>'Поделитесь своим опытом и мнением с нами и другими пользователями','comments_count'=>'%s комментариев','prev'=>'Предыдущая','next'=>'Следующая','comments_empty'=>'Комментариев пока нет. Будьте первым!','comments_closed'=>'Новые комментарии закрыты.'],
            'hy'=>['author'=>'Հեղինակ','pending'=>'Ձեր մեկնաբանությունը սպասում է ադմինի հաստատմանը։','reply'=>'Պատասխանել','edit'=>'Խմբագրել','name'=>'Անուն','name_ph'=>'Ձեր անունը','email'=>'Էլ. փոստ','email_ph'=>'Ձեր էլ. փոստը','cookies'=>'Պահպանել իմ անունը և էլ. փոստը հաջորդ մեկնաբանությունների համար','title_reply'=>'Գրեք ձեր մեկնաբանությունը','title_reply_to'=>'Պատասխանել %s-ին','cancel'=>'Չեղարկել','submit'=>'Ուղարկել մեկնաբանությունը','notes'=>'Ձեր էլ. փոստը չի հրապարակվի։ Պարտադիր դաշտերը նշված են <span class="required">*</span>։','comment_label'=>'Մեկնաբանության տեքստ','comment_ph'=>'Գրեք ձեր մեկնաբանությունը, փորձը կամ հարցը...','comments_kicker'=>'Մեկնաբանություններ և կարծիքներ','comments_title'=>'Ձեր կարծիքը կարևոր է մեզ համար','comments_desc'=>'Կիսվեք ձեր փորձով և կարծիքով մեզ և մյուս օգտատերերի հետ','comments_count'=>'%s մեկնաբանություն','prev'=>'Նախորդ','next'=>'Հաջորդ','comments_empty'=>'Դեռ մեկնաբանություններ չկան։ Եղեք առաջինը։','comments_closed'=>'Նոր մեկնաբանություններն անջատված են։'],
        ];
        return $S[$lang][$key] ?? $S['fa'][$key] ?? $key;
    }
}

/**
 * [FIX] فعال‌سازی پشتیبانی کامنت روی پست‌تایپ‌های صحیح
 *
 * قبلاً: add_post_type_support('blog', 'comments') — پست‌تایپی به نام
 * 'blog' وجود نداشت، پس این کد بی‌اثر بود.
 *
 * الان: کامنت فقط روی نوشته‌ها (post) و برگه‌ها (page) فعال می‌شود.
 * روی portfolio (آگهی دست‌دوم) و زیرمجموعه‌هایش غیرفعال می‌ماند
 * چون کاربر برای آگهی‌ها از فرم تماس/تماس تلفنی استفاده می‌کند.
 */
add_action( 'init', function () {
    add_post_type_support( 'post', 'comments' ); // اطمینان مجدد
    add_post_type_support( 'page', 'comments' );

    // [FIX] حذف صریح پشتیبانی کامنت از portfolio
    remove_post_type_support( 'portfolio', 'comments' );
    remove_post_type_support( 'portfolio', 'trackbacks' );
}, 20 );


/**
 * [FIX] جلوگیری از نمایش/پذیرش کامنت روی portfolio حتی اگر
 * در پست‌های قدیمی meta روی 'open' باشه.
 */
add_filter( 'comments_open', function ( $open, $post_id ) {
    if ( 'portfolio' === get_post_type( $post_id ) ) {
        return false;
    }
    return $open;
}, 20, 2 );

add_filter( 'pings_open', function ( $open, $post_id ) {
    if ( 'portfolio' === get_post_type( $post_id ) ) {
        return false;
    }
    return $open;
}, 20, 2 );


/**
 * تابع callback برای نمایش هر نظر
 * [FIX] نام تابع از 'dab_glass_comment_callback' (میراث از قالب قبلی)
 * به 'glass_comment_callback' تغییر کرد. alias هم برای سازگاری حفظ شد
 * تا اگر در فایل comments.php به نام قدیمی صدا زده شده باشه crash نکنه.
 */
function glass_comment_callback( $comment, $args, $depth ) {
    $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
    // whitelist سخت‌گیرانه برای اطمینان (در صورت override بد args)
    $tag = in_array( $tag, [ 'div', 'li' ], true ) ? $tag : 'li';
    $is_author = ( (int) $comment->user_id === (int) get_post_field( 'post_author', $comment->comment_post_ID ) );
    ?>
    <<?php echo esc_html( $tag ); ?> <?php comment_class( '', $comment ); ?> id="comment-<?php comment_ID(); ?>">
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body<?php echo $is_author ? ' is-post-author' : ''; ?>">

            <!-- هدر نظر -->
            <div class="gc-header">
                <div class="gc-author">
                    <?php echo get_avatar( $comment, 56, '', '', array( 'class' => 'gc-avatar' ) ); ?>
                    <div class="gc-author-info">
                        <div class="gc-name-row">
                            <span class="gc-name"><?php comment_author(); ?></span>
                            <?php if ( $is_author ) : ?>
                                <span class="gc-badge"><?php echo esc_html( glass_comment_t( 'author' ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        <a class="gc-date" href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <time datetime="<?php comment_time( 'c' ); ?>">
                                <?php echo esc_html( get_comment_date( '', $comment ) . ' — ' . get_comment_time() ); ?>
                            </time>
                        </a>
                    </div>
                </div>
            </div>

            <!-- متن نظر -->
            <div class="gc-content">
                <?php comment_text(); ?>
            </div>

            <!-- در انتظار تایید -->
            <?php if ( '0' === $comment->comment_approved ) : ?>
                <div class="gc-moderation">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo esc_html( glass_comment_t( 'pending' ) ); ?>
                </div>
            <?php endif; ?>

            <!-- دکمه‌ها -->
            <div class="gc-actions">
                <?php
                comment_reply_link( array_merge( $args, array(
                    'add_below'  => 'div-comment',
                    'depth'      => $depth,
                    'max_depth'  => $args['max_depth'],
                    'reply_text' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 10 20 15 15 20"/><path d="M4 4v7a4 4 0 004 4h12"/></svg> ' . esc_html( glass_comment_t( 'reply' ) ) . '',
                ) ) );

                edit_comment_link(
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> ' . esc_html( glass_comment_t( 'edit' ) ) . '',
                    '<span class="gc-edit-link">',
                    '</span>'
                );
                ?>
            </div>

        </article>
    </<?php echo esc_html( $tag ); ?>>
    <?php
}


/**
 * سفارشی‌سازی فیلدهای فرم
 */
add_filter( 'comment_form_default_fields', function ( $fields ) {
    $commenter = wp_get_current_commenter();
    $req       = get_option( 'require_name_email' );
    $required  = $req ? ' required="required"' : '';
    $star      = $req ? ' <span class="required">*</span>' : '';

    $fields['author'] =
        '<p class="comment-form-author">
            <label for="author">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                ' . esc_html( glass_comment_t( 'name' ) ) . '' . $star . '
            </label>
            <input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr( glass_comment_t( 'name_ph' ) ) . '"' . $required . ' />
        </p>';

    $fields['email'] =
        '<p class="comment-form-email">
            <label for="email">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                ' . esc_html( glass_comment_t( 'email' ) ) . '' . $star . '
            </label>
            <input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr( glass_comment_t( 'email_ph' ) ) . '"' . $required . ' />
        </p>';

    $fields['cookies'] =
        '<p class="comment-form-cookies-consent">
            <input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" />
            <label for="wp-comment-cookies-consent">' . esc_html( glass_comment_t( 'cookies' ) ) . '</label>
        </p>';

    return $fields;
} );


/**
 * تنظیمات پیش‌فرض فرم
 */
add_filter( 'comment_form_defaults', function ( $defaults ) {
    $defaults['title_reply']        = glass_comment_t( 'title_reply' );
    $defaults['title_reply_to']     = glass_comment_t( 'title_reply_to' );
    $defaults['cancel_reply_link']  = glass_comment_t( 'cancel' );
    $defaults['label_submit']       = glass_comment_t( 'submit' );
    $defaults['class_submit']       = 'gc-submit';
    $defaults['submit_button']      = '<button name="%1$s" type="submit" id="%2$s" class="%3$s"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> %4$s</button>';
    $defaults['comment_notes_before'] = '<p class="gc-form-notes">' . wp_kses_post( glass_comment_t( 'notes' ) ) . '</p>';
    $defaults['comment_notes_after']  = '';
    $defaults['comment_field']        =
        '<p class="comment-form-comment">
            <label for="comment">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                ' . esc_html( glass_comment_t( 'comment_label' ) ) . ' <span class="required">*</span>
            </label>
            <textarea id="comment" name="comment" cols="45" rows="7" required="required" placeholder="' . esc_attr( glass_comment_t( 'comment_ph' ) ) . '"></textarea>
        </p>';

    return $defaults;
} );


/**
 * ترتیب فیلدها: اول متن نظر، بعد 3 فیلد کنار هم
 */
add_filter( 'comment_form_fields', function ( $fields ) {
    $new = array();

    // متن نظر اول
    if ( isset( $fields['comment'] ) ) {
        $new['comment'] = $fields['comment'];
    }

    // دو فیلد در یک ردیف
    $row = '<div class="gc-fields-row">';
    foreach ( array( 'author', 'email' ) as $k ) {
        if ( isset( $fields[ $k ] ) ) {
            $row .= $fields[ $k ];
        }
    }
    $row .= '</div>';
    $new['fields_row'] = $row;

    // چک‌باکس
    if ( isset( $fields['cookies'] ) ) {
        $new['cookies'] = $fields['cookies'];
    }

    return $new;
} );


/**
 * [FIX] Alias برای سازگاری با نام قدیمی تابع callback
 * فایل comments.php از 'dab_glass_comment_callback' استفاده می‌کند.
 * این alias اطمینان می‌دهد اگر کسی به نام قدیمی صدا بزند، تابع جدید فراخوانی شود.
 */
if ( ! function_exists( 'dab_glass_comment_callback' ) ) {
    function dab_glass_comment_callback( $comment, $args, $depth ) {
        glass_comment_callback( $comment, $args, $depth );
    }
}
/**
 * ضداسپم نظرات: فیلد وب‌سایت و هر نوع لینک/href در متن دیدگاه مسدود است.
 */
add_filter( 'comment_form_default_fields', function ( $fields ) {
    unset( $fields['url'] );
    return $fields;
}, 100 );

add_filter( 'pre_comment_author_url', '__return_empty_string', 100 );

/**
 * تشخیص لینک‌های HTML و متنی پیش از ثبت نظر.
 */
function glass_comment_contains_blocked_link( string $content ): bool {
    return (bool) preg_match(
        '~<\s*a\b|\bhref\s*=|\burl\s*=|(?:https?://|ftp://|mailto:|www\.)|(?:^|[\s>])(?:[a-z0-9-]+\.)+(?:com|net|org|ir|co|io|me|info|biz)(?:[/\s<]|$)~iu',
        $content
    );
}

add_filter( 'preprocess_comment', function ( $commentdata ) {
    $content = isset( $commentdata['comment_content'] ) ? (string) $commentdata['comment_content'] : '';
    $type    = isset( $commentdata['comment_type'] ) ? (string) $commentdata['comment_type'] : '';

    // پینگ‌بک/ترک‌بک توسط وردپرس مدیریت می‌شوند؛ این قانون برای دیدگاه کاربران است.
    if ( '' === $type && glass_comment_contains_blocked_link( $content ) ) {
        wp_die(
            esc_html__( 'درج لینک، آدرس اینترنتی و تگ href در دیدگاه‌ها مجاز نیست.', 'glassmorphism-child-pro' ),
            esc_html__( 'لینک در دیدگاه مسدود است', 'glassmorphism-child-pro' ),
            [ 'response' => 400, 'back_link' => true ]
        );
    }

    $commentdata['comment_author_url'] = '';
    return $commentdata;
}, 20 );

/**
 * پاک‌سازی دفاعی خروجی نظرات قدیمی: لینک قابل کلیک یا URL نمایش داده نشود.
 */
add_filter( 'comment_text', function ( $text ) {
    $text = preg_replace( '~<a\b[^>]*>(.*?)</a>~is', '$1', (string) $text );
    $text = preg_replace( '~(?:https?://|ftp://|mailto:|www\.)[^\s<]+|(?:^|[\s>])(?:[a-z0-9-]+\.)+(?:com|net|org|ir|co|io|me|info|biz)(?:[/\s<]|$)~iu', ' ', (string) $text );
    return $text;
}, 100 );
