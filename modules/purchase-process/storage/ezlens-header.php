<?php
/**
 * عنوان: هدر سایت EzLens
 * فایل: ezlens-header.php
 * محل: modules/purchase-process/storage/
 * فعال‌سازی از پنل فرایند خرید
 *
 * - منوی حساب بدون ایموجی (SVG مینیمال)
 * - پنل حساب به داخل صفحه باز می‌شود
 * - AJAX: جستجو / سبد / منوی حساب
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- AJAX handlers (یک‌بار) ---------- */
if ( ! function_exists( 'ezlens_header_ajax_boot' ) ) {
	function ezlens_header_ajax_boot() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;
		add_action( 'wp_ajax_ez_live_search', 'ezlens_header_live_search' );
		add_action( 'wp_ajax_nopriv_ez_live_search', 'ezlens_header_live_search' );
		add_action( 'wp_ajax_ez_get_account_menu', 'ezlens_header_account_menu' );
		add_action( 'wp_ajax_nopriv_ez_get_account_menu', 'ezlens_header_account_menu' );

/* اجبار: هر handler قدیمی منوی حساب (با ایموجی) حذف شود */
add_action( 'init', function () {
	remove_all_actions( 'wp_ajax_ez_get_account_menu' );
	remove_all_actions( 'wp_ajax_nopriv_ez_get_account_menu' );
	add_action( 'wp_ajax_ez_get_account_menu', 'ezlens_header_account_menu' );
	add_action( 'wp_ajax_nopriv_ez_get_account_menu', 'ezlens_header_account_menu' );
}, 99 );

		add_action( 'wp_ajax_ez_get_cart_mini', 'ezlens_header_cart_mini' );
		add_action( 'wp_ajax_nopriv_ez_get_cart_mini', 'ezlens_header_cart_mini' );
		add_action( 'wp_ajax_ez_get_cart_count', 'ezlens_header_cart_count' );
		add_action( 'wp_ajax_nopriv_ez_get_cart_count', 'ezlens_header_cart_count' );
		add_action( 'wp_ajax_ez_cart_remove_item', 'ezlens_header_cart_remove' );
		add_action( 'wp_ajax_nopriv_ez_cart_remove_item', 'ezlens_header_cart_remove' );
	}
	ezlens_header_ajax_boot();
}

if ( ! function_exists( 'ezlens_header_svg' ) ) {
	function ezlens_header_svg( $name ) {
		$icons = array(
			'login'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>',
			'dashboard'=> '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>',
			'orders'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
			'user'     => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
			'logout'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
			'heart'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
			'wallet'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>',
			'support'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
		);
		return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
	}
}

if ( ! function_exists( 'ezlens_header_account_menu' ) ) {
	function ezlens_header_account_menu() {
		$svg = function ( $name ) {
			if ( function_exists( 'ezlens_header_svg' ) ) {
				$out = ezlens_header_svg( $name );
				if ( $out ) {
					return $out;
				}
			}
			$map = array(
				'user' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
				'dashboard' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>',
				'orders' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
				'map' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
				'settings' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>',
				'logout' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
				'login' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>',
			);
			return isset( $map[ $name ] ) ? $map[ $name ] : '';
		};
		$row = function ( $url, $label, $icon, $style = '' ) use ( $svg ) {
			return '<a href="' . esc_url( $url ) . '" class="ezh-panel-item"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
				. '<span class="ezh-pi-icon">' . $svg( $icon ) . '</span>'
				. '<span class="ezh-pi-label">' . esc_html( $label ) . '</span></a>';
		};
		$css = '<style>.ezh-panel-item{display:flex!important;align-items:center!important;gap:10px!important}.ezh-pi-icon{display:inline-flex;width:20px;height:20px;color:#031f8a;flex-shrink:0}.ezh-pi-icon svg{width:18px;height:18px;stroke:currentColor}</style>';
		$html = $css;
		if ( is_user_logged_in() ) {
			$u = wp_get_current_user();
			$name = $u->display_name ? $u->display_name : $u->user_login;
			$html .= '<div class="ezh-panel-item" style="font-weight:700;color:#031f8a;pointer-events:none">'
				. '<span class="ezh-pi-icon">' . $svg( 'user' ) . '</span><span>' . esc_html( $name ) . '</span></div>';
			$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			$html .= $row( $base, 'داشبورد', 'dashboard' );
			$html .= $row( function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'orders', '', $base ) : $base, 'سفارش‌ها', 'orders' );
			$html .= $row( function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'edit-address', '', $base ) : $base, 'آدرس‌ها', 'map' );
			$html .= $row( function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'edit-account', '', $base ) : $base, 'تنظیمات حساب', 'settings' );
			$html .= $row( wp_logout_url( home_url( '/' ) ), 'خروج', 'logout', 'color:#dc2626' );
		} else {
			$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			$html .= $row( $base, 'ورود / ثبت‌نام', 'login' );
		}
		wp_send_json_success( array( 'html' => $html ) );
		exit;
	}
}

