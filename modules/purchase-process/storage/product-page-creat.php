<?php
/**
 * عنوان نمایشی: ویرایشگر مدرن محصول
 * نام فایل: modern-product-editor
 * مسئولیت: مینیمال و مدرن‌سازی صفحه افزودن/ویرایش محصول ووکامرس + قابلیت‌های حرفه‌ای + محدودیت ارتفاع ویرایشگر توضیحات
 * وابستگی: WooCommerce
 * پیشوند: ezp_
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------ */
/* ۱. تشخیص صفحه محصول                                                */
/* ------------------------------------------------------------------ */

if ( ! function_exists( 'ezp_mpe_is_product_screen' ) ) {

	function ezp_mpe_is_product_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}
		if ( 'product' !== $screen->post_type ) {
			return false;
		}
		if ( ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			return false;
		}
		return true;
	}
}

/* ------------------------------------------------------------------ */
/* ۲. بارگذاری Assets                                                 */
/* ------------------------------------------------------------------ */

if ( ! function_exists( 'ezp_mpe_enqueue' ) ) {

	function ezp_mpe_enqueue() {
		if ( ! ezp_mpe_is_product_screen() ) {
			return;
		}

		$version = '1.1.0';

		// --- CSS ---
		wp_register_style( 'ezp-modern-product-editor', false, array(), $version );
		wp_enqueue_style( 'ezp-modern-product-editor' );
		wp_add_inline_style( 'ezp-modern-product-editor', ezp_mpe_css() );

		// --- JS ---
		wp_register_script( 'ezp-modern-product-editor', false, array( 'jquery' ), $version, true );
		wp_enqueue_script( 'ezp-modern-product-editor' );

		$currency = function_exists( 'get_woocommerce_currency_symbol' )
			? get_woocommerce_currency_symbol()
			: '';

		$js  = 'window.ezpMPE = ' . wp_json_encode( array(
			'currency'   => $currency,
			'labels'     => array(
				'draft'       => 'پیش‌نویس',
				'publish'     => 'منتشر شده',
				'pending'     => 'در انتظار بررسی',
				'private'     => 'خصوصی',
				'future'      => 'زمان‌بندی شده',
				'instock'     => 'موجود',
				'outofstock'  => 'ناموجود',
				'onbackorder' => 'پیش‌خرید',
			),
		) ) . ";\n";
		$js .= ezp_mpe_js();

		wp_add_inline_script( 'ezp-modern-product-editor', $js );
	}
	add_action( 'admin_enqueue_scripts', 'ezp_mpe_enqueue', 20 );
}

/* ------------------------------------------------------------------ */
/* ۳. CSS                                                             */
/* ------------------------------------------------------------------ */

if ( ! function_exists( 'ezp_mpe_css' ) ) {

	function ezp_mpe_css() {
		return <<<CSS
/* ===== EzLens Modern Product Editor ===== */

body.post-type-product #wpcontent { background:#f7f8fa; }
body.post-type-product #poststuff { padding-top:0; }

/* --- Title --- */
body.post-type-product #titlediv #title {
    border-radius:10px;
    border:1.5px solid #e5e7eb;
    padding:14px 18px;
    font-size:20px;
    font-weight:600;
    height:auto;
    line-height:1.4;
    box-shadow:none;
    background:#fff;
    transition:border-color .15s, box-shadow .15s;
}
body.post-type-product #titlediv #title:focus {
    border-color:#031f8a;
    box-shadow:0 0 0 3px rgba(3,31,138,.08);
    outline:none;
}
body.post-type-product #titlediv #title-prompt-text {
    padding:16px 18px; font-size:20px; color:#9ca3af;
}

