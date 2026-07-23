<?php
/**
 * Comments Template
 * Alborz Ghaleb Design
 * 
 * @package Alborz_Ghaleb
 */

if (!defined('ABSPATH')) {
    exit;
}

if (post_password_required()) {
    return;
}
?>

<section id="comments" class="gc-section">
    <div class="container">

        <!-- Section Header -->
        <div class="gc-section-head">
            <span class="gc-section-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                </svg>
                <?php echo esc_html( function_exists('glass_comment_t') ? glass_comment_t('comments_kicker') : __( 'دیدگاه‌ها و نظرات', 'glassmorphism-child-pro' ) ); ?>
            </span>
            <p style="font-weight:700;font-size:1.2rem;"><?php echo esc_html( function_exists('glass_comment_t') ? glass_comment_t('comments_title') : __( 'نظر شما برای ما ارزشمنده', 'glassmorphism-child-pro' ) ); ?></p>
            <p><?php echo esc_html( function_exists('glass_comment_t') ? glass_comment_t('comments_desc') : __( 'تجربه و دیدگاه خود را با ما و دیگر کاربران به اشتراک بگذارید', 'glassmorphism-child-pro' ) ); ?></p>
        </div>

        <?php if (have_comments()) : ?>

            <!-- Comments Count -->
            <div class="gc-count">
                <span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                    </svg>
                    <?php
                    $count = get_comments_number();
                    printf(
                        esc_html( function_exists('glass_comment_t') ? glass_comment_t('comments_count') : __( '%s دیدگاه', 'glassmorphism-child-pro' ) ),
                        number_format_i18n($count)
                    );
                    ?>
                </span>
            </div>

            <!-- Comments List -->
            <ol class="gc-list">
                <?php
                wp_list_comments([
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 56,
                    'callback'    => function_exists( 'glass_comment_callback' ) ? 'glass_comment_callback' : 'dab_glass_comment_callback',
                ]);
                ?>
            </ol>

            <!-- Pagination -->
            <?php
            the_comments_pagination([
                'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg> ' . esc_html( function_exists('glass_comment_t') ? glass_comment_t('prev') : __( 'قبلی', 'glassmorphism-child-pro' ) ),
                'next_text' => esc_html( function_exists('glass_comment_t') ? glass_comment_t('next') : __( 'بعدی', 'glassmorphism-child-pro' ) ) . ' <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>',
            ]);
            ?>

        <?php else : ?>

            <?php if (comments_open()) : ?>
                <div class="gc-empty">
                    <div class="gc-empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <p><?php echo esc_html( function_exists('glass_comment_t') ? glass_comment_t('comments_empty') : __( 'هنوز دیدگاهی ثبت نشده. اولین نفر باشید!', 'glassmorphism-child-pro' ) ); ?></p>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <?php if (comments_open()) : ?>

            <!-- Separator -->
            <div class="gc-separator">
                <span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 19l7-7 3 3-7 7-3-3z"/>
                        <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>
                        <path d="M2 2l7.586 7.586"/>
                        <circle cx="11" cy="11" r="2"/>
                    </svg>
                </span>
            </div>

            <!-- Comment Form -->
            <?php
            comment_form([
                'title_reply_before' => '<h3 id="reply-title" class="gc-form-title">',
                'title_reply_after'  => '</h3>',
            ]);
            ?>

        <?php endif; ?>

        <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
            <div class="gc-closed">
                <p><?php echo esc_html( function_exists('glass_comment_t') ? glass_comment_t('comments_closed') : __( 'امکان ثبت دیدگاه جدید وجود ندارد.', 'glassmorphism-child-pro' ) ); ?></p>
            </div>
        <?php endif; ?>

    </div>
</section>
