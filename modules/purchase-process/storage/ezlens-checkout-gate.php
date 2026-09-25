<?php
/**
 * EzLens Checkout Gate + Iran Layer
 * نسخه: 3.0 (رسپانسیو کامل + اسکلتون لودینگ + فیلدهای جدید)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   Helpers
   ============================================================ */
function ezck_gate_get_icon( $name ) {
	$paths = array(
		EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg',
		EZLAUTH_PLUGIN_DIR . 'assets/icons/' . $name . '.svg',
	);
	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			$c = file_get_contents( $path );
			if ( false !== $c && trim( $c ) !== '' ) return $c;
		}
	}
	return '';
}

function ezck_gate_icon_or( $name, $fallback ) {
	$ico = ezck_gate_get_icon( $name );
	if ( ! empty( $ico ) ) return $ico;
	return ezck_gate_get_icon( $fallback );
}

function ezck_gate_is_checkout_ready() {
	return shortcode_exists( 'ezlens_checkout' ) || shortcode_exists( 'woocommerce_checkout' );
}

function ezck_gate_render_checkout() {
	if ( shortcode_exists( 'ezlens_checkout' ) ) return do_shortcode( '[ezlens_checkout]' );
	if ( shortcode_exists( 'woocommerce_checkout' ) ) return do_shortcode( '[woocommerce_checkout]' );
	return '<div style="max-width:600px;margin:40px auto;padding:24px;background:#fff;border:1px solid #e8edf5;border-radius:16px;text-align:center;font-family:IRANYekan,Tahoma,sans-serif;">صفحه پرداخت در دسترس نیست.</div>';
}

/* ============================================================
   تشخیص صفحه گیت (برای استفاده در چند جا)
   ============================================================ */
function ezck_is_gate_or_checkout_page() {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) return true;
	global $post;
	if ( ! $post ) return false;
	if ( has_shortcode( $post->post_content, 'ezlens_checkout_gated' ) ) return true;
	if ( function_exists( 'is_singular' ) && is_singular() ) {
		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
		if ( is_string( $elementor_data ) && strpos( $elementor_data, 'ezlens_checkout_gated' ) !== false ) return true;
	}
	return false;
}

/* ============================================================
   BODY CLASS
   ============================================================ */
if ( ! function_exists( 'ezck_add_body_class' ) ) {
	add_filter( 'body_class', 'ezck_add_body_class' );
	function ezck_add_body_class( $classes ) {
		global $post;
		if ( $post ) {
			if ( has_shortcode( $post->post_content, 'ezlens_checkout_gated' ) ) {
				$classes[] = 'ezck-gate-page';
			} elseif ( function_exists( 'is_singular' ) && is_singular() ) {
				$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
				if ( is_string( $elementor_data ) && strpos( $elementor_data, 'ezlens_checkout_gated' ) !== false ) {
					$classes[] = 'ezck-gate-page';
				}
			}
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			$classes[] = 'ezck-checkout-page';
		}
		return $classes;
	}
}

/* ============================================================
   🆕 اسکلتون لودینگ — جلوگیری از FOUC
   ============================================================ */
