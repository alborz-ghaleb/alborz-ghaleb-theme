<?php
/** User Panel — User Dashboard (shortcode [glass_user_dashboard]) */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   6. داشبورد کاربر  [glass_user_dashboard]
   ════════════════════════════════════════ */

add_shortcode( 'glass_user_dashboard', 'glass_render_user_dashboard' );
function glass_render_user_dashboard() {

    // اگر لاگین نیست → فرم ورود نشان بده
    if ( ! is_user_logged_in() ) {
        return glass_render_login_panel();
    }

    $user_id = get_current_user_id();
    $user    = wp_get_current_user();

    // پیام‌های وضعیت
    $messages = [
        'sold'         => [ glass_t('dmsg_sold'), 'success' ],
        'restored'     => [ glass_t('dmsg_restored'), 'success' ],
        'deleted'      => [ glass_t('dmsg_deleted'), 'success' ],
        'renewed'      => [ glass_t('dmsg_renewed'), 'success' ],
        'renew_failed' => [ glass_t('dmsg_renewfail'), 'error' ],
        'featured' => [ __( 'آگهی شما ویژه شد.', 'glassmorphism-child-pro' ), 'success' ],
        'feature_failed' => [ __( 'ویژه کردن آگهی ناموفق بود.', 'glassmorphism-child-pro' ), 'error' ],
    ];
    $msg_html = '';
    if ( isset( $_GET['glass_msg'] ) && isset( $messages[ $_GET['glass_msg'] ] ) ) {
        $m = $messages[ sanitize_key( $_GET['glass_msg'] ) ];
        $msg_html = '<div class="glass-auth-notice glass-auth-notice--' . esc_attr( $m[1] ) . '">' . esc_html( $m[0] ) . '</div>';
    }

    // آگهی‌های کاربر
    $ads = new WP_Query( [
        'post_type'      => 'portfolio',
        'author'         => $user_id,
        'post_status'    => [ 'publish', 'pending', 'draft' ],
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    $renew_amount = number_format( absint( get_theme_mod( 'glass_pf_renew_amount', 50000 ) ) );
    $renew_pay    = (bool) get_theme_mod( 'glass_pf_renew_pay_enabled', true );
    $feature_enabled = function_exists( 'glass_core_feature_enabled' ) ? glass_core_feature_enabled() : false;
    $feature_amount = function_exists( 'glass_core_feature_amount' ) ? number_format_i18n( glass_core_feature_amount() ) : '';

    // آدرس برگه ثبت آگهی (با fallback به مسیر پیش‌فرض)
    $submit_url = function_exists( 'glass_get_submit_url' ) ? glass_get_submit_url() : get_theme_mod( 'glass_pf_submit_url', '' );
    if ( empty( $submit_url ) ) {
        $submit_url = home_url( '/ثبت-آگهی/' );
    }

    ob_start();
    ?>
    <div class="glass-dash-wrap">

        <!-- هدر داشبورد -->
        <div class="glass-dash-header">
            <div>
                <h2 class="glass-dash-title"><?php echo esc_html( glass_t('dash_title') ); ?></h2>
                <p class="glass-dash-sub"><?php printf( esc_html( glass_t('dash_welcome') ), esc_html( $user->display_name ) ); ?></p>
            </div>
            <div class="glass-dash-header-actions">
                <a href="<?php echo esc_url( $submit_url ); ?>" class="glass-auth-btn glass-dash-newad">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <?php echo esc_html( glass_t('dash_new') ); ?>
                </a>
                <a href="<?php echo esc_url( wp_logout_url( home_url('/') ) ); ?>" class="glass-auth-btn glass-auth-btn--ghost glass-dash-logout"><?php echo esc_html( glass_t('dash_logout') ); ?></a>
            </div>
        </div>

        <?php echo wp_kses_post( $msg_html ); ?>

        <?php if ( ! $ads->have_posts() ) : ?>
            <div class="glass-dash-empty">
                <p><?php echo esc_html( glass_t('dash_empty') ); ?></p>
                <a href="<?php echo esc_url( $submit_url ); ?>" class="glass-auth-btn glass-dash-empty-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <?php echo esc_html( glass_t('dash_first') ); ?>
                </a>
            </div>
        <?php else : ?>

            <div class="glass-dash-list">
                <?php while ( $ads->have_posts() ) : $ads->the_post();
                    $pid    = get_the_ID();
                    $state  = glass_get_ad_state( $pid );
                    $status = get_post_status( $pid );
                    $expiry = (int) get_post_meta( $pid, 'portfolio_expiry', true );
                    $city   = get_post_meta( $pid, 'portfolio_city', true );
                    // آیا این آگهی توسط کاربر ثبت شده؟ (آگهی مدیر دکمه تمدید/فروخته ندارد)
                    $is_user_ad = glass_is_user_submitted_ad( $pid );
                    $is_featured = function_exists( 'glass_core_is_featured_ad' ) ? glass_core_is_featured_ad( $pid ) : ( (int) get_post_meta( $pid, 'portfolio_featured_until', true ) > time() );
                    $ad_price = absint( get_post_meta( $pid, 'portfolio_price', true ) );

                    // برچسب وضعیت
                    $state_labels = [
                        'active'  => [ glass_t('st_active'), '#10b981' ],
                        'sold'    => [ glass_t('st_sold'), '#ef4444' ],
                        'expired' => [ glass_t('st_expired'), '#f59e0b' ],
                    ];
                    $sl = $state_labels[ $state ];

                    $days_left = $expiry > 0 ? ceil( ( $expiry - time() ) / DAY_IN_SECONDS ) : 0;
                    ?>
                    <div class="glass-dash-card glass-dash-card--<?php echo esc_attr( $state ); ?>">

                        <div class="glass-dash-card-thumb">
                            <?php if ( has_post_thumbnail( $pid ) ) : ?>
                                <?php echo get_the_post_thumbnail( $pid, 'glass-thumb', [ 'loading' => 'lazy', 'decoding' => 'async' ] ); ?>
                            <?php else : ?>
                                <div class="glass-dash-noimg"><?php echo esc_html( glass_t('noimg') ); ?></div>
                            <?php endif; ?>
                            <span class="glass-dash-badge" style="background: <?php echo esc_attr( $sl[1] ); ?>;"><?php echo esc_html( $sl[0] ); ?></span>
                        </div>

                        <div class="glass-dash-card-body">
                            <h3 class="glass-dash-card-title"><?php the_title(); ?></h3>
                            <div class="glass-dash-meta">
                                <?php if ( $city ) : ?><span>📍 <?php echo esc_html( $city ); ?></span><?php endif; ?>
                                <?php if ( $ad_price > 0 ) : ?><span>💰 <?php echo esc_html( function_exists('glass_ui_money') ? glass_ui_money( $ad_price ) : number_format_i18n( $ad_price ) ); ?></span><?php else : ?><span>💰 <?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('negotiable') : __( 'توافقی', 'glassmorphism-child-pro' ) ); ?></span><?php endif; ?>
                                <?php if ( $is_featured ) : ?><span style="color:#b45309;">⭐ <?php esc_html_e( 'ویژه', 'glassmorphism-child-pro' ); ?></span><?php endif; ?>
                                <?php if ( $status === 'pending' ) : ?>
                                    <span style="color:#f59e0b;"><?php echo esc_html( glass_t('st_pending') ); ?></span>
                                <?php elseif ( $status === 'draft' ) : ?>
                                    <span style="color:#94a3b8;"><?php echo esc_html( glass_t('st_draft') ); ?></span>
                                <?php elseif ( $state === 'active' && $days_left > 0 ) : ?>
                                    <span><?php printf( esc_html( glass_t('st_days_left') ), esc_html( $days_left ) ); ?></span>
                                <?php elseif ( $state === 'expired' ) : ?>
                                    <span style="color:#f59e0b;"><?php echo esc_html( glass_t('st_is_expired') ); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- دکمه‌های اکشن -->
                            <div class="glass-dash-actions">

                                <?php if ( $status === 'publish' ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="glass-dash-btn glass-dash-btn--view" target="_blank"><?php echo esc_html( glass_t('act_view') ); ?></a>
                                <?php endif; ?>

                                <!-- تمدید: فقط برای آگهی‌های کاربری (آگهی مدیر انقضا ندارد) -->
                                <?php if ( $is_user_ad && in_array( $state, [ 'active', 'expired' ], true ) && $status === 'publish' ) : ?>
                                    <form method="post" class="glass-dash-inline" onsubmit="return confirm('<?php echo esc_js( $renew_pay ? glass_t('cf_renew_pay') : glass_t('cf_renew_free') ); ?>');">
                                        <?php wp_nonce_field( 'glass_ad_action', 'glass_ad_action_nonce' ); ?>
                                        <input type="hidden" name="glass_ad_id" value="<?php echo esc_attr( $pid ); ?>">
                                        <input type="hidden" name="glass_ad_action" value="renew">
                                        <button type="submit" class="glass-dash-btn glass-dash-btn--renew">
                                            <?php echo esc_html( glass_t('act_renew') ); ?><?php echo $renew_pay ? ' (' . esc_html( $renew_amount ) . ')' : ''; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ( $feature_enabled && $is_user_ad && ! $is_featured && $status === 'publish' ) : ?>
                                    <form method="post" class="glass-dash-inline" onsubmit="return confirm('<?php echo esc_js( sprintf( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('feature_confirm') : __( 'آگهی با پرداخت %s تومان ویژه شود؟', 'glassmorphism-child-pro' ), function_exists('glass_ui_money') ? glass_ui_money( absint( str_replace(',', '', $feature_amount ) ) ) : $feature_amount ) ); ?>');">
                                        <?php wp_nonce_field( 'glass_ad_action', 'glass_ad_action_nonce' ); ?>
                                        <input type="hidden" name="glass_ad_id" value="<?php echo esc_attr( $pid ); ?>">
                                        <input type="hidden" name="glass_ad_action" value="feature">
                                        <button type="submit" class="glass-dash-btn glass-dash-btn--feature">⭐ <?php esc_html_e( 'ویژه کردن', 'glassmorphism-child-pro' ); ?><?php echo $feature_amount ? ' (' . esc_html( $feature_amount ) . ')' : ''; ?></button>
                                    </form>
                                <?php endif; ?>

                                <!-- فروخته شد / بازگردانی (فقط آگهی کاربری) -->
                                <?php if ( $is_user_ad && $state === 'active' && $status === 'publish' ) : ?>
                                    <form method="post" class="glass-dash-inline" onsubmit="return confirm('<?php echo esc_js( glass_t('cf_sold') ); ?>');">
                                        <?php wp_nonce_field( 'glass_ad_action', 'glass_ad_action_nonce' ); ?>
                                        <input type="hidden" name="glass_ad_id" value="<?php echo esc_attr( $pid ); ?>">
                                        <input type="hidden" name="glass_ad_action" value="mark_sold">
                                        <button type="submit" class="glass-dash-btn glass-dash-btn--sold"><?php echo esc_html( glass_t('act_sold') ); ?></button>
                                    </form>
                                <?php elseif ( $state === 'sold' ) : ?>
                                    <form method="post" class="glass-dash-inline">
                                        <?php wp_nonce_field( 'glass_ad_action', 'glass_ad_action_nonce' ); ?>
                                        <input type="hidden" name="glass_ad_id" value="<?php echo esc_attr( $pid ); ?>">
                                        <input type="hidden" name="glass_ad_action" value="unmark_sold">
                                        <button type="submit" class="glass-dash-btn glass-dash-btn--restore"><?php echo esc_html( glass_t('act_restore') ); ?></button>
                                    </form>
                                <?php endif; ?>

                                <!-- ویرایش (لینک به پیشخوان) -->
                                <?php $front_edit_url = add_query_arg( [ 'glass_edit_ad' => $pid, 'glass_edit_nonce' => wp_create_nonce( 'glass_edit_ad_' . $pid ) ], function_exists( 'glass_get_submit_url' ) ? glass_get_submit_url() : home_url( '/ثبت-آگهی/' ) ); ?>
                                <a href="<?php echo esc_url( $front_edit_url ); ?>" class="glass-dash-btn glass-dash-btn--edit"><?php echo esc_html( glass_t('act_edit') ); ?></a>

                                <!-- حذف -->
                                <form method="post" class="glass-dash-inline" onsubmit="return confirm('<?php echo esc_js( glass_t('cf_delete') ); ?>');">
                                    <?php wp_nonce_field( 'glass_ad_action', 'glass_ad_action_nonce' ); ?>
                                    <input type="hidden" name="glass_ad_id" value="<?php echo esc_attr( $pid ); ?>">
                                    <input type="hidden" name="glass_ad_action" value="delete">
                                    <button type="submit" class="glass-dash-btn glass-dash-btn--delete"><?php echo esc_html( glass_t('act_delete') ); ?></button>
                                </form>

                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