if ( ! function_exists( 'ezlens_header_live_search' ) ) {
	function ezlens_header_live_search() {
		$s = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
		if ( strlen( $s ) < 2 ) {
			wp_send_json_success( array( 'html' => '' ) );
		}
		$q = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 8,
				's'              => $s,
				'post_status'    => 'publish',
			)
		);
		$html = '';
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$img = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
				if ( ! $img ) {
					$img = wc_placeholder_img_src( 'thumbnail' );
				}
				$html .= '<a class="ez-search-item" href="' . esc_url( get_permalink() ) . '">'
					. '<img src="' . esc_url( $img ) . '" alt="" loading="lazy" />'
					. '<span>' . esc_html( get_the_title() ) . '</span></a>';
			}
			wp_reset_postdata();
		}
		wp_send_json_success( array( 'html' => $html ) );
	}
}

if ( ! function_exists( 'ezlens_header_cart_count' ) ) {
	function ezlens_header_cart_count() {
		$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
		wp_send_json_success( array( 'count' => (int) $count ) );
	}
}

if ( ! function_exists( 'ezlens_header_cart_mini' ) ) {
	function ezlens_header_cart_mini() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_success( array( 'html' => '<div class="ez-cart-empty">سبد خرید خالی است</div>', 'count' => 0 ) );
		}
		$cart  = WC()->cart;
		$count = $cart->get_cart_contents_count();
		if ( $count < 1 ) {
			wp_send_json_success( array( 'html' => '<div class="ez-cart-empty">سبد خرید خالی است</div>', 'count' => 0 ) );
		}
		$html = '<div class="ez-cart-panel-inner">';
		foreach ( $cart->get_cart() as $key => $item ) {
			$p = $item['data'];
			if ( ! $p ) {
				continue;
			}
			$img = $p->get_image( array( 56, 56 ) );
			$html .= '<div class="ez-cart-item">'
				. $img
				. '<div class="ez-cart-item-info"><span class="title">' . esc_html( $p->get_name() ) . '</span>'
				. '<span class="qty-price"><span>' . (int) $item['quantity'] . ' عدد</span>'
				. '<span class="price">' . wp_kses_post( $cart->get_product_subtotal( $p, $item['quantity'] ) ) . '</span></span></div>'
				. '<button type="button" class="ez-cart-remove" data-key="' . esc_attr( $key ) . '" aria-label="حذف">×</button></div>';
		}
		$html .= '</div><div class="ez-cart-footer"><div class="total"><span>جمع</span><span class="amount">' . wp_kses_post( $cart->get_cart_subtotal() ) . '</span></div>'
			. '<div class="actions"><a class="view-cart" href="' . esc_url( wc_get_cart_url() ) . '">مشاهده سبد</a>'
			. '<a class="checkout" href="' . esc_url( wc_get_checkout_url() ) . '">تسویه حساب</a></div></div>';
		wp_send_json_success( array( 'html' => $html, 'count' => (int) $count ) );
	}
}

if ( ! function_exists( 'ezlens_header_cart_remove' ) ) {
	function ezlens_header_cart_remove() {
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		if ( $key && function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->remove_cart_item( $key );
			wp_send_json_success();
		}
		wp_send_json_error();
	}
}