/* --- Postboxes --- */
body.post-type-product .postbox {
    border-radius:12px;
    border:1px solid #e5e7eb;
    background:#fff;
    box-shadow:0 1px 2px rgba(0,0,0,.03);
    overflow:hidden;
    margin-bottom:14px;
}
body.post-type-product .postbox > .postbox-header,
body.post-type-product .postbox > .hndle {
    border-bottom:1px solid #eef1f5;
    background:linear-gradient(180deg,#fff,#fbfcfe);
    padding:12px 16px;
}
body.post-type-product .postbox > .postbox-header .hndle,
body.post-type-product .postbox > .hndle {
    font-size:14px; font-weight:700; color:#111827;
}
body.post-type-product .postbox .inside {
    padding:14px 16px; margin:0;
}

/* --- Publish box --- */
body.post-type-product #submitdiv .handlediv { display:none; }
body.post-type-product #submitdiv #major-publishing-actions {
    background:#f9fafb;
    border-top:1px solid #e5e7eb;
    padding:12px 16px;
    display:flex; gap:8px; align-items:center; flex-wrap:wrap;
}
body.post-type-product #publishing-action { margin-left:auto; }
body.post-type-product #publishing-action #publish {
    background:#031f8a;
    border-color:#031f8a;
    border-radius:8px;
    padding:8px 22px;
    height:auto;
    font-weight:600;
    transition:background .15s, transform .1s;
}
body.post-type-product #publishing-action #publish:hover {
    background:#0a2ba8; border-color:#0a2ba8;
    transform:translateY(-1px);
}

/* --- Product data tabs --- */
body.post-type-product #woocommerce-product-data { border-radius:12px; }
body.post-type-product #woocommerce-product-data .wc-tabs {
    background:#f9fafb;
    border-bottom:1px solid #e5e7eb;
    padding:0 8px;
    display:flex; flex-wrap:wrap; gap:2px;
}
body.post-type-product #woocommerce-product-data .wc-tabs li { margin:0; }
body.post-type-product #woocommerce-product-data .wc-tabs li a {
    display:inline-block;
    padding:12px 16px;
    border:none; background:transparent;
    color:#6b7280;
    font-weight:600; font-size:13px;
    border-radius:8px 8px 0 0;
    text-decoration:none;
    box-shadow:none;
    transition:color .15s, background .15s;
}
body.post-type-product #woocommerce-product-data .wc-tabs li a:hover {
    color:#031f8a; background:rgba(3,31,138,.04);
}
body.post-type-product #woocommerce-product-data .wc-tabs li.active a {
    color:#031f8a; background:#fff;
    border-bottom:2px solid #031f8a;
    margin-bottom:-1px;
}
body.post-type-product #woocommerce-product-data .panel-wrap {
    background:#fff; padding:4px 20px 20px;
}

/* --- Inputs --- */
body.post-type-product .form-field input[type="text"],
body.post-type-product .form-field input[type="number"],
body.post-type-product .form-field input[type="email"],
body.post-type-product .form-field input[type="url"],
body.post-type-product .form-field input[type="password"],
body.post-type-product .form-field select,
body.post-type-product .form-field textarea,
body.post-type-product .wc-metabox input[type="text"],
body.post-type-product .wc-metabox input[type="number"],
body.post-type-product .wc-metabox select,
body.post-type-product .wc-metabox textarea {
    border-radius:8px;
    border:1.5px solid #e5e7eb;
    padding:8px 12px;
    height:auto; min-height:40px;
    box-shadow:none;
    background:#fff;
    transition:border-color .15s, box-shadow .15s;
}
body.post-type-product .form-field input:focus,
body.post-type-product .form-field select:focus,
body.post-type-product .form-field textarea:focus,
body.post-type-product .wc-metabox input:focus,
body.post-type-product .wc-metabox select:focus,
body.post-type-product .wc-metabox textarea:focus {
    border-color:#031f8a;
    box-shadow:0 0 0 3px rgba(3,31,138,.08);
    outline:none;
}

/* --- Buttons --- */
body.post-type-product .button,
body.post-type-product .button-secondary {
    border-radius:8px;
    padding:6px 14px; height:auto; min-height:34px;
    font-weight:600;
    transition:background .15s, transform .1s, border-color .15s;
}
body.post-type-product .button-primary {
    background:#031f8a; border-color:#031f8a;
}
body.post-type-product .button-primary:hover {
    background:#0a2ba8; border-color:#0a2ba8;
    transform:translateY(-1px);
}

/* --- Product images --- */
body.post-type-product #product_images_container ul.product_images li.image {
    border-radius:10px; overflow:hidden;
    border-color:#e5e7eb;
    box-shadow:0 1px 2px rgba(0,0,0,.04);
}