if ( ! function_exists( 'ezck_loading_overlay' ) ) {
	add_action( 'wp_head', 'ezck_loading_overlay_style', 1 );
	function ezck_loading_overlay_style() {
		if ( ! ezck_is_gate_or_checkout_page() ) return;
		?>
<style id="ezck-loading-overlay">
html body.ezck-loading-active:not(.ezck-ready)::before{
	content:"";position:fixed;inset:0;z-index:99998;
	background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 50%,#e0e7ff 100%);
	pointer-events:all;
}
html body.ezck-loading-active:not(.ezck-ready)::after{
	content:"";position:fixed;inset:0;z-index:99999;
	pointer-events:none;
	background:
		linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent) 0 0 / 40% 100% no-repeat,
		/* left/right skeleton cards */
		linear-gradient(#fff,#fff) center top 12% / min(92%,1100px) 18px no-repeat,
		linear-gradient(#fff,#fff) calc(50% - min(46%,520px) + 8px) top 18% / min(44%,500px) 160px no-repeat,
		linear-gradient(#fff,#fff) calc(50% + 12px) top 18% / min(44%,500px) 160px no-repeat,
		linear-gradient(#fff,#fff) calc(50% - min(46%,520px) + 8px) top 42% / min(44%,500px) 200px no-repeat,
		linear-gradient(#fff,#fff) calc(50% + 12px) top 42% / min(44%,500px) 120px no-repeat,
		linear-gradient(#fff,#fff) calc(50% - min(46%,520px) + 8px) top 68% / min(44%,500px) 140px no-repeat,
		linear-gradient(#fff,#fff) calc(50% + 12px) top 62% / min(44%,500px) 180px no-repeat;
	border-radius:0;
	opacity:.95;
	animation:ezckSkeletonShine 1.4s ease-in-out infinite;
	filter: drop-shadow(0 8px 24px rgba(15,23,42,.06));
}
html body.ezck-loading-active:not(.ezck-ready)::after{
	/* softer rounded skeleton via mask approximation */
	box-shadow:
		0 0 0 0 transparent;
}
@keyframes ezckSkeletonShine{
	0%{background-position: -40% 0, center top 12%, calc(50% - min(46%,520px) + 8px) top 18%, calc(50% + 12px) top 18%, calc(50% - min(46%,520px) + 8px) top 42%, calc(50% + 12px) top 42%, calc(50% - min(46%,520px) + 8px) top 68%, calc(50% + 12px) top 62%;}
	100%{background-position: 140% 0, center top 12%, calc(50% - min(46%,520px) + 8px) top 18%, calc(50% + 12px) top 18%, calc(50% - min(46%,520px) + 8px) top 42%, calc(50% + 12px) top 42%, calc(50% - min(46%,520px) + 8px) top 68%, calc(50% + 12px) top 62%;}
}
@media (max-width: 900px){
	html body.ezck-loading-active:not(.ezck-ready)::after{
		background:
			linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent) 0 0 / 50% 100% no-repeat,
			linear-gradient(#fff,#fff) center top 10% / min(92%,420px) 16px no-repeat,
			linear-gradient(#fff,#fff) center top 16% / min(92%,420px) 140px no-repeat,
			linear-gradient(#fff,#fff) center top 38% / min(92%,420px) 160px no-repeat,
			linear-gradient(#fff,#fff) center top 60% / min(92%,420px) 120px no-repeat,
			linear-gradient(#fff,#fff) center top 78% / min(92%,420px) 100px no-repeat;
		animation:ezckSkeletonShineMobile 1.4s ease-in-out infinite;
	}
	@keyframes ezckSkeletonShineMobile{
		0%{background-position:-50% 0, center top 10%, center top 16%, center top 38%, center top 60%, center top 78%;}
		100%{background-position:150% 0, center top 10%, center top 16%, center top 38%, center top 60%, center top 78%;}
	}
}
</style>
<script>
(function(){
	// فعال کردن overlay بلافاصله
	if (document.documentElement) {
		document.documentElement.style.setProperty('--ezck-loading', '1');
	}
	function activate(){
		if (document.body) document.body.classList.add('ezck-loading-active');
	}
	activate();
	if (!document.body) {
		new MutationObserver(function(m,o){
			if (document.body) { activate(); o.disconnect(); }
		}).observe(document.documentElement, {childList:true});
	}
})();
</script>
		<?php
	}

	add_action( 'wp_footer', 'ezck_loading_overlay_ready_script', 1 );
	function ezck_loading_overlay_ready_script() {
		if ( ! ezck_is_gate_or_checkout_page() ) return;
		?>
<script>
(function(){
	function ready(){
		if (!document.body.classList.contains('ezck-ready')) {
			document.body.classList.add('ezck-ready');
		}
	}
	if (document.readyState === 'complete') {
		setTimeout(ready, 120);
	} else if (document.readyState === 'interactive') {
		setTimeout(ready, 200);
	} else {
		document.addEventListener('DOMContentLoaded', function(){ setTimeout(ready, 200); });
		window.addEventListener('load', function(){ setTimeout(ready, 80); });
	}
	setTimeout(ready, 3500); // fallback
})();
</script>
		<?php
	}
}

/* ============================================================
   SHARED STEPS BAR
   ============================================================ */
if ( ! function_exists( 'ezck_render_steps' ) ) {
	function ezck_render_steps( $current = 2 ) {
		$ico_cart  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
		$ico_user  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
		$ico_truck = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18" r="2.5"></circle><circle cx="18.5" cy="18" r="2.5"></circle></svg>';
		$ico_card  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
		$ico_done  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="9 12 11 14 15 10"></polyline></svg>';

		$state = function( $n ) use ( $current ) {
			if ( $n < $current ) return 'is-done';
			if ( $n === $current ) return 'is-active';
			return '';
		};
		$line_state = function( $n ) use ( $current ) { return $n < $current ? 'is-done' : ''; };
		$dot = function( $n, $icon ) use ( $state, $ico_done ) { return $state( $n ) === 'is-done' ? $ico_done : $icon; };
		?>
		<div class="ezp-steps" dir="rtl">
			<div class="ezp-step <?php echo esc_attr( $state( 1 ) ); ?>">
				<div class="ezp-step-dot"><?php echo $dot( 1, $ico_cart ); ?></div>
				<span class="ezp-step-text">سبد خرید</span>
			</div>
			<div class="ezp-step-line <?php echo esc_attr( $line_state( 2 ) ); ?>"></div>
			<div class="ezp-step <?php echo esc_attr( $state( 2 ) ); ?>">
				<div class="ezp-step-dot"><?php echo $dot( 2, $ico_user ); ?></div>
				<span class="ezp-step-text">ورود / ثبت‌نام</span>
			</div>
			<div class="ezp-step-line <?php echo esc_attr( $line_state( 3 ) ); ?>"></div>
			<div class="ezp-step <?php echo esc_attr( $state( 3 ) ); ?>">
				<div class="ezp-step-dot"><?php echo $dot( 3, $ico_truck ); ?></div>
				<span class="ezp-step-text">تکمیل پرداخت</span>
			</div>
			<div class="ezp-step-line <?php echo esc_attr( $line_state( 4 ) ); ?>"></div>
			<div class="ezp-step <?php echo esc_attr( $state( 4 ) ); ?>">
				<div class="ezp-step-dot"><?php echo $dot( 4, $ico_card ); ?></div>
				<span class="ezp-step-text">پرداخت</span>
			</div>
			<div class="ezp-step-line <?php echo esc_attr( $line_state( 5 ) ); ?>"></div>
			<div class="ezp-step <?php echo esc_attr( $state( 5 ) ); ?>">
				<div class="ezp-step-dot"><?php echo $ico_done; ?></div>
				<span class="ezp-step-text">تأیید سفارش</span>
			</div>
		</div>
		<?php
	}
}

/* ============================================================
   SHARED STEPS CSS
   ============================================================ */
if ( ! function_exists( 'ezck_steps_css' ) ) {
	function ezck_steps_css() {
		?>
body.ezck-gate-page .ezp-steps,
body.ezck-checkout-page .ezp-steps{
	display:flex !important;align-items:flex-start !important;justify-content:space-between !important;
	gap:0 !important;flex-wrap:nowrap !important;
	width:min(85em, 100vw - 2em) !important;max-width:min(85em, 100vw - 2em) !important;
	margin:0 auto 20px !important;padding:1.125em 1.375em !important;
	background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%) !important;
	border:1.5px solid #e8edf5 !important;border-radius:18px !important;
	box-shadow:0 8px 24px rgba(15,23,42,.05) !important;
	overflow-x:auto !important;scrollbar-width:none !important;box-sizing:border-box !important;
}
body.ezck-gate-page .ezp-steps::-webkit-scrollbar,
body.ezck-checkout-page .ezp-steps::-webkit-scrollbar{display:none}
body.ezck-gate-page .ezp-step,
body.ezck-checkout-page .ezp-step{
	display:flex !important;flex-direction:column !important;align-items:center !important;
	gap:6px !important;padding:0 2px !important;flex-shrink:0 !important;min-width:76px !important;
	background:transparent !important;border:0 !important;
}
body.ezck-gate-page .ezp-step-dot,
body.ezck-checkout-page .ezp-step-dot{
	width:40px !important;height:40px !important;border-radius:50% !important;
	display:flex !important;align-items:center !important;justify-content:center !important;
	background:#f1f5f9 !important;border:2px solid #e8edf5 !important;color:#64748b !important;
	flex-shrink:0 !important;transition:all .35s cubic-bezier(.4,0,.2,1) !important;
	box-shadow:0 2px 6px rgba(15,23,42,.04) !important;
}
body.ezck-gate-page .ezp-step-dot svg,
body.ezck-checkout-page .ezp-step-dot svg{
	width:17px !important;height:17px !important;fill:none !important;
	stroke:currentColor !important;stroke-width:2 !important;
	stroke-linecap:round !important;stroke-linejoin:round !important;display:block !important;
}
body.ezck-gate-page .ezp-step-text,
body.ezck-checkout-page .ezp-step-text{
	font-size:10.5px !important;font-weight:700 !important;color:#64748b !important;
	white-space:nowrap !important;line-height:1.5 !important;text-align:center !important;
}
body.ezck-gate-page .ezp-step.is-active .ezp-step-dot,
body.ezck-checkout-page .ezp-step.is-active .ezp-step-dot{
	background:linear-gradient(135deg,#031f8a 0%,#2563eb 100%) !important;
	border-color:#031f8a !important;color:#fff !important;
	box-shadow:0 0 0 5px rgba(3,31,138,.12), 0 8px 20px rgba(3,31,138,.25) !important;
	transform:scale(1.06) !important;
	animation:ezckStepPulse 2s ease-in-out infinite !important;
}
@keyframes ezckStepPulse{
	0%,100%{box-shadow:0 0 0 5px rgba(3,31,138,.12), 0 8px 20px rgba(3,31,138,.25); transform:scale(1.06);}
	50%{box-shadow:0 0 0 9px rgba(3,31,138,.18), 0 10px 24px rgba(37,99,235,.35); transform:scale(1.1);}
}
@media (prefers-reduced-motion: reduce){
	body.ezck-gate-page .ezp-step.is-active .ezp-step-dot,
	body.ezck-checkout-page .ezp-step.is-active .ezp-step-dot{animation:none !important;}
	html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount{animation:none !important;}
}
body.ezck-gate-page .ezp-step.is-active .ezp-step-text,
body.ezck-checkout-page .ezp-step.is-active .ezp-step-text{color:#031f8a !important;font-weight:800 !important}
body.ezck-gate-page .ezp-step.is-done .ezp-step-dot,
body.ezck-checkout-page .ezp-step.is-done .ezp-step-dot{
	background:linear-gradient(135deg,#dcfce7 0%,#a7f3d0 100%) !important;
	border-color:#059669 !important;color:#059669 !important;
}
body.ezck-gate-page .ezp-step.is-done .ezp-step-text,
body.ezck-checkout-page .ezp-step.is-done .ezp-step-text{color:#059669 !important}
body.ezck-gate-page .ezp-step-line,
body.ezck-checkout-page .ezp-step-line{
	flex:1 1 auto !important;height:3px !important;
	min-width:16px !important;max-width:none !important;
	margin-top:19px !important;background:#e8edf5 !important;
	border-radius:3px !important;align-self:flex-start !important;
	transition:background .35s ease !important;
}
body.ezck-gate-page .ezp-step-line.is-done,
body.ezck-checkout-page .ezp-step-line.is-done{
	background:linear-gradient(90deg,#059669 0%,#031f8a 100%) !important;
}
@media (max-width: 768px){
	body.ezck-gate-page .ezp-steps,body.ezck-checkout-page .ezp-steps{max-width:100% !important;padding:14px 12px !important;margin-bottom:16px !important;border-radius:14px !important}
	body.ezck-gate-page .ezp-step,body.ezck-checkout-page .ezp-step{min-width:60px !important;gap:5px !important}
	body.ezck-gate-page .ezp-step-dot,body.ezck-checkout-page .ezp-step-dot{width:36px !important;height:36px !important}
	body.ezck-gate-page .ezp-step-dot svg,body.ezck-checkout-page .ezp-step-dot svg{width:15px !important;height:15px !important}
	body.ezck-gate-page .ezp-step-text,body.ezck-checkout-page .ezp-step-text{font-size:9.5px !important}
	body.ezck-gate-page .ezp-step-line,body.ezck-checkout-page .ezp-step-line{margin-top:17px !important;min-width:8px !important;max-width:24px !important}
}
@media (max-width: 480px){
	body.ezck-gate-page .ezp-steps,body.ezck-checkout-page .ezp-steps{padding:12px 8px !important}
	body.ezck-gate-page .ezp-step,body.ezck-checkout-page .ezp-step{min-width:48px !important;gap:4px !important;padding:0 1px !important}
	body.ezck-gate-page .ezp-step-dot,body.ezck-checkout-page .ezp-step-dot{width:32px !important;height:32px !important}
	body.ezck-gate-page .ezp-step-dot svg,body.ezck-checkout-page .ezp-step-dot svg{width:13px !important;height:13px !important}
	body.ezck-gate-page .ezp-step-text,body.ezck-checkout-page .ezp-step-text{font-size:8.5px !important;letter-spacing:-.15px !important}
	body.ezck-gate-page .ezp-step-line,body.ezck-checkout-page .ezp-step-line{margin-top:15px !important;min-width:5px !important;max-width:16px !important}
}
@media (max-width: 380px){
	body.ezck-gate-page .ezp-step-text,body.ezck-checkout-page .ezp-step-text{font-size:8px !important;letter-spacing:-.2px !important}
	body.ezck-gate-page .ezp-step,body.ezck-checkout-page .ezp-step{min-width:42px !important}
	body.ezck-gate-page .ezp-step-line,body.ezck-checkout-page .ezp-step-line{min-width:4px !important;max-width:12px !important}
}
		<?php
	}
}

/* ============================================================
   OTP GATE
   ============================================================ */
function ezck_gate_render() {
	if ( ! function_exists( 'WC' ) ) {
		return '<div style="max-width:600px;margin:40px auto;padding:24px;background:#fff;border:1px solid #e8edf5;border-radius:16px;text-align:center;font-family:IRANYekan,Tahoma,sans-serif;color:#0f172a;">ووکامرس فعال نیست.</div>';
	}
	if ( is_user_logged_in() ) return ezck_gate_render_checkout();

	$cart_url   = wc_get_cart_url();
	$ajax_url   = admin_url( 'admin-ajax.php' );
	$ajax_nonce = wp_create_nonce( 'minimal_auth_secure_nonce_v5' );

	$otp_expiry = 2;
	if ( class_exists( 'EzLens_Auth_Settings' ) ) {
		$tmp = (int) EzLens_Auth_Settings::get( 'otp_expiry_minutes' );
		if ( $tmp > 0 ) $otp_expiry = $tmp;
	}
	$checkout_url = wc_get_checkout_url();

	$ico_phone  = ezck_gate_icon_or( 'phone', 'phone-svgrepo-com' );
	$ico_check  = ezck_gate_icon_or( 'check-circle', 'check' );
	$ico_shield = ezck_gate_icon_or( 'shield', 'shield-check' );
	$ico_lock   = ezck_gate_icon_or( 'lock', 'lock-password-unlocked-svgrepo-com' );
	$ico_edit   = ezck_gate_icon_or( 'edit', 'edit-2' );
	$ico_clock  = ezck_gate_icon_or( 'clock', 'calendar' );
	$ico_info   = ezck_gate_icon_or( 'info', 'alert-circle' );

	$ico_arrow_left = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';

	$font_url = EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/';

	ob_start();
	?>
<div class="ezckg" dir="rtl" id="ezckgRoot" data-ajax="<?php echo esc_url( $ajax_url ); ?>" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>" data-expiry="<?php echo esc_attr( $otp_expiry ); ?>" data-checkout="<?php echo esc_url( $checkout_url ); ?>">
<script>document.body.classList.add('ezck-gate-page');</script>
<style>
@font-face{font-family:'EzLensVazir';src:url('<?php echo esc_url( $font_url . 'Vazirmatn-Regular.woff2' ); ?>') format('woff2');font-weight:400;font-display:swap}
@font-face{font-family:'EzLensVazir';src:url('<?php echo esc_url( $font_url . 'Vazirmatn-Medium.woff2' ); ?>') format('woff2');font-weight:500;font-display:swap}
@font-face{font-family:'EzLensVazir';src:url('<?php echo esc_url( $font_url . 'Vazirmatn-Bold.woff2' ); ?>') format('woff2');font-weight:700;font-display:swap}

html body.ezck-gate-page .wd-page-content,
html body.ezck-gate-page .main-page-wrapper,
html body.ezck-gate-page main#main-content,
html body.ezck-gate-page main.wd-content-layout,
html body.ezck-gate-page main#main-content.container,
html body.ezck-gate-page .content-layout-wrapper,
html body.ezck-gate-page .wd-content-layout,
html body.ezck-gate-page .wd-content-area,
html body.ezck-gate-page .site-content,
html body.ezck-gate-page .woocommerce.entry-content,
html body.ezck-gate-page .entry-content,
html body.ezck-gate-page .elementor,
html body.ezck-gate-page .elementor-section,
html body.ezck-gate-page .elementor-container,
html body.ezck-gate-page .e-con,
html body.ezck-gate-page .e-atomic-element,
html body.ezck-gate-page [data-elementor-type="wp-post"],
html body.ezck-gate-page .elementor-widget,
html body.ezck-gate-page .elementor-widget-container,
html body.ezck-gate-page .elementor-shortcode,
html body.ezck-gate-page .woocommerce{
	max-width:100% !important;min-width:0 !important;
	margin-left:0 !important;margin-right:0 !important;
	padding-left:0 !important;padding-right:0 !important;
	float:none !important;left:auto !important;right:auto !important;
	transform:none !important;
	grid-template-columns:none !important;grid-template-areas:none !important;column-count:1 !important;
}
html body.ezck-gate-page main#main-content,
html body.ezck-gate-page main.wd-content-layout,
html body.ezck-gate-page main#main-content.container{
	display:flex !important;flex-direction:column !important;
	align-items:center !important;justify-content:flex-start !important;
	padding:0 !important;margin:0 !important;width:100% !important;max-width:100% !important;
}
html body.ezck-gate-page #secondary,
html body.ezck-gate-page aside.sidebar,
html body.ezck-gate-page .sidebar-container,
html body.ezck-gate-page .wd-sidebar,
html body.ezck-gate-page .woodmart-sidebar,
html body.ezck-gate-page .wd-sidebar-container{display:none !important;width:0 !important;flex:0 0 0 !important}
html body.ezck-gate-page .e-con,
html body.ezck-gate-page [data-element_type="e-flexbox"],
html body.ezck-gate-page .elementor-widget-shortcode,
html body.ezck-gate-page .elementor-widget-container{
	display:flex !important;flex-direction:column !important;
	align-items:center !important;justify-content:flex-start !important;flex-wrap:wrap !important;
}
html body.ezck-gate-page .e-con > *,
html body.ezck-gate-page [data-element_type="e-flexbox"] > *,
html body.ezck-gate-page .elementor-widget-shortcode > *,
html body.ezck-gate-page .elementor-shortcode,
html body.ezck-gate-page .elementor-shortcode > *{
	flex:1 1 100% !important;max-width:100% !important;
	min-width:0 !important;margin-left:auto !important;margin-right:auto !important;
}
html body.ezck-gate-page .ezckg{
	display:flex !important;flex-direction:column !important;
	align-items:center !important;justify-content:flex-start !important;
	width:100% !important;max-width:100% !important;margin:0 auto !important;
	padding:28px 16px 60px !important;
	box-sizing:border-box !important;
}
html body.ezck-gate-page .ezckg > .ezckg-card{
	width:100% !important;max-width:520px !important;
	margin-left:auto !important;margin-right:auto !important;
	align-self:center !important;box-sizing:border-box !important;
}
@media (min-width: 992px){
	html body.ezck-gate-page .ezckg > .ezckg-card{width:800px !important;max-width:800px !important;min-width:0 !important}
}
@media (min-width: 601px) and (max-width: 991px){
	html body.ezck-gate-page .ezckg > .ezckg-card{width:100% !important;max-width:640px !important}
}
@media (max-width: 600px){
	html body.ezck-gate-page .ezckg > .ezckg-card{width:100% !important;max-width:100% !important}
	html body.ezck-gate-page .ezckg{padding:18px 12px 50px !important}
}

<?php ezck_steps_css(); ?>

.ezckg{--ez-primary:#031f8a;--ez-primary-dark:#011663;--ez-primary-light:#2563eb;--ez-text:#0f172a;--ez-text-soft:#334155;--ez-muted:#64748b;--ez-border:#e8edf5;--ez-soft:#f8fafc;--ez-success:#059669;--ez-success-soft:#dcfce7;--ez-danger:#dc2626;--ez-warning:#f59e0b;font-family:'EzLensVazir',Tahoma,Arial,sans-serif;color:var(--ez-text);line-height:1.8;font-size:14px;transition:opacity .4s ease;-webkit-text-size-adjust:100%}
.ezckg.is-leaving{opacity:0}
.ezckg *,.ezckg *::before,.ezckg *::after{box-sizing:border-box}
.ezckg a{text-decoration:none;color:inherit}
.ezckg button{font-family:inherit}
.ezckg-card{background:#fff;border:1px solid var(--ez-border);border-radius:18px;box-shadow:0 15px 50px -20px rgba(3,31,138,.15);overflow:hidden;position:relative;width:100%;max-width:100%;box-sizing:border-box}
.ezckg-card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--ez-primary),var(--ez-primary-light),var(--ez-primary))}
.ezckg-card-body{padding:32px 36px;min-width:0;max-width:100%;box-sizing:border-box}
@media (min-width: 992px){.ezckg-card-body{padding:40px 60px}}
@media(max-width:600px){.ezckg-card{border-radius:14px}.ezckg-card-body{padding:22px 16px}}
@media(max-width:400px){.ezckg-card-body{padding:20px 12px}}
.ezckg-hero{text-align:center;margin-bottom:24px}
.ezckg-title{display:flex !important;align-items:center !important;justify-content:center !important;gap:12px !important;margin:0 0 8px !important;font-size:20px !important;font-weight:800 !important;color:var(--ez-text) !important;line-height:1.4 !important}
.ezckg-title-icon{width:42px;height:42px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:var(--ez-primary);box-shadow:0 4px 12px rgba(3,31,138,.08);animation:ezckgIconIn .6s cubic-bezier(.34,1.56,.64,1)}
.ezckg-title-icon svg{width:22px !important;height:22px !important;fill:none !important;stroke:currentColor !important;stroke-width:1.8 !important;stroke-linecap:round !important;stroke-linejoin:round !important}
.ezckg-title-icon.is-warm{background:linear-gradient(135deg,#fef3c7,#fde68a) !important;color:#92400e !important;box-shadow:0 4px 12px rgba(245,158,11,.12) !important}
@keyframes ezckgIconIn{from{opacity:0;transform:scale(.7) rotate(-15deg)}to{opacity:1;transform:scale(1) rotate(0)}}
.ezckg-subtitle{margin:0;font-size:13px;color:var(--ez-muted);line-height:1.8}
@media(max-width:480px){
	.ezckg-title{font-size:17px !important;gap:10px !important}
	.ezckg-title-icon{width:36px;height:36px;border-radius:10px}
	.ezckg-title-icon svg{width:19px !important;height:19px !important}
	.ezckg-subtitle{font-size:12px}
}
.ezckg-section{display:none}
.ezckg-section.is-active{display:block;animation:ezckgFadeUp .4s cubic-bezier(.4,0,.2,1)}
@keyframes ezckgFadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ezckg-field{margin-bottom:14px}
.ezckg-label{display:block;margin-bottom:6px;font-size:12.5px;font-weight:700;color:var(--ez-text-soft)}
.ezckg-input-wrap{position:relative;display:flex;align-items:center;border:2px solid var(--ez-border);border-radius:12px;background:#fff;transition:all .25s}
.ezckg-input-wrap:focus-within{border-color:var(--ez-primary);box-shadow:0 0 0 4px rgba(3,31,138,.08)}
.ezckg-input-wrap.is-error{border-color:var(--ez-danger);box-shadow:0 0 0 4px rgba(220,38,38,.08);animation:ezckgShake .4s}
@keyframes ezckgShake{10%,90%{transform:translateX(-2px)}20%,80%{transform:translateX(4px)}30%,50%,70%{transform:translateX(-6px)}40%,60%{transform:translateX(6px)}}
.ezckg-input-icon{width:48px;height:48px;display:flex;align-items:center;justify-content:center;color:var(--ez-muted);flex-shrink:0}
.ezckg-input-icon svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.ezckg-input{flex:1;height:48px;border:0;outline:0;padding:0 14px;font-family:'EzLensVazir',Tahoma,sans-serif;font-size:16px;font-weight:700;color:var(--ez-text);background:transparent;direction:ltr!important;text-align:left!important;letter-spacing:1.4px;font-variant-numeric:tabular-nums;min-width:0}
.ezckg-input::placeholder{color:#cbd5e1;font-weight:400;letter-spacing:1px;text-align:left}

/* ================================================================
   🆕 OTP ROW — رسپانسیو کامل با Grid + aspect-ratio
   ================================================================ */
.ezckg-otp-row{
	display:grid !important;
	grid-template-columns:repeat(6, minmax(0, 1fr)) !important;
	gap:clamp(4px, 1.2vw, 9px) !important;
	justify-content:center !important;
	align-items:center !important;
	margin:8px auto 18px !important;
	direction:ltr !important;
	width:100% !important;
	max-width:420px !important;
	padding:0 !important;
	box-sizing:border-box !important;
}
.ezckg-otp-box{
	width:100% !important;
	min-width:0 !important;
	max-width:none !important;
	height:auto !important;
	aspect-ratio:5 / 6 !important;
	border:2px solid var(--ez-border) !important;
	border-radius:clamp(9px, 2.5vw, 13px) !important;
	text-align:center !important;
	font-family:'EzLensVazir',Tahoma,sans-serif !important;
	font-size:clamp(16px, 5vw, 22px) !important;
	font-weight:800 !important;
	color:var(--ez-primary) !important;
	background:#fff !important;
	outline:0 !important;
	padding:0 !important;
	margin:0 !important;
	transition:transform .22s cubic-bezier(.34,1.56,.64,1),border-color .22s,box-shadow .25s,background .22s !important;
	-moz-appearance:textfield !important;
	caret-color:var(--ez-primary) !important;
	box-shadow:0 2px 4px rgba(15,23,42,.03) !important;
	line-height:1 !important;
	box-sizing:border-box !important;
	-webkit-appearance:none !important;
	appearance:none !important;
}
.ezckg-otp-box::-webkit-outer-spin-button,
.ezckg-otp-box::-webkit-inner-spin-button{-webkit-appearance:none !important;margin:0 !important}
.ezckg-otp-box:focus{
	border-color:transparent !important;
	background:linear-gradient(#fff,#fff) padding-box,linear-gradient(135deg,#031f8a 0%,#2563eb 50%,#60a5fa 100%) border-box !important;
	box-shadow:0 0 0 4px rgba(3,31,138,.12),0 6px 16px rgba(3,31,138,.15) !important;
	transform:translateY(-3px) scale(1.03) !important;
}
.ezckg-otp-box.is-filled{
	border-color:transparent !important;
	background:linear-gradient(180deg,#f8fafc 0%,#eef2ff 100%) padding-box,linear-gradient(135deg,#031f8a 0%,#2563eb 100%) border-box !important;
	color:var(--ez-primary) !important;
	box-shadow:0 4px 12px rgba(3,31,138,.1) !important;
	animation:ezckgOtpPop .3s cubic-bezier(.34,1.56,.64,1) !important;
}
@keyframes ezckgOtpPop{0%{transform:scale(1)}40%{transform:scale(1.12)}100%{transform:scale(1)}}
.ezckg-otp-box.is-error{
	border-color:transparent !important;
	background:linear-gradient(#fff5f5,#fef2f2) padding-box,linear-gradient(135deg,#dc2626,#ef4444) border-box !important;
	color:#b91c1c !important;
	box-shadow:0 0 0 4px rgba(220,38,38,.08) !important;
	animation:ezckgShake .4s !important;
}
.ezckg-otp-box.is-filled:focus{
	background:linear-gradient(180deg,#eef2ff 0%,#e0e7ff 100%) padding-box,linear-gradient(135deg,#031f8a 0%,#2563eb 50%,#60a5fa 100%) border-box !important;
}

/* موبایل کوچک */
@media(max-width:480px){
	.ezckg-otp-row{gap:4px !important;max-width:100% !important;margin:6px auto 16px !important}
	.ezckg-otp-box{border-width:1.5px !important;font-size:clamp(15px, 5.5vw, 20px) !important}
}
@media(max-width:360px){
	.ezckg-otp-row{gap:3px !important}
	.ezckg-otp-box{font-size:clamp(14px, 5.8vw, 18px) !important;border-radius:8px !important}
}

.ezckg-timer-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;font-size:12.5px;color:var(--ez-muted);gap:8px;flex-wrap:wrap}
.ezckg-timer{display:inline-flex;align-items:center;gap:6px;font-weight:700;color:var(--ez-primary);direction:ltr;font-variant-numeric:tabular-nums}
.ezckg-timer svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2}
.ezckg-timer.is-warning{color:var(--ez-warning)}
.ezckg-timer.is-danger{color:var(--ez-danger);animation:ezckgPulse 1s infinite}
@keyframes ezckgPulse{0%,100%{opacity:1}50%{opacity:.5}}
.ezckg-resend{background:transparent;border:0;color:var(--ez-primary);font-size:12.5px;font-weight:700;cursor:pointer;padding:4px 8px;border-radius:6px;transition:background .2s;font-family:inherit}
.ezckg-resend:hover:not(:disabled){background:rgba(3,31,138,.06)}
.ezckg-resend:disabled{opacity:.4;cursor:not-allowed}
.ezckg-phone-display{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:16px;padding:10px 12px;background:var(--ez-soft);border-radius:10px;font-size:12.5px;color:var(--ez-text-soft);flex-wrap:wrap}
.ezckg-phone-display>svg{width:15px;height:15px;fill:none;stroke:var(--ez-primary);stroke-width:2;flex-shrink:0}
.ezckg-phone-display strong{color:var(--ez-primary);direction:ltr!important;font-family:'EzLensVazir',Tahoma,sans-serif;font-weight:800;letter-spacing:1.4px;font-variant-numeric:tabular-nums;unicode-bidi:embed;display:inline-block;text-align:left}
.ezckg-phone-edit{display:inline-flex;align-items:center;gap:4px;background:transparent;border:0;color:var(--ez-primary);font-size:11.5px;font-weight:700;cursor:pointer;padding:4px 10px;border-radius:6px;transition:background .2s;margin-right:auto;font-family:inherit}
.ezckg-phone-edit:hover{background:rgba(3,31,138,.06)}
.ezckg-phone-edit svg{width:11px;height:11px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.ezckg-btn{position:relative !important;overflow:hidden !important;display:inline-flex !important;align-items:center !important;justify-content:center !important;gap:9px !important;width:100% !important;min-height:52px !important;padding:0 24px !important;border:0 !important;border-radius:12px !important;background:linear-gradient(135deg,#031f8a 0%,#011663 100%) !important;color:#fff !important;font-family:'EzLensVazir',Tahoma,Arial,sans-serif !important;font-size:14.5px !important;font-weight:800 !important;line-height:1 !important;cursor:pointer !important;transition:transform .2s ease, box-shadow .25s ease !important;box-shadow:0 6px 20px rgba(3,31,138,.28) !important;outline:none !important;-webkit-tap-highlight-color:transparent !important}
.ezckg-btn > span,.ezckg-btn > svg{position:relative !important;z-index:2 !important;display:inline-flex !important;align-items:center !important;justify-content:center !important;color:#fff !important;fill:none !important;stroke:currentColor !important;pointer-events:none !important}
.ezckg-btn > svg{width:18px !important;height:18px !important;flex-shrink:0 !important}
.ezckg-btn::before{content:"";position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(120deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%);transition:left .7s ease;pointer-events:none;z-index:1}
.ezckg-btn:hover:not(:disabled){background:linear-gradient(135deg,#041a7a 0%,#010d4a 100%) !important;transform:translateY(-2px) !important;box-shadow:0 10px 30px rgba(3,31,138,.42) !important}
.ezckg-btn:hover:not(:disabled)::before{left:100%}
.ezckg-btn:active:not(:disabled){transform:scale(.98) !important}
.ezckg-btn:focus-visible{box-shadow:0 0 0 4px rgba(3,31,138,.25), 0 6px 20px rgba(3,31,138,.28) !important}
.ezckg-btn:disabled{opacity:.75 !important;cursor:not-allowed !important}
.ezckg-btn.is-loading > span,.ezckg-btn.is-loading > svg{visibility:hidden !important;opacity:0 !important}
.ezckg-btn.is-loading::after{content:"";position:absolute;top:50%;left:50%;width:20px;height:20px;margin:-10px 0 0 -10px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:ezckgBtnSpin .7s linear infinite;z-index:3}
@keyframes ezckgBtnSpin{to{transform:rotate(360deg)}}
.ezckg-btn.is-success{background:linear-gradient(135deg,#059669 0%,#047857 100%) !important;box-shadow:0 6px 20px rgba(5,150,105,.45) !important}
.ezckg-ripple{position:absolute;border-radius:50%;background:rgba(255,255,255,.5);transform:scale(0);animation:ezckgRipple .7s;pointer-events:none;z-index:1}
@keyframes ezckgRipple{to{transform:scale(4);opacity:0}}
.ezckg-back{display:inline-flex !important;align-items:center !important;gap:6px !important;margin-top:12px !important;padding:9px 14px !important;background:transparent !important;border:1.5px solid var(--ez-border) !important;border-radius:9px !important;color:var(--ez-text-soft) !important;font-size:12.5px !important;font-weight:700 !important;cursor:pointer !important;text-decoration:none !important;transition:all .2s !important;font-family:inherit}
.ezckg-back:hover{border-color:var(--ez-primary) !important;color:var(--ez-primary) !important;background:rgba(3,31,138,.04) !important}
.ezckg-back svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
.ezckg-back-row{display:flex;justify-content:center;margin-top:12px}
.ezckg-message{display:none;align-items:flex-start;gap:8px;padding:11px 13px;margin-bottom:12px;border-radius:10px;font-size:12.5px;font-weight:600;line-height:1.7;animation:ezckgFadeUp .3s}
.ezckg-message.is-visible{display:flex}
.ezckg-message.is-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c}
.ezckg-message.is-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.ezckg-message.is-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
.ezckg-message svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;margin-top:2px}
.ezckg-trust{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:10px 18px;margin-top:20px;padding-top:16px;border-top:1px dashed var(--ez-border)}
.ezckg-trust-item{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--ez-muted)}
.ezckg-trust-item svg{width:12px;height:12px;fill:none;stroke:var(--ez-primary);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.ezckg-success{text-align:center;padding:20px 0}
.ezckg-success-icon{width:72px;height:72px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--ez-success-soft),#a7f3d0);border-radius:50%;color:var(--ez-success);animation:ezckgSuccessPop .6s cubic-bezier(.34,1.56,.64,1)}
.ezckg-success-icon svg{width:36px;height:36px;fill:none;stroke:currentColor;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
@keyframes ezckgSuccessPop{0%{transform:scale(.3);opacity:0}60%{transform:scale(1.15)}100%{transform:scale(1);opacity:1}}
.ezckg-success-title{margin:0 0 6px;font-size:19px;font-weight:800;color:var(--ez-success)}
.ezckg-success-sub{margin:0 0 16px;font-size:12.5px;color:var(--ez-muted)}
.ezckg-success-progress{height:4px;background:var(--ez-border);border-radius:20px;overflow:hidden;max-width:180px;margin:0 auto}
.ezckg-success-progress-fill{height:100%;background:linear-gradient(90deg,var(--ez-primary),var(--ez-success));border-radius:inherit;width:0;animation:ezckgProgress 1.5s forwards}
@keyframes ezckgProgress{to{width:100%}}
.ezckg-info{display:flex;align-items:flex-start;gap:9px;padding:10px 12px;margin-top:12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;font-size:11.5px;color:#075985;line-height:1.7}
.ezckg-info svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;margin-top:2px}
.ezckg-info strong{font-weight:700}
</style>

<?php ezck_render_steps( 2 ); ?>

<div class="ezckg-card">
	<div class="ezckg-card-body">
		<div class="ezckg-section is-active" id="ezckgStepPhone">
			<div class="ezckg-hero">
				<h2 class="ezckg-title">
					<span class="ezckg-title-icon"><?php echo $ico_phone; ?></span>
					<span>ورود / ثبت‌نام سریع</span>
				</h2>
				<p class="ezckg-subtitle">برای تکمیل خرید، شماره موبایل خود را وارد کنید.<br>کد تأیید فقط چند ثانیه‌ای ارسال می‌شود.</p>
			</div>
			<div class="ezckg-message" id="ezckgMsgPhone"></div>
			<div class="ezckg-field">
				<label class="ezckg-label" for="ezckgPhone">شماره موبایل</label>
				<div class="ezckg-input-wrap" id="ezckgPhoneWrap">
					<span class="ezckg-input-icon"><?php echo $ico_phone; ?></span>
					<input type="tel" id="ezckgPhone" class="ezckg-input" placeholder="0919 096 1795" inputmode="numeric" autocomplete="tel" maxlength="13" dir="ltr">
				</div>
			</div>
			<button type="button" class="ezckg-btn" id="ezckgSendOtp">
				<span>دریافت کد تأیید</span>
				<?php echo $ico_arrow_left; ?>
			</button>
			<div class="ezckg-info">
				<?php echo $ico_info; ?>
				<div><strong>حساب شما محفوظ است.</strong> اگر قبلاً ثبت‌نام کرده‌اید، با همین شماره وارد می‌شوید. اگر نه، به‌صورت خودکار ثبت‌نام می‌شوید.</div>
			</div>
			<div class="ezckg-trust">
				<span class="ezckg-trust-item"><?php echo $ico_shield; ?> پرداخت امن</span>
				<span class="ezckg-trust-item"><?php echo $ico_lock; ?> اطلاعات رمزنگاری‌شده</span>
				<span class="ezckg-trust-item"><?php echo $ico_check; ?> بدون رمز عبور</span>
			</div>
			<div class="ezckg-back-row">
				<a href="<?php echo esc_url( $cart_url ); ?>" class="ezckg-back">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px"><polyline points="9 18 15 12 9 6"></polyline></svg>
					بازگشت به سبد خرید
				</a>
			</div>
		</div>

		<div class="ezckg-section" id="ezckgStepOtp">
			<div class="ezckg-hero">
				<h2 class="ezckg-title">
					<span class="ezckg-title-icon is-warm"><?php echo $ico_phone; ?></span>
					<span>کد تأیید را وارد کنید</span>
				</h2>
				<p class="ezckg-subtitle">کد ۶ رقمی به شماره زیر ارسال شد.</p>
			</div>
			<div class="ezckg-phone-display">
				<?php echo $ico_phone; ?>
				<strong id="ezckgPhoneDisplay"></strong>
				<button type="button" class="ezckg-phone-edit" id="ezckgEditPhone"><?php echo $ico_edit; ?>ویرایش</button>
			</div>
			<div class="ezckg-message" id="ezckgMsgOtp"></div>
			<div class="ezckg-otp-row" id="ezckgOtpRow" dir="ltr">
				<input type="tel" class="ezckg-otp-box" data-index="0" maxlength="1" inputmode="numeric" autocomplete="one-time-code" aria-label="رقم ۱">
				<input type="tel" class="ezckg-otp-box" data-index="1" maxlength="1" inputmode="numeric" aria-label="رقم ۲">
				<input type="tel" class="ezckg-otp-box" data-index="2" maxlength="1" inputmode="numeric" aria-label="رقم ۳">
				<input type="tel" class="ezckg-otp-box" data-index="3" maxlength="1" inputmode="numeric" aria-label="رقم ۴">
				<input type="tel" class="ezckg-otp-box" data-index="4" maxlength="1" inputmode="numeric" aria-label="رقم ۵">
				<input type="tel" class="ezckg-otp-box" data-index="5" maxlength="1" inputmode="numeric" aria-label="رقم ۶">
			</div>
			<div class="ezckg-timer-row">
				<span class="ezckg-timer" id="ezckgTimer"><?php echo $ico_clock; ?><span id="ezckgTimerText">۰۲:۰۰</span></span>
				<button type="button" class="ezckg-resend" id="ezckgResend" disabled>ارسال مجدد</button>
			</div>
			<button type="button" class="ezckg-btn" id="ezckgVerifyOtp">
				<?php echo $ico_check; ?>
				<span>تأیید و ادامه خرید</span>
			</button>
			<div class="ezckg-back-row">
				<button type="button" class="ezckg-back" id="ezckgBackToPhone">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px"><polyline points="9 18 15 12 9 6"></polyline></svg>
					ویرایش شماره موبایل
				</button>
			</div>
		</div>

		<div class="ezckg-section" id="ezckgStepSuccess">
			<div class="ezckg-success">
				<div class="ezckg-success-icon"><?php echo $ico_check; ?></div>
				<h2 class="ezckg-success-title">خوش آمدید!</h2>
				<p class="ezckg-success-sub">در حال انتقال به صفحه ثبت سفارش...</p>
				<div class="ezckg-success-progress"><div class="ezckg-success-progress-fill"></div></div>
			</div>
		</div>
	</div>
</div>

<script>
(function(){
	'use strict';
	var root = document.getElementById('ezckgRoot');
	if (!root) return;
	var AJAX = root.getAttribute('data-ajax');
	var NONCE = root.getAttribute('data-nonce');
	var EXPIRY = parseInt(root.getAttribute('data-expiry') || '2', 10);
	var REDIR = root.getAttribute('data-checkout');
	var $ = function(s) { return root.querySelector(s); };
	var $$ = function(s) { return root.querySelectorAll(s); };
	var stepPhone = $('#ezckgStepPhone');
	var stepOtp = $('#ezckgStepOtp');
	var stepSuccess = $('#ezckgStepSuccess');
	var phoneInput = $('#ezckgPhone');
	var phoneWrap = $('#ezckgPhoneWrap');
	var phoneDisplay = $('#ezckgPhoneDisplay');
	var otpBoxes = Array.prototype.slice.call($$('.ezckg-otp-box'));
	var btnSend = $('#ezckgSendOtp');
	var btnVerify = $('#ezckgVerifyOtp');
	var btnResend = $('#ezckgResend');
	var btnEdit = $('#ezckgEditPhone');
	var btnBack = $('#ezckgBackToPhone');
	var msgPhone = $('#ezckgMsgPhone');
	var msgOtp = $('#ezckgMsgOtp');
	var timerInterval = null;
	var timerLeft = 0;
	var faDigits = '۰۱۲۳۴۵۶۷۸۹';
	var toFa = function(str) { return String(str || '').replace(/[0-9]/g, function(d) { return faDigits.charAt(d); }); };
	var toEn = function(str) { return String(str || '').replace(/[۰-۹]/g, function(d) { return faDigits.indexOf(d); }); };
	var normalizePhone = function(v) {
		v = toEn(String(v || '')).replace(/[^\d]/g, '');
		if (v.indexOf('98') === 0 && v.length >= 12) v = '0' + v.slice(2);
		if (v.charAt(0) === '9' && v.length === 10) v = '0' + v;
		return v.slice(0, 11);
	};
	var formatPhone = function(v) {
		v = normalizePhone(v);
		if (v.length <= 4) return v;
		if (v.length <= 7) return v.slice(0,4) + ' ' + v.slice(4);
		return v.slice(0,4) + ' ' + v.slice(4,7) + ' ' + v.slice(7,11);
	};
	var isValidPhone = function(v) { return /^09\d{9}$/.test(normalizePhone(v)); };
	var showMsg = function(el, text, type) {
		if (!el) return;
		type = type || 'error';
		el.className = 'ezckg-message is-visible is-' + type;
		var icon = '';
		if (type === 'success') icon = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12l2.5 2.5L16 9"/></svg>';
		else if (type === 'info') icon = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
		else icon = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
		el.innerHTML = icon + '<span>' + text + '</span>';
	};
	var hideMsg = function(el) { if (el) { el.className = 'ezckg-message'; el.innerHTML = ''; } };
	var setLoading = function(btn, loading) {
		if (!btn) return;
		btn.disabled = loading;
		btn.classList.toggle('is-loading', loading);
	};
	root.querySelectorAll('.ezckg-btn').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			var rect = btn.getBoundingClientRect();
			var size = Math.max(rect.width, rect.height);
			var ripple = document.createElement('span');
			ripple.className = 'ezckg-ripple';
			ripple.style.width = ripple.style.height = size + 'px';
			ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
			ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
			btn.appendChild(ripple);
			setTimeout(function() { ripple.remove(); }, 700);
		});
	});
	var showStep = function(step) {
		[stepPhone, stepOtp, stepSuccess].forEach(function(s) { if (s) s.classList.remove('is-active'); });
		if (step) step.classList.add('is-active');
	};
	phoneInput.addEventListener('input', function() {
		var start = phoneInput.selectionStart;
		var before = phoneInput.value.length;
		phoneInput.value = formatPhone(phoneInput.value);
		var after = phoneInput.value.length;
		try { phoneInput.setSelectionRange(start + (after - before), start + (after - before)); } catch(e) {}
		phoneWrap.classList.remove('is-error');
		hideMsg(msgPhone);
	});
	phoneInput.addEventListener('keydown', function(e) {
		if (e.key === 'Enter' && isValidPhone(phoneInput.value)) { e.preventDefault(); btnSend.click(); }
	});
	btnSend.addEventListener('click', function() {
		var raw = normalizePhone(phoneInput.value);
		if (!raw) { phoneWrap.classList.add('is-error'); showMsg(msgPhone, 'لطفاً شماره موبایل خود را وارد کنید.', 'error'); phoneInput.focus(); return; }
		if (!isValidPhone(raw)) { phoneWrap.classList.add('is-error'); showMsg(msgPhone, 'شماره موبایل وارد شده معتبر نیست.', 'error'); phoneInput.focus(); return; }
		hideMsg(msgPhone);
		setLoading(btnSend, true);
		var fd = new FormData();
		fd.append('action', 'ezlens_otp_send');
		fd.append('security', NONCE);
		fd.append('mobile', raw);
		fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				setLoading(btnSend, false);
				if (res && res.success) {
					phoneDisplay.textContent = formatPhone(raw);
					phoneDisplay.setAttribute('dir', 'ltr');
					showStep(stepOtp);
					startTimer(EXPIRY * 60);
					setTimeout(function() { if (otpBoxes[0]) otpBoxes[0].focus(); }, 250);
				} else {
					showMsg(msgPhone, (res && res.data && res.data.message) || 'خطا در ارسال کد.', 'error');
				}
			})
			.catch(function() { setLoading(btnSend, false); showMsg(msgPhone, 'خطای ارتباط با سرور. دوباره تلاش کنید.', 'error'); });
	});
	var getOtpValue = function() {
		var v = '';
		otpBoxes.forEach(function(b) { v += (b.dataset.en || ''); });
		return v;
	};
	var clearOtp = function() {
		otpBoxes.forEach(function(b) { b.value = ''; b.dataset.en = ''; b.classList.remove('is-filled', 'is-error'); });
	};
	otpBoxes.forEach(function(box, idx) {
		box.addEventListener('input', function() {
			var raw = toEn(box.value).replace(/[^\d]/g, '').slice(-1);
			if (raw === '') { box.value = ''; box.dataset.en = ''; box.classList.remove('is-filled'); hideMsg(msgOtp); return; }
			box.dataset.en = raw;
			box.value = toFa(raw);
			box.classList.add('is-filled');
			box.classList.remove('is-error');
			hideMsg(msgOtp);
			if (idx < otpBoxes.length - 1) { otpBoxes[idx + 1].focus(); otpBoxes[idx + 1].select(); }
			if (getOtpValue().length === 6) setTimeout(function() { btnVerify.click(); }, 180);
		});
		box.addEventListener('keydown', function(e) {
			if (e.key === 'Backspace') {
				if (!box.value && idx > 0) {
					e.preventDefault();
					var prev = otpBoxes[idx - 1];
					prev.focus(); prev.value = ''; prev.dataset.en = ''; prev.classList.remove('is-filled');
				} else if (box.value) {
					setTimeout(function() { if (!box.value) { box.dataset.en = ''; box.classList.remove('is-filled'); } }, 0);
				}
			}
			if (e.key === 'ArrowLeft' && idx < otpBoxes.length - 1) { e.preventDefault(); otpBoxes[idx + 1].focus(); otpBoxes[idx + 1].select(); }
			if (e.key === 'ArrowRight' && idx > 0) { e.preventDefault(); otpBoxes[idx - 1].focus(); otpBoxes[idx - 1].select(); }
			if (e.key === 'Enter' && getOtpValue().length === 6) { e.preventDefault(); btnVerify.click(); }
		});
		box.addEventListener('focus', function() { setTimeout(function() { try { box.select(); } catch(e) {} }, 0); });
		box.addEventListener('paste', function(e) {
			e.preventDefault();
			var text = (e.clipboardData || window.clipboardData).getData('text');
			var digits = toEn(text).replace(/[^\d]/g, '').slice(0, 6);
			if (!digits) return;
			digits.split('').forEach(function(d, i) {
				if (otpBoxes[i]) { otpBoxes[i].dataset.en = d; otpBoxes[i].value = toFa(d); otpBoxes[i].classList.add('is-filled'); }
			});
			var nextIdx = Math.min(digits.length, 5);
			if (otpBoxes[nextIdx]) { otpBoxes[nextIdx].focus(); otpBoxes[nextIdx].select(); }
			if (getOtpValue().length === 6) setTimeout(function() { btnVerify.click(); }, 180);
		});
	});
	btnVerify.addEventListener('click', function() {
		var code = getOtpValue();
		var mobile = normalizePhone(phoneInput.value);
		if (code.length !== 6) { otpBoxes.forEach(function(b) { b.classList.add('is-error'); }); showMsg(msgOtp, 'لطفاً کد ۶ رقمی را کامل وارد کنید.', 'error'); return; }
		hideMsg(msgOtp);
		setLoading(btnVerify, true);
		var fd = new FormData();
		fd.append('action', 'ezlens_otp_verify');
		fd.append('security', NONCE);
		fd.append('mobile', mobile);
		fd.append('code', code);
		fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				setLoading(btnVerify, false);
				if (res && res.success) {
					btnVerify.classList.add('is-success');
					clearInterval(timerInterval);
					showStep(stepSuccess);
					setTimeout(function() { root.classList.add('is-leaving'); setTimeout(function() { window.location.replace(REDIR); }, 400); }, 1400);
				} else {
					otpBoxes.forEach(function(b) { b.classList.add('is-error'); });
					showMsg(msgOtp, (res && res.data && res.data.message) || 'کد وارد شده صحیح نیست.', 'error');
					clearOtp();
					if (otpBoxes[0]) otpBoxes[0].focus();
				}
			})
			.catch(function() { setLoading(btnVerify, false); showMsg(msgOtp, 'خطای ارتباط با سرور. دوباره تلاش کنید.', 'error'); });
	});
	var startTimer = function(seconds) {
		clearInterval(timerInterval);
		timerLeft = seconds;
		btnResend.disabled = true;
		updateTimerDisplay();
		timerInterval = setInterval(function() {
			timerLeft--;
			updateTimerDisplay();
			if (timerLeft <= 0) { clearInterval(timerInterval); btnResend.disabled = false; }
		}, 1000);
	};
	var updateTimerDisplay = function() {
		var el = $('#ezckgTimerText');
		var timer = $('#ezckgTimer');
		if (!el) return;
		var m = Math.floor(timerLeft / 60);
		var s = timerLeft % 60;
		el.textContent = toFa(String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0'));
		timer.classList.toggle('is-warning', timerLeft <= 30 && timerLeft > 10);
		timer.classList.toggle('is-danger', timerLeft <= 10);
	};
	btnResend.addEventListener('click', function() {
		var mobile = normalizePhone(phoneInput.value);
		if (!isValidPhone(mobile)) return;
		btnResend.disabled = true;
		hideMsg(msgOtp);
		showMsg(msgOtp, 'در حال ارسال مجدد کد...', 'info');
		var fd = new FormData();
		fd.append('action', 'ezlens_otp_send');
		fd.append('security', NONCE);
		fd.append('mobile', mobile);
		fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				if (res && res.success) {
					showMsg(msgOtp, 'کد جدید ارسال شد.', 'success');
					startTimer(EXPIRY * 60);
					clearOtp();
					if (otpBoxes[0]) otpBoxes[0].focus();
				} else {
					btnResend.disabled = false;
					showMsg(msgOtp, (res && res.data && res.data.message) || 'خطا در ارسال مجدد.', 'error');
				}
			})
			.catch(function() { btnResend.disabled = false; showMsg(msgOtp, 'خطای ارتباط با سرور.', 'error'); });
	});
	var backToPhone = function() {
		clearInterval(timerInterval);
		clearOtp();
		hideMsg(msgOtp);
		showStep(stepPhone);
		setTimeout(function() { phoneInput.focus(); }, 250);
	};
	btnEdit.addEventListener('click', backToPhone);
	btnBack.addEventListener('click', backToPhone);
})();
</script>
</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'ezlens_checkout_gated', 'ezck_gate_render' );

/* ============================================================
   Steps bar on checkout page
   ============================================================ */
if ( ! function_exists( 'ezck_checkout_steps_bar' ) ) {
	add_action( 'woocommerce_before_checkout_form', 'ezck_checkout_steps_bar', 5 );
	function ezck_checkout_steps_bar() {
		if ( is_user_logged_in() === false ) return;
		ezck_render_steps( 3 );
	}
}

/* ============================================================
   🆕 Save Lat/Lng to order
   ============================================================ */
if ( ! function_exists( 'ezck_save_latlng_to_order' ) ) {
	add_action( 'woocommerce_checkout_create_order', 'ezck_save_latlng_to_order', 10, 2 );
	function ezck_save_latlng_to_order( $order, $data ) {
		if ( ! empty( $_POST['ezck_lat'] ) && ! empty( $_POST['ezck_lng'] ) ) {
			$order->update_meta_data( '_ezck_lat', sanitize_text_field( wp_unslash( $_POST['ezck_lat'] ) ) );
			$order->update_meta_data( '_ezck_lng', sanitize_text_field( wp_unslash( $_POST['ezck_lng'] ) ) );
		}
	}
	add_action( 'woocommerce_admin_order_data_after_shipping_address', 'ezck_show_latlng_admin', 10, 1 );
	function ezck_show_latlng_admin( $order ) {
		$lat = $order->get_meta( '_ezck_lat' );
		$lng = $order->get_meta( '_ezck_lng' );
		if ( $lat && $lng ) {
			echo '<p style="margin-top:8px;"><strong>موقعیت روی نقشه:</strong><br>';
			echo '<a href="https://www.openstreetmap.org/?mlat=' . esc_attr( $lat ) . '&mlon=' . esc_attr( $lng ) . '#map=17/' . esc_attr( $lat ) . '/' . esc_attr( $lng ) . '" target="_blank" rel="noopener">مشاهده روی OpenStreetMap</a></p>';
		}
	}
}

/* ============================================================
   🆕 Server-side safety — filter زودتر + sanitization کامل
   ============================================================ */
if ( ! function_exists( 'ezck_fill_missing_address_server_side' ) ) {
	add_filter( 'woocommerce_checkout_posted_data', 'ezck_fill_posted_data_pre_validation', 5 );
	function ezck_fill_posted_data_pre_validation( $data ) {
		if ( ! function_exists( 'WC' ) || ! WC()->customer ) return $data;
		$prefix = 'billing';
		$data['billing_country']  = 'IR';
		$data['shipping_country'] = 'IR';

		$address_1 = isset( $data[ $prefix . '_address_1' ] ) ? sanitize_text_field( (string) $data[ $prefix . '_address_1' ] ) : '';
		$city      = isset( $data[ $prefix . '_city' ] )      ? sanitize_text_field( (string) $data[ $prefix . '_city' ] ) : '';
		$state     = isset( $data[ $prefix . '_state' ] )     ? sanitize_text_field( (string) $data[ $prefix . '_state' ] ) : '';
		$postcode  = isset( $data[ $prefix . '_postcode' ] )  ? sanitize_text_field( (string) $data[ $prefix . '_postcode' ] ) : '';
		$plaque    = isset( $data[ $prefix . '_plaque' ] )    ? sanitize_text_field( (string) $data[ $prefix . '_plaque' ] ) : '';

		if ( $address_1 === '' ) { $data[ $prefix . '_address_1' ] = 'تهران'; }
		if ( $city === '' )      { $data[ $prefix . '_city' ]      = 'تهران'; }
		if ( $postcode === '' )  { $data[ $prefix . '_postcode' ]  = '1234567890'; }
		if ( $plaque === '' )    { $data[ $prefix . '_plaque' ]    = '-'; }

		if ( $state === '' ) {
			$valid_states = array();
			if ( function_exists( 'WC' ) && WC()->countries ) {
				$valid_states = WC()->countries->get_states( 'IR' );
			}
			$default_state_code = 'THR';
			if ( is_array( $valid_states ) && ! empty( $valid_states ) ) {
				if ( ! isset( $valid_states[ $default_state_code ] ) ) {
					$keys = array_keys( $valid_states );
					$default_state_code = $keys[0];
				}
			}
			$data[ $prefix . '_state' ] = $default_state_code;
		}
		return $data;
	}

	add_action( 'woocommerce_after_checkout_validation', 'ezck_fill_missing_address_server_side', 5, 2 );
	function ezck_fill_missing_address_server_side( $data, $errors ) {
		if ( ! function_exists( 'WC' ) || ! WC()->customer ) return;
		$prefix = 'billing';
		$_POST['billing_country']  = 'IR';
		$_POST['shipping_country'] = 'IR';
		if ( empty( $_POST[ $prefix . '_address_1' ] ) ) $_POST[ $prefix . '_address_1' ] = 'تهران';
		if ( empty( $_POST[ $prefix . '_city' ] ) )      $_POST[ $prefix . '_city' ]      = 'تهران';
		if ( empty( $_POST[ $prefix . '_postcode' ] ) )  $_POST[ $prefix . '_postcode' ]  = '1234567890';
		if ( empty( $_POST[ $prefix . '_state' ] ) )     $_POST[ $prefix . '_state' ]     = 'THR';
		if ( empty( $_POST[ $prefix . '_plaque' ] ) )    $_POST[ $prefix . '_plaque' ]    = '-';

		if ( WC()->customer ) {
			WC()->customer->set_billing_country( 'IR' );
			WC()->customer->set_billing_address_1( sanitize_text_field( wp_unslash( $_POST['billing_address_1'] ?? 'تهران' ) ) );
			WC()->customer->set_billing_city( sanitize_text_field( wp_unslash( $_POST['billing_city'] ?? 'تهران' ) ) );
			WC()->customer->set_billing_state( sanitize_text_field( wp_unslash( $_POST['billing_state'] ?? 'THR' ) ) );
			WC()->customer->set_billing_postcode( sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ?? '1234567890' ) ) );
			WC()->customer->set_shipping_country( 'IR' );
			WC()->customer->set_shipping_address_1( sanitize_text_field( wp_unslash( $_POST['billing_address_1'] ?? 'تهران' ) ) );
			WC()->customer->set_shipping_city( sanitize_text_field( wp_unslash( $_POST['billing_city'] ?? 'تهران' ) ) );
			WC()->customer->set_shipping_state( sanitize_text_field( wp_unslash( $_POST['billing_state'] ?? 'THR' ) ) );
			WC()->customer->set_shipping_postcode( sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ?? '1234567890' ) ) );
			WC()->customer->save();
			if ( WC()->cart ) {
				WC()->cart->calculate_shipping();
				WC()->cart->calculate_totals();
			}
		}
	}
}

/* ============================================================
   Allow checkout without selected shipping method
   ============================================================ */
if ( ! function_exists( 'ezck_allow_checkout_without_shipping_method' ) ) {
	add_action( 'woocommerce_checkout_process', 'ezck_auto_select_shipping_method', 1 );
	function ezck_auto_select_shipping_method() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) return;
		if ( ! WC()->cart->needs_shipping() || ! WC()->cart->show_shipping() ) return;
		$chosen = WC()->session->get( 'chosen_shipping_methods' );
		$packages = WC()->shipping()->get_packages();
		if ( empty( $packages ) ) return;
		$needs_update = false;
		$new_chosen   = is_array( $chosen ) ? $chosen : array();
		foreach ( $packages as $i => $package ) {
			$package_key = (string) $i;
			if ( ! empty( $new_chosen[ $package_key ] ) ) continue;
			if ( ! empty( $_POST['shipping_method'][ $i ] ) ) {
				$new_chosen[ $package_key ] = sanitize_text_field( wp_unslash( $_POST['shipping_method'][ $i ] ) );
				$needs_update = true;
				continue;
			}
			if ( ! empty( $package['rates'] ) && is_array( $package['rates'] ) ) {
				$first_rate = reset( $package['rates'] );
				if ( $first_rate && is_object( $first_rate ) && ! empty( $first_rate->id ) ) {
					$new_chosen[ $package_key ] = $first_rate->id;
					$needs_update = true;
				}
			}
		}
		if ( $needs_update && ! empty( $new_chosen ) ) {
			WC()->session->set( 'chosen_shipping_methods', $new_chosen );
			if ( empty( $_POST['shipping_method'] ) || ! is_array( $_POST['shipping_method'] ) ) {
				$_POST['shipping_method'] = array();
			}
			foreach ( $new_chosen as $idx => $method_id ) {
				$_POST['shipping_method'][ $idx ] = $method_id;
			}
			WC()->cart->calculate_shipping();
			WC()->cart->calculate_totals();
		}
	}

	add_action( 'woocommerce_after_checkout_validation', 'ezck_remove_no_shipping_method_error', 99, 2 );
	function ezck_remove_no_shipping_method_error( $data, $errors ) {
		if ( ! $errors || ! is_wp_error( $errors ) ) return;
		$messages_to_kill = array(
			'هیچ روش حمل و نقلی انتخاب نشده است',
			'هیچ روش حمل‌ونقلی انتخاب نشده است',
			'هیچ روش حمل و نقلی انتخاب نشده',
			'No shipping method has been selected',
			'no shipping method has been selected',
		);
		foreach ( $errors->get_error_codes() as $code ) {
			foreach ( $errors->get_error_messages( $code ) as $msg ) {
				$msg_lower = mb_strtolower( $msg );
				foreach ( $messages_to_kill as $needle ) {
					if ( false !== mb_strpos( $msg_lower, mb_strtolower( $needle ) ) ) {
						$errors->remove( $code );
						break 2;
					}
				}
			}
		}
		foreach ( array( 'shipping', 'shipping_method', 'no_shipping' ) as $sc ) {
			if ( $errors->get_error_message( $sc ) ) $errors->remove( $sc );
		}
	}

	add_action( 'woocommerce_checkout_create_order', 'ezck_force_shipping_on_order', 5, 2 );
	function ezck_force_shipping_on_order( $order, $data ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) return;
		$chosen = WC()->session->get( 'chosen_shipping_methods' );
		if ( ! empty( $chosen ) && is_array( $chosen ) ) {
			foreach ( $chosen as $m ) { if ( ! empty( $m ) ) return; }
		}
		$packages = WC()->shipping()->get_packages();
		if ( empty( $packages ) ) return;
		$new_chosen = array();
		foreach ( $packages as $i => $package ) {
			if ( ! empty( $package['rates'] ) ) {
				$first = reset( $package['rates'] );
				if ( $first && ! empty( $first->id ) ) $new_chosen[ (string) $i ] = $first->id;
			}
		}
		if ( ! empty( $new_chosen ) ) WC()->session->set( 'chosen_shipping_methods', $new_chosen );
	}
}


/* ============================================================
   Cart quantity label on checkout (Persian text instead of x N)
   ============================================================ */
if ( ! function_exists( 'ezck_checkout_qty_label' ) ) {
	add_filter( 'woocommerce_checkout_cart_item_quantity', 'ezck_checkout_qty_label', 20, 3 );
	function ezck_checkout_qty_label( $html, $cart_item, $cart_item_key ) {
		$qty = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
		$fa  = str_replace(
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
			array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
			(string) $qty
		);
		return ' <strong class="product-quantity ezck-qty-label">' . esc_html( $fa . ' عدد خرید' ) . '</strong>';
	}
}

/* ============================================================
   COUPON under order total
   ============================================================ */
if ( ! function_exists( 'ezck_coupon_under_total' ) ) {
	add_action( 'woocommerce_review_order_after_order_total', 'ezck_coupon_under_total', 10 );
	function ezck_coupon_under_total() {
		if ( ! function_exists( 'wc_coupons_enabled' ) || ! wc_coupons_enabled() ) return;
		$apply_coupon_nonce = wp_create_nonce( 'apply-coupon' );
		$wc_ajax_url = function_exists( 'WC' ) ? WC()->ajax_url() : admin_url( 'admin-ajax.php' );
		?>
		<tr class="ezck-coupon-row">
			<td colspan="2" class="ezck-coupon-cell">
				<div class="ezck-coupon-wrap" data-open="0" data-nonce="<?php echo esc_attr( $apply_coupon_nonce ); ?>" data-ajax-url="<?php echo esc_url( $wc_ajax_url ); ?>">
					<button type="button" class="ezck-coupon-toggle" aria-expanded="false" aria-controls="ezckCouponPanel">
						<span class="ezck-coupon-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M20.59 13.41 12 22l-9-9V3h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
								<circle cx="7.5" cy="7.5" r="1.5"/>
							</svg>
						</span>
						<span class="ezck-coupon-txt">کد تخفیف دارید؟ اینجا وارد کنید</span>
						<span class="ezck-coupon-chev" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</button>
					<div class="ezck-coupon-panel" id="ezckCouponPanel" hidden>
						<div class="ezck-coupon-form">
							<div class="ezck-coupon-input-row">
								<input type="text" name="ezck_coupon_code" class="ezck-coupon-input" id="ezck_coupon_code" placeholder="کد تخفیف را وارد کنید" value="" autocomplete="off" autocapitalize="off" spellcheck="false">
								<button type="button" class="ezck-coupon-apply" id="ezck_coupon_apply">
									<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
									<span>اعمال</span>
								</button>
							</div>
							<div class="ezck-coupon-hint">
								<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
								کد تخفیف در همان لحظه روی مجموع سفارش اعمال می‌شود.
							</div>
							<div class="ezck-coupon-feedback" id="ezck_coupon_feedback" hidden></div>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<?php
	}
}

/* ============================================================
   TOTAL PAYABLE
   ============================================================ */
if ( ! function_exists( 'ezck_total_payable_row' ) ) {
	add_action( 'woocommerce_review_order_after_order_total', 'ezck_total_payable_row', 20 );
	function ezck_total_payable_row() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) return;
		$total = (int) round( (float) WC()->cart->total );
		$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : 'تومان';
		?>
		<tr class="ezck-payable-row">
			<td colspan="2" class="ezck-payable-cell">
				<div class="ezck-payable" data-total="<?php echo esc_attr( $total ); ?>">
					<div class="ezck-payable__head">
						<span class="ezck-payable__label">
							<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
							مجموع کل قابل پرداخت
						</span>
						<span class="ezck-payable__amount" id="ezckPayableAmount" data-raw="<?php echo esc_attr( $total ); ?>">
							<?php echo esc_html( number_format( $total ) ); ?>
							<span class="ezck-payable__currency"><?php echo esc_html( $currency ); ?></span>
						</span>
					</div>
					<div class="ezck-payable__words">
						<span class="ezck-payable__words-label">
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
							به حروف:
						</span>
						<span class="ezck-payable__words-value" id="ezckPayableWords">—</span>
					</div>
				</div>
			</td>
		</tr>
		<?php
	}
}

/* ============================================================
   TRUST BADGES
   ============================================================ */
if ( ! function_exists( 'ezck_checkout_trust_badges' ) ) {
	add_action( 'woocommerce_review_order_after_submit', 'ezck_checkout_trust_badges', 10 );
	function ezck_checkout_trust_badges() {
		$ico_shield = ezck_gate_icon_or( 'shield', 'shield-check' );
		$ico_lock   = ezck_gate_icon_or( 'lock', 'lock-password-unlocked-svgrepo-com' );
		$ico_truck  = ezck_gate_icon_or( 'truck', 'delivery' );
		$ico_check  = ezck_gate_icon_or( 'check-circle', 'check' );
		?>
		<div class="ezck-trust-badges" dir="rtl">
			<div class="ezck-trust-item"><span class="ezck-trust-icon"><?php echo $ico_shield; ?></span><div><strong>تضمین اصالت کالا</strong><span>۱۰۰٪ اورجینال</span></div></div>
			<div class="ezck-trust-item"><span class="ezck-trust-icon"><?php echo $ico_lock; ?></span><div><strong>پرداخت امن</strong><span>اطلاعات رمزنگاری‌شده</span></div></div>
			<div class="ezck-trust-item"><span class="ezck-trust-icon"><?php echo $ico_truck; ?></span><div><strong>ارسال سریع</strong><span>به سراسر کشور</span></div></div>
			<div class="ezck-trust-item"><span class="ezck-trust-icon"><?php echo $ico_check; ?></span><div><strong>۷ روز ضمانت</strong><span>بازگشت بدون قید</span></div></div>
		</div>
		<?php
	}
}

/* ============================================================
   🆕 فیلدهای پلاک و واحد + ترتیب جدید فیلدها
   ============================================================ */

/* Remove default order notes (use billing_address_2 notes in form instead) */
if ( ! function_exists( 'ezck_remove_order_comments_field' ) ) {
	add_filter( 'woocommerce_checkout_fields', 'ezck_remove_order_comments_field', 100001 );
	function ezck_remove_order_comments_field( $fields ) {
		if ( isset( $fields['order']['order_comments'] ) ) {
			unset( $fields['order']['order_comments'] );
		}
		return $fields;
	}
}

if ( ! function_exists( 'ezck_reorder_billing_fields' ) ) {
	add_filter( 'woocommerce_checkout_fields', 'ezck_reorder_billing_fields', 99999 );
	function ezck_reorder_billing_fields( $fields ) {

		// Remove unused company field only (address_2 reused as notes)
		unset( $fields['billing']['billing_company'] );

		// Palette 1 — customer only
		$fields['billing']['billing_first_name']['priority'] = 10;
		$fields['billing']['billing_first_name']['required'] = true;
		$fields['billing']['billing_first_name']['class']    = array( 'form-row-first', 'ezck-p1' );
		$fields['billing']['billing_first_name']['label']    = 'نام';

		$fields['billing']['billing_last_name']['priority']  = 20;
		$fields['billing']['billing_last_name']['required']  = true;
		$fields['billing']['billing_last_name']['class']     = array( 'form-row-last', 'ezck-p1' );
		$fields['billing']['billing_last_name']['label']     = 'نام خانوادگی';

		$fields['billing']['billing_phone']['priority']      = 30;
		$fields['billing']['billing_phone']['required']      = true;
		$fields['billing']['billing_phone']['class']         = array( 'form-row-first', 'ezck-p1' );
		$fields['billing']['billing_phone']['label']         = 'شماره تماس';

		$fields['billing']['billing_email']['priority']      = 40;
		$fields['billing']['billing_email']['required']      = false;
		$fields['billing']['billing_email']['class']         = array( 'form-row-last', 'ezck-p1' );
		$fields['billing']['billing_email']['label']         = 'ایمیل (اختیاری)';

		// Palette 3 — shipping details (all optional)
		$fields['billing']['billing_state']['priority']      = 50;
		$fields['billing']['billing_state']['required']      = false;
		$fields['billing']['billing_state']['class']         = array( 'form-row-first', 'ezck-p3' );
		$fields['billing']['billing_state']['label']         = 'نام استان';

		$fields['billing']['billing_city']['priority']       = 60;
		$fields['billing']['billing_city']['required']       = false;
		$fields['billing']['billing_city']['class']          = array( 'form-row-last', 'ezck-p3' );
		$fields['billing']['billing_city']['label']          = 'نام شهر';

		$fields['billing']['billing_address_1']['priority']  = 70;
		$fields['billing']['billing_address_1']['required']  = false;
		$fields['billing']['billing_address_1']['class']     = array( 'form-row-wide', 'ezck-p3' );
		$fields['billing']['billing_address_1']['label']     = 'خیابان';
		$fields['billing']['billing_address_1']['placeholder'] = 'مثلاً: محله فلان، خیابان فلان، کوچه فلان';
		$fields['billing']['billing_address_1']['type']      = 'textarea';
		$fields['billing']['billing_address_1']['custom_attributes'] = array(
			'rows'      => '3',
			'maxlength' => '400',
		);

		$fields['billing']['billing_plaque'] = array(
			'type'              => 'text',
			'label'             => 'پلاک',
			'placeholder'       => 'مثلاً ۱۲',
			'required'          => false,
			'class'             => array( 'form-row-first', 'ezck-field-plaque', 'ezck-p3' ),
			'priority'          => 80,
			'custom_attributes' => array( 'inputmode' => 'numeric', 'maxlength' => '10' ),
		);

		$fields['billing']['billing_unit'] = array(
			'type'              => 'text',
			'label'             => 'واحد',
			'placeholder'       => 'مثلاً ۳',
			'required'          => false,
			'class'             => array( 'form-row-last', 'ezck-field-unit', 'ezck-p3' ),
			'priority'          => 90,
			'custom_attributes' => array( 'inputmode' => 'numeric', 'maxlength' => '10' ),
		);

		$fields['billing']['billing_postcode']['priority']    = 100;
		$fields['billing']['billing_postcode']['required']    = false;
		$fields['billing']['billing_postcode']['class']       = array( 'form-row-first', 'ezck-p3' );
		$fields['billing']['billing_postcode']['label']       = 'کد پستی';
		$fields['billing']['billing_postcode']['placeholder'] = '۱۰ رقم';

		// Notes / description (optional)
		$fields['billing']['billing_address_2']['priority']  = 110;
		$fields['billing']['billing_address_2']['required']  = false;
		$fields['billing']['billing_address_2']['class']     = array( 'form-row-wide', 'ezck-p3' );
		$fields['billing']['billing_address_2']['label']     = 'توضیحات';
		$fields['billing']['billing_address_2']['placeholder'] = 'توضیحات تکمیلی برای پیک (اختیاری)';
		$fields['billing']['billing_address_2']['type']      = 'textarea';
		$fields['billing']['billing_address_2']['custom_attributes'] = array(
			'rows'      => '2',
			'maxlength' => '300',
		);

		return $fields;
	}
}

/* ============================================================
   🆕 ذخیره پلاک/واحد در متای مشتری ووکامرس
   ============================================================ */

/* ============================================================
   Soft validation: palette 3 address fields stay optional
   ============================================================ */
if ( ! function_exists( 'ezck_soft_address_validation' ) ) {
	add_action( 'woocommerce_after_checkout_validation', 'ezck_soft_address_validation', 5, 2 );
	function ezck_soft_address_validation( $data, $errors ) {
		if ( ! $errors instanceof WP_Error ) return;
		$optional_keys = array(
			'billing_state',
			'billing_city',
			'billing_address_1',
			'billing_postcode',
			'billing_plaque',
			'billing_unit',
			'billing_email',
			'billing_address_2',
		);
		foreach ( $optional_keys as $key ) {
			// Remove required errors for optional presentation fields
			$codes = $errors->get_error_codes();
			foreach ( $codes as $code ) {
				$msg = $errors->get_error_message( $code );
				if ( false !== strpos( (string) $code, $key ) || false !== strpos( (string) $msg, $key ) ) {
					// Only drop "required" style messages; keep format errors if any
					if ( false !== strpos( strtolower( (string) $msg ), 'required' )
						|| false !== strpos( (string) $msg, 'اجباری' )
						|| false !== strpos( (string) $msg, 'الزامی' )
						|| false !== strpos( (string) $msg, 'لازم' ) ) {
						$errors->remove( $code );
					}
				}
			}
		}
	}
}


/* Force optional shipping field flags even if theme overrides earlier */
if ( ! function_exists( 'ezck_force_optional_shipping_fields' ) ) {
	add_filter( 'woocommerce_checkout_fields', 'ezck_force_optional_shipping_fields', 100000 );
	function ezck_force_optional_shipping_fields( $fields ) {
		$optional = array( 'billing_state', 'billing_city', 'billing_address_1', 'billing_address_2', 'billing_postcode', 'billing_plaque', 'billing_unit', 'billing_email' );
		foreach ( $optional as $key ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['required'] = false;
			}
		}
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['required'] = true;
			$fields['billing']['billing_phone']['label']    = 'شماره تماس';
		}
		if ( isset( $fields['billing']['billing_address_1'] ) ) {
			$fields['billing']['billing_address_1']['label']       = 'خیابان';
			$fields['billing']['billing_address_1']['placeholder'] = 'مثلاً: محله فلان، خیابان فلان، کوچه فلان';
		}
		if ( isset( $fields['billing']['billing_state'] ) ) {
			$fields['billing']['billing_state']['label'] = 'نام استان';
		}
		if ( isset( $fields['billing']['billing_city'] ) ) {
			$fields['billing']['billing_city']['label'] = 'نام شهر';
		}
		if ( isset( $fields['billing']['billing_postcode'] ) ) {
			$fields['billing']['billing_postcode']['label'] = 'کد پستی';
		}
		return $fields;
	}
}

if ( ! function_exists( 'ezck_save_plaque_unit_to_customer' ) ) {
	add_action( 'woocommerce_checkout_update_customer', 'ezck_save_plaque_unit_to_customer', 10, 2 );
	function ezck_save_plaque_unit_to_customer( $customer, $data ) {
		if ( ! empty( $_POST['billing_plaque'] ) ) {
			$customer->update_meta_data( 'billing_plaque', sanitize_text_field( wp_unslash( $_POST['billing_plaque'] ) ) );
		}
		if ( ! empty( $_POST['billing_unit'] ) ) {
			$customer->update_meta_data( 'billing_unit', sanitize_text_field( wp_unslash( $_POST['billing_unit'] ) ) );
		}
	}
}

/* ============================================================
   🆕 ذخیره پلاک/واحد در متای سفارش
   ============================================================ */
if ( ! function_exists( 'ezck_save_plaque_unit_to_order' ) ) {
	add_action( 'woocommerce_checkout_create_order', 'ezck_save_plaque_unit_to_order', 20, 2 );
	function ezck_save_plaque_unit_to_order( $order, $data ) {
		if ( ! empty( $_POST['billing_plaque'] ) ) {
			$order->update_meta_data( '_billing_plaque', sanitize_text_field( wp_unslash( $_POST['billing_plaque'] ) ) );
		}
		if ( ! empty( $_POST['billing_unit'] ) ) {
			$order->update_meta_data( '_billing_unit', sanitize_text_field( wp_unslash( $_POST['billing_unit'] ) ) );
		}
	}
}

/* ============================================================
   🆕 نمایش پلاک/واحد در پنل ادمین سفارش
   ============================================================ */
if ( ! function_exists( 'ezck_show_plaque_unit_admin' ) ) {
	add_action( 'woocommerce_admin_order_data_after_billing_address', 'ezck_show_plaque_unit_admin', 10, 1 );
	function ezck_show_plaque_unit_admin( $order ) {
		$plaque = $order->get_meta( '_billing_plaque' );
		$unit   = $order->get_meta( '_billing_unit' );
		if ( $plaque ) echo '<p><strong>پلاک:</strong> ' . esc_html( $plaque ) . '</p>';
		if ( $unit )   echo '<p><strong>واحد / طبقه:</strong> ' . esc_html( $unit ) . '</p>';
	}
}

/* ============================================================
   CHECKOUT PAGE — CSS + JS
   ============================================================ */
if ( ! function_exists( 'ezck_style_checkout_page' ) ) {
	add_action( 'wp_head', 'ezck_style_checkout_page', 999 );
	function ezck_style_checkout_page() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) return;
		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) return;
		$font_url = EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/';
		$leaflet_css_local = EZLAUTH_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.css';
		$has_local_leaflet = file_exists( EZLAUTH_PLUGIN_DIR . 'assets/vendor/leaflet/leaflet.css' );
		?>
<?php if ( $has_local_leaflet ) : ?>
<link rel="stylesheet" href="<?php echo esc_url( $leaflet_css_local ); ?>">
<?php else : ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<?php endif; ?>

<style id="ezck-checkout-styles-v20">
@font-face{font-family:'EzLensVazir';src:url('<?php echo esc_url( $font_url . 'Vazirmatn-Regular.woff2' ); ?>') format('woff2');font-weight:400;font-display:swap}
@font-face{font-family:'EzLensVazir';src:url('<?php echo esc_url( $font_url . 'Vazirmatn-Medium.woff2' ); ?>') format('woff2');font-weight:500;font-display:swap}
@font-face{font-family:'EzLensVazir';src:url('<?php echo esc_url( $font_url . 'Vazirmatn-Bold.woff2' ); ?>') format('woff2');font-weight:700;font-display:swap}

/* مخفی‌سازی قوی بخش آدرس ارسال */
body.ezck-checkout-page #ship-to-different-address,
body.ezck-checkout-page #ship-to-different-address-checkbox,
body.ezck-checkout-page h3#ship-to-different-address,
body.ezck-checkout-page .ship-to-different-address,
body.ezck-checkout-page .woocommerce-shipping-fields,
body.ezck-checkout-page .woocommerce-shipping-fields > h3,
body.ezck-checkout-page .woocommerce-shipping-fields__field-wrapper,
body.ezck-checkout-page .shipping_address,
body.ezck-checkout-page #shipping_country_field,
body.ezck-checkout-page #shipping_first_name_field,
body.ezck-checkout-page #shipping_last_name_field,
body.ezck-checkout-page #shipping_address_1_field,
body.ezck-checkout-page #shipping_address_2_field,
body.ezck-checkout-page #shipping_city_field,
body.ezck-checkout-page #shipping_state_field,
body.ezck-checkout-page #shipping_postcode_field,
body.ezck-checkout-page #shipping_phone_field,
body.ezck-checkout-page #shipping_company_field,
body.ezck-gate-page #ship-to-different-address,
body.ezck-gate-page .woocommerce-shipping-fields,
body.ezck-gate-page .shipping_address{
	display:none !important;visibility:hidden !important;
	height:0 !important;max-height:0 !important;min-height:0 !important;
	overflow:hidden !important;opacity:0 !important;
	position:absolute !important;left:-99999px !important;top:-99999px !important;
	pointer-events:none !important;margin:0 !important;padding:0 !important;border:0 !important;
}

html body.ezck-checkout-page,
html body.ezck-checkout-page *{box-sizing:border-box}
html body.ezck-checkout-page{background:#f7f9fc !important;color:#0f172a !important;font-family:'EzLensVazir',Tahoma,Arial,sans-serif !important;font-size:14px !important;line-height:1.8 !important;overflow-x:hidden !important}

html body.ezck-checkout-page .wd-content-area,
html body.ezck-checkout-page .woocommerce.entry-content{
	display:flex !important;flex-direction:column !important;
	align-items:center !important;justify-content:flex-start !important;
	width:100% !important;max-width:100% !important;margin:0 auto !important;padding:24px 16px 60px !important;
}
html body.ezck-checkout-page #secondary,
html body.ezck-checkout-page aside.sidebar,
html body.ezck-checkout-page .sidebar-container,
html body.ezck-checkout-page .wd-sidebar,
html body.ezck-checkout-page .wd-sidebar-container,
html body.ezck-checkout-page .woodmart-sidebar{display:none !important;width:0 !important;flex:0 0 0 !important}

html body.ezck-checkout-page .wd-checkout-coupon,
html body.ezck-checkout-page .wd-checkout-login,
html body.ezck-checkout-page .woocommerce-form-coupon-toggle,
html body.ezck-checkout-page .woocommerce-form-login-toggle,
html body.ezck-checkout-page form.checkout_coupon:not(.ezck-coupon-form),
html body.ezck-checkout-page form.woocommerce-form-login,
html body.ezck-checkout-page .woocommerce-info,
html body.ezck-checkout-page .woocommerce-notices-wrapper .woocommerce-info,
html body.ezck-checkout-page .wc-block-components-notice-banner.is-info{
	display:none !important;visibility:hidden !important;height:0 !important;
	overflow:hidden !important;margin:0 !important;padding:0 !important;border:0 !important;
}

<?php ezck_steps_css(); ?>


/* Checkout page container width in em — 85em desktop, never overflow viewport */
html body.ezck-checkout-page .e-con,
html body.ezck-checkout-page .e-con.e-flex,
html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
html body.ezck-checkout-page .elementor-container{
	width:min(85em, 100vw - 2em) !important;
	max-width:min(85em, 100vw - 2em) !important;
	margin-left:auto !important;
	margin-right:auto !important;
	box-sizing:border-box !important;
}
html body.ezck-checkout-page .elementor-section.elementor-section-boxed{
	max-width:min(85em, 100vw - 2em) !important;
	margin-left:auto !important;
	margin-right:auto !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form){
	width:min(85em, 100vw - 2em) !important;
	max-width:min(85em, 100vw - 2em) !important;
	box-sizing:border-box !important;
}
html body.ezck-checkout-page .ezp-steps{
	width:min(85em, 100vw - 2em) !important;
	max-width:min(85em, 100vw - 2em) !important;
}
@media (max-width: 90em){
	html body.ezck-checkout-page .e-con,
	html body.ezck-checkout-page .e-con.e-flex,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
	html body.ezck-checkout-page .elementor-container,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form),
	html body.ezck-checkout-page .ezp-steps{
		width:min(78em, 100vw - 2em) !important;
		max-width:min(78em, 100vw - 2em) !important;
	}
}
@media (max-width: 75em){
	html body.ezck-checkout-page .e-con,
	html body.ezck-checkout-page .e-con.e-flex,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
	html body.ezck-checkout-page .elementor-container,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form),
	html body.ezck-checkout-page .ezp-steps{
		width:min(70em, 100vw - 2em) !important;
		max-width:min(70em, 100vw - 2em) !important;
	}
}
@media (max-width: 64em){
	html body.ezck-checkout-page .e-con,
	html body.ezck-checkout-page .e-con.e-flex,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
	html body.ezck-checkout-page .elementor-container,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form),
	html body.ezck-checkout-page .ezp-steps{
		width:min(58em, 100vw - 1.5em) !important;
		max-width:min(58em, 100vw - 1.5em) !important;
	}
}
@media (max-width: 48em){
	html body.ezck-checkout-page .e-con,
	html body.ezck-checkout-page .e-con.e-flex,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
	html body.ezck-checkout-page .elementor-container,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form),
	html body.ezck-checkout-page .ezp-steps{
		width:100% !important;
		max-width:100% !important;
	}
}
@media (max-width: 30em){
	html body.ezck-checkout-page .e-con,
	html body.ezck-checkout-page .e-con.e-flex,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
	html body.ezck-checkout-page .elementor-container,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form),
	html body.ezck-checkout-page .ezp-steps{
		width:100% !important;
		max-width:100% !important;
	}
}

