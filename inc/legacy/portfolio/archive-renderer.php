<?php
/**
 * Portfolio — Archive Page Renderer
 *
 * این فایل بخشی از تقسیم functions-portfolio.php است.
 * شامل تابع replywp_render_portfolio_page() که صفحه آرشیو پورتفولیو را رندر می‌کند.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ════════════════════════════════════════
   رندر صفحه
════════════════════════════════════════ */

if (!function_exists('replywp_render_portfolio_page')) {
    function replywp_render_portfolio_page() {

        get_header();

        $page_title       = function_exists('glass_ui_misc_t') ? glass_ui_misc_t('used_ads') : 'دست دوم';
        $city_button_text = replywp_get_city_button_text();

        if (is_tax('themsah_theme_type')) {
            $t = get_queried_object();
            if ($t && !is_wp_error($t)) {
                $page_title = replywp_clean_plain_text($t->name);
            }
        }

        if (is_tax('portfolio_city')) {
            $t = get_queried_object();
            if ($t && !is_wp_error($t)) {
                $page_title = sprintf( function_exists('glass_ui_misc_t') ? glass_ui_misc_t('used_ads_in') : 'دست دوم در %s', replywp_clean_plain_text($t->name) );
            }
        }
        ?>

        <style>
        :root{
            --fl-primary:#2D5F93;
            --fl-primary-dark:#1B4A73;
            --fl-accent:#A4B400;
            --fl-accent-hover:#7C8B00;
            --fl-text:#17212B;
            --fl-text-light:#64748B;
            --fl-white:#FFFFFF;
            --fl-glass-bg:#ffffff;
            --fl-glass-border:rgba(0,0,0,0.07);
            --fl-glass-blur:0px;
            --fl-speed:.35s;
            --fl-ease:cubic-bezier(.4,0,.2,1);
            --fl-city-btn-top:88px;
        }

        /* ══ PAGE ══ */
        .fl-pf-page{
            direction:rtl;
            max-width:1320px;
            margin:0 auto;
            padding:30px 20px;
            display:flex;
            flex-direction:row;
            flex-wrap:nowrap;
            gap:28px;
            align-items:stretch;
        }

        /* ══ SIDEBAR ══ */
        .fl-pf-sidebar{
            width:280px;
            min-width:280px;
            flex-shrink:0;
            position:sticky;
            top:100px;
            align-self:start;
            height:fit-content;
        }
        .fl-pf-main{
            flex:1;
            min-width:0;
            align-self:start;
        }

        .fl-pf-panel{
            background:#ffffff;
            border:1px solid rgba(0,0,0,0.07);
            border-radius:14px;
            box-shadow:0 12px 40px rgba(0,0,0,.06);
            overflow:hidden;
        }

        .fl-pf-sidebar-head{
            display:flex;
            align-items:center;
            gap:10px;
            padding:16px 20px;
            border-bottom:1px solid rgba(0,0,0,0.07);
            background:linear-gradient(135deg,var(--fl-primary),var(--fl-primary-dark));
            color:var(--fl-white);
            font-size:.92rem;
            font-weight:700;
        }

        .fl-pf-sidebar-head svg{
            width:18px;
            height:18px;
            stroke:var(--fl-white);
            fill:none;
            stroke-width:2;
            flex-shrink:0;
        }

        /* ══ CITY LIST ══ */
        .fl-city-list{
            list-style:none;
            margin:0;
            padding:8px 0;
            max-height:420px;
            overflow-y:auto;
            scrollbar-width:thin;
            scrollbar-color:var(--fl-accent) transparent;
        }

        .fl-city-list::-webkit-scrollbar{width:4px}
        .fl-city-list::-webkit-scrollbar-thumb{background:var(--fl-accent);border-radius:4px}
        .fl-city-list li{margin:0;padding:0;list-style:none}

        .fl-city-list li a{
            display:flex;
            align-items:center;
            gap:8px;
            padding:10px 20px;
            color:var(--fl-text);
            text-decoration:none;
            font-size:.84rem;
            font-weight:600;
            transition:all var(--fl-speed) var(--fl-ease);
            border-right:3px solid transparent;
        }

        .fl-city-list li a::before{
            content:'';
            width:6px;
            height:6px;
            border-radius:50%;
            background:var(--fl-accent);
            flex-shrink:0;
            opacity:.5;
            transition:all var(--fl-speed) var(--fl-ease);
        }

        .fl-city-list li a:hover{
            background:rgba(45,95,147,.06);
            color:var(--fl-primary);
            border-right-color:var(--fl-accent);
        }

        .fl-city-list li a:hover::before{opacity:1;transform:scale(1.4)}

        .fl-city-list li.current-menu-item a,
        .fl-city-list li.current_page_item a{
            background:rgba(45,95,147,.08);
            color:#17212b;
            font-weight:700;
            border-right-color:var(--fl-primary);
        }

        .fl-city-list li.current-menu-item a::before,
        .fl-city-list li.current_page_item a::before{
            background:var(--fl-primary);
            opacity:1;
        }

        .fl-city-list .sub-menu{list-style:none;margin:0;padding:0 12px 0 0}
        .fl-city-list .sub-menu li a{padding-right:36px;font-size:.8rem;font-weight:500;color:#334155}

        /* ══ MAIN ══ */
        .fl-pf-main{flex:1;min-width:0}

        .fl-pf-title{
            font-size:1.15rem;
            font-weight:700;
            color:var(--fl-primary-dark);
            margin:0 0 24px;
            padding-bottom:14px;
            border-bottom:2px solid var(--fl-accent);
            display:inline-block;
        }

        /* ══ SUBMIT BANNER ══ */
        .fl-pf-submit-banner{
            background:#ffffff;
            border:1px solid rgba(0,0,0,.08);
            border-radius:14px;
            padding:14px 18px;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            flex-wrap:wrap;
            box-shadow:0 1px 3px rgba(0,0,0,.04);
        }
        .fl-pf-submit-banner-title{
            margin:0 0 4px 0;
            font-size:.92rem;
            font-weight:700;
            color:var(--fl-text);
        }
        .fl-pf-submit-banner-desc{
            margin:0;
            font-size:.78rem;
            color:var(--fl-text-light);
        }
        .fl-pf-submit-banner-btn{
            text-decoration:none;
            padding:9px 18px;
            border-radius:10px;
            background:var(--fl-primary);
            color:#fff;
            font-weight:700;
            font-size:.84rem;
            box-shadow:0 4px 12px rgba(45,95,147,0.2);
            display:inline-flex;
            align-items:center;
            gap:7px;
            transition:all 0.25s ease;
            white-space:nowrap;
        }
        .fl-pf-submit-banner-btn:hover{
            background:var(--fl-primary-dark);
            transform:translateY(-2px);
            color:#fff;
        }
        .dark-mode .fl-pf-submit-banner{
            background:#162B3F;
            border-color:rgba(255,255,255,.08);
        }
        .dark-mode .fl-pf-submit-banner-title{
            color:#f1f5f9;
        }
        .dark-mode .fl-pf-submit-banner-desc{
            color:#94a3b8;
        }

        /* ══ GRID - 4 ستون دسکتاپ ══ */
        .fl-pf-grid{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
        }

        /* ══ CARD ══ */
        .fl-pf-card{
            background:#ffffff;
            border:1px solid rgba(0,0,0,0.07);
            border-radius:18px;
            box-shadow:0 8px 30px rgba(0,0,0,.05);
            overflow:hidden;
            transition:all var(--fl-speed) var(--fl-ease);
            display:flex;
            flex-direction:column;
        }

        .fl-pf-card:hover{
            transform:translateY(-4px);
            box-shadow:0 16px 48px rgba(0,0,0,.1);
        }

        .fl-pf-thumb{position:relative;overflow:hidden;aspect-ratio:16/10}

        .fl-pf-thumb img{
            width:100%;height:100%;
            object-fit:cover;
            transition:transform .5s var(--fl-ease);
        }

        .fl-pf-card:hover .fl-pf-thumb img{transform:scale(1.06)}

        .fl-pf-thumb-empty{
            width:100%;height:100%;
            min-height:140px;
            display:flex;align-items:center;justify-content:center;
            background:linear-gradient(135deg,rgba(45,95,147,.08),rgba(164,180,0,.08));
            color:#334155;font-size:.78rem;
        }

        .fl-pf-badge{
            position:absolute;top:10px;right:10px;
            display:flex;align-items:center;gap:4px;
            padding:4px 10px;border-radius:14px;
            background:rgba(0,0,0,.55);
            color:#17212b;font-size:.7rem;font-weight:600;
        }

        .fl-pf-badge svg{width:11px;height:11px;stroke:var(--fl-accent);fill:none;stroke-width:2}

        .fl-pf-body{padding:14px 16px;flex:1;display:flex;flex-direction:column}

        .fl-pf-card-title{
            font-size:.88rem;font-weight:700;color:var(--fl-text);
            margin:0 0 8px;line-height:1.6;
        }

        .fl-pf-card-title a{
            color:#17212b;text-decoration:none;
            transition:color var(--fl-speed) var(--fl-ease);
        }

        .fl-pf-card-title a:hover{color:var(--fl-primary)}

        .fl-pf-excerpt{
            font-size:.78rem;color:#334155;line-height:1.8;
            margin:0 0 14px;flex:1;
            display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
        }

        .fl-pf-footer{
            display:flex;align-items:center;justify-content:center;
            padding-top:12px;border-top:1px solid rgba(0,0,0,.06);
        }

        .fl-pf-more{
            display:inline-flex;align-items:center;gap:4px;
            padding:6px 20px;border-radius:14px;
            background:linear-gradient(135deg,var(--fl-accent),var(--fl-accent-hover));
            color:#17212b;text-decoration:none;
            font-size:.76rem;font-weight:400;
            transition:all var(--fl-speed) var(--fl-ease);
        }

        .fl-pf-more:hover{
            transform:scale(1.05);
            box-shadow:0 4px 16px rgba(164,180,0,.3);
            color:#17212b;
        }

        .fl-pf-more svg{width:12px;height:12px;stroke:var(--fl-white);fill:none;stroke-width:2.5}
        .fl-pf-more,
        .fl-pf-more-label{font-weight:400 !important}

        /* ══ PAGINATION ══ */
        .fl-pf-pagi{
            margin-top:36px;display:flex;justify-content:center;gap:6px;flex-wrap:wrap;
        }

        .fl-pf-pagi a,
        .fl-pf-pagi span{
            display:inline-flex;align-items:center;justify-content:center;
            min-width:40px;height:40px;padding:0 12px;border-radius:12px;
            font-size:.84rem;font-weight:400;text-decoration:none;
            transition:all var(--fl-speed) var(--fl-ease);
        }

        .fl-pf-pagi a{
            background:#ffffff;border:1px solid rgba(0,0,0,0.07);
        }

        .fl-pf-pagi a:hover{background:var(--fl-primary);color:var(--fl-white);border-color:var(--fl-primary)}

        .fl-pf-pagi .current{
            background:linear-gradient(135deg,var(--fl-primary),var(--fl-primary-dark));
            color:var(--fl-white);border:1px solid var(--fl-primary);
        }

        /* ══ EMPTY ══ */
        .fl-pf-empty{text-align:center;padding:60px 20px;color:#334155;font-size:.92rem}
        .fl-pf-empty svg{width:60px;height:60px;stroke:var(--fl-text-light);fill:none;stroke-width:1.2;margin-bottom:16px;opacity:.4}

        /* ══ MOBILE CITY BUTTON ══ */
        .fl-pf-mob-btn{
            display:none;
            position:fixed;
            top:var(--fl-city-btn-top, 88px);
            right:14px;
            z-index:99995;
            height:44px;
            padding:0 18px;
            border-radius:22px;
            border:1px solid rgba(0,0,0,0.07);
            cursor:pointer;
            background:#ffffff;
            box-shadow:0 6px 24px rgba(0,0,0,.08);
            transition:
                top .28s var(--fl-ease),
                transform var(--fl-speed) var(--fl-ease),
                background var(--fl-speed) var(--fl-ease),
                box-shadow var(--fl-speed) var(--fl-ease);
            align-items:center;
            justify-content:center;
            gap:8px;
            color:var(--fl-primary-dark);
            font-size:.84rem;
            font-weight:700;
            font-family:inherit;
            direction:rtl;
            white-space:nowrap;
        }

        .fl-pf-mob-btn svg{width:16px;height:16px;stroke:var(--fl-primary);fill:none;stroke-width:2;flex-shrink:0}
        .fl-pf-mob-btn:active{transform:scale(.95)}

        /* ══ OVERLAY ══ */
        .fl-pf-overlay{
            display:none;position:fixed;inset:0;z-index:99996;
            background:rgba(0,0,0,.4);
            opacity:0;transition:opacity .4s var(--fl-ease);pointer-events:none;
        }

        .fl-pf-overlay.fl-on{display:block;opacity:1;pointer-events:auto}

        /* ══ DRAWER ══ */
        .fl-pf-drawer{
            position:fixed;bottom:0;right:0;left:0;z-index:99997;
            max-height:78vh;
            background:#ffffff;
            border:1px solid rgba(0,0,0,0.07);
            border-bottom:none;
            border-radius:28px 28px 0 0;
            box-shadow:
                0 -16px 60px rgba(0,0,0,.12),
                0 -4px 20px rgba(0,0,0,.06),
;
            transform:translateY(100%);
            visibility:hidden;
            transition:
                transform .45s cubic-bezier(.32,.72,.32,1),
                visibility .45s cubic-bezier(.32,.72,.32,1);
            overflow:hidden;
            display:flex;
            flex-direction:column;
        }

        .fl-pf-drawer.fl-on{transform:translateY(0);visibility:visible}

        .fl-pf-drawer-bar{
            display:flex;align-items:center;justify-content:center;
            padding:14px 0 6px;flex-shrink:0;
        }

        .fl-pf-drawer-bar span{
            width:42px;height:5px;border-radius:5px;background:rgba(0,0,0,.12);
        }

        .fl-pf-drawer-head{
            display:flex;align-items:center;justify-content:space-between;
            padding:12px 22px 14px;
            border-bottom:1px solid rgba(0,0,0,0.07);
            flex-shrink:0;
            background:#f1f5f9;
        }

        .fl-pf-drawer-title{
            display:flex;align-items:center;gap:8px;
            font-size:.95rem;font-weight:700;color:var(--fl-primary-dark);
        }

        .fl-pf-drawer-title svg{width:18px;height:18px;stroke:var(--fl-primary);fill:none;stroke-width:2}

        /* CLOSE BUTTON */
        .fl-pf-drawer-x{
            width:44px;height:44px;border-radius:14px;border:none;cursor:pointer;
            background:linear-gradient(135deg, var(--fl-primary), var(--fl-primary-dark));
            display:flex;align-items:center;justify-content:center;
            transition:all var(--fl-speed) var(--fl-ease);
            box-shadow:0 4px 14px rgba(45,95,147,.18);
            flex-shrink:0;
        }

        .fl-pf-drawer-x:hover{opacity:.85;transform:scale(1.05)}

        .fl-pf-drawer-x svg{
            width:22px;height:22px;
            stroke:var(--fl-white);fill:none;stroke-width:2.5;
        }

        /* SEARCH */
        .fl-pf-drawer-search-wrap{padding:12px 14px 8px;flex-shrink:0}

        .fl-pf-drawer-search{
            display:flex;align-items:center;gap:10px;
            padding:12px 14px;border-radius:16px;
            background:#ffffff;
            border:1px solid rgba(0,0,0,0.07);
            box-shadow:0 4px 16px rgba(0,0,0,.04);
            transition:all var(--fl-speed) var(--fl-ease);
        }

        .fl-pf-drawer-search:focus-within{
            border-color:rgba(164,180,0,.35);
            background:#ffffff;
            box-shadow:0 4px 20px rgba(164,180,0,.08);
        }

        .fl-pf-drawer-search svg{width:16px;height:16px;stroke:var(--fl-text-light);fill:none;stroke-width:2;flex-shrink:0}

        .fl-pf-drawer-search input{
            width:100%;
            border:none !important;outline:none !important;
            background:none !important;box-shadow:none !important;
            margin:0 !important;padding:0 !important;
            font-family:inherit;font-size:.88rem;color:var(--fl-text);
        }

        .fl-pf-drawer-search input::placeholder{color:var(--fl-text-light)}

        /* DRAWER LIST */
        .fl-drawer-list{
            list-style:none;margin:0;
            padding:10px 8px 28px;
            max-height:calc(78vh - 180px);
            overflow-y:auto;-webkit-overflow-scrolling:touch;
            overscroll-behavior:contain;
            display:flex;flex-direction:column;gap:4px;
        }

        .fl-drawer-list li{
            margin:0;padding:0;list-style:none;
            opacity:0;transform:translateY(20px);
            transition:opacity .35s var(--fl-ease), transform .35s var(--fl-ease);
        }

        .fl-pf-drawer.fl-on .fl-drawer-list li{opacity:1;transform:translateY(0)}

        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(1){transition-delay:.06s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(2){transition-delay:.10s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(3){transition-delay:.14s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(4){transition-delay:.18s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(5){transition-delay:.22s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(6){transition-delay:.26s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(7){transition-delay:.30s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(8){transition-delay:.34s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(9){transition-delay:.38s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(10){transition-delay:.42s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(11){transition-delay:.46s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(12){transition-delay:.50s}
        .fl-pf-drawer.fl-on .fl-drawer-list li:nth-child(n+13){transition-delay:.54s}

        .fl-drawer-list li a{
            display:flex;align-items:center;gap:10px;
            padding:13px 20px;
            color:var(--fl-text);text-decoration:none;
            font-size:.88rem;font-weight:600;
            transition:all var(--fl-speed) var(--fl-ease);
            border-radius:14px;border-right:3px solid transparent;
        }

        .fl-drawer-list li a::before{
            content:'';width:8px;height:8px;border-radius:50%;
            background:var(--fl-accent);flex-shrink:0;opacity:.4;
            transition:all var(--fl-speed) var(--fl-ease);
        }

        .fl-drawer-list li a:hover,
        .fl-drawer-list li a:active{
            background:#ffffff;
            color:var(--fl-primary);border-right-color:var(--fl-accent);
        }

        .fl-drawer-list li a:hover::before,
        .fl-drawer-list li a:active::before{opacity:1;transform:scale(1.3)}

        .fl-drawer-list li.current-menu-item a{
            background:#ffffff;
            box-shadow:0 2px 10px rgba(0,0,0,.04);
            color:var(--fl-primary-dark);font-weight:700;border-right-color:var(--fl-primary);
        }

        .fl-drawer-list li.current-menu-item a::before{background:var(--fl-primary);opacity:1}

        .fl-drawer-list .sub-menu{list-style:none;margin:0;padding:0 14px 0 0}
        .fl-drawer-list .sub-menu li a{padding-right:40px;font-size:.82rem;font-weight:500;color:var(--fl-text-light)}
        .fl-drawer-list .sub-menu li a::before{width:5px;height:5px}

        .fl-pf-drawer-empty-search{
            display:none;text-align:center;
            padding:18px 16px 26px;
            color:var(--fl-text-light);font-size:.82rem;
        }

        .fl-pf-drawer-empty-search.show{display:block}

        /* ══ RESPONSIVE ══ */
        @media(max-width:1200px){
            .fl-pf-grid{grid-template-columns:repeat(3,1fr);gap:18px}
        }

        @media(max-width:1024px){
            .fl-pf-grid{grid-template-columns:repeat(2,1fr);gap:18px}
            .fl-pf-sidebar{width:240px;min-width:240px}
        }

        /* ══ TABLET / MOBILE ══ */
        @media(max-width:768px){
            .fl-pf-page{
                flex-direction:column;
                padding:20px 14px;
                gap:20px;
            }

            .fl-pf-sidebar{display:none}
            .fl-pf-mob-btn{display:flex}

            /* ══ همیشه 2 ستون در موبایل ══ */
            .fl-pf-grid{
                grid-template-columns:repeat(2, minmax(0, 1fr));
                gap:12px;
            }

            .fl-pf-title{font-size:1.2rem}

            .fl-pf-body{padding:12px}

            .fl-pf-card-title{
                font-size:.8rem;
                line-height:1.6;
                margin:0 0 6px;
            }

            .fl-pf-excerpt{
                font-size:.72rem;
                line-height:1.7;
                margin:0 0 10px;
                -webkit-line-clamp:2;
            }

            .fl-pf-footer{padding-top:10px}

            .fl-pf-more{
                padding:5px 14px;
                font-size:.72rem;
            }

            .fl-pf-badge{
                top:8px;right:8px;
                font-size:.62rem;
                padding:3px 8px;
            }

            .fl-pf-badge svg{width:9px;height:9px}
        }

        /* ══ موبایل کوچک - همچنان 2 ستون ══ */
        @media(max-width:480px){
            .fl-pf-page{padding:14px 10px}

            .fl-pf-grid{
                grid-template-columns:repeat(2, minmax(0, 1fr));
                gap:10px;
            }

            .fl-pf-thumb{
                aspect-ratio:1/1;
            }

            .fl-pf-body{padding:10px}

            .fl-pf-card-title{
                font-size:.74rem;
                margin:0 0 5px;
            }

            .fl-pf-excerpt{
                font-size:.66rem;
                margin:0 0 8px;
            }

            .fl-pf-footer{padding-top:8px}

            .fl-pf-more{
                padding:5px 12px;
                font-size:.68rem;
            }
        }

        /* ══ خیلی کوچک ══ */
        @media(max-width:340px){
            .fl-pf-grid{
                grid-template-columns:repeat(2, minmax(0, 1fr));
                gap:8px;
            }

            .fl-pf-page{padding:12px 8px}

            .fl-pf-card-title{font-size:.7rem}
            .fl-pf-more{padding:4px 10px;font-size:.64rem}
        }

        /* ══ DARK MODE ══ */
        .dark-mode .fl-pf-panel{ background:#162B3F !important; border-color:rgba(255,255,255,.08) !important; }
        .dark-mode .fl-pf-sidebar-head{ background:linear-gradient(135deg,#2D5F93,#1B4A73) !important; }
        .dark-mode .fl-city-list li a{ color:#e2e8f0 !important; }
        .dark-mode .fl-city-list li a:hover{ background:#1F3D57 !important; color:#ffffff !important; }
        .dark-mode .fl-city-list li.current-menu-item a,
        .dark-mode .fl-city-list li.current_page_item a{ background:#1F3D57 !important; color:#ffffff !important; }
        .dark-mode .fl-city-list .sub-menu li a{ color:#94a3b8 !important; }
        .dark-mode .fl-pf-submit-banner{ background:#162B3F !important; border-color:rgba(255,255,255,.08) !important; }
        .dark-mode .fl-pf-submit-banner-title{ color:#f1f5f9; }
        .dark-mode .fl-pf-submit-banner-desc{ color:#94a3b8; }
        .dark-mode .fl-pf-submit-banner-btn{ color:#ffffff !important; }
        .dark-mode .fl-pf-card{ background:#162B3F !important; border-color:rgba(255,255,255,.08) !important; }
        .dark-mode .fl-pf-card-title a{ color:#e2e8f0 !important; }
        .dark-mode .fl-pf-excerpt{ color:#94a3b8; }
        .dark-mode .fl-pf-more{ color:#0F1724 !important; }
        .dark-mode .fl-pf-pagi a{ background:#162B3F !important; border-color:rgba(255,255,255,.08) !important; color:#e2e8f0 !important; }
        .dark-mode .fl-pf-pagi a:hover{ background:#2D5F93 !important; color:#ffffff !important; }
        .dark-mode .fl-pf-pagi .current{ background:#2D5F93 !important; color:#ffffff !important; }
        .dark-mode .fl-pf-empty{ color:#94a3b8; }
        .dark-mode .fl-pf-mob-btn{ background:#162B3F !important; border-color:rgba(255,255,255,.08) !important; color:#e2e8f0 !important; }
        .dark-mode .fl-pf-mob-btn:hover{ background:#1F3D57 !important; }
        .dark-mode .fl-pf-overlay{ background:rgba(0,0,0,.6) !important; }
        .dark-mode .fl-pf-drawer{ background:#162B3F !important; border-color:rgba(255,255,255,.08) !important; }
        .dark-mode .fl-pf-drawer-bar span{ background:rgba(255,255,255,.2) !important; }
        .dark-mode .fl-pf-drawer-head{ background:#162B3F !important; border-bottom-color:rgba(255,255,255,.08) !important; }
        .dark-mode .fl-pf-drawer-title{ color:#e2e8f0 !important; }
        .dark-mode .fl-pf-drawer-title svg{ stroke:#A4B400 !important; }
        .dark-mode .fl-pf-drawer-x{ background:#2D5F93 !important; }
        .dark-mode .fl-pf-drawer-x:hover{ background:#1B4A73 !important; }
        .dark-mode .fl-pf-drawer-search{ background:#0F1724 !important; border-color:rgba(255,255,255,.08) !important; }
        .dark-mode .fl-pf-drawer-search:focus-within{ background:#0F1724 !important; }
        .dark-mode .fl-pf-drawer-search input{ color:#e2e8f0 !important; }
        .dark-mode .fl-pf-drawer-search input::placeholder{ color:#64748B; }
        .dark-mode .fl-pf-drawer-search svg{ stroke:#7FB3E8 !important; }
        .dark-mode .fl-drawer-list li a{ color:#e2e8f0 !important; }
        .dark-mode .fl-drawer-list li a:hover{ background:#1F3D57 !important; color:#ffffff !important; border-right-color:#A4B400 !important; }
        .dark-mode .fl-drawer-list li.current-menu-item a{ background:#1F3D57 !important; color:#ffffff !important; border-right-color:#2D5F93 !important; }
        .dark-mode .fl-drawer-list .sub-menu li a{ color:#94a3b8 !important; }
        .dark-mode .fl-pf-drawer-empty-search{ color:#94a3b8 !important; }
        </style>

        <div class="fl-pf-page">

            <aside class="fl-pf-sidebar">
                <div class="fl-pf-panel">
                    <div class="fl-pf-sidebar-head">
                        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>انتخاب شهر</span>
                    </div>
                    <?php replywp_render_city_menu('fl-city-list'); ?>
                </div>
            </aside>

            <main class="fl-pf-main">

                <h1 class="fl-pf-title"><?php echo esc_html($page_title); ?></h1>

                <!-- بنر ثبت آگهی جدید دست‌دوم -->
                    <div class="fl-pf-submit-banner">
                        <div>
                            <h3 class="fl-pf-submit-banner-title"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('have_used_equipment') : __( 'آیا تجهیزات دست‌دوم برای فروش دارید؟', 'glassmorphism-child-pro' ) ); ?></h3>
                            <p class="fl-pf-submit-banner-desc"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('submit_free_desc') : __( 'آگهی خود را به صورت کاملاً رایگان در چند ثانیه ثبت کنید.', 'glassmorphism-child-pro' ) ); ?></p>
                        </div>
                        <a href="<?php echo esc_url( ( function_exists('glass_get_submit_url') ? glass_get_submit_url() : home_url('/ثبت-آگهی/') ) ); ?>" class="glass-btn-submit fl-pf-submit-banner-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('submit_used_ad') : __( 'ثبت آگهی دست‌دوم', 'glassmorphism-child-pro' ) ); ?>
                        </a>
                    </div>

                <?php if (have_posts()) : ?>

                    <div class="fl-pf-grid">
                        <?php while (have_posts()) : the_post();
                            $ad_price = absint( get_post_meta( get_the_ID(), 'portfolio_price', true ) );
                            $ad_old_price = absint( get_post_meta( get_the_ID(), 'portfolio_old_price', true ) );
                            $ad_discount = min( 99, absint( get_post_meta( get_the_ID(), 'portfolio_discount_percent', true ) ) );
                            $ad_is_featured = ( (int) get_post_meta( get_the_ID(), 'portfolio_featured_until', true ) ) > time();
                        ?>

                            <article class="fl-pf-card <?php echo esc_attr( $ad_is_featured ? 'fl-pf-card--featured' : '' ); ?>">
                                <div class="fl-pf-thumb">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium_large', array('loading' => 'lazy')); ?>
                                        </a>
                                    <?php else : ?>
                                        <div class="fl-pf-thumb-empty"><span>بدون تصویر</span></div>
                                    <?php endif; ?>

                                    <?php
                                    $ct = get_the_terms(get_the_ID(), 'portfolio_city');
                                    if (!empty($ct) && !is_wp_error($ct)) :
                                    ?>
                                        <span class="fl-pf-badge">
                                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <?php echo esc_html($ct[0]->name); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="fl-pf-body">
                                    <p class="fl-pf-card-title" style="font-weight:700;">
                                        <a href="<?php the_permalink(); ?>"><?php echo esc_html(replywp_clean_plain_text(get_the_title())); ?></a>
                                    </p>

                                    <?php if ( $ad_is_featured ) : ?><div style="display:inline-flex;margin-bottom:8px;background:#f59e0b;color:#fff;border-radius:999px;padding:3px 9px;font-size:.68rem;font-weight:700;">⭐ <?php esc_html_e( 'ویژه', 'glassmorphism-child-pro' ); ?></div><?php endif; ?>

                                    <?php if (has_excerpt()) : ?>
                                        <p class="fl-pf-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '...')); ?></p>
                                    <?php endif; ?>

                                    <div class="fl-pf-price" style="margin:8px 0;font-size:.82rem;font-weight:700;color:var(--fl-primary);">
                                        <?php if ( $ad_discount > 0 ) : ?><span style="background:#ef4444;color:#fff;border-radius:999px;padding:2px 7px;margin-left:6px;font-size:.68rem;"><?php echo esc_html( $ad_discount ); ?>٪</span><?php endif; ?>
                                        <?php if ( $ad_old_price > 0 ) : ?><del style="color:var(--fl-text-light);font-weight:400;margin-left:6px;"><?php echo esc_html( number_format_i18n( $ad_old_price ) ); ?></del><?php endif; ?>
                                        <?php echo $ad_price > 0 ? esc_html( function_exists('glass_ui_money') ? glass_ui_money( $ad_price ) : number_format_i18n( $ad_price ) ) : esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('negotiable') : __( 'توافقی', 'glassmorphism-child-pro' ) ); ?>
                                    </div>

                                    <div class="fl-pf-footer">
                                                                                <?php
                                        $fl_view_title = function_exists('replywp_clean_plain_text') ? replywp_clean_plain_text(get_the_title()) : get_the_title();
                                        $fl_view_label = function_exists( 'glass_ui_misc_t' ) ? glass_ui_misc_t( 'view_ad' ) : __( 'مشاهده آگهی', 'glassmorphism-child-pro' );
                                        ?>
                                        <a href="<?php the_permalink(); ?>" class="fl-pf-more" aria-label="<?php echo esc_attr( sprintf( __( 'مشاهده آگهی: %s', 'glassmorphism-child-pro' ), $fl_view_title ) ); ?>" title="<?php echo esc_attr( $fl_view_title ); ?>">
                                            <span class="fl-pf-more-label"><?php echo esc_html( $fl_view_label ); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </article>

                        <?php endwhile; ?>
                    </div>

                    <div class="fl-pf-pagi">
                        <?php echo paginate_links(array('prev_text' => '→', 'next_text' => '←')); ?>
                    </div>

                <?php else : ?>

                    <div class="fl-pf-empty">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/><line x1="8" y1="15" x2="16" y2="15" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <p>موردی یافت نشد.</p>
                    </div>

                <?php endif; ?>

            </main>
        </div>

        <!-- MOBILE BUTTON -->
        <button class="fl-pf-mob-btn" aria-label="<?php echo esc_attr($city_button_text); ?>">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php echo esc_html($city_button_text); ?>
        </button>

        <!-- OVERLAY -->
        <div class="fl-pf-overlay"></div>

        <!-- DRAWER -->
        <div class="fl-pf-drawer">
            <div class="fl-pf-drawer-bar"><span></span></div>

            <div class="fl-pf-drawer-head">
                <div class="fl-pf-drawer-title">
                    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>انتخاب شهر</span>
                </div>
                <button class="fl-pf-drawer-x" aria-label="بستن">
                    <svg viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="fl-pf-drawer-search-wrap">
                <div class="fl-pf-drawer-search">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-linecap="round" stroke-linejoin="round"/><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <input type="text" class="fl-pf-city-search-input" placeholder="جستجوی شهر..." autocomplete="off">
                </div>
            </div>

            <?php replywp_render_city_menu('fl-drawer-list'); ?>

            <div class="fl-pf-drawer-empty-search">شهری با این نام پیدا نشد.</div>
        </div>

        <!-- JS -->
        <script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
        (function(){
            document.addEventListener('DOMContentLoaded', function(){

                var btn         = document.querySelector('.fl-pf-mob-btn');
                var drawer      = document.querySelector('.fl-pf-drawer');
                var overlay     = document.querySelector('.fl-pf-overlay');
                var closeX      = document.querySelector('.fl-pf-drawer-x');
                var searchInput = document.querySelector('.fl-pf-city-search-input');
                var drawerList  = document.querySelector('.fl-drawer-list');
                var emptySearch = document.querySelector('.fl-pf-drawer-empty-search');

                var headerGroup = document.getElementById('flHeaderGroup');
                var headerMain  = document.getElementById('flHeader');
                var infoBar     = headerGroup ? headerGroup.querySelector('.fl-info') : null;

                if (!btn) return;

                /* ═══ BUTTON POSITION ═══ */
                function updateBtnPos() {
                    if (window.innerWidth > 768) {
                        document.documentElement.style.removeProperty('--fl-city-btn-top');
                        return;
                    }

                    var gap = 10;
                    var top = 88;

                    if (headerGroup) {
                        var isStuck  = headerGroup.classList.contains('stuck');
                        var isHidden = headerMain && headerMain.classList.contains('h-hidden');

                        if (isStuck && isHidden) {
                            if (infoBar) {
                                top = Math.round(infoBar.getBoundingClientRect().bottom + gap);
                            } else {
                                top = 52 + gap;
                            }
                        } else {
                            top = Math.round(headerGroup.getBoundingClientRect().bottom + gap);
                        }
                    }

                    if (top < 50) top = 50;
                    if (top > 160) top = 160;

                    document.documentElement.style.setProperty('--fl-city-btn-top', top + 'px');
                }

                var raf = null;
                function reqUpdate() {
                    if (raf) cancelAnimationFrame(raf);
                    raf = requestAnimationFrame(updateBtnPos);
                }

                /* ═══ CITY SEARCH ═══ */
                function resetCitySearch() {
                    if (!drawerList) return;
                    var items = drawerList.querySelectorAll('li');
                    items.forEach(function(li){ li.style.display = ''; });
                    if (searchInput) searchInput.value = '';
                    if (emptySearch) emptySearch.classList.remove('show');
                }

                function filterCityMenu(keyword) {
                    if (!drawerList) return;
                    var topItems = drawerList.children;
                    var visibleCount = 0;
                    var q = (keyword || '').trim().toLowerCase();

                    for (var i = 0; i < topItems.length; i++) {
                        var item = topItems[i];
                        if (!item || item.tagName.toLowerCase() !== 'li') continue;
                        var text = (item.textContent || '').trim().toLowerCase();

                        if (!q) {
                            item.style.display = '';
                            visibleCount++;
                        } else if (text.indexOf(q) !== -1) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    }

                    if (emptySearch) {
                        if (q && visibleCount === 0) {
                            emptySearch.classList.add('show');
                        } else {
                            emptySearch.classList.remove('show');
                        }
                    }
                }

                /* ═══ DRAWER ═══ */
                function openDrawer() {
                    if (!drawer || !overlay) return;
                    overlay.classList.add('fl-on');
                    drawer.classList.add('fl-on');
                    document.body.style.overflow = 'hidden';
                    document.documentElement.style.overflow = 'hidden';
                    setTimeout(function(){
                        if (searchInput) searchInput.focus();
                    }, 220);
                }

                function closeDrawer() {
                    if (drawer) drawer.classList.remove('fl-on');
                    if (overlay) overlay.classList.remove('fl-on');
                    document.body.style.overflow = '';
                    document.documentElement.style.overflow = '';
                    resetCitySearch();
                }

                /* ═══ INIT ═══ */
                updateBtnPos();
                setTimeout(updateBtnPos, 150);
                setTimeout(updateBtnPos, 500);

                window.addEventListener('load', updateBtnPos);
                window.addEventListener('resize', function(){
                    reqUpdate();
                    if (window.innerWidth > 768) closeDrawer();
                });
                window.addEventListener('scroll', reqUpdate, { passive: true });

                if (headerGroup) {
                    var mo = new MutationObserver(reqUpdate);
                    mo.observe(headerGroup, { attributes:true, attributeFilter:['class','style'] });
                    if (headerMain) mo.observe(headerMain, { attributes:true, attributeFilter:['class','style'] });
                    if (infoBar)    mo.observe(infoBar,    { attributes:true, attributeFilter:['class','style'] });
                }

                if (drawer && overlay) {
                    btn.addEventListener('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        openDrawer();
                    });

                    if (closeX) closeX.addEventListener('click', closeDrawer);
                    overlay.addEventListener('click', closeDrawer);

                    var sy = 0, cy = 0;
                    drawer.addEventListener('touchstart', function(e){ sy = e.touches[0].clientY; }, { passive:true });
                    drawer.addEventListener('touchmove',  function(e){ cy = e.touches[0].clientY; }, { passive:true });
                    drawer.addEventListener('touchend', function(){
                        if (cy - sy > 80) closeDrawer();
                        sy = 0; cy = 0;
                    });

                    document.addEventListener('keydown', function(e){
                        if (e.key === 'Escape') closeDrawer();
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', function(){
                        filterCityMenu(this.value);
                    });
                }

            });
        })();
        </script>

        <?php
        get_footer();
    }
}