/* ---------- Localize for front ---------- */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		?>
		<script>
		window.ezHeaderAjax = window.ezHeaderAjax || {
			url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
			searchNonce: <?php echo wp_json_encode( wp_create_nonce( 'ez_header' ) ); ?>,
			accountNonce: <?php echo wp_json_encode( wp_create_nonce( 'ez_header' ) ); ?>,
			cartNonce: <?php echo wp_json_encode( wp_create_nonce( 'ez_header' ) ); ?>
		};
		</script>
		<?php
	},
	1
);

/* ---------- Markup: shortcode [ezlens_header] یا auto در wp_body_open ---------- */
if ( ! function_exists( 'ezlens_header_render' ) ) {
	function ezlens_header_render() {
		static $printed = false;
		if ( $printed ) {
			return;
		}
		$printed = true;
		$home    = home_url( '/' );
		$cart    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : $home;
		$account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : $home;
		$logo    = 'https://ezlens.ir/wp-content/uploads/2026/07/logo-ezlens-120120.png';
		?>
<style id="ezh-css">
<?php
// compact critical CSS from user + account icon + inward panel
echo <<<'CSS'
.ezh-header-wrap *,.ezh-header-wrap *::before,.ezh-header-wrap *::after{box-sizing:border-box;margin:0;padding:0}
.ezh-header-wrap{font-family:Tahoma,IRANYekan,Arial,sans-serif;direction:rtl;width:100%;-webkit-font-smoothing:antialiased}
.ezh-header-wrap a{text-decoration:none;color:inherit}
.ezh-top-strip{height:4px;width:100%;background:#0d046d}
.ezh-header-spacer{height:148px;width:100%}
.ezh-header{position:fixed;top:0;left:0;right:0;z-index:99999;width:100%;background:#fff;border-bottom:1px solid rgba(3,31,138,.06);box-shadow:0 1px 3px rgba(0,0,0,.02);transition:transform .35s cubic-bezier(.22,.61,.36,1),box-shadow .3s ease;overflow:visible!important}
.ezh-header.ezh-hidden{transform:translateY(-100%)}
.ezh-header.ezh-scrolled{box-shadow:0 6px 24px rgba(3,31,138,.08)}
.ezh-top{max-width:1500px;margin:0 auto;padding:0 clamp(1rem,3vw,2.5rem);height:82px;display:flex;align-items:center;gap:1.2rem;overflow:visible}
.ezh-logo{display:flex;align-items:center;gap:.8rem;flex-shrink:0}
.ezh-logo img{height:56px;width:auto;display:block;border-radius:9px}
.ezh-logo-text{display:flex;flex-direction:column;line-height:1.15}
.ezh-brand{font-size:clamp(1.4rem,2.2vw,2rem);font-weight:700;color:#031f8a;display:flex;align-items:baseline;gap:.22rem}
.ezh-brand .ezh-lens{color:#ff6b6d}
.ezh-tagline{font-size:.7rem;font-weight:500;color:#94a3b8;margin-top:2px}
.ezh-search-wrap{flex:0 1 520px;position:relative;margin:0 auto;overflow:visible;z-index:100002}
.ezh-search-form{display:flex;align-items:center;background:#f5f7fa;border:1px solid rgba(3,31,138,.06);border-radius:999px;padding:3px 3px 3px 6px;height:48px;transition:all .35s}
.ezh-search-form:focus-within{background:#fff;border-color:rgba(3,31,138,.25);box-shadow:0 0 0 3px rgba(3,31,138,.05)}
.ezh-search-form input{flex:1;border:none;background:transparent;outline:none;padding:6px 16px;font-family:inherit;font-size:.95rem;color:#0f172a;min-width:0;height:40px}
.ezh-search-form button{border:none;background:transparent;color:#031f8a;cursor:pointer;padding:6px 10px;display:flex}
.ezh-search-form button svg{width:22px;height:22px;stroke:currentColor;fill:none;stroke-width:2.2}
.ezh-search-drop{position:absolute;top:calc(100% + 10px);right:0;left:0;background:#fff;border:1px solid #e8edf5;border-radius:16px;padding:8px;box-shadow:0 16px 48px rgba(3,31,138,.14);opacity:0;visibility:hidden;transform:translateY(8px);transition:all .22s;z-index:999999;max-height:420px;overflow-y:auto}
.ezh-search-drop.ezh-open{opacity:1;visibility:visible;transform:translateY(0)}
.ezh-search-drop a.ez-search-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:12px;font-size:.9rem;font-weight:500;color:#334155}
.ezh-search-drop a.ez-search-item:hover{background:rgba(3,31,138,.05);color:#031f8a}
.ezh-search-drop .ez-search-item img{width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid #e8edf5}
.ezh-search-empty,.ezh-search-loading{text-align:center;color:#94a3b8;font-size:.9rem;padding:20px 12px}
.ezh-tools{display:flex;align-items:center;gap:.5rem;flex-shrink:0;overflow:visible;position:relative;z-index:100002}
.ezh-tool{position:relative;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;border:none;background:transparent;color:#334155;cursor:pointer}
.ezh-tool:hover,.ezh-tool.ezh-is-open{background:rgba(3,31,138,.05);color:#031f8a}
.ezh-tool svg{width:26px;height:26px;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.ezh-tool .ezh-badge{position:absolute;top:3px;left:3px;min-width:20px;height:20px;padding:0 5px;background:#ff6b6d;color:#fff;font-size:.6rem;font-weight:700;border-radius:99px;display:flex;align-items:center;justify-content:center}
.ezh-panel{position:absolute;top:calc(100% + 10px);background:#fff;border:1px solid #e8edf5;border-radius:14px;padding:12px;box-shadow:0 16px 48px rgba(0,0,0,.12);opacity:0;visibility:hidden;pointer-events:none;transition:all .22s ease;z-index:999999}
.ezh-tool.ezh-is-open .ezh-panel{opacity:1;visibility:visible;pointer-events:auto}
/* پنل حساب: باز شدن به داخل صفحه (به سمت چپ دکمه در RTL = سمت مرکز) */
#ezhAccountToggle .ezh-panel{
  left:0!important;right:auto!important;
  min-width:260px!important;max-width:min(300px,calc(100vw - 24px))!important;
  transform:translateY(6px) scale(.97)!important;transform-origin:top left!important;
}
#ezhAccountToggle.ezh-is-open .ezh-panel{transform:translateY(0) scale(1)!important}
#ezhAccountToggle .ezh-panel-title{font-size:.7rem;font-weight:700;color:#94a3b8;padding:0 4px 8px;border-bottom:1px solid #f1f5f9;margin-bottom:6px}
#ezhAccountToggle .ezh-panel-item{display:flex!important;align-items:center!important;gap:10px!important;padding:10px 12px!important;border-radius:9px!important;font-size:.9rem!important;font-weight:500!important;color:#334155!important}
#ezhAccountToggle .ezh-panel-item:hover{background:rgba(3,31,138,.05)!important;color:#031f8a!important}
#ezhAccountToggle .ezh-pi-icon{display:flex;width:20px;height:20px;color:#031f8a;flex-shrink:0}
#ezhAccountToggle .ezh-pi-icon svg{width:18px;height:18px}
#ezhCartTool .ezh-panel{left:0!important;right:auto!important;min-width:320px;max-width:min(420px,calc(100vw - 24px));padding:0;overflow:hidden;max-height:540px;border-radius:16px;transform:translateY(6px) scale(.97);transform-origin:top left}
#ezhCartTool.ezh-is-open .ezh-panel{transform:translateY(0) scale(1)}
.ez-cart-item{display:flex;align-items:center;gap:14px;padding:12px 16px;border-bottom:1px solid #f1f5f9}
.ez-cart-item img{width:56px;height:56px;border-radius:10px;object-fit:cover}
.ez-cart-footer{padding:14px 18px;border-top:1px solid #e8edf5;background:#fafbfc;display:flex;flex-direction:column;gap:10px}
.ez-cart-footer .actions{display:flex;gap:10px}
.ez-cart-footer .actions a{flex:1;text-align:center;padding:11px 0;border-radius:12px;font-weight:700;font-size:.85rem}
.ez-cart-footer .actions .view-cart{background:#f1f5f9;color:#334155}
.ez-cart-footer .actions .checkout{background:#031f8a;color:#fff}
.ezh-nav-bar{border-top:1px solid rgba(3,31,138,.06);background:#fff;margin-top:5px}
.ezh-nav-inner{max-width:1500px;margin:0 auto;padding:0 clamp(1rem,3vw,2.5rem);height:52px;display:flex;align-items:center}
.ezh-nav-list{display:flex;align-items:center;list-style:none;margin:0;padding:0;width:100%}
.ezh-nav-list>li{position:relative}
.ezh-nav-list>li>a{display:flex;align-items:center;padding:.35rem .8rem;border-radius:.6rem;font-size:.85rem;font-weight:500;color:#475569}
.ezh-nav-list>li>a:hover{background:rgba(3,31,138,.05);color:#031f8a}
.ezh-nav-list .ezh-sub{position:absolute;top:100%;right:0;min-width:210px;background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:8px;box-shadow:0 16px 40px rgba(0,0,0,.14);opacity:0;visibility:hidden;transform:translateY(8px);transition:.2s;z-index:999999;list-style:none}
.ezh-nav-list .ezh-has-sub:hover>.ezh-sub{opacity:1;visibility:visible;transform:translateY(0)}
.ezh-nav-list .ezh-sub a{display:block;padding:10px 14px;border-radius:8px;font-size:.88rem;color:#475569}
.ezh-nav-list .ezh-sub a:hover{background:rgba(3,31,138,.07);color:#031f8a}
.ezh-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:44px;height:44px;background:transparent;border:none;cursor:pointer;padding:10px}
.ezh-burger span{display:block;height:2.5px;background:#334155;border-radius:2px;width:20px}
.ezh-mobile{display:none;position:fixed;inset:0;z-index:999999;background:#fff;flex-direction:column;padding:1.5rem;overflow-y:auto}
.ezh-mobile.ezh-open{display:flex}
.ezh-mobile-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem}
.ezh-mobile-brand{font-size:1.4rem;font-weight:700;color:#031f8a}
.ezh-mobile-brand span{color:#ff6b6d}
.ezh-mobile-close{width:40px;height:40px;border:none;background:#f1f5f9;border-radius:50%;font-size:1.2rem;cursor:pointer}
.ezh-mobile ul{list-style:none;display:flex;flex-direction:column;gap:2px;padding:0}
.ezh-mobile ul a{display:block;padding:12px 14px;border-radius:9px;font-size:1rem;font-weight:500;color:#334155}
@media(max-width:1024px){
  #ezhCartTool,#ezhAccountToggle{display:none!important}
  .ezh-nav-bar{display:none}
  .ezh-burger{display:flex}
  .ezh-header-spacer{height:78px}
  .ezh-search-wrap{flex:1;max-width:none}
}
@media(max-width:768px){.ezh-header-spacer{height:68px}.ezh-top{height:64px}.ezh-logo img{height:40px}.ezh-tagline{display:none}}
CSS;
?>
</style>
<div class="ezh-header-wrap" dir="rtl">
<div class="ezh-header-spacer"></div>
<header class="ezh-header" id="ezhHeader">
  <div class="ezh-top-strip"></div>
  <div class="ezh-top">
    <a href="<?php echo esc_url( $home ); ?>" class="ezh-logo">
      <img src="<?php echo esc_url( $logo ); ?>" alt="ایزی لنز">
      <div class="ezh-logo-text">
        <div class="ezh-brand"><span>ایزی</span><span class="ezh-lens">لنز</span></div>
        <span class="ezh-tagline">مرجع تخصصی سلامت بینایی</span>
      </div>
    </a>
    <div class="ezh-search-wrap">
      <form class="ezh-search-form" id="ezhSearchForm" action="<?php echo esc_url( $home ); ?>" method="get" autocomplete="off">
        <input type="search" name="s" id="ezhSearchInput" placeholder="جستجو..." autocomplete="off">
        <button type="submit" aria-label="جستجو"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></button>
      </form>
      <div class="ezh-search-drop" id="ezhSearchDrop"></div>
    </div>
    <div class="ezh-tools">
      <div class="ezh-tool" id="ezhCartTool" aria-label="سبد خرید">
        <a href="<?php echo esc_url( $cart ); ?>" id="ezhCartLink" style="display:contents;color:inherit">
          <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span class="ezh-badge" id="ezhCartBadge">0</span>
        </a>
        <div class="ezh-panel" id="ezhCartPanel"><div id="ezhCartMiniContent"><div style="text-align:center;padding:20px;color:#94a3b8">…</div></div></div>
      </div>
      <div class="ezh-tool" id="ezhAccountToggle" aria-label="حساب کاربری">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <div class="ezh-panel">
          <div class="ezh-panel-title">حساب کاربری</div>
          <div id="ezhAccountContent">
            <a href="<?php echo esc_url( $account ); ?>" class="ezh-panel-item"><span class="ezh-pi-icon"><?php echo ezlens_header_svg( 'login' ); ?></span><span class="ezh-pi-label">ورود / ثبت‌نام</span></a>
          </div>
        </div>
      </div>
      <button class="ezh-burger" id="ezhBurger" aria-label="منو"><span></span><span></span><span></span></button>
    </div>
  </div>
  <div class="ezh-nav-bar"><div class="ezh-nav-inner">
    <ul class="ezh-nav-list">
      <li class="ezh-has-sub"><a href="<?php echo esc_url( $home ); ?>">صفحه اصلی</a>
        <ul class="ezh-sub">
          <li><a href="<?php echo esc_url( $home ); ?>product-category/contact_lens/">لنزهای طبی</a></li>
          <li><a href="<?php echo esc_url( $home ); ?>product-category/prescription_glasses/">عینک طبی</a></li>
          <li><a href="<?php echo esc_url( $home ); ?>product-category/sunglasses/">عینک آفتابی</a></li>
          <li><a href="<?php echo esc_url( $home ); ?>product-category/eyeglass_lenses/">عدسی عینک</a></li>
        </ul>
      </li>
      <li><a href="<?php echo esc_url( $home ); ?>">لابراتوار</a></li>
      <li><a href="<?php echo esc_url( $home ); ?>">مقالات</a></li>
      <li><a href="<?php echo esc_url( $account ); ?>">حساب کاربری</a></li>
    </ul>
  </div></div>
</header>
<div class="ezh-mobile" id="ezhMobile">
  <div class="ezh-mobile-top"><div class="ezh-mobile-brand">ایزی <span>لنز</span></div><button class="ezh-mobile-close" id="ezhMobileClose">×</button></div>
  <ul>
    <li><a href="<?php echo esc_url( $home ); ?>">صفحه اصلی</a></li>
    <li><a href="<?php echo esc_url( $account ); ?>">حساب کاربری</a></li>
    <li><a href="<?php echo esc_url( $cart ); ?>">سبد خرید</a></li>
  </ul>
</div>
<script>
(function(){
  'use strict';
  var ajax = window.ezHeaderAjax || { url: '/wp-admin/admin-ajax.php', searchNonce:'', accountNonce:'', cartNonce:'' };
  var header=document.getElementById('ezhHeader');
  var searchInput=document.getElementById('ezhSearchInput');
  var searchDrop=document.getElementById('ezhSearchDrop');
  var accountToggle=document.getElementById('ezhAccountToggle');
  var accountContent=document.getElementById('ezhAccountContent');
  var cartTool=document.getElementById('ezhCartTool');
  var cartPanel=document.getElementById('ezhCartPanel');
  var cartMiniContent=document.getElementById('ezhCartMiniContent');
  var cartBadge=document.getElementById('ezhCartBadge');
  var burger=document.getElementById('ezhBurger');
  var mobile=document.getElementById('ezhMobile');
  var accountLoaded=false, cartLoaded=false, searchTimer=null, accountCloseTimer=null, cartCloseTimer=null;
  function isMobileView(){ return window.innerWidth<=1024; }
  var lastScroll=0, isHeaderHidden=false, scrollPending=false;
  window.addEventListener('scroll',function(){
    if(scrollPending)return; scrollPending=true;
    requestAnimationFrame(function(){
      var y=window.pageYOffset;
      if(y>lastScroll&&y>50){ if(!isHeaderHidden){header.classList.add('ezh-hidden');isHeaderHidden=true;} }
      else { if(isHeaderHidden){header.classList.remove('ezh-hidden');isHeaderHidden=false;} }
      header.classList.toggle('ezh-scrolled',y>20); lastScroll=y; scrollPending=false;
    });
  },{passive:true});
  searchInput&&searchInput.addEventListener('input',function(){
    clearTimeout(searchTimer);
    var q=this.value.trim();
    if(q.length<2){searchDrop.classList.remove('ezh-open');return;}
    searchTimer=setTimeout(function(){
      searchDrop.innerHTML='<div class="ezh-search-loading">در حال جستجو</div>';
      searchDrop.classList.add('ezh-open');
      var fd=new FormData(); fd.append('action','ez_live_search'); fd.append('s',q);
      fetch(ajax.url,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(d){
        searchDrop.innerHTML=(d.success&&d.data&&d.data.html)?d.data.html:'<div class="ezh-search-empty">نتیجه‌ای یافت نشد</div>';
      });
    },350);
  });
  document.addEventListener('click',function(e){
    if(searchDrop&&!searchDrop.contains(e.target)&&searchInput&&!searchInput.contains(e.target)) searchDrop.classList.remove('ezh-open');
    if(accountToggle&&!accountToggle.contains(e.target)) accountToggle.classList.remove('ezh-is-open');
    if(cartTool&&!cartTool.contains(e.target)) cartTool.classList.remove('ezh-is-open');
  });
  function loadAccount(){
    var fd=new FormData(); fd.append('action','ez_get_account_menu');
    fetch(ajax.url,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(d){
      if(d.success&&d.data&&d.data.html){
        var html=d.data.html;
        /* اگر هنوز ایموجی آمد، منوی SVG محلی بساز */
        if(/[\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}]/u.test(html) || /[📋📦📍⚙️🚪👋🔑]/.test(html)){
          html=buildSvgAccountMenu();
        }
        accountContent.innerHTML=html; accountLoaded=true;
      }
    }).catch(function(){ if(accountContent) accountContent.innerHTML=buildSvgAccountMenu(); accountLoaded=true; });
  }
  function buildSvgAccountMenu(){
    var ic=function(d){return '<span class="ezh-pi-icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+d+'</svg></span>';};
    var row=function(href,label,path,style){return '<a href="'+href+'" class="ezh-panel-item"'+(style?' style="'+style+'"':'')+'>'+ic(path)+'<span class="ezh-pi-label">'+label+'</span></a>';};
    var base=<?php echo json_encode( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) ); ?>;
    var home=<?php echo json_encode( home_url( '/' ) ); ?>;
    var logout=<?php echo json_encode( wp_logout_url( home_url( '/' ) ) ); ?>;
    var css='<style>.ezh-panel-item{display:flex!important;align-items:center!important;gap:10px!important}.ezh-pi-icon{display:inline-flex;width:20px;height:20px;color:#031f8a}.ezh-pi-icon svg{width:18px;height:18px}</style>';
    var h=css;
    <?php if ( is_user_logged_in() ) :
      $u = wp_get_current_user();
      $nm = $u->display_name ? $u->display_name : $u->user_login;
    ?>
    h+='<div class="ezh-panel-item" style="font-weight:700;color:#031f8a;pointer-events:none">'+ic('<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>')+'<span><?php echo esc_js( $nm ); ?></span></div>';
    h+=row(base,'داشبورد','<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>');
    h+=row(base.replace(/\/?$/,'')+'/orders/','سفارش‌ها','<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>');
    h+=row(base.replace(/\/?$/,'')+'/edit-address/','آدرس‌ها','<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>');
    h+=row(base.replace(/\/?$/,'')+'/edit-account/','تنظیمات حساب','<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>');
    h+=row(logout,'خروج','<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>','color:#dc2626');
    <?php else : ?>
    h+=row(base,'ورود / ثبت‌نام','<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>');
    <?php endif; ?>
    return h;
  }
  if(accountToggle){
    accountToggle.addEventListener('mouseenter',function(){ if(isMobileView())return; clearTimeout(accountCloseTimer); this.classList.add('ezh-is-open'); if(!accountLoaded)loadAccount(); });
    accountToggle.addEventListener('mouseleave',function(){ if(isMobileView())return; accountCloseTimer=setTimeout(function(){accountToggle.classList.remove('ezh-is-open');},250); });
  }
  function loadCartMini(){
    var fd=new FormData(); fd.append('action','ez_get_cart_mini');
    fetch(ajax.url,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(d){
      if(d.success&&d.data){ cartMiniContent.innerHTML=d.data.html||''; cartLoaded=true; if(d.data.count!==undefined){cartBadge.textContent=d.data.count;cartBadge.style.display=d.data.count>0?'flex':'none';} }
    });
  }
  if(cartTool){
    cartTool.addEventListener('mouseenter',function(){ if(isMobileView())return; clearTimeout(cartCloseTimer); this.classList.add('ezh-is-open'); if(!cartLoaded)loadCartMini(); });
    cartTool.addEventListener('mouseleave',function(){ if(isMobileView())return; cartCloseTimer=setTimeout(function(){cartTool.classList.remove('ezh-is-open');},250); });
  }
  function updateCartBadge(){
    var fd=new FormData(); fd.append('action','ez_get_cart_count');
    fetch(ajax.url,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(d){
      if(d.success&&d.data){ cartBadge.textContent=d.data.count; cartBadge.style.display=d.data.count>0?'flex':'none'; }
    });
  }
  updateCartBadge();
  if(burger&&mobile){
    burger.addEventListener('click',function(){ mobile.classList.toggle('ezh-open'); document.body.style.overflow=mobile.classList.contains('ezh-open')?'hidden':''; });
    var mc=document.getElementById('ezhMobileClose'); if(mc) mc.addEventListener('click',function(){ mobile.classList.remove('ezh-open'); document.body.style.overflow=''; });
  }
})();
</script>
</div>
		<?php
	}
}

add_shortcode( 'ezlens_header', 'ezlens_header_render' );

// اگر از Elementor هدر استفاده نمی‌شود می‌توان فعال کرد:
// add_action( 'wp_body_open', 'ezlens_header_render', 5 );


/* پیام افزودن به سبد — فارسی */
add_filter( 'woocommerce_add_to_cart_message_html', function ( $message, $products ) {
	$names = array();
	if ( is_array( $products ) ) {
		foreach ( $products as $pid => $qty ) {
			$names[] = get_the_title( $pid );
		}
	}
	$name = $names ? implode( '، ', $names ) : 'محصول';
	$cart = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	return sprintf(
		'<a href="%s" tabindex="1" class="button wc-forward">مشاهده سبد</a> «%s» با موفقیت به سبد خرید اضافه شد.',
		esc_url( $cart ),
		esc_html( $name )
	);
}, 20, 2 );

add_filter( 'gettext', function ( $translated, $text, $domain ) {
	if ( ! in_array( $domain, array( 'woocommerce', 'woodmart', 'woodmart-core' ), true ) ) {
		return $translated;
	}
	$map = array(
		'Product successfully added to your cart.' => 'محصول با موفقیت به سبد خرید اضافه شد.',
		'Continue shopping' => 'ادامه خرید',
		'View cart' => 'مشاهده سبد',
		'View Cart' => 'مشاهده سبد',
		'Checkout' => 'تسویه حساب',
		'has been added to your cart' => 'به سبد خرید اضافه شد',
		'added to your cart' => 'به سبد خرید اضافه شد',
		'Close' => 'بستن',
	);
	return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}, 20, 3 );