html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form){
	display:grid !important;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr) !important;
	gap:24px !important;align-items:start !important;
	width:min(85em, 100vw - 2em) !important;max-width:min(85em, 100vw - 2em) !important;
	margin:0 auto !important;padding:0 !important;float:none !important;
	box-sizing:border-box !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) > *{min-width:0 !important;max-width:100% !important;float:none !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) #customer_details{
	grid-column:1 !important;background:#fff !important;border:1px solid #e8edf5 !important;
	border-radius:20px !important;padding:32px !important;
	box-shadow:0 6px 30px rgba(15,23,42,.05) !important;
	width:100% !important;max-width:100% !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) #order_review_heading{display:none !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) .checkout-order-review{
	grid-column:2 !important;background:#fff !important;border:1px solid #e8edf5 !important;
	border-radius:20px !important;padding:28px !important;
	box-shadow:0 8px 32px rgba(15,23,42,.06) !important;
	position:sticky !important;top:24px !important;align-self:start !important;
	width:100% !important;max-width:100% !important;
	min-width:0 !important;overflow:hidden !important;
}
@media (max-width: 900px){
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form){grid-template-columns:1fr !important;gap:18px !important}
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) #customer_details,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) .checkout-order-review{grid-column:1 !important;padding:22px 18px !important;border-radius:16px !important;position:static !important}
}