/* --- Quick Stats panel --- */
#ezp-product-quick-stats {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:16px;
    margin-bottom:14px;
    box-shadow:0 1px 2px rgba(0,0,0,.03);
}
#ezp-product-quick-stats h4 {
    margin:0 0 12px;
    font-size:12px; font-weight:700;
    color:#111827;
    text-transform:uppercase; letter-spacing:.04em;
}
#ezp-product-quick-stats .ezp-stat {
    display:flex; justify-content:space-between; align-items:center;
    padding:7px 0;
    border-bottom:1px dashed #eef1f5;
    font-size:13px;
}
#ezp-product-quick-stats .ezp-stat:last-child { border-bottom:none; }
#ezp-product-quick-stats .ezp-stat-label { color:#6b7280; }
#ezp-product-quick-stats .ezp-stat-value {
    font-weight:600; color:#111827; direction:ltr;
}
#ezp-product-quick-stats .ezp-status {
    display:inline-block;
    padding:3px 10px;
    border-radius:999px;
    font-size:11px; font-weight:700;
}
.ezp-status-draft    { background:#fef3c7; color:#92400e; }
.ezp-status-publish  { background:#d1fae5; color:#065f46; }
.ezp-status-pending  { background:#dbeafe; color:#1e40af; }
.ezp-status-private  { background:#ede9fe; color:#5b21b6; }
.ezp-status-future   { background:#e0e7ff; color:#3730a3; }

/* --- Copy SKU --- */
.ezp-copy-sku {
    display:inline-flex; align-items:center; gap:4px;
    margin-right:8px;
    padding:2px 8px;
    background:#f3f4f6;
    border:1px solid #e5e7eb;
    border-radius:6px;
    font-size:11px; color:#374151;
    cursor:pointer;
    transition:background .15s, color .15s, border-color .15s;
}
.ezp-copy-sku:hover { background:#e5e7eb; color:#111827; }
.ezp-copy-sku.copied {
    background:#d1fae5; color:#065f46; border-color:#6ee7b7;
}

/* --- Excerpt char counter --- */
.ezp-char-counter {
    display:block; text-align:right;
    font-size:11px; color:#6b7280;
    margin-top:4px; direction:ltr;
}
.ezp-char-counter.warn { color:#d97706; }
.ezp-char-counter.over { color:#c0392b; font-weight:600; }

/* ===== Editor Max Height with Scroll ===== */

/* --- Visual (TinyMCE) Mode --- */
body.post-type-product #wp-description-wrap .wp-editor-container,
body.post-type-product #wp-excerpt-wrap .wp-editor-container {
    border:1.5px solid #e5e7eb;
    border-radius:8px;
    overflow:hidden;
    transition:border-color .15s, box-shadow .15s;
}
body.post-type-product #wp-description-wrap .wp-editor-container:focus-within,
body.post-type-product #wp-excerpt-wrap .wp-editor-container:focus-within {
    border-color:#031f8a;
    box-shadow:0 0 0 3px rgba(3,31,138,.08);
}

body.post-type-product #wp-description-wrap .mce-tinymce,
body.post-type-product #wp-excerpt-wrap .mce-tinymce {
    border:none;
    box-shadow:none;
}

body.post-type-product #wp-description-wrap .mce-edit-area,
body.post-type-product #wp-excerpt-wrap .mce-edit-area {
    max-height:460px;
    overflow-y:auto;
}

body.post-type-product #wp-description-wrap iframe,
body.post-type-product #description_ifr,
body.post-type-product #wp-excerpt-wrap iframe,
body.post-type-product #excerpt_ifr {
    max-height:460px !important;
    min-height:180px;
    display:block;
}

/* --- Text (HTML) Mode --- */
body.post-type-product #wp-description-wrap .wp-editor-area,
body.post-type-product #wp-excerpt-wrap .wp-editor-area {
    max-height:460px;
    min-height:180px;
    overflow-y:auto;
    resize:vertical;
    border:none !important;
    box-shadow:none !important;
    padding:12px 14px;
    font-family:'Vazirmatn','IranYekan',Tahoma,sans-serif;
    font-size:14px;
    line-height:1.9;
}

/* --- Scrollbar styling (webkit) --- */
body.post-type-product #wp-description-wrap .mce-edit-area::-webkit-scrollbar,
body.post-type-product #wp-description-wrap .wp-editor-area::-webkit-scrollbar,
body.post-type-product #wp-excerpt-wrap .mce-edit-area::-webkit-scrollbar,
body.post-type-product #wp-excerpt-wrap .wp-editor-area::-webkit-scrollbar {
    width:8px;
}
body.post-type-product #wp-description-wrap .mce-edit-area::-webkit-scrollbar-thumb,
body.post-type-product #wp-description-wrap .wp-editor-area::-webkit-scrollbar-thumb,
body.post-type-product #wp-excerpt-wrap .mce-edit-area::-webkit-scrollbar-thumb,
body.post-type-product #wp-excerpt-wrap .wp-editor-area::-webkit-scrollbar-thumb {
    background:#cbd5e1;
    border-radius:4px;
}
body.post-type-product #wp-description-wrap .mce-edit-area::-webkit-scrollbar-thumb:hover,
body.post-type-product #wp-description-wrap .wp-editor-area::-webkit-scrollbar-thumb:hover {
    background:#94a3b8;
}
CSS;
	}
}