html body.ezck-checkout-page form.checkout.woocommerce-checkout #billing_plaque_field,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #billing_unit_field{
	display:block !important;visibility:visible !important;height:auto !important;
	opacity:1 !important;position:static !important;overflow:visible !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .woocommerce-billing-fields__field-wrapper{
	display:grid !important;grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:16px 18px !important;
}
@media (max-width: 700px){
	html body.ezck-checkout-page form.checkout.woocommerce-checkout .woocommerce-billing-fields__field-wrapper{grid-template-columns:1fr !important;gap:14px !important}
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row{display:flex !important;flex-direction:column;gap:6px;margin:0 !important;padding:0 !important;width:100% !important;float:none !important;min-width:0}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row-wide,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #billing_address_1_field,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_comments_field,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #billing_postcode_field{grid-column:1 / -1 !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row label{font-size:13px !important;font-weight:700 !important;color:#334155 !important;display:flex !important;align-items:center;gap:4px;margin:0 !important;line-height:1.5}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row label .required{color:#dc2626 !important;font-weight:700 !important;text-decoration:none !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row label .optional{color:#94a3b8 !important;font-weight:500 !important;font-size:11px !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row input[type="text"],
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row input[type="email"],
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row input[type="tel"],
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row input[type="number"],
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row input[type="password"],
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row select,
html body.ezck-checkout-page form.checkout.woocommerce-checkout .select2-container .select2-selection--single{
	width:100% !important;min-height:48px !important;padding:11px 14px !important;
	font-family:'EzLensVazir',Tahoma,sans-serif !important;
	font-size:14px !important;font-weight:500 !important;
	color:#0f172a !important;background:#fff !important;
	border:2px solid #e8edf5 !important;border-radius:12px !important;
	outline:none !important;transition:border-color .22s ease, box-shadow .22s ease !important;
	box-shadow:none !important;-webkit-appearance:none !important;-moz-appearance:none !important;
	appearance:none !important;line-height:1.4 !important;text-align:right !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row input:focus,
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row select:focus,
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row textarea:focus{border-color:#031f8a !important;box-shadow:0 0 0 4px rgba(3,31,138,.1) !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.woocommerce-invalid input,
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.woocommerce-invalid select,
html body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.woocommerce-invalid textarea{border-color:#dc2626 !important;box-shadow:0 0 0 4px rgba(220,38,38,.08) !important}

html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table{width:100% !important;border-collapse:separate !important;border-spacing:0 !important;margin:0 0 18px !important;font-size:13px !important;border:0 !important;background:transparent !important;display:table !important;table-layout:fixed !important;word-break:break-word !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table thead{display:none !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody{display:block !important;width:100% !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item{
	display:flex !important;flex-direction:row !important;flex-wrap:nowrap !important;
	align-items:center !important;justify-content:space-between !important;gap:0.75em !important;
	width:100% !important;margin:0 0 0.65em !important;
	background:linear-gradient(135deg,#ffffff 0%,#f8fafc 45%,#eef2ff 100%) !important;
	border:1.5px solid #e0e7ff !important;border-radius:0.9em !important;padding:0.85em 1em !important;
	box-shadow:0 0.35em 1em rgba(3,31,138,.06) !important;box-sizing:border-box !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td{
	display:block !important;padding:0 !important;border:0 !important;background:transparent !important;
	vertical-align:middle !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-name{
	flex:1 1 auto !important;min-width:0 !important;max-width:100% !important;
	font-size:0.85em !important;font-weight:700 !important;color:#0f172a !important;line-height:1.4 !important;
	white-space:nowrap !important;overflow:hidden !important;text-overflow:ellipsis !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-name a{
	color:inherit !important;text-decoration:none !important;
	white-space:nowrap !important;overflow:hidden !important;text-overflow:ellipsis !important;
	display:inline !important;max-width:100% !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-name .product-quantity,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-name .ezck-qty-label{
	display:inline-flex !important;align-items:center !important;margin-right:0.4em !important;vertical-align:middle !important;
	white-space:nowrap !important;
}
/* Price chip — always single line: number + currency */
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total{
	flex:0 0 auto !important;white-space:nowrap !important;text-align:left !important;
	line-height:1 !important;overflow:visible !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .amount,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .woocommerce-Price-amount,
html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount,
html body.ezck-checkout-page #order_review .cart_item .amount{
	display:inline-flex !important;flex-direction:row-reverse !important;flex-wrap:nowrap !important;
	align-items:center !important;justify-content:center !important;gap:0.35em !important;
	white-space:nowrap !important;direction:rtl !important;unicode-bidi:isolate !important;
	padding:0.5em 0.85em !important;border-radius:999px !important;
	background:linear-gradient(135deg,#031f8a 0%,#0b2db0 40%,#2563eb 100%) !important;
	background-size:200% 200% !important;
	animation:ezckPriceGlow 3.2s ease-in-out infinite !important;
	color:#fff !important;font-weight:800 !important;font-size:0.88em !important;
	box-shadow:0 0.3em 0.9em rgba(3,31,138,.28) !important;
	line-height:1.15 !important;max-width:none !important;
	letter-spacing:0 !important;
}
@keyframes ezckPriceGlow{
	0%,100%{background-position:0% 50%;box-shadow:0 0.3em 0.9em rgba(3,31,138,.28);}
	50%{background-position:100% 50%;box-shadow:0 0.4em 1.1em rgba(37,99,235,.38);}
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .amount bdi,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .woocommerce-Price-amount bdi,
html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount bdi,
html body.ezck-checkout-page #order_review .cart_item .amount bdi{
	display:inline-flex !important;flex-direction:row-reverse !important;flex-wrap:nowrap !important;
	align-items:center !important;gap:0.35em !important;white-space:nowrap !important;
	direction:rtl !important;unicode-bidi:isolate !important;color:#fff !important;
	margin:0 !important;padding:0 !important;line-height:1.15 !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .woocommerce-Price-currencySymbol,
html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-currencySymbol{
	display:inline !important;white-space:nowrap !important;
	font-size:0.78em !important;font-weight:700 !important;
	color:rgba(255,255,255,.9) !important;line-height:1 !important;
	margin:0 !important;padding:0 !important;float:none !important;
	position:static !important;width:auto !important;height:auto !important;
}
html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount *,
html body.ezck-checkout-page #order_review .cart_item .product-total *{
	white-space:nowrap !important;
}
html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount::before,
html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount::after{
	content:none !important;display:none !important;
}
@media (max-width: 36em){
	html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item{
		padding:0.7em 0.75em !important;gap:0.5em !important;
	}
	html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-name{
		font-size:0.78em !important;
	}
	html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .amount,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tbody tr.cart_item td.product-total .woocommerce-Price-amount,
	html body.ezck-checkout-page #order_review .cart_item .woocommerce-Price-amount{
		font-size:0.8em !important;padding:0.42em 0.7em !important;gap:0.28em !important;
	}
}


/* Subtotal / total row amounts single-line */
html body.ezck-checkout-page #order_review .cart-subtotal .amount,
html body.ezck-checkout-page #order_review .order-total .amount,
html body.ezck-checkout-page #order_review .woocommerce-shipping-totals .amount{
	white-space:nowrap !important;display:inline-flex !important;align-items:center !important;gap:0.3em !important;
}
html body.ezck-checkout-page #order_review .order-total .amount{
	font-weight:800 !important;color:#031f8a !important;
}

html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot{display:block !important;width:100% !important;margin-top:4px !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr{display:grid !important;grid-template-columns:minmax(0,1fr) minmax(0,1fr) !important;align-items:center !important;gap:8px !important;padding:12px 0 !important;border-bottom:1px dashed #e8edf5 !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr th{text-align:right !important;font-weight:700 !important;color:#64748b !important;font-size:13px !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr td{text-align:left !important;font-weight:800 !important;color:#0f172a !important;font-size:13px !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot .order-total{margin-top:6px !important;padding:14px !important;background:linear-gradient(135deg,#f0f4ff 0%,#e0e7ff 100%) !important;border:1px solid #c7d2fe !important;border-radius:14px !important;grid-template-columns:auto auto !important;justify-content:space-between !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot .order-total th{color:#031f8a !important;font-size:14px !important;font-weight:900 !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot .order-total td,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot .order-total .amount{color:#031f8a !important;font-size:18px !important;font-weight:900 !important}

.ezck-payable-row,.ezck-coupon-row{display:block !important;width:100% !important;grid-template-columns:none !important;padding:10px 0 !important;border-bottom:0 !important;gap:0 !important}

/* Full-width payable + coupon inside order review */
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr.ezck-payable-row,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr.ezck-coupon-row{
	display:block !important;
	width:100% !important;
	max-width:100% !important;
	grid-template-columns:none !important;
	padding:8px 0 !important;
	border:0 !important;
	margin:0 !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr.ezck-payable-row th,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr.ezck-coupon-row th{
	display:none !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr.ezck-payable-row td,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot tr.ezck-coupon-row td,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot td.ezck-payable-cell,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #order_review .shop_table tfoot td.ezck-coupon-cell{
	display:block !important;
	width:100% !important;
	max-width:100% !important;
	padding:0 !important;
	border:0 !important;
	box-sizing:border-box !important;
}
html body.ezck-checkout-page .ezck-payable,
html body.ezck-checkout-page .ezck-coupon-wrap{
	width:100% !important;
	max-width:100% !important;
	box-sizing:border-box !important;
}

.ezck-payable-cell,.ezck-coupon-cell{display:block !important;width:100% !important;max-width:100% !important;box-sizing:border-box !important;padding:0 !important;text-align:right !important}
.ezck-payable{margin:6px 0 0 !important;padding:20px 22px !important;border-radius:18px !important;background:linear-gradient(135deg,#031f8a 0%,#0a2bb8 45%,#011663 100%) !important;box-shadow:0 14px 36px rgba(3,31,138,.3) !important;color:#fff !important;display:flex !important;flex-direction:column !important;gap:16px !important;position:relative !important;overflow:hidden !important;width:100% !important;max-width:100% !important;box-sizing:border-box !important}
.ezck-payable::before{content:"" !important;position:absolute !important;inset:auto -20% -40% auto !important;width:220px !important;height:220px !important;border-radius:50% !important;background:rgba(255,255,255,.08) !important;pointer-events:none !important}
.ezck-qty-label{display:inline-block !important;margin-right:6px !important;padding:3px 10px !important;border-radius:999px !important;background:#eef2ff !important;color:#031f8a !important;font-size:11.5px !important;font-weight:800 !important;border:1px solid #c7d2fe !important;white-space:nowrap !important}
.ezck-payable__head{display:flex !important;flex-wrap:wrap !important;align-items:center !important;justify-content:space-between !important;gap:12px !important;padding-bottom:12px !important;border-bottom:1px solid rgba(255,255,255,.15) !important}
.ezck-payable__label{display:inline-flex !important;align-items:center !important;gap:10px !important;font-size:14.5px !important;font-weight:800 !important;color:rgba(255,255,255,.9) !important}
.ezck-payable__label svg{width:18px !important;height:18px !important;fill:none !important;stroke:currentColor !important;stroke-width:2 !important}
.ezck-payable__amount{font-size:26px !important;font-weight:900 !important;color:#fff !important;display:inline-flex !important;align-items:baseline !important;gap:6px !important;flex-wrap:wrap !important}
.ezck-payable__currency{font-size:13px !important;font-weight:700 !important;color:rgba(255,255,255,.85) !important}
.ezck-payable__words{display:flex !important;flex-wrap:wrap !important;align-items:flex-start !important;gap:8px !important;background:rgba(255,255,255,.1) !important;padding:12px 14px !important;border-radius:12px !important;border:1px solid rgba(255,255,255,.15) !important}
.ezck-payable__words-label{display:inline-flex !important;align-items:center !important;gap:6px !important;font-size:12px !important;font-weight:800 !important;color:rgba(255,255,255,.85) !important}
.ezck-payable__words-label svg{width:14px !important;height:14px !important;fill:none !important;stroke:currentColor !important;stroke-width:2 !important}
.ezck-payable__words-value{font-size:14.5px !important;font-weight:800 !important;color:#fff !important;line-height:1.8 !important;word-break:break-word !important;flex:1 1 200px !important}
@media (max-width: 480px){
	.ezck-payable{padding:14px !important;gap:12px !important}
	.ezck-payable__amount{font-size:18px !important}
	.ezck-payable__words-value{font-size:12.5px !important;flex-basis:100% !important}
}

.ezck-coupon-wrap{margin-top:10px;padding-top:12px;border-top:1px dashed #e8edf5;display:flex;flex-direction:column;width:100%;max-width:100%;box-sizing:border-box}
.ezck-coupon-toggle{display:flex !important;align-items:center !important;gap:14px !important;width:100% !important;min-height:58px !important;padding:14px 18px !important;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 55%,#e0e7ff 100%) !important;border:2px solid #c7d2fe !important;border-radius:14px !important;cursor:pointer !important;color:#031f8a !important;font-family:'EzLensVazir',Tahoma,sans-serif !important;font-size:14px !important;font-weight:800 !important;text-align:right !important;box-shadow:0 6px 18px rgba(3,31,138,.08) !important;transition:border-color .2s,box-shadow .2s,transform .2s !important;box-sizing:border-box !important}
.ezck-coupon-toggle:hover{border-color:#031f8a !important;box-shadow:0 10px 24px rgba(3,31,138,.14) !important;transform:translateY(-1px) !important}
.ezck-coupon-icon{width:40px;height:40px;flex:0 0 40px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#031f8a,#2563eb);color:#fff;border-radius:12px;box-shadow:0 6px 14px rgba(3,31,138,.25)}
.ezck-coupon-icon svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2}
.ezck-coupon-txt{flex:1;color:#031f8a;font-weight:800;min-width:0;line-height:1.5}
.ezck-coupon-chev{width:28px;height:28px;flex:0 0 28px;display:flex;align-items:center;justify-content:center;color:#64748b;background:#fff;border:1px solid #e2e8f0;border-radius:9px;transition:transform .3s,background .2s}
.ezck-coupon-chev svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2.5}
.ezck-coupon-wrap[data-open="1"] .ezck-coupon-toggle{border-radius:14px 14px 0 0 !important;border-bottom-color:transparent !important}
.ezck-coupon-wrap[data-open="1"] .ezck-coupon-chev{transform:rotate(180deg);background:#eef2ff;color:#031f8a}
.ezck-coupon-panel{padding:18px 16px;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);border:2px solid #c7d2fe;border-top:0;border-bottom-left-radius:14px;border-bottom-right-radius:14px;width:100%;box-sizing:border-box}
.ezck-coupon-panel[hidden]{display:none !important}
.ezck-coupon-input-row{display:flex;gap:8px}
.ezck-coupon-input{flex:1;min-width:0;min-height:46px !important;padding:11px 14px !important;background:#fff !important;border:2px solid #e8edf5 !important;border-radius:10px !important;font-family:'EzLensVazir',Tahoma,sans-serif !important;font-size:13.5px !important;outline:none !important;text-align:right !important}
.ezck-coupon-input:focus{border-color:#031f8a !important;box-shadow:0 0 0 4px rgba(3,31,138,.1) !important}
.ezck-coupon-apply{display:inline-flex !important;align-items:center !important;justify-content:center !important;gap:6px !important;min-height:46px !important;padding:0 20px !important;border:0 !important;border-radius:10px !important;background:linear-gradient(135deg,#031f8a 0%,#011663 100%) !important;color:#fff !important;font-family:'EzLensVazir',Tahoma,sans-serif !important;font-size:13.5px !important;font-weight:800 !important;cursor:pointer !important;flex:0 0 auto !important}
.ezck-coupon-apply svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2.5}
.ezck-coupon-hint{display:flex;align-items:flex-start;gap:6px;margin-top:10px;font-size:11px;font-weight:500;color:#64748b;line-height:1.7}
.ezck-coupon-hint svg{width:13px;height:13px;flex-shrink:0;margin-top:2px;fill:none;stroke:currentColor;stroke-width:2}
.ezck-coupon-feedback{margin-top:10px;padding:10px 12px;border-radius:10px;font-size:12px;font-weight:600;line-height:1.7}
.ezck-coupon-feedback.is-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c}
.ezck-coupon-feedback.is-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
@media (max-width: 480px){ .ezck-coupon-input-row{flex-direction:column} .ezck-coupon-apply{width:100%} }


/* LTR numeric / latin fields */
html body.ezck-checkout-page #billing_phone,
html body.ezck-checkout-page #billing_email,
html body.ezck-checkout-page #billing_postcode,
html body.ezck-checkout-page #billing_plaque,
html body.ezck-checkout-page #billing_unit,
html body.ezck-checkout-page input[type="tel"],
html body.ezck-checkout-page input[type="email"],
html body.ezck-checkout-page input[inputmode="numeric"],
html body.ezck-checkout-page input[inputmode="tel"],
html body.ezck-checkout-page .ezck-coupon-input{
	direction:ltr !important;
	text-align:left !important;
	unicode-bidi:plaintext;
	font-variant-numeric:tabular-nums;
}
html body.ezck-checkout-page #billing_phone::placeholder,
html body.ezck-checkout-page #billing_email::placeholder,
html body.ezck-checkout-page #billing_postcode::placeholder,
html body.ezck-checkout-page #billing_plaque::placeholder,
html body.ezck-checkout-page #billing_unit::placeholder{
	direction:ltr !important;
	text-align:left !important;
}

/* Hide leftover order comments UI */
html body.ezck-checkout-page .woocommerce-additional-fields,
html body.ezck-checkout-page #order_comments_field,
html body.ezck-checkout-page #order_comments,
html body.ezck-checkout-page .woocommerce-additional-fields__field-wrapper{
	display:none !important;visibility:hidden !important;height:0 !important;
	max-height:0 !important;overflow:hidden !important;margin:0 !important;padding:0 !important;border:0 !important;
}

/* Province select — minimal modern */
html body.ezck-checkout-page #billing_state_field .select2-container{width:100% !important}
html body.ezck-checkout-page #billing_state_field .select2-container .select2-selection--single{
	min-height:48px !important;height:48px !important;
	border:1.5px solid #e8edf5 !important;border-radius:12px !important;
	background:#fff !important;padding:0 12px !important;
	display:flex !important;align-items:center !important;
	box-shadow:none !important;
}
html body.ezck-checkout-page #billing_state_field .select2-container--open .select2-selection--single,
html body.ezck-checkout-page #billing_state_field .select2-container.select2-container--focus .select2-selection--single{
	border-color:#031f8a !important;
	box-shadow:0 0 0 4px rgba(3,31,138,.1) !important;
}
html body.ezck-checkout-page #billing_state_field .select2-selection__rendered{
	padding:0 !important;line-height:48px !important;
	color:#0f172a !important;font-weight:600 !important;font-size:14px !important;
	text-align:right !important;
}
html body.ezck-checkout-page #billing_state_field .select2-selection__arrow{
	height:46px !important;left:8px !important;right:auto !important;
}
html body.ezck-checkout-page .select2-dropdown{
	border:1px solid #e2e8f0 !important;border-radius:12px !important;
	box-shadow:0 16px 36px rgba(15,23,42,.14) !important;
	overflow:hidden !important;background:#fff !important;z-index:99999 !important;
}
html body.ezck-checkout-page .select2-results__option{
	padding:10px 14px !important;font-size:13.5px !important;text-align:right !important;
}
html body.ezck-checkout-page .select2-results__option--highlighted[aria-selected],
html body.ezck-checkout-page .select2-results__option--highlighted{
	background:#eef2ff !important;color:#031f8a !important;
}
html body.ezck-checkout-page #billing_state{
	min-height:48px !important;border-radius:12px !important;border:1.5px solid #e8edf5 !important;
}

/* Custom province entry */
html body.ezck-checkout-page #ezck-state-custom-wrap{
	display:none;margin-top:8px;width:100%;
}
html body.ezck-checkout-page #ezck-state-custom-wrap.is-open{display:block}
html body.ezck-checkout-page #ezck_state_custom{
	width:100% !important;min-height:48px !important;padding:11px 14px !important;
	border:1.5px solid #e8edf5 !important;border-radius:12px !important;background:#fff !important;
	font-family:inherit !important;font-size:14px !important;text-align:right !important;outline:none !important;
}
html body.ezck-checkout-page #ezck_state_custom:focus{
	border-color:#031f8a !important;box-shadow:0 0 0 4px rgba(3,31,138,.1) !important;
}
html body.ezck-checkout-page #ezck-state-custom-hint{
	margin-top:6px;font-size:11.5px;color:#64748b;line-height:1.6;
}


/* Trust badges — keep icons inside square boxes */
html body.ezck-checkout-page .ezck-trust-badges{
	display:grid !important;grid-template-columns:repeat(2,minmax(0,1fr)) !important;
	gap:10px !important;margin-top:18px !important;padding-top:18px !important;
	border-top:1px dashed #e8edf5 !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-item{
	display:flex !important;align-items:center !important;gap:10px !important;
	padding:12px !important;background:#f8fafc !important;border:1px solid #e8edf5 !important;
	border-radius:12px !important;min-width:0 !important;overflow:hidden !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-icon{
	width:42px !important;height:42px !important;flex:0 0 42px !important;
	display:inline-flex !important;align-items:center !important;justify-content:center !important;
	background:linear-gradient(135deg,#eef2ff,#e0e7ff) !important;border-radius:12px !important;
	color:#031f8a !important;overflow:hidden !important;position:relative !important;
	margin:0 auto 0 0 !important;
	line-height:0 !important;text-align:center !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-icon > *{
	margin:auto !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-icon svg,
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-icon img{
	width:18px !important;height:18px !important;max-width:18px !important;max-height:18px !important;
	min-width:18px !important;min-height:18px !important;
	display:block !important;flex:none !important;
	margin:0 auto !important;
	position:relative !important;left:0 !important;right:0 !important;top:0 !important;bottom:0 !important;
	fill:none !important;stroke:currentColor !important;stroke-width:2 !important;
	overflow:visible !important;
	transform:none !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-item{
	justify-content:flex-start !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-item .ezck-trust-icon{
	align-self:center !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-item > div{min-width:0 !important;flex:1 1 auto !important}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-item strong{
	font-size:12px !important;font-weight:800 !important;color:#0f172a !important;display:block !important;
	line-height:1.4 !important;white-space:nowrap !important;overflow:hidden !important;text-overflow:ellipsis !important;
}
html body.ezck-checkout-page .ezck-trust-badges .ezck-trust-item span{
	font-size:10.5px !important;font-weight:500 !important;color:#64748b !important;display:block !important;
	line-height:1.4 !important;
}
@media (max-width:480px){
	html body.ezck-checkout-page .ezck-trust-badges{grid-template-columns:1fr !important}
}

.ezck-trust-badges{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:18px;padding-top:18px;border-top:1px dashed #e8edf5}
@media (max-width:480px){.ezck-trust-badges{grid-template-columns:1fr}}
.ezck-trust-badges .ezck-trust-item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border:1px solid #e8edf5;border-radius:10px}
.ezck-trust-badges .ezck-trust-icon{width:34px;height:34px;flex:0 0 34px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef2ff,#e0e7ff);border-radius:9px;color:#031f8a}
.ezck-trust-badges .ezck-trust-icon svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2}
.ezck-trust-badges .ezck-trust-item strong{font-size:12px;font-weight:800;color:#0f172a;display:block}
.ezck-trust-badges .ezck-trust-item span{font-size:10.5px;font-weight:500;color:#64748b;display:block}

html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%) !important;border-radius:16px !important;padding:20px !important;border:1px solid #e8edf5 !important;margin:0 !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods{list-style:none !important;margin:0 0 18px !important;padding:0 !important;border:0 !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li{
	position:relative !important;overflow:hidden !important;
	padding:16px 18px !important;margin:0 0 10px !important;
	background:linear-gradient(135deg,#ffffff 0%,#f8fafc 100%) !important;
	border:2px solid #e8edf5 !important;border-radius:14px !important;cursor:pointer;
	display:flex !important;flex-wrap:wrap;align-items:center;gap:10px;list-style:none !important;
	transition:border-color .25s,box-shadow .25s,transform .2s !important;
}
/* Sweeping shimmer on payment method (ZarinPal etc.) */
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li::before{
	content:"" !important;position:absolute !important;inset:0 !important;
	background:linear-gradient(110deg, transparent 20%, rgba(255,255,255,.55) 45%, rgba(255,255,255,.85) 50%, rgba(255,255,255,.55) 55%, transparent 80%) !important;
	transform:translateX(-120%) skewX(-12deg) !important;
	animation:ezckPayShimmer 2.8s ease-in-out infinite !important;
	pointer-events:none !important;z-index:1 !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li > *{
	position:relative !important;z-index:2 !important;
}
@keyframes ezckPayShimmer{
	0%{transform:translateX(-120%) skewX(-12deg);}
	45%,100%{transform:translateX(130%) skewX(-12deg);}
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li:hover{
	border-color:#c7d2fe !important;
	box-shadow:0 8px 22px rgba(3,31,138,.08) !important;
	transform:translateY(-1px) !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li:has(input:checked),
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li.wc_payment_method:has(input[type="radio"]:checked){
	background:linear-gradient(135deg,#eff6ff 0%,#e0e7ff 50%,#dbeafe 100%) !important;
	border-color:#031f8a !important;
	box-shadow:0 10px 28px rgba(3,31,138,.14), 0 0 0 1px rgba(3,31,138,.08) !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li:has(input:checked)::before{
	animation-duration:2.2s !important;
	background:linear-gradient(110deg, transparent 15%, rgba(255,255,255,.35) 40%, rgba(255,255,255,.75) 50%, rgba(255,255,255,.35) 60%, transparent 85%) !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li input[type="radio"]{appearance:none !important;-webkit-appearance:none !important;width:20px !important;height:20px !important;margin:0 !important;border:2px solid #cbd5e1 !important;border-radius:50% !important;background:#fff !important;cursor:pointer;flex-shrink:0}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li input[type="radio"]:checked{border-color:#031f8a !important;background:#031f8a !important;box-shadow:inset 0 0 0 3px #fff !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li label{display:flex !important;align-items:center;gap:10px;font-weight:700 !important;color:#0f172a !important;cursor:pointer;margin:0 !important;flex:1;font-size:14.5px !important;min-width:0}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment div.payment_box{background:transparent !important;color:#64748b !important;font-size:13px !important;line-height:1.8 !important;margin:0 !important;padding:10px 0 0 !important;width:100% !important;border:0 !important}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment div.payment_box::before,
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment div.payment_box::after{display:none !important}
@media (prefers-reduced-motion: reduce){
	html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment ul.payment_methods li::before{animation:none !important;}
}

/* Mobile: center main palettes/containers (content inside unchanged) */
@media (max-width: 48em){
	html body.ezck-checkout-page{
		overflow-x:hidden !important;
	}
	html body.ezck-checkout-page .wd-content-area,
	html body.ezck-checkout-page .woocommerce.entry-content,
	html body.ezck-checkout-page .site-content,
	html body.ezck-checkout-page .main-page-wrapper,
	html body.ezck-checkout-page .wd-page-content{
		display:flex !important;
		flex-direction:column !important;
		align-items:center !important;
		justify-content:flex-start !important;
		width:100% !important;
		max-width:100% !important;
		margin-left:auto !important;
		margin-right:auto !important;
		padding-left:0.75em !important;
		padding-right:0.75em !important;
		box-sizing:border-box !important;
	}
	html body.ezck-checkout-page .e-con,
	html body.ezck-checkout-page .e-con.e-flex,
	html body.ezck-checkout-page .elementor-container,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed,
	html body.ezck-checkout-page .elementor-section.elementor-section-boxed > .elementor-container,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form),
	html body.ezck-checkout-page .ezp-steps,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) #customer_details,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) .checkout-order-review,
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form) #order_review{
		width:calc(100% - 0px) !important;
		max-width:100% !important;
		margin-left:auto !important;
		margin-right:auto !important;
		float:none !important;
		position:relative !important;
		left:auto !important;
		right:auto !important;
		transform:none !important;
		box-sizing:border-box !important;
	}
	html body.ezck-checkout-page form.checkout.woocommerce-checkout:not(.wd-checkout-form){
		grid-template-columns:1fr !important;
	}
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment #place_order{
	position:relative !important;overflow:hidden !important;isolation:isolate !important;
	width:100% !important;min-height:56px !important;padding:0 24px !important;border:0 !important;border-radius:14px !important;
	background:linear-gradient(135deg,#031f8a 0%,#011663 100%) !important;color:#fff !important;
	font-family:'EzLensVazir',Tahoma,sans-serif !important;font-size:16px !important;font-weight:800 !important;
	cursor:pointer;box-shadow:0 6px 20px rgba(3,31,138,.3) !important;
	transition:transform .22s, box-shadow .25s !important;
	z-index:1 !important;
}
/* Internal sweeping shine across the button surface */
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment #place_order::after{
	content:"" !important;position:absolute !important;top:0 !important;bottom:0 !important;left:0 !important;
	width:45% !important;pointer-events:none !important;z-index:2 !important;
	background:linear-gradient(105deg,
		transparent 0%,
		rgba(255,255,255,0) 35%,
		rgba(255,255,255,.18) 45%,
		rgba(255,255,255,.55) 50%,
		rgba(255,255,255,.18) 55%,
		rgba(255,255,255,0) 65%,
		transparent 100%) !important;
	transform:translateX(-160%) skewX(-18deg) !important;
	animation:ezckBtnShine 2.6s ease-in-out infinite !important;
}
@keyframes ezckBtnShine{
	0%{transform:translateX(-160%) skewX(-18deg);}
	40%{transform:translateX(280%) skewX(-18deg);}
	100%{transform:translateX(280%) skewX(-18deg);}
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment #place_order:hover:not(:disabled){
	transform:translateY(-2px) !important;box-shadow:0 12px 32px rgba(3,31,138,.45) !important;
}
html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment #place_order:hover:not(:disabled)::after{
	animation-duration:1.8s !important;
}
@media (prefers-reduced-motion: reduce){
	html body.ezck-checkout-page form.checkout.woocommerce-checkout #payment #place_order::after{animation:none !important;}
}

.ezck-map-modal{position:fixed !important;inset:0 !important;z-index:9999999 !important;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.55);backdrop-filter:blur(8px)}
.ezck-map-modal.is-open{display:flex !important}
.ezck-map-modal__dialog{width:100%;max-width:920px;max-height:90vh;background:#fff;border-radius:20px;display:flex;flex-direction:column;overflow:hidden}
.ezck-map-modal__head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 20px;border-bottom:1px solid #e8edf5;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%)}
.ezck-map-modal__title{margin:0;font-size:15px;font-weight:800;color:#031f8a;display:flex;align-items:center;gap:8px}
.ezck-map-modal__title svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2}
.ezck-map-modal__close{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid #e8edf5;border-radius:10px;background:#fff;color:#64748b;cursor:pointer}
.ezck-map-modal__close svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2.5}
.ezck-map-modal__search{display:flex;gap:8px;padding:14px 20px;border-bottom:1px solid #e8edf5}
.ezck-map-modal__search-input{flex:1;min-height:44px;padding:10px 14px;border:2px solid #e8edf5;border-radius:10px;font-family:inherit;font-size:13.5px;outline:none;text-align:right;min-width:0}
.ezck-map-modal__search-input:focus{border-color:#031f8a;box-shadow:0 0 0 4px rgba(3,31,138,.1)}
.ezck-map-modal__search-btn{min-height:44px;padding:0 20px;border:0;border-radius:10px;background:linear-gradient(135deg,#031f8a 0%,#011663 100%);color:#fff;font-family:inherit;font-size:13.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;flex-shrink:0}
.ezck-map-modal__search-btn svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2.5}
.ezck-map-modal__map{flex:1;min-height:380px;background:#e8edf5}
#ezckLeafletMap{width:100%;height:100%;min-height:380px}
.ezck-map-modal__foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;border-top:1px solid #e8edf5;background:#f8fafc;flex-wrap:wrap}
.ezck-map-modal__result{flex:1;min-width:0;font-size:12.5px;font-weight:600;color:#334155;line-height:1.7;word-break:break-word}
.ezck-map-modal__result span{display:inline-block;max-width:100%;word-break:break-word;padding:6px 10px;background:#fff;border:1px solid #e8edf5;border-radius:8px;font-weight:700;color:#031f8a}
.ezck-map-modal__btn{min-height:44px;padding:0 22px;border:0;border-radius:10px;font-family:inherit;font-size:13.5px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
.ezck-map-modal__btn--primary{background:linear-gradient(135deg,#031f8a 0%,#011663 100%);color:#fff}
.ezck-map-modal__btn--primary:disabled{opacity:.5;cursor:not-allowed}
.ezck-map-modal__btn--ghost{background:#fff;color:#334155;border:1.5px solid #e8edf5}
.ezck-map-modal__btn svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2}
@media (max-width: 600px){
	.ezck-map-modal{padding:0}
	.ezck-map-modal__dialog{max-width:100%;max-height:100vh;border-radius:0;height:100vh}
	.ezck-map-modal__map,#ezckLeafletMap{min-height:auto;flex:1}
	.ezck-map-modal__foot{flex-direction:column;align-items:stretch}
	.ezck-map-modal__btn{width:100%;justify-content:center}
}


/* Three checkout palettes — presentation only */
html body.ezck-checkout-page .ezck-palette{
	grid-column:1 / -1 !important;
	margin:0 0 18px !important;
	padding:18px !important;
	background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%) !important;
	border:1.5px solid #e8edf5 !important;
	border-radius:18px !important;
	box-shadow:0 8px 24px rgba(15,23,42,.045) !important;
	display:grid !important;
	grid-template-columns:minmax(0,1fr) minmax(0,1fr) !important;
	gap:10px 14px !important;
	width:100% !important;
	max-width:100% !important;
	box-sizing:border-box !important;
	float:none !important;
	clear:both !important;
}
html body.ezck-checkout-page .ezck-palette__head{
	grid-column:1 / -1 !important;
	display:flex !important;
	align-items:center !important;
	gap:12px !important;
	margin:0 0 4px !important;
	padding:0 2px 12px !important;
	border-bottom:1px dashed #e2e8f0 !important;
}
html body.ezck-checkout-page .ezck-palette__icon{
	width:42px;height:42px;flex:0 0 42px;
	display:flex;align-items:center;justify-content:center;
	border-radius:12px;
	background:linear-gradient(135deg,#031f8a,#2563eb);
	color:#fff;box-shadow:0 6px 14px rgba(3,31,138,.2);
}
html body.ezck-checkout-page .ezck-palette__icon svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2}
html body.ezck-checkout-page .ezck-palette__title{font-size:15px;font-weight:800;color:#031f8a;line-height:1.4}
html body.ezck-checkout-page .ezck-palette__sub{font-size:11.5px;color:#64748b;margin-top:2px}
html body.ezck-checkout-page .ezck-palette .form-row{
	float:none !important;
	width:auto !important;
	margin:0 !important;
	padding:0 !important;
	clear:none !important;
	min-width:0 !important;
	max-width:100% !important;
	box-sizing:border-box !important;
}
html body.ezck-checkout-page .ezck-palette .form-row-first,
html body.ezck-checkout-page .ezck-palette .form-row-last,
html body.ezck-checkout-page .ezck-palette .form-row-wide{
	float:none !important;
	width:auto !important;
}
html body.ezck-checkout-page .ezck-palette .form-row-wide,
html body.ezck-checkout-page .ezck-palette #billing_address_1_field,
html body.ezck-checkout-page .ezck-palette #billing_postcode_field{
	grid-column:1 / -1 !important;
}
html body.ezck-checkout-page .ezck-palette .ezck-addr-wrap,
html body.ezck-checkout-page .ezck-palette .ezck-map-inline{
	grid-column:1 / -1 !important;
	margin:0 !important;
}
html body.ezck-checkout-page .ezck-map-inline{
	display:flex;flex-wrap:wrap;gap:10px;align-items:center;
	padding:12px;border:1.5px dashed #c7d2fe;border-radius:14px;
	background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%);
}
html body.ezck-checkout-page .ezck-map-inline__btn,
html body.ezck-checkout-page #ezckOpenMap.ezck-map-inline__btn{
	display:inline-flex;align-items:center;justify-content:center;gap:8px;
	min-height:48px;padding:0 18px;
	border:1.5px solid #c7d2fe;border-radius:12px;
	background:linear-gradient(135deg,#ffffff 0%,#eef2ff 100%);
	color:#031f8a;font-family:inherit;font-size:13px;font-weight:800;
	cursor:pointer;box-shadow:0 4px 12px rgba(3,31,138,.06);
	transition:transform .2s,box-shadow .2s,border-color .2s;
}
html body.ezck-checkout-page .ezck-map-inline__btn:hover{transform:translateY(-1px);border-color:#031f8a;box-shadow:0 8px 18px rgba(3,31,138,.12)}
html body.ezck-checkout-page .ezck-map-inline__btn svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2}
html body.ezck-checkout-page .ezck-map-inline__hint{font-size:11.5px;color:#64748b;line-height:1.7;flex:1 1 180px}
@media (max-width:700px){
	html body.ezck-checkout-page .ezck-palette{grid-template-columns:1fr !important;padding:14px !important;gap:12px !important}
	html body.ezck-checkout-page .ezck-palette .form-row-first,
	html body.ezck-checkout-page .ezck-palette .form-row-last{grid-column:1 / -1 !important}
}


/* CD address list inside palette 2 */
html body.ezck-checkout-page .ezck-palette #ezcd-co-addr,
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr{
	grid-column:1 / -1 !important;
	margin:0 0 8px !important;
	padding:14px !important;
	border:1.5px solid #e2e8f0 !important;
	border-radius:14px !important;
	background:linear-gradient(145deg,#f8fafc,#eef2ff) !important;
	width:100% !important;
	max-width:100% !important;
	box-sizing:border-box !important;
}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-title{font-weight:800;font-size:14px;color:#031f8a;margin:0 0 6px}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-hint,
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-empty{margin:0 0 10px;font-size:12.5px;color:#64748b;line-height:1.6}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-list{display:flex;flex-direction:column;gap:8px}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-item{
	display:flex;align-items:flex-start;gap:10px;padding:12px 14px;
	background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;cursor:pointer;
	transition:border-color .2s,box-shadow .2s;
}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-item:hover{border-color:#c7d2fe;box-shadow:0 6px 16px rgba(3,31,138,.08)}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-item:has(input:checked){border-color:#031f8a;background:linear-gradient(145deg,#eff6ff,#e0e7ff);box-shadow:0 0 0 3px rgba(3,31,138,.08)}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-body{display:flex;flex-direction:column;gap:4px;min-width:0}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-body strong{font-size:13.5px;color:#0f172a}
html body.ezck-checkout-page .ezck-palette .ezcd-co-addr-body small{font-size:12px;color:#64748b;line-height:1.5}
html body.ezck-checkout-page .ezck-palette #billing_address_2_field{grid-column:1 / -1 !important}
html body.ezck-checkout-page .ezck-address-module{grid-column:1 / -1 !important;display:flex;flex-direction:column;gap:10px;margin-top:2px}
html body.ezck-checkout-page .ezck-address-module__label{font-size:13.5px !important;font-weight:800 !important;color:#031f8a !important;display:flex;align-items:center;gap:8px}
html body.ezck-checkout-page .ezck-address-module__label::before{content:"";width:18px;height:18px;background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23031f8a' stroke-width='2'><path d='M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z'/><circle cx='12' cy='10' r='3'/></svg>") center/contain no-repeat;display:inline-block}
html body.ezck-checkout-page .ezck-address-module__textarea{width:100% !important;min-height:96px !important;padding:14px 16px !important;font-family:'EzLensVazir',Tahoma,sans-serif !important;font-size:14px !important;line-height:1.9 !important;background:#fff !important;border:2px solid #e8edf5 !important;border-radius:12px !important;outline:none !important;resize:vertical}
html body.ezck-checkout-page .ezck-address-module__textarea:focus{border-color:#031f8a !important;box-shadow:0 0 0 4px rgba(3,31,138,.1) !important}
html body.ezck-checkout-page .ezck-address-module__actions{display:flex;gap:8px;flex-wrap:wrap}
html body.ezck-checkout-page .ezck-address-module__btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:42px;padding:0 16px;border:1.5px solid #e8edf5;border-radius:10px;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%);color:#031f8a;font-family:inherit;font-size:12.5px;font-weight:700;cursor:pointer}
html body.ezck-checkout-page .ezck-address-module__btn svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2}
html body.ezck-checkout-page .ezck-address-module__hint{display:flex;align-items:flex-start;gap:6px;font-size:11.5px;color:#64748b;line-height:1.7}
html body.ezck-checkout-page .ezck-address-module__hint svg{width:13px;height:13px;flex-shrink:0;margin-top:2px;fill:none;stroke:currentColor;stroke-width:2}

/* پیکر آدرس‌ها داخل گرید */
.ezck-addr-wrap{grid-column:1 / -1 !important;margin:0 0 8px !important}
</style>

<?php
		$leaflet_js_local = EZLAUTH_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js';
		if ( $has_local_leaflet ) : ?>
<script src="<?php echo esc_url( $leaflet_js_local ); ?>"></script>
<?php else : ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<?php endif; ?>

<script>
(function(){
	'use strict';
	var faDigits = '۰۱۲۳۴۵۶۷۸۹';
	var ezckAddressFilled  = false;
	var ezckUpdating       = false;
	var ezckObserverPaused = false;

	function numberToPersianWords(num){
		num = Math.floor(Number(num) || 0);
		if (num === 0) return 'صفر';
		if (num < 0) return 'منفی ' + numberToPersianWords(-num);
		var ones    = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
		var teens   = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
		var tens    = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
		var hundreds= ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
		var scales  = ['', 'هزار', 'میلیون', 'میلیارد', 'بیلیون'];
		function threeDigit(n){
			var parts = [];
			var h = Math.floor(n / 100);
			var r = n % 100;
			if (h > 0) parts.push(hundreds[h]);
			if (r >= 10 && r < 20) parts.push(teens[r - 10]);
			else {
				var t = Math.floor(r / 10);
				var o = r % 10;
				if (t > 0) parts.push(tens[t]);
				if (o > 0) parts.push(ones[o]);
			}
			return parts.join(' و ');
		}
		var result = [];
		var scaleIndex = 0;
		while (num > 0) {
			var chunk = num % 1000;
			if (chunk > 0) {
				var chunkText = threeDigit(chunk);
				if (scales[scaleIndex]) chunkText += ' ' + scales[scaleIndex];
				result.unshift(chunkText);
			}
			num = Math.floor(num / 1000);
			scaleIndex++;
			if (scaleIndex >= scales.length) break;
		}
		return result.join(' و ');
	}
	function toFa(str){return String(str == null ? '' : str).replace(/[0-9]/g, function(d){return faDigits.charAt(parseInt(d, 10));});}
	function walkTextNodes(node, cb){
		if (node.nodeType === 3) { cb(node); return; }
		if (node.nodeType !== 1) return;
		if (node.tagName === 'SCRIPT' || node.tagName === 'STYLE') return;
		if (node.classList && node.classList.contains('ezck-no-fa')) return;
		for (var i = 0; i < node.childNodes.length; i++) walkTextNodes(node.childNodes[i], cb);
	}
	function convertPricesToPersian(){
		var scope = document.querySelector('#order_review');
		if (!scope) return;
		scope.querySelectorAll('.woocommerce-Price-amount, .amount, .woocommerce-Price-currencySymbol').forEach(function(node){
			if (node.dataset.ezckFa === '1') return;
			walkTextNodes(node, function(textNode){
				var text = textNode.nodeValue;
				if (text && /[0-9]/.test(text)) textNode.nodeValue = toFa(text);
			});
			node.dataset.ezckFa = '1';
		});
	}
	function refreshPayable(){
		var wrap = document.querySelector('.ezck-payable');
		if (!wrap) return;
		var amountEl = document.getElementById('ezckPayableAmount');
		var wordsEl  = document.getElementById('ezckPayableWords');
		if (!amountEl || !wordsEl) return;
		var totalRaw = '';
		var totalCell = document.querySelector('#order_review .order-total .amount, #order_review .order-total td bdi, #order_review .order-total td strong');
		if (totalCell) {
			totalRaw = totalCell.textContent.replace(/[^\d۰-۹]/g, '');
			totalRaw = totalRaw.replace(/[۰-۹]/g, function(d){ return faDigits.indexOf(d); });
		}
		if (!totalRaw) totalRaw = wrap.getAttribute('data-total') || '0';
		var total = parseInt(totalRaw, 10) || 0;
		if (total <= 0) { amountEl.setAttribute('data-raw', '0'); wordsEl.textContent = '—'; return; }
		var currency = amountEl.querySelector('.ezck-payable__currency');
		var currencyText = currency ? currency.textContent : 'تومان';
		amountEl.innerHTML = toFa(total.toLocaleString('en-US')) + ' <span class="ezck-payable__currency">' + currencyText + '</span>';
		amountEl.setAttribute('data-raw', String(total));
		wordsEl.textContent = numberToPersianWords(total) + ' تومان';
	}
	function hideOrphans(){
		document.querySelectorAll('body.ezck-checkout-page p.form-row').forEach(function(el){
			if (el.querySelector('input[name="coupon_code"], input#coupon_code, button[name="apply_coupon"]')) {
				el.style.setProperty('display', 'none', 'important');
				el.style.setProperty('height', '0', 'important');
				el.style.setProperty('overflow', 'hidden', 'important');
			}
		});
	}
	function ezckForceFix() {
		['billing_country', 'shipping_country'].forEach(function(id) {
			var input = document.getElementById(id);
			if (input) {
				input.value = 'IR';
				var wrap = input.closest('p, div, .form-row, [id$="_field"]');
				if (wrap) {
					wrap.style.setProperty('display', 'none', 'important');
					wrap.style.setProperty('height', '0', 'important');
					wrap.style.setProperty('overflow', 'hidden', 'important');
				}
			}
		});
		var shipDiffCb = document.getElementById('ship-to-different-address-checkbox');
		if (shipDiffCb) {
			if (shipDiffCb.checked) {
				shipDiffCb.checked = false;
				shipDiffCb.dispatchEvent(new Event('change', { bubbles: true }));
			}
			var wrap = shipDiffCb.closest('h3, p, label, div');
			if (wrap) {
				wrap.style.setProperty('display', 'none', 'important');
				wrap.style.setProperty('height', '0', 'important');
			}
		}
		var shipFields = document.querySelector('.woocommerce-shipping-fields');
		if (shipFields) {
			shipFields.style.setProperty('display', 'none', 'important');
			shipFields.style.setProperty('height', '0', 'important');
		}
	}

	/* Move address lists under customer fields so palette builder can group them */
	function ezckMoveAddressPicker() {
		var emailField = document.getElementById('billing_email_field');
		var lastNameField = document.getElementById('billing_last_name_field');
		var anchor = emailField || lastNameField;
		if (!anchor || !anchor.parentNode) return;

		var cdAddr = document.getElementById('ezcd-co-addr');
		var gateAddr = document.getElementById('ezck-iran-addr');

		// Prefer CD list; place both near billing fields before palette grouping
		if (cdAddr && cdAddr.parentNode !== anchor.parentNode) {
			anchor.parentNode.insertBefore(cdAddr, anchor.nextSibling);
		}
		if (gateAddr) {
			if (cdAddr) {
				gateAddr.style.setProperty('display', 'none', 'important');
			} else if (gateAddr.parentNode !== anchor.parentNode) {
				anchor.parentNode.insertBefore(gateAddr, anchor.nextSibling);
			}
		}
	}

	/* Build three visual palettes — always re-group fields */
	function ezckBuildPalettes() {
		var billing = document.querySelector('.woocommerce-billing-fields__field-wrapper');
		if (!billing) return;

		var p1Ids = ['billing_first_name_field','billing_last_name_field','billing_phone_field','billing_email_field'];
		var p3Ids = ['billing_state_field','billing_city_field','billing_address_1_field','billing_plaque_field','billing_unit_field','billing_postcode_field','billing_address_2_field'];

		function ensurePalette(key, title, sub, iconSvg) {
			var box = billing.querySelector('.ezck-palette--' + key);
			if (!box) {
				box = document.createElement('div');
				box.className = 'ezck-palette ezck-palette--' + key;
				box.innerHTML = ''
					+ '<div class="ezck-palette__head">'
					+ '  <div class="ezck-palette__icon">' + iconSvg + '</div>'
					+ '  <div><div class="ezck-palette__title">' + title + '</div><div class="ezck-palette__sub">' + sub + '</div></div>'
					+ '</div>';
			}
			return box;
		}

		var icoUser = '<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
		var icoPin  = '<svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
		var icoHome = '<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';

		var p1 = ensurePalette('customer', 'مشخصات مشتری', 'نام، نام خانوادگی و شماره تماس الزامی است', icoUser);
		var p2 = ensurePalette('address', 'آدرس', 'آدرس ذخیره‌شده را انتخاب کنید یا از روی نقشه مشخص کنید', icoPin);
		var p3 = ensurePalette('shipping', 'اطلاعات ارسال', 'همه فیلدهای این بخش اختیاری هستند', icoHome);

		// Order: p1, p2, p3 at top of wrapper
		billing.insertBefore(p1, billing.firstChild);
		billing.insertBefore(p2, p1.nextSibling);
		billing.insertBefore(p3, p2.nextSibling);

		// Palette 1 — customer only
		p1Ids.forEach(function(id){
			var el = document.getElementById(id);
			if (el) p1.appendChild(el);
		});

		// Palette 2 — saved addresses + map
		var cdAddr = document.getElementById('ezcd-co-addr');
		var gateAddr = document.getElementById('ezck-iran-addr');
		if (cdAddr) {
			p2.appendChild(cdAddr);
			if (gateAddr) gateAddr.style.setProperty('display', 'none', 'important');
		} else if (gateAddr) {
			gateAddr.style.removeProperty('display');
			p2.appendChild(gateAddr);
		}

		var mapRow = p2.querySelector('.ezck-map-inline');
		if (!mapRow) {
			mapRow = document.createElement('div');
			mapRow.className = 'ezck-map-inline';
			mapRow.innerHTML = ''
				+ '<button type="button" class="ezck-map-inline__btn" id="ezckOpenMap">'
				+ '  <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>'
				+ '  <span>انتخاب آدرس از روی نقشه</span>'
				+ '</button>'
				+ '<div class="ezck-map-inline__hint">با انتخاب روی نقشه، استان، شهر و خیابان به‌صورت خودکار تکمیل می‌شوند.</div>';
			p2.appendChild(mapRow);
		} else {
			p2.appendChild(mapRow);
		}

		// Palette 3 — shipping details
		p3Ids.forEach(function(id){
			var el = document.getElementById(id);
			if (el) {
				el.style.removeProperty('display');
				p3.appendChild(el);
			}
		});

		// Soften required UI on optional shipping fields (theme may force required stars)
		p3Ids.forEach(function(id){
			var el = document.getElementById(id);
			if (!el) return;
			el.classList.remove('validate-required');
			var lab = el.querySelector('label');
			if (lab) {
				lab.classList.remove('required_field');
				var star = lab.querySelector('span.required');
				if (star) star.remove();
				var input = el.querySelector('input,select,textarea');
				if (input) input.removeAttribute('aria-required');
			}
		});

		// Keep country hidden if present
		var country = document.getElementById('billing_country_field');
		if (country) {
			country.style.setProperty('display', 'none', 'important');
			country.style.setProperty('height', '0', 'important');
			country.style.setProperty('overflow', 'hidden', 'important');
		}
	}

	function initAddressModule(){
		var provinceSelect = document.querySelector('#billing_state');
		var cityInput      = document.querySelector('#billing_city');
		var addr1Input     = document.querySelector('#billing_address_1');
		var postcodeInput  = document.querySelector('#billing_postcode');
		var plaqueInput    = document.querySelector('#billing_plaque');

		var btnMap = document.getElementById('ezckOpenMap');
		if (!btnMap) return;
		if (btnMap.getAttribute('data-ezck-bound') === '1') return;
		btnMap.setAttribute('data-ezck-bound', '1');

		var faMap = {
			'تهران':'THR','البرز':'ABZ','اصفهان':'ESF','فارس':'FRS','خراسان رضوی':'RKH','خراسان جنوبی':'SKH','خراسان شمالی':'NKH',
			'آذربایجان شرقی':'EAZ','آذربایجان غربی':'WAZ','اردبیل':'ADL','بوشهر':'BHR','چهارمحال و بختیاری':'CHB','گیلان':'GIL',
			'گلستان':'GLS','همدان':'HDN','هرمزگان':'HRZ','ایلام':'ILM','کرمان':'KRN','کرمانشاه':'KRH','خوزستان':'KHZ',
			'کهگیلویه و بویراحمد':'KBD','کردستان':'KRD','لرستان':'LRS','مازندران':'MZN','مرکزی':'MKZ','قزوین':'GZN',
			'قم':'QHM','سمنان':'SMN','سیستان و بلوچستان':'SBN','یزد':'YZD','زنجان':'ZJN'
		};

		function syncFields(fullAddress, meta){
			var billCountry = document.querySelector('#billing_country');
			var shipCountry = document.querySelector('#shipping_country');
			if (billCountry) billCountry.value = 'IR';
			if (shipCountry) shipCountry.value = 'IR';
			if (addr1Input && fullAddress) addr1Input.value = fullAddress;
			if (cityInput && meta && meta.city) cityInput.value = meta.city;
			if (postcodeInput && meta && meta.postcode) postcodeInput.value = meta.postcode;
			if (plaqueInput && meta && meta.house_number) plaqueInput.value = meta.house_number;
			if (provinceSelect && meta && meta.state){
				var code = faMap[meta.state];
				if (code){
					provinceSelect.value = code;
					provinceSelect.dispatchEvent(new Event('change', { bubbles: true }));
				}
			}
			['billing_address_1','billing_city','billing_postcode','billing_plaque','billing_state'].forEach(function(id){
				var el = document.getElementById(id);
				if (el) {
					el.dispatchEvent(new Event('input', { bubbles: true }));
					el.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
			if ( window.jQuery ) {
				try { window.jQuery('body').trigger('update_checkout', { update_shipping_method: true }); } catch(e){}
			}
		}

		if (!document.querySelector('input[name="ezck_lat"]')){
			var formEl = document.querySelector('form.checkout');
			var target = formEl || document.body;
			var i1 = document.createElement('input'); i1.type = 'hidden'; i1.name = 'ezck_lat'; i1.value = '';
			var i2 = document.createElement('input'); i2.type = 'hidden'; i2.name = 'ezck_lng'; i2.value = '';
			target.appendChild(i1); target.appendChild(i2);
		}

		var currentAddress = '';
		var currentMeta = {};
		var currentLatLng = null;
		var leafletMap = null, leafletMarker = null;

		function openMapModal(){
			if (document.querySelector('.ezck-map-modal')){
				document.querySelector('.ezck-map-modal').classList.add('is-open');
				document.documentElement.style.overflow = 'hidden';
				setTimeout(initLeaflet, 200);
				return;
			}
			var modal = document.createElement('div');
			modal.className = 'ezck-map-modal is-open';
			modal.innerHTML = ''
				+ '<div class="ezck-map-modal__dialog" role="dialog" aria-modal="true">'
				+ '  <div class="ezck-map-modal__head">'
				+ '    <h3 class="ezck-map-modal__title"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> انتخاب آدرس روی نقشه</h3>'
				+ '    <button type="button" class="ezck-map-modal__close" aria-label="Close"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>'
				+ '  </div>'
				+ '  <div class="ezck-map-modal__search">'
				+ '    <input type="text" class="ezck-map-modal__search-input" id="ezckMapSearch" placeholder="جستجوی آدرس، خیابان یا محله...">'
				+ '    <button type="button" class="ezck-map-modal__search-btn" id="ezckMapSearchBtn"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><span>جستجو</span></button>'
				+ '  </div>'
				+ '  <div class="ezck-map-modal__map"><div id="ezckLeafletMap"></div></div>'
				+ '  <div class="ezck-map-modal__foot">'
				+ '    <div class="ezck-map-modal__result" id="ezckMapResult">روی نقشه کلیک کنید یا جستجو کنید</div>'
				+ '    <div class="ezck-map-modal__actions">'
				+ '      <button type="button" class="ezck-map-modal__btn ezck-map-modal__btn--ghost" id="ezckMapCancel">انصراف</button>'
				+ '      <button type="button" class="ezck-map-modal__btn ezck-map-modal__btn--primary" id="ezckMapConfirm" disabled><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span>تأیید آدرس</span></button>'
				+ '    </div>'
				+ '  </div>'
				+ '</div>';
			document.body.appendChild(modal);
			document.documentElement.style.overflow = 'hidden';
			function closeModal(){ modal.classList.remove('is-open'); document.documentElement.style.overflow = ''; }
			modal.querySelector('.ezck-map-modal__close').addEventListener('click', closeModal);
			modal.querySelector('#ezckMapCancel').addEventListener('click', closeModal);
			modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
			document.getElementById('ezckMapSearchBtn').addEventListener('click', function(){
				var q = (document.getElementById('ezckMapSearch').value || '').trim();
				if (!q) return;
				fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&countrycodes=ir&q=' + encodeURIComponent(q), { headers: { 'Accept-Language': 'fa' } })
				.then(function(r){ return r.json(); })
				.then(function(list){
					if (!list || !list[0]) return;
					var item = list[0];
					placeMarker(parseFloat(item.lat), parseFloat(item.lon));
					if (leafletMap) leafletMap.setView([parseFloat(item.lat), parseFloat(item.lon)], 16);
					applyAddress(item.address || {}, item.display_name || '');
				}).catch(function(){});
			});
			setTimeout(initLeaflet, 200);
		}

		function initLeaflet(){
			var mapEl = document.getElementById('ezckLeafletMap');
			if (!mapEl || typeof L === 'undefined') return;
			if (leafletMap) { setTimeout(function(){ leafletMap.invalidateSize(); }, 100); return; }
			leafletMap = L.map(mapEl, { center: [35.6892, 51.3890], zoom: 12, zoomControl: true });
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(leafletMap);
			leafletMap.on('click', function(e){ placeMarker(e.latlng.lat, e.latlng.lng); });
			setTimeout(function(){ leafletMap.invalidateSize(); }, 150);
		}

		function placeMarker(lat, lng){
			currentLatLng = { lat: lat, lng: lng };
			if (leafletMarker) leafletMap.removeLayer(leafletMarker);
			leafletMarker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
			leafletMarker.on('dragend', function(){ var ll = leafletMarker.getLatLng(); placeMarker(ll.lat, ll.lng); });
			var resultEl = document.getElementById('ezckMapResult');
			var confirmBtn = document.getElementById('ezckMapConfirm');
			if (resultEl) resultEl.textContent = 'در حال دریافت آدرس...';
			if (confirmBtn) confirmBtn.disabled = true;
			fetch('https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=' + lat + '&lon=' + lng, { headers: { 'Accept-Language': 'fa' } })
			.then(function(r){ return r.json(); })
			.then(function(data){ applyAddress((data && data.address) || {}, (data && data.display_name) || ''); })
			.catch(function(){ if (resultEl) resultEl.textContent = lat.toFixed(5) + ' , ' + lng.toFixed(5); if (confirmBtn) confirmBtn.disabled = false; });
		}

		function applyAddress(addr, displayName){
			currentMeta = {
				state: addr.state || addr.province || addr.region || '',
				city: addr.city || addr.town || addr.village || addr.municipality || addr.county || '',
				postcode: addr.postcode || '',
				road: addr.road || addr.street || '',
				suburb: addr.suburb || addr.neighbourhood || '',
				house_number: addr.house_number || ''
			};
			var parts = [];
			if (currentMeta.state) parts.push(currentMeta.state);
			if (currentMeta.city && currentMeta.city !== currentMeta.state) parts.push(currentMeta.city);
			if (currentMeta.suburb) parts.push(currentMeta.suburb);
			if (currentMeta.road) parts.push(currentMeta.road);
			if (addr.house_number) parts.push('پلاک ' + addr.house_number);
			currentAddress = displayName || parts.join('، ');
			var resultEl = document.getElementById('ezckMapResult');
			if (resultEl) resultEl.innerHTML = '<span>' + String(currentAddress).replace(/</g,'&lt;') + '</span>';
			var confirmBtn = document.getElementById('ezckMapConfirm');
			if (confirmBtn) confirmBtn.disabled = false;
		}

		document.addEventListener('click', function(e){
			if (!e.target.closest('#ezckMapConfirm')) return;
			if (!currentAddress) return;
			syncFields(currentAddress, currentMeta);
			var lat = document.querySelector('input[name="ezck_lat"]');
			var lng = document.querySelector('input[name="ezck_lng"]');
			if (lat && currentLatLng) lat.value = currentLatLng.lat;
			if (lng && currentLatLng) lng.value = currentLatLng.lng;
			var modal = document.querySelector('.ezck-map-modal');
			if (modal) modal.classList.remove('is-open');
			document.documentElement.style.overflow = '';
		});

		btnMap.addEventListener('click', openMapModal);
	}

	/* Coupon */
	var couponBusy = false;
	function showCouponFeedback(msg, type){
		var feedback = document.getElementById('ezck_coupon_feedback');
		if (!feedback) return;
		feedback.hidden = false;
		feedback.className = 'ezck-coupon-feedback is-' + type;
		feedback.textContent = msg;
	}
	function hideCouponFeedback(){
		var feedback = document.getElementById('ezck_coupon_feedback');
		if (!feedback) return;
		feedback.hidden = true; feedback.textContent = ''; feedback.className = 'ezck-coupon-feedback';
	}
	function applyCoupon(){
		if (couponBusy) return;
		var wrap = document.querySelector('.ezck-coupon-wrap');
		if (!wrap) return;
		var input    = wrap.querySelector('.ezck-coupon-input');
		var applyBtn = wrap.querySelector('.ezck-coupon-apply');
		var nonce    = wrap.getAttribute('data-nonce') || '';
		var code = (input && input.value || '').trim();
		if (!code){ showCouponFeedback('لطفاً کد تخفیف را وارد کنید.', 'error'); if (input) input.focus(); return; }
		couponBusy = true;
		if (applyBtn) applyBtn.disabled = true;
		hideCouponFeedback();
		var ajaxUrl = wrap.getAttribute('data-ajax-url') || '/?wc-ajax=apply_coupon';
		var sep = ajaxUrl.indexOf('?') > -1 ? '&' : '?';
		if (ajaxUrl.indexOf('wc-ajax') === -1) ajaxUrl += sep + 'wc-ajax=apply_coupon';
		var body = 'security=' + encodeURIComponent(nonce) + '&coupon_code=' + encodeURIComponent(code);
		fetch(ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: body, credentials: 'same-origin' })
		.then(function(r){ return r.text(); })
		.then(function(text){
			couponBusy = false; if (applyBtn) applyBtn.disabled = false;
			var json = null; try { json = JSON.parse(text); } catch(e) { json = null; }
			if (json && json.fragments) {
				Object.keys(json.fragments).forEach(function(selector){
					var html = json.fragments[selector]; if (!html) return;
					var m = selector.match(/^([a-zA-Z]+)\.([\w-]+)/);
					if (m) { var el = document.querySelector('.' + m[2]); if (el) el.outerHTML = html; }
				});
				setTimeout(function(){ convertPricesToPersian(); refreshPayable(); }, 500);
			} else {
				var tmp = document.createElement('div'); tmp.innerHTML = text;
				var successEl = tmp.querySelector('.woocommerce-message');
				var errorEl   = tmp.querySelector('.woocommerce-error');
				if (successEl) { showCouponFeedback('کد تخفیف با موفقیت اعمال شد.', 'success'); if (input) input.value = ''; }
				else if (errorEl) { var txt = errorEl.textContent.replace(/\s+/g, ' ').trim(); showCouponFeedback(txt || 'کد تخفیف معتبر نیست.', 'error'); }
				window.location.reload();
			}
		})
		.catch(function(){ couponBusy = false; if (applyBtn) applyBtn.disabled = false; showCouponFeedback('خطای ارتباط با سرور. دوباره تلاش کنید.', 'error'); });
	}
	document.addEventListener('click', function(e){
		var toggleBtn = e.target.closest('.ezck-coupon-toggle');
		if (toggleBtn) {
			e.preventDefault();
			var wrap = toggleBtn.closest('.ezck-coupon-wrap'); if (!wrap) return;
			var panel = wrap.querySelector('.ezck-coupon-panel');
			var input = wrap.querySelector('.ezck-coupon-input');
			var isOpen = wrap.getAttribute('data-open') === '1';
			if (isOpen) { wrap.setAttribute('data-open', '0'); if (panel) panel.hidden = true; toggleBtn.setAttribute('aria-expanded', 'false'); }
			else { wrap.setAttribute('data-open', '1'); if (panel) panel.hidden = false; toggleBtn.setAttribute('aria-expanded', 'true'); if (input) setTimeout(function(){ try { input.focus(); } catch(err){} }, 150); }
			return;
		}
		var applyBtn = e.target.closest('#ezck_coupon_apply');
		if (applyBtn) { e.preventDefault(); applyCoupon(); return; }
	});
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape') {
			var openWrap = document.querySelector('.ezck-coupon-wrap[data-open="1"]');
			if (openWrap) {
				openWrap.setAttribute('data-open', '0');
				var panel = openWrap.querySelector('.ezck-coupon-panel'); if (panel) panel.hidden = true;
				var toggle = openWrap.querySelector('.ezck-coupon-toggle');
				if (toggle) { toggle.setAttribute('aria-expanded','false'); toggle.focus(); }
			}
		}
		if (e.key === 'Enter' && e.target && e.target.id === 'ezck_coupon_code') { e.preventDefault(); applyCoupon(); }
	});
	document.addEventListener('input', function(e){ if (e.target && e.target.id === 'ezck_coupon_code') hideCouponFeedback(); });

	function setupObserver(){
		var target = document.querySelector('#order_review');
		if (!target) return;
		var observer = new MutationObserver(function(){
			if (ezckObserverPaused || ezckUpdating) return;
			if (window.__ezckMutTimer) clearTimeout(window.__ezckMutTimer);
			window.__ezckMutTimer = setTimeout(function(){
				ezckObserverPaused = true;
				convertPricesToPersian();
				refreshPayable();
				setTimeout(function(){ ezckObserverPaused = false; }, 300);
			}, 400);
		});
		observer.observe(target, { childList: true, subtree: true });
	}

	
	/* Allow typing a province that is not in the list */
	function ezckEnableCustomState(){
		var sel = document.getElementById('billing_state');
		var field = document.getElementById('billing_state_field');
		if (!sel || !field || field.getAttribute('data-ezck-custom-state') === '1') return;
		field.setAttribute('data-ezck-custom-state', '1');

		var hasOther = false;
		Array.prototype.forEach.call(sel.options, function(o){ if (o.value === '__other__') hasOther = true; });
		if (!hasOther) {
			var opt = document.createElement('option');
			opt.value = '__other__';
			opt.textContent = 'سایر (ورود دستی نام استان)';
			sel.appendChild(opt);
		}

		var wrap = document.getElementById('ezck-state-custom-wrap');
		if (!wrap) {
			wrap = document.createElement('div');
			wrap.id = 'ezck-state-custom-wrap';
			wrap.innerHTML = ''
				+ '<input type="text" id="ezck_state_custom" name="ezck_state_custom" placeholder="نام استان را بنویسید" autocomplete="address-level1" />'
				+ '<div id="ezck-state-custom-hint">اگر استان در لیست نبود، از گزینه «سایر» استفاده کنید و نام را وارد کنید.</div>';
			var holder = sel.closest('.woocommerce-input-wrapper') || field;
			holder.appendChild(wrap);
		}

		var customInput = document.getElementById('ezck_state_custom');

		function syncCustomVisibility(){
			if (sel.value === '__other__') {
				wrap.classList.add('is-open');
				if (customInput && !customInput.value) customInput.focus();
			} else {
				wrap.classList.remove('is-open');
			}
		}

		sel.addEventListener('change', syncCustomVisibility);
		syncCustomVisibility();

		if (customInput) {
			customInput.addEventListener('change', function(){
				var v = (customInput.value || '').trim();
				if (!v) return;
				// Add/select free-text option without rapid update storms
				var exists = false;
				Array.prototype.forEach.call(sel.options, function(o){ if (o.value === v) exists = true; });
				if (!exists) {
					var o = document.createElement('option');
					o.value = v; o.textContent = v; sel.appendChild(o);
				}
				sel.value = v;
				// One delayed WC refresh is enough
				if (window.jQuery) {
					if (window.__ezckCustomStateTimer) clearTimeout(window.__ezckCustomStateTimer);
					window.__ezckCustomStateTimer = setTimeout(function(){
						try { window.jQuery(document.body).trigger('update_checkout'); } catch(e){}
					}, 500);
				}
			});
		}

		// Before place order, ensure value is not stuck on __other__
		var form = document.querySelector('form.checkout');
		if (form && form.getAttribute('data-ezck-state-submit') !== '1') {
			form.setAttribute('data-ezck-state-submit', '1');
			form.addEventListener('submit', function(){
				if (sel.value === '__other__' && customInput) {
					var v = (customInput.value || '').trim();
					if (v) {
						var exists = false;
						Array.prototype.forEach.call(sel.options, function(o){ if (o.value === v) exists = true; });
						if (!exists) {
							var o = document.createElement('option');
							o.value = v; o.textContent = v; sel.appendChild(o);
						}
						sel.value = v;
					}
				}
			});
		}
	}

	function initAll(){
		hideOrphans();
		ezckForceFix();
		ezckMoveAddressPicker();
		ezckBuildPalettes();
		initAddressModule();
		ezckEnableCustomState();
		convertPricesToPersian();
		refreshPayable();
		setupObserver();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	if (window.jQuery){
		window.jQuery(document.body).on('updated_checkout', function(){
			ezckObserverPaused = true;
			setTimeout(function(){
				ezckForceFix();
				ezckMoveAddressPicker();
				ezckBuildPalettes();
				initAddressModule();
				ezckEnableCustomState();
				convertPricesToPersian();
				refreshPayable();
				setTimeout(function(){ ezckObserverPaused = false; }, 300);
			}, 300);
		});
	}

	setTimeout(function(){ ezckForceFix(); ezckMoveAddressPicker(); ezckBuildPalettes(); initAddressModule(); }, 300);
	setTimeout(function(){ ezckForceFix(); ezckMoveAddressPicker(); ezckBuildPalettes(); initAddressModule(); }, 800);
	setTimeout(function(){ ezckForceFix(); ezckMoveAddressPicker(); ezckBuildPalettes(); initAddressModule(); }, 1500);
	setTimeout(function(){ ezckForceFix(); ezckMoveAddressPicker(); ezckBuildPalettes(); initAddressModule(); }, 2500);

	setTimeout(function(){
		ezckObserverPaused = true;
		convertPricesToPersian();
		refreshPayable();
		setTimeout(function(){ ezckObserverPaused = false; }, 300);
	}, 1000);
})();
</script>
		<?php
	}
}

/* ============================================================
   لایه ایران — پیکر آدرس‌های من (نمایش در بالای فرم، بعدا با JS جابجا می‌شود)
   ============================================================ */
if ( ! function_exists( 'ezck_iran_checkout_layer' ) ) {
	add_action( 'init', 'ezck_iran_checkout_layer', 20 );
	function ezck_iran_checkout_layer() {
		add_filter( 'default_checkout_billing_country', function () { return 'IR'; } );
		add_filter( 'default_checkout_shipping_country', function () { return 'IR'; } );
		add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false', 999 );
		add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false', 999 );
		add_action( 'woocommerce_before_checkout_billing_form', 'ezck_iran_address_picker', 3 );
		add_action( 'wp_footer', 'ezck_iran_checkout_footer', 40 );
	}

	function ezck_iran_get_addresses() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) return array();
		$list = get_user_meta( $user_id, 'ezcd_address_book', true );
		if ( ! is_array( $list ) || empty( $list ) ) {
			$alt = get_user_meta( $user_id, 'ezlens_cd_addresses', true );
			if ( is_array( $alt ) && ! empty( $alt ) ) $list = $alt;
		}
		if ( ! is_array( $list ) ) $list = array();
		if ( empty( $list ) && class_exists( 'WC_Customer' ) ) {
			$c = new WC_Customer( $user_id );
			foreach ( array( 'billing', 'shipping' ) as $t ) {
				$a1 = call_user_func( array( $c, "get_{$t}_address_1" ) );
				if ( ! $a1 ) continue;
				$list[] = array(
					'id'         => $t,
					'label'      => ( 'billing' === $t ) ? 'صورتحساب' : 'ارسال',
					'first_name' => call_user_func( array( $c, "get_{$t}_first_name" ) ),
					'last_name'  => call_user_func( array( $c, "get_{$t}_last_name" ) ),
					'address_1'  => $a1,
					'address_2'  => call_user_func( array( $c, "get_{$t}_address_2" ) ),
					'city'       => call_user_func( array( $c, "get_{$t}_city" ) ),
					'state'      => call_user_func( array( $c, "get_{$t}_state" ) ),
					'postcode'   => call_user_func( array( $c, "get_{$t}_postcode" ) ),
					'phone'      => ( 'billing' === $t ) ? $c->get_billing_phone() : '',
					'plaque'     => (string) get_user_meta( $user_id, 'billing_plaque', true ),
					'unit'       => (string) get_user_meta( $user_id, 'billing_unit', true ),
				);
			}
		}
		return array_values( $list );
	}

	function ezck_iran_address_picker() {
		static $done = false;
		if ( $done || ! is_user_logged_in() ) return;
		$done = true;
		$list  = ezck_iran_get_addresses();
		$count = count( $list );
		$fa = function ( $n ) {
			return str_replace(
				array( '0','1','2','3','4','5','6','7','8','9' ),
				array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' ),
				(string) $n
			);
		};
		$svg_pin = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
		$svg_check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
		$svg_empty = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
		?>
		<div class="ezck-addr-wrap" id="ezck-iran-addr" data-count="<?php echo esc_attr( (string) $count ); ?>">
			<div class="ezck-addr-head">
				<div class="ezck-addr-head-icon"><?php echo $svg_pin; ?></div>
				<div class="ezck-addr-head-text">
					<div class="ezck-addr-head-title">آدرس‌های من</div>
					<div class="ezck-addr-head-sub">برای پر شدن سریع فیلدها، یکی را انتخاب کنید</div>
				</div>
				<?php if ( $count > 0 ) : ?>
					<div class="ezck-addr-head-count"><?php echo esc_html( $fa( $count ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php if ( empty( $list ) ) : ?>
				<div class="ezck-addr-empty">
					<div class="ezck-addr-empty-icon"><?php echo $svg_empty; ?></div>
					<div class="ezck-addr-empty-title">هنوز آدرسی ذخیره نکرده‌اید</div>
					<div class="ezck-addr-empty-sub">پس از این خرید، این آدرس به‌طور خودکار ذخیره می‌شود</div>
				</div>
			<?php else : ?>
				<div class="ezck-addr-list">
					<?php foreach ( $list as $i => $a ) :
						$id  = esc_attr( isset( $a['id'] ) ? $a['id'] : '' );
						$lab = isset( $a['label'] ) && $a['label'] !== '' ? $a['label'] : 'آدرس';
						$parts = array_filter( array(
							isset( $a['address_1'] ) ? $a['address_1'] : '',
							! empty( $a['plaque'] ) ? ( 'پلاک ' . $a['plaque'] ) : '',
							! empty( $a['unit'] ) ? ( 'واحد ' . $a['unit'] ) : ( isset( $a['address_2'] ) ? $a['address_2'] : '' ),
							isset( $a['city'] ) ? $a['city'] : '',
						) );
						$line = esc_html( trim( implode( '، ', $parts ) ) );
						$json = esc_attr( wp_json_encode( $a ) );
						?>
						<label class="ezck-addr-item" style="--ezck-i:<?php echo (int) $i; ?>;">
							<input type="radio" name="ezck_saved_addr" class="ezck-iran-addr-radio" value="<?php echo $id; ?>" data-json="<?php echo $json; ?>" />
							<span class="ezck-addr-item-radio" aria-hidden="true"></span>
							<span class="ezck-addr-item-body">
								<span class="ezck-addr-item-label"><?php echo $svg_pin; ?><?php echo esc_html( $lab ); ?></span>
								<span class="ezck-addr-item-line"><?php echo $line; ?></span>
							</span>
							<span class="ezck-addr-item-check" aria-hidden="true"><?php echo $svg_check; ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	function ezck_iran_checkout_footer() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) return;
		?>
		<style id="ezck-iran-layer">
		#billing_country_field, #shipping_country_field, .ezck-hide-country,
		p#billing_country_field, p#shipping_country_field,
		.form-row#billing_country_field, .form-row#shipping_country_field{
			display:none !important;visibility:hidden !important;height:0 !important;
			overflow:hidden !important;opacity:0 !important;position:absolute !important;
			left:-99999px !important;top:-99999px !important;pointer-events:none !important;
			margin:0 !important;padding:0 !important;border:0 !important;
		}
		#ship-to-different-address, #ship-to-different-address-checkbox,
		h3#ship-to-different-address, .ship-to-different-address,
		.shipping_address, .woocommerce-shipping-fields,
		.woocommerce-shipping-fields > h3, .woocommerce-shipping-fields__field-wrapper,
		#shipping_country_field, #shipping_first_name_field, #shipping_last_name_field,
		#shipping_address_1_field, #shipping_address_2_field, #shipping_city_field,
		#shipping_state_field, #shipping_postcode_field, #shipping_phone_field,
		#shipping_company_field{
			display:none !important;visibility:hidden !important;height:0 !important;
			overflow:hidden !important;opacity:0 !important;position:absolute !important;
			left:-99999px !important;pointer-events:none !important;
			margin:0 !important;padding:0 !important;border:0 !important;
		}
		#billing_plaque_field, #billing_unit_field{
			display:block !important;visibility:visible !important;height:auto !important;
			opacity:1 !important;position:static !important;overflow:visible !important;
		}
		#billing_plaque_field label, #billing_unit_field label{
			display:block !important;font-size:13px !important;font-weight:800 !important;
			color:#334155 !important;margin-bottom:6px !important;
		}
		#billing_plaque_field input, #billing_unit_field input{
			display:block !important;width:100% !important;min-height:48px !important;
			padding:11px 14px !important;text-align:center !important;font-weight:800 !important;
			font-size:15px !important;background:linear-gradient(180deg,#fff 0%,#f8fafc 100%) !important;
			border:2px solid #e8edf5 !important;border-radius:12px !important;color:#0f172a !important;
			outline:none !important;
		}
		#billing_plaque_field input:focus, #billing_unit_field input:focus{
			border-color:#031f8a !important;background:#fff !important;
			box-shadow:0 0 0 4px rgba(3,31,138,.1) !important;
		}
		body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.ezck-field-plaque,
		body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.ezck-field-unit{
			grid-column: auto / span 1 !important;
		}
		@media (max-width: 700px){
			body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.ezck-field-plaque,
			body.ezck-checkout-page form.checkout.woocommerce-checkout .form-row.ezck-field-unit{
				grid-column: 1 / -1 !important;
			}
		}

		.ezck-addr-wrap{grid-column:1 / -1 !important;margin:0 0 18px;padding:14px;border:1px solid #e8edf5;border-radius:16px;background:linear-gradient(145deg,#f8fafc 0%,#eef2ff 100%);position:relative;overflow:hidden;animation:ezckAddrIn .5s cubic-bezier(.22,1,.36,1) both}
		@keyframes ezckAddrIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
		.ezck-addr-head{display:flex;align-items:center;gap:12px;margin:0 0 12px;padding:0 4px}
		.ezck-addr-head-icon{width:42px;height:42px;flex:0 0 42px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:linear-gradient(135deg,#031f8a 0%,#2563eb 100%);color:#fff}
		.ezck-addr-head-icon svg{width:20px;height:20px;stroke:#fff;fill:none}
		.ezck-addr-head-text{flex:1;min-width:0}
		.ezck-addr-head-title{font-size:14.5px;font-weight:800;color:#031f8a;line-height:1.4}
		.ezck-addr-head-sub{font-size:11.5px;color:#64748b;margin-top:2px}
		.ezck-addr-head-count{flex:0 0 auto;min-width:26px;height:26px;padding:0 9px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:#fff;border:1px solid #c7d2fe;color:#031f8a;font-size:12px;font-weight:800}
		.ezck-addr-list{display:flex;flex-direction:column;gap:8px}
		.ezck-addr-item{position:relative;display:flex;align-items:center;gap:12px;padding:12px 14px;background:#fff;border:1.5px solid #e2e8f0;border-radius:13px;cursor:pointer;transition:border-color .22s,box-shadow .22s,transform .18s;animation:ezckAddrItemIn .42s cubic-bezier(.22,1,.36,1) both;animation-delay:calc(var(--ezck-i, 0) * 60ms + 80ms)}
		@keyframes ezckAddrItemIn{from{opacity:0;transform:translateX(12px)}to{opacity:1;transform:translateX(0)}}
		.ezck-addr-item:hover{border-color:#c7d2fe;transform:translateY(-2px);box-shadow:0 8px 20px rgba(3,31,138,.08)}
		.ezck-addr-item input[type="radio"].ezck-iran-addr-radio{position:absolute !important;opacity:0 !important;pointer-events:none !important;width:0 !important;height:0 !important}
		.ezck-addr-item-radio{position:relative;flex:0 0 20px;width:20px;height:20px;border:2px solid #cbd5e1;border-radius:50%;background:#fff}
		.ezck-addr-item-radio::after{content:"";position:absolute;inset:3px;border-radius:50%;background:#031f8a;transform:scale(0);transition:transform .22s}
		.ezck-addr-item-body{flex:1;min-width:0;display:flex;flex-direction:column;gap:4px}
		.ezck-addr-item-label{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:800;color:#0f172a}
		.ezck-addr-item-label svg{width:15px;height:15px;flex-shrink:0;color:#031f8a}
		.ezck-addr-item-line{font-size:12px;color:#64748b;line-height:1.6;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;word-break:break-word}
		.ezck-addr-item-check{flex:0 0 26px;width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:#eff6ff;color:#031f8a;opacity:0;transform:scale(.6);transition:opacity .25s,transform .25s}
		.ezck-addr-item-check svg{width:14px;height:14px;stroke:currentColor}
		.ezck-addr-item:has(input:checked){border-color:#031f8a;background:linear-gradient(145deg,#eff6ff 0%,#e0e7ff 100%);box-shadow:0 8px 24px rgba(3,31,138,.14),0 0 0 3px rgba(3,31,138,.08)}
		.ezck-addr-item:has(input:checked) .ezck-addr-item-radio{border-color:#031f8a}
		.ezck-addr-item:has(input:checked) .ezck-addr-item-radio::after{transform:scale(1)}
		.ezck-addr-item:has(input:checked) .ezck-addr-item-check{opacity:1;transform:scale(1);background:linear-gradient(135deg,#031f8a,#2563eb);color:#fff}
		.ezck-addr-empty{text-align:center;padding:22px 14px;border:1.5px dashed #c7d2fe;border-radius:13px;background:rgba(255,255,255,.6)}
		.ezck-addr-empty-icon{width:52px;height:52px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;border-radius:15px;background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#031f8a}
		.ezck-addr-empty-icon svg{width:26px;height:26px}
		.ezck-addr-empty-title{font-size:13.5px;font-weight:800;color:#031f8a;margin-bottom:4px}
		.ezck-addr-empty-sub{font-size:11.5px;color:#64748b}
		@media (max-width: 600px){
			.ezck-addr-wrap{padding:12px;border-radius:14px}
			.ezck-addr-head-icon{width:36px;height:36px;flex:0 0 36px}
			.ezck-addr-head-title{font-size:13.5px}
			.ezck-addr-item{padding:11px 12px;gap:10px}
			.ezck-addr-item-radio{flex:0 0 18px;width:18px;height:18px}
			.ezck-addr-item-label{font-size:13px}
			.ezck-addr-item-line{font-size:11.5px}
			.ezck-addr-item-check{flex:0 0 22px;width:22px;height:22px}
		}
		</style>
		<script>
		(function(){
			function ezckForceFix(){
				['billing_country','shipping_country'].forEach(function(id){
					var input = document.getElementById(id);
					if (input) {
						input.value = 'IR';
						var wrap = input.closest('p,div,.form-row,[id$="_field"]');
						if (wrap) {
							wrap.style.setProperty('display','none','important');
							wrap.style.setProperty('height','0','important');
							wrap.style.setProperty('overflow','hidden','important');
						}
					}
				});
				var shipDiffCb = document.getElementById('ship-to-different-address-checkbox');
				if (shipDiffCb) {
					if (shipDiffCb.checked) {
						shipDiffCb.checked = false;
						shipDiffCb.dispatchEvent(new Event('change', { bubbles: true }));
					}
					var wrap = shipDiffCb.closest('h3,p,label,div');
					if (wrap) { wrap.style.setProperty('display','none','important'); wrap.style.setProperty('height','0','important'); }
				}
				var shipFields = document.querySelector('.woocommerce-shipping-fields');
				if (shipFields) { shipFields.style.setProperty('display','none','important'); shipFields.style.setProperty('height','0','important'); }
			}
			if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ezckForceFix); else ezckForceFix();
			if (window.jQuery) window.jQuery(document.body).on('updated_checkout', function(){ setTimeout(ezckForceFix, 150); setTimeout(ezckForceFix, 600); });
			setTimeout(ezckForceFix, 300); setTimeout(ezckForceFix, 800); setTimeout(ezckForceFix, 1500); setTimeout(ezckForceFix, 2500);
		})();
		</script>
		<script>
		(function(){
			function fill(d){
				if(!d) return;
				var map={
					billing_first_name:d.first_name||'', billing_last_name:d.last_name||'',
					billing_address_1:d.address_1||'',
					billing_city:d.city||'', billing_state:d.state||'', billing_postcode:d.postcode||'',
					billing_phone:d.phone||'',
					billing_plaque:d.plaque||'', billing_unit:d.unit||d.address_2||'',
					billing_country:'IR'
				};
				Object.keys(map).forEach(function(k){
					var el=document.getElementById(k)||document.querySelector('[name="'+k+'"]');
					if(el){
						el.value=map[k];
						el.dispatchEvent(new Event('change',{bubbles:true}));
						el.dispatchEvent(new Event('input',{bubbles:true}));
					}
				});
				var addrText = document.getElementById('ezckAddressText');
				if (addrText && d.address_1) addrText.value = d.address_1;
				if(window.jQuery) jQuery(document.body).trigger('update_checkout');
			}
			document.querySelectorAll('.ezck-iran-addr-radio').forEach(function(r){
				r.addEventListener('change',function(){
					if(!r.checked) return;
					try{ fill(JSON.parse(r.getAttribute('data-json')||'{}')); }catch(e){}
				});
			});
			var bc=document.getElementById('billing_country'); if(bc) bc.value='IR';
		})();
		</script>
		<?php
	}
}