/* ------------------------------------------------------------------ */
/* ۴. JS                                                              */
/* ------------------------------------------------------------------ */

if ( ! function_exists( 'ezp_mpe_js' ) ) {

	function ezp_mpe_js() {
		return <<<JS
(function($){
    'use strict';

    $(function(){
        if ( !$('body.post-type-product').length ) return;

        var cfg = window.ezpMPE || { currency:'', labels:{} };

        /* ---------- Quick Stats Panel ---------- */
        function buildStats(){
            var \$publish = $('#submitdiv');
            if ( !\$publish.length || $('#ezp-product-quick-stats').length ) return;

            var status     = $('#post_status').val() || 'draft';
            var statusText = cfg.labels[status] || status;
            var statusCls  = 'ezp-status-' + ( status === 'publish' ? 'publish' : status );

            var pid    = $('#post_ID').val() || '';
            var sku    = $('#_sku').val() || '—';
            var price  = $('#_regular_price').val() || '—';
            var stock  = $('#_stock').val() || '';
            var sst    = $('#_stock_status').val() || 'instock';
            var sstTxt = cfg.labels[sst] || sst;

            var html = ''
                + '<div id="ezp-product-quick-stats">'
                +   '<h4>نمای سریع محصول</h4>'
                +   '<div class="ezp-stat"><span class="ezp-stat-label">وضعیت</span>'
                +       '<span class="ezp-stat-value"><span class="ezp-status ' + statusCls + '">' + statusText + '</span></span></div>'
                +   '<div class="ezp-stat"><span class="ezp-stat-label">شناسه</span>'
                +       '<span class="ezp-stat-value">' + ( pid ? '#' + pid : '—' ) + '</span></div>'
                +   '<div class="ezp-stat"><span class="ezp-stat-label">کد SKU</span>'
                +       '<span class="ezp-stat-value" id="ezp-stat-sku">' + sku + '</span></div>'
                +   '<div class="ezp-stat"><span class="ezp-stat-label">قیمت</span>'
                +       '<span class="ezp-stat-value" id="ezp-stat-price">' + price + ( price !== '—' && cfg.currency ? ' ' + cfg.currency : '' ) + '</span></div>'
                +   '<div class="ezp-stat"><span class="ezp-stat-label">موجودی</span>'
                +       '<span class="ezp-stat-value" id="ezp-stat-stock">' + sstTxt + ( stock ? ' (' + stock + ')' : '' ) + '</span></div>'
                + '</div>';

            \$publish.before( html );
        }
        buildStats();

        /* ---------- Live Stats Update ---------- */
        $(document).on('input change', '#_sku, #_regular_price, #_sale_price, #_stock, #_stock_status', function(){
            var p = cfg.currency ? ' ' + cfg.currency : '';
            var pr = $('#_regular_price').val() || '—';
            $('#ezp-stat-sku').text( $('#_sku').val() || '—' );
            $('#ezp-stat-price').text( pr + ( pr !== '—' ? p : '' ) );

            var s  = $('#_stock').val() || '';
            var ss = $('#_stock_status').val() || 'instock';
            $('#ezp-stat-stock').text( ( cfg.labels[ss] || ss ) + ( s ? ' (' + s + ')' : '' ) );
        });

        /* ---------- Status badge on publish-box change ---------- */
        $(document).on('change', '#post_status', function(){
            var v  = $(this).val() || 'draft';
            var c  = 'ezp-status-' + ( v === 'publish' ? 'publish' : v );
            $('#ezp-product-quick-stats .ezp-status')
                .attr('class', 'ezp-status ' + c)
                .text( cfg.labels[v] || v );
        });

        /* ---------- Copy SKU ---------- */
        var \$sku = $('#_sku');
        if ( \$sku.length && !\$sku.siblings('.ezp-copy-sku').length ) {
            var \$btn = $('<button type="button" class="ezp-copy-sku" title="کپی کد SKU">کپی SKU</button>');
            \$sku.after( \$btn );

            \$btn.on('click', function(e){
                e.preventDefault();
                var v = \$sku.val();
                if ( !v ) return;
                var done = function(){
                    \$btn.addClass('copied').text('کپی شد');
                    setTimeout(function(){ \$btn.removeClass('copied').text('کپی SKU'); }, 1500);
                };
                if ( navigator.clipboard && navigator.clipboard.writeText ) {
                    navigator.clipboard.writeText(v).then(done);
                } else {
                    \$sku[0].select();
                    try { document.execCommand('copy'); done(); } catch(err) {}
                }
            });
        }

        /* ---------- Excerpt char counter ---------- */
        var \$ex = $('#excerpt');
        if ( \$ex.length && !\$ex.siblings('.ezp-char-counter').length ) {
            var \$c = $('<span class="ezp-char-counter"></span>');
            \$ex.after( \$c );
            var upd = function(){
                var len = \$ex.val().length;
                \$c.text( len + ' / 160' );
                \$c.removeClass('warn over');
                if ( len > 160 ) \$c.addClass('over');
                else if ( len > 120 ) \$c.addClass('warn');
            };
            \$ex.on('input', upd);
            upd();
        }

        /* ---------- Ctrl/Cmd + S = Save ---------- */
        $(document).on('keydown.ezpMPE', function(e){
            if ( ( e.ctrlKey || e.metaKey ) && ( e.key === 's' || e.key === 'S' ) ) {
                var \$p = $('#publish');
                if ( \$p.length ) {
                    e.preventDefault();
                    \$p.trigger('click');
                }
            }
        });

        /* ---------- Editor: تنظیم ارتفاع و اسکرول داخلی ---------- */
        function applyEditorScroll(){
            // اعمال روی iframe حالت بصری
            $('.mce-edit-area iframe').each(function(){
                this.style.setProperty('max-height', '460px', 'important');
            });

            // اعمال روی textarea حالت HTML
            $('.wp-editor-area').each(function(){
                this.style.setProperty('max-height', '460px');
                this.style.setProperty('overflow-y', 'auto');
            });
        }

        // اجرا در بارگذاری اولیه
        setTimeout(applyEditorScroll, 400);
        setTimeout(applyEditorScroll, 1200);

        // اجرا در تغییر تب (Visual ↔ Text)
        $(document).on('click', '.wp-switch-editor', function(){
            setTimeout(applyEditorScroll, 150);
        });

        // اجرا هنگام سوییچ به تب توضیحات ووکامرس
        $(document).on('click', '.wc-tabs a[href="#tab-description"]', function(){
            setTimeout(applyEditorScroll, 200);
        });

        // اجرا هنگام تغییر حالت تمام‌صفحه (Fullscreen) که TinyMCE اعمال می‌کند
        $(document).on('click', '.mce-fullscreen', function(){
            setTimeout(function(){
                $('.mce-edit-area iframe').each(function(){
                    if ( $('body').hasClass('wp-fullscreen-active') || $('.mce-fullscreen').hasClass('mce-active') ) {
                        this.style.removeProperty('max-height');
                    } else {
                        this.style.setProperty('max-height', '460px', 'important');
                    }
                });
            }, 300);
        });

        /* ---------- Focus on title for new product ---------- */
        if ( $('body.post-new-php').length ) {
            setTimeout(function(){ $('#title').trigger('focus'); }, 200);
        }
    });
})(jQuery);
JS;
	}
}