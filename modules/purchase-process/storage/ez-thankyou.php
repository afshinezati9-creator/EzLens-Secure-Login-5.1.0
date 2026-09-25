<?php
/**
 * Plugin Name: EzLens Thank You
 * Description: صفحه تشکر مینیمال — آیکون پلاگین، انیمیشن، آدرس و مسیر حرفه‌ای
 * Version:     5.9.4
 * Author:      Afshin Ezati
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EZTY_VERSION' ) ) {
	define( 'EZTY_VERSION', '5.9.4' );
}

add_filter( 'elementor/widget/render_content', function ( $content, $widget ) {
	if ( ! is_object( $widget ) || ! method_exists( $widget, 'get_name' ) || $widget->get_name() !== 'html' ) {
		return $content;
	}
	$s = method_exists( $widget, 'get_settings' ) ? $widget->get_settings() : array();
	$html = isset( $s['html'] ) ? (string) $s['html'] : '';
	if ( $html !== '' && strpos( $html, 'ezlens-success' ) !== false ) {
		return '';
	}
	return $content;
}, 10, 2 );

add_action( 'wp_head', function () {
	echo '<style id="ezty-hide-old">.ezlens-success{display:none!important;height:0!important;overflow:hidden!important}</style>';
}, 999 );

/* ---- Plugin icons from disk (EzLens Secure Login) ---- */
if ( ! function_exists( 'ezty_icon' ) ) {
	function ezty_icon( $name ) {
		static $cache = array();
		if ( isset( $cache[ $name ] ) ) {
			return $cache[ $name ];
		}

		$paths = array();
		// Aliases for clearer semantics
		$aliases = array(
			'send'         => array( 'send', 'send-svgrepo-com', 'truck', 'delivery' ),
			'activity'     => array( 'activity', 'trending-up', 'bar-chart', 'clipboard-check' ),
			'eye'          => array( 'eye', 'eye-svgrepo-com', 'search', 'map' ),
			'delivery'     => array( 'delivery', 'truck', 'package' ),
			'package'      => array( 'package', 'package-box-ui-2-svgrepo-com', 'box' ),
			'award'        => array( 'award', 'award-svgrepo-com', 'check-circle', 'star', 'shield' ),
			'credit-card'  => array( 'credit-card', 'wallet', 'dollar-sign', 'banknote', 'receipt' ),
			'copy'         => array( 'copy', 'clipboard', 'clipboard-check', 'file-text' ),
			'receipt'      => array( 'receipt', 'clipboard-check', 'file-text' ),
		);
		$names = isset( $aliases[ $name ] ) ? $aliases[ $name ] : array( $name );

		if ( defined( 'EZLAUTH_PLUGIN_DIR' ) ) {
			foreach ( $names as $n ) {
				$paths[] = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $n . '.svg';
				$paths[] = EZLAUTH_PLUGIN_DIR . 'assets/icons/' . $n . '.svg';
			}
		}
		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			$base = trailingslashit( WP_PLUGIN_DIR );
			foreach ( $names as $n ) {
				$paths[] = $base . 'EzLens-Secure-Login-5.1.0/assets/icons/modern/' . $n . '.svg';
				$paths[] = $base . 'ezlens-secure-login/assets/icons/modern/' . $n . '.svg';
			}
		}
		if ( defined( 'EZTY_PLUGIN_DIR' ) ) {
			foreach ( $names as $n ) {
				$paths[] = EZTY_PLUGIN_DIR . 'assets/icons/modern/' . $n . '.svg';
			}
		}

		$svg = '';
		foreach ( array_unique( $paths ) as $path ) {
			if ( ! is_readable( $path ) ) {
				continue;
			}
			$raw = @file_get_contents( $path );
			if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
				continue;
			}
			$svg = ezty_normalize_svg( $raw );
			if ( $svg !== '' ) {
				break;
			}
		}

		if ( $svg === '' ) {
			// Minimal fallback glyph (no external dep)
			$svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/></svg>';
		}

		$cache[ $name ] = $svg;
		return $svg;
	}
}

if ( ! function_exists( 'ezty_normalize_svg' ) ) {
	function ezty_normalize_svg( $svg ) {
		$svg = trim( (string) $svg );
		if ( substr( $svg, 0, 3 ) === "\xEF\xBB\xBF" ) {
			$svg = substr( $svg, 3 );
		}
		$svg = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg );
		$svg = preg_replace( '/<!DOCTYPE[^>]*>/i', '', $svg );
		$svg = trim( $svg );
		$pos = strpos( $svg, '<svg' );
		if ( false === $pos ) {
			return '';
		}
		if ( $pos > 0 ) {
			$svg = substr( $svg, $pos );
		}
		if ( stripos( $svg, 'viewBox' ) === false ) {
			$svg = preg_replace( '/<svg\b/', '<svg viewBox="0 0 24 24"', $svg, 1 );
		}
		$svg = preg_replace( '/\s(width|height)="[^"]*"/i', '', $svg );
		// Force Latin digits (site-wide FA converter may re-break; JS repairs again)
		$svg = str_replace(
			array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
			$svg
		);
		return trim( $svg );
	}
}

if ( ! function_exists( 'ezty_is_thankyou' ) ) {
	function ezty_is_thankyou() {
		return function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
	}
}

if ( ! function_exists( 'ezty_fa' ) ) {
	function ezty_fa( $text ) {
		return str_replace(
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
			array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
			(string) $text
		);
	}
}

if ( ! function_exists( 'ezty_normalize_mobile' ) ) {
	function ezty_normalize_mobile( $m ) {
		if ( class_exists( 'EzLens_Auth_Helper' ) && method_exists( 'EzLens_Auth_Helper', 'normalize_mobile' ) ) {
			return EzLens_Auth_Helper::normalize_mobile( $m );
		}
		$m = preg_replace( '/[^0-9]/', '', (string) $m );
		if ( strpos( $m, '98' ) === 0 && strlen( $m ) >= 12 ) {
			$m = '0' . substr( $m, 2 );
		}
		if ( substr( $m, 0, 1 ) === '9' && strlen( $m ) === 10 ) {
			$m = '0' . $m;
		}
		return $m;
	}
}

add_action( 'wp_head', function () {
	if ( ezty_is_thankyou() ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	}
}, 5 );

/* Notifications */
add_action( 'woocommerce_order_status_changed', 'ezty_send_notifications', 20, 4 );
add_action( 'woocommerce_thankyou', 'ezty_send_notifications_thankyou', 20, 1 );

function ezty_send_notifications_thankyou( $order_id ) {
	if ( ! $order_id ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_ezty_notified' ) === 'yes' ) {
		return;
	}
	if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ), true ) ) {
		ezty_do_notify( $order );
	}
}

function ezty_send_notifications( $order_id, $old_status, $new_status, $order ) {
	if ( ! $order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order || ! in_array( $new_status, array( 'processing', 'completed', 'on-hold' ), true ) ) {
		return;
	}
	if ( $order->get_meta( '_ezty_notified' ) === 'yes' ) {
		return;
	}
	ezty_do_notify( $order );
}

function ezty_do_notify( $order ) {
	$num  = $order->get_order_number();
	$name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ?: 'مشتری';
	$phone = $order->get_billing_phone();
	$email = $order->get_billing_email();
	$total = $order->get_formatted_order_total();
	$method = $order->get_payment_method_title();
	$status = wc_get_order_status_name( $order->get_status() );
	$account_url = wc_get_page_permalink( 'myaccount' );

	if ( $phone ) {
		$phone = ezty_normalize_mobile( $phone );
		$tpl = class_exists( 'EzLens_Auth_Settings' ) ? (string) EzLens_Auth_Settings::get( 'sms_thankyou_template' ) : '';
		if ( ! $tpl ) {
			$tpl = "سلام {name}\nسفارش #{number} با موفقیت ثبت شد\nمبلغ: {total}\nپیگیری: {account_url}";
		}
		$msg = str_replace(
			array( '{name}', '{number}', '{total}', '{status}', '{method}', '{account_url}', '{site_name}' ),
			array( $name, $num, wp_strip_all_tags( $total ), $status, $method ?: '-', $account_url, get_bloginfo( 'name' ) ),
			$tpl
		);
		ezty_send_sms( $phone, $msg );
	}

	if ( $email && is_email( $email ) ) {
		$sub = class_exists( 'EzLens_Auth_Settings' ) ? (string) EzLens_Auth_Settings::get( 'email_thankyou_subject' ) : '';
		if ( ! $sub ) {
			$sub = 'تأیید سفارش #{number} — {site_name}';
		}
		$sub = str_replace( array( '{number}', '{site_name}' ), array( $num, get_bloginfo( 'name' ) ), $sub );
		ezty_send_email( $email, $sub, ezty_build_email_html( $order, $name, $num, $total, $method, $status, $account_url ) );
	}

	$order->update_meta_data( '_ezty_notified', 'yes' );
	$order->update_meta_data( '_ezty_notified_at', current_time( 'mysql' ) );
	$order->save();
}

function ezty_send_sms( $mobile, $message ) {
	$mobile = ezty_normalize_mobile( $mobile );
	if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
		return false;
	}
	if ( class_exists( 'EzLens_Auth_SMS' ) ) {
		$sms = new EzLens_Auth_SMS();
		if ( method_exists( $sms, 'send' ) ) {
			$res = $sms->send( $mobile, $message );
			if ( ! empty( $res['success'] ) ) {
				return true;
			}
		}
	}
	if ( class_exists( 'EzLens_Auth_Messaging' ) && method_exists( 'EzLens_Auth_Messaging', 'send_sms' ) ) {
		$res = EzLens_Auth_Messaging::send_sms( $mobile, $message );
		if ( ! empty( $res['success'] ) ) {
			return true;
		}
	}
	return false;
}

function ezty_send_email( $to, $subject, $html ) {
	return wp_mail( $to, $subject, $html, array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
	) );
}

function ezty_build_email_html( $order, $name, $num, $total, $method, $status, $account_url ) {
	$rows = '';
	foreach ( $order->get_items() as $item ) {
		$rows .= '<tr><td style="padding:10px 12px;border-bottom:1px solid #eee;font-size:13px;font-weight:600">' . esc_html( $item->get_name() ) . '</td>'
			. '<td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:center">' . (int) $item->get_quantity() . '</td>'
			. '<td style="padding:10px 12px;border-bottom:1px solid #eee;text-align:left;direction:ltr">' . wp_kses_post( wc_price( $item->get_subtotal() ) ) . '</td></tr>';
	}
	return '<!DOCTYPE html><html dir="rtl" lang="fa"><body style="margin:0;padding:24px;background:#f4f6fb;font-family:Tahoma,sans-serif;direction:rtl">'
		. '<table width="100%" style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden">'
		. '<tr><td style="background:linear-gradient(135deg,#031f8a,#0a3d91);padding:28px;text-align:center;color:#fff">'
		. '<div style="font-size:18px;font-weight:800">سفارش شما با موفقیت ثبت شد</div>'
		. '<p style="margin:8px 0 0;opacity:.9;font-size:13px">' . esc_html( $name ) . ' عزیز، از اعتمادتان سپاسگزاریم</p></td></tr>'
		. '<tr><td style="padding:20px">'
		. '<p style="text-align:center;margin:0 0 16px"><span style="display:inline-block;padding:8px 18px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:999px;font-weight:700;color:#059669;font-size:13px">شماره سفارش: <b dir="ltr">' . esc_html( $num ) . '</b></span></p>'
		. '<p style="font-size:13px;margin:0 0 6px">مبلغ: ' . wp_kses_post( $order->get_formatted_order_total() ) . '</p>'
		. '<p style="font-size:13px;margin:0 0 6px">پرداخت: ' . esc_html( $method ?: '-' ) . '</p>'
		. '<p style="font-size:13px;margin:0 0 16px">وضعیت: ' . esc_html( $status ) . '</p>'
		. '<table width="100%" style="border-collapse:collapse;border:1px solid #eee">' . $rows . '</table>'
		. '<p style="text-align:center;margin:20px 0 0"><a href="' . esc_url( $account_url ) . '" style="display:inline-block;padding:12px 28px;background:#031f8a;color:#fff;text-decoration:none;border-radius:10px;font-size:13px;font-weight:700">پیگیری سفارش</a></p>'
		. '</td></tr></table></body></html>';
}


/* Clear product qty text on thank-you */
add_filter( 'woocommerce_order_item_quantity_html', 'ezty_qty_html', 20, 2 );
function ezty_qty_html( $html, $item ) {
	if ( ! function_exists( 'ezty_is_thankyou' ) || ! ezty_is_thankyou() ) {
		return $html;
	}
	$qty = (int) $item->get_quantity();
	if ( $qty <= 0 ) {
		return $html;
	}
	$label = ( $qty === 1 )
		? '۱ عدد'
		: ezty_fa( (string) $qty ) . ' عدد';
	return ' <span class="ezty-qty">(' . esc_html( $label ) . ')</span>';
}

add_filter( 'woocommerce_order_item_name', 'ezty_item_name_clean', 20, 3 );
function ezty_item_name_clean( $name, $item, $is_visible = true ) {
	if ( ! function_exists( 'ezty_is_thankyou' ) || ! ezty_is_thankyou() ) {
		return $name;
	}
	// Keep name; quantity shown separately via qty filter
	return $name;
}

/* ---- Assets ---- */
add_action( 'wp_head', 'ezty_assets', 99 );
function ezty_assets() {
	if ( ! ezty_is_thankyou() ) {
		return;
	}
	?>
<style id="ezty-v59">
html body.woocommerce-order-received{
	--ez-navy:#031f8a;--ez-navy-deep:#011663;
	--ez-grad:linear-gradient(135deg,#031f8a 0%,#0a3d91 45%,#011663 100%);
	--ez-ok:#059669;--ez-ok-bg:#ecfdf5;--ez-ok-bd:#a7f3d0;
	--ez-bg:#f3f5fa;--ez-white:#ffffff;--ez-text:#0f172a;--ez-mute:#64748b;--ez-line:#e6ebf5;--ez-soft:#f7f9fc;
	background:var(--ez-bg)!important;
}
html body.woocommerce-order-received .main-page-wrapper,
html body.woocommerce-order-received .website-wrapper{background:var(--ez-bg)!important}
html body.woocommerce-order-received .container,
html body.woocommerce-order-received .wd-content-layout,
html body.woocommerce-order-received .site-content,
html body.woocommerce-order-received main#main-content,
html body.woocommerce-order-received .entry-content,
html body.woocommerce-order-received .woocommerce{
	max-width:980px!important;width:100%!important;
	margin-left:auto!important;margin-right:auto!important;
	padding-left:16px!important;padding-right:16px!important;
	float:none!important;clear:both!important;
}
html body.woocommerce-order-received .woocommerce-thankyou-order-received,
html body.woocommerce-order-received .woocommerce-notice--success,
html body.woocommerce-order-received ul.woocommerce-order-overview,
html body.woocommerce-order-received ul.order_details,
html body.woocommerce-order-received .woocommerce-order > p:first-of-type,
html body.woocommerce-order-received .woocommerce-customer-details{display:none!important}

#ezty{
	display:block!important;position:relative!important;z-index:5!important;
	clear:both!important;float:none!important;width:100%!important;max-width:980px!important;
	margin:0 auto 48px!important;padding:0!important;direction:rtl!important;
	font-size:14px!important;line-height:1.75!important;color:var(--ez-text)!important;
}
#ezty *{box-sizing:border-box!important}
#ezty a{text-decoration:none!important;color:inherit!important}
#ezty button{font-family:inherit!important;cursor:pointer!important;border:0!important;background:transparent!important;margin:0!important;padding:0!important}
#ezty svg{
	display:block!important;width:100%!important;height:100%!important;
	fill:none!important;stroke:currentColor!important;stroke-width:2!important;
	stroke-linecap:round!important;stroke-linejoin:round!important;
}

/* animations */
@keyframes eztyFadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
@keyframes eztyPop{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:scale(1)}}
@keyframes eztyPulse{0%,100%{box-shadow:0 0 0 0 rgba(3,31,138,.25)}50%{box-shadow:0 0 0 12px rgba(3,31,138,0)}}
@keyframes eztyLine{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes eztyStepIn{from{opacity:0;transform:translateY(10px) scale(.92)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes eztyShimmer{0%{background-position:100% 0}100%{background-position:-100% 0}}
#ezty .ezty-anim{animation:eztyFadeUp .55s ease both}
#ezty .ezty-anim-d1{animation-delay:.06s}
#ezty .ezty-anim-d2{animation-delay:.12s}
#ezty .ezty-anim-d3{animation-delay:.18s}
#ezty .ezty-anim-d4{animation-delay:.24s}
#ezty .ezty-anim-d5{animation-delay:.3s}
#ezty .ezty-progress-item{animation:eztyStepIn .5s cubic-bezier(.34,1.2,.64,1) both}
#ezty .ezty-progress-item:nth-child(1){animation-delay:.05s}
#ezty .ezty-progress-item:nth-child(3){animation-delay:.15s}
#ezty .ezty-progress-item:nth-child(5){animation-delay:.25s}
#ezty .ezty-progress-item:nth-child(7){animation-delay:.35s}
#ezty .ezty-progress-sep{animation:eztyLine .55s ease both}
#ezty .ezty-progress-sep:nth-child(2){animation-delay:.12s}
#ezty .ezty-progress-sep:nth-child(4){animation-delay:.22s}
#ezty .ezty-progress-sep:nth-child(6){animation-delay:.32s}
@media(prefers-reduced-motion:reduce){
	#ezty .ezty-anim,#ezty .ezty-mark,#ezty .ezty-progress-sep,#ezty .ezty-progress-item{animation:none!important}
}

/* progress */
#ezty .ezty-progress{
	display:flex!important;align-items:flex-start!important;justify-content:space-between!important;
	gap:0!important;margin:0 0 20px!important;padding:20px 16px 18px!important;
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	position:relative!important;overflow:hidden!important;
}
#ezty .ezty-progress-item{
	display:flex!important;flex-direction:column!important;align-items:center!important;gap:8px!important;
	flex:1 1 0!important;min-width:0!important;text-align:center!important;
	font-size:12px!important;font-weight:600!important;color:var(--ez-mute)!important;
	position:relative!important;z-index:2!important;
}
#ezty .ezty-progress-item .ezty-badge{
	width:48px!important;height:48px!important;border-radius:16px!important;
	background:var(--ez-soft)!important;color:var(--ez-mute)!important;border:1px solid var(--ez-line)!important;
	display:flex!important;align-items:center!important;justify-content:center!important;
	transition:transform .25s,box-shadow .25s!important;
	position:relative!important;
}
#ezty .ezty-progress-item .ezty-badge svg{width:22px!important;height:22px!important}
#ezty .ezty-progress-item.is-done .ezty-badge{
	background:var(--ez-grad)!important;color:#fff!important;border-color:transparent!important;
	box-shadow:0 6px 16px rgba(3,31,138,.2)!important;
}
#ezty .ezty-progress-item.is-done:hover .ezty-badge{transform:translateY(-2px) scale(1.04)!important}
#ezty .ezty-progress-item .ezty-plabel{line-height:1.3!important;font-size:12.5px!important}

/* Final step — "you are here" */
#ezty .ezty-progress-item.is-active{
	color:var(--ez-ok)!important;font-weight:800!important;
}
#ezty .ezty-progress-item.is-active .ezty-badge{
	width:56px!important;height:56px!important;border-radius:18px!important;
	background:linear-gradient(135deg,#059669 0%,#10b981 50%,#047857 100%)!important;
	color:#fff!important;border:3px solid #fff!important;
	box-shadow:0 0 0 3px rgba(5,150,105,.35),0 8px 22px rgba(5,150,105,.35)!important;
	animation:eztyPulse 2.2s ease-in-out infinite!important;
}
#ezty .ezty-progress-item.is-active .ezty-badge svg{width:26px!important;height:26px!important;stroke:#fff!important}
#ezty .ezty-progress-item.is-active .ezty-plabel{
	background:var(--ez-ok-bg)!important;color:var(--ez-ok)!important;
	padding:3px 10px!important;border-radius:999px!important;border:1px solid var(--ez-ok-bd)!important;
	font-size:11.5px!important;
}

/* Connector rails */
#ezty .ezty-progress-sep{
	flex:1 1 28px!important;max-width:56px!important;min-width:20px!important;
	height:4px!important;margin:24px 4px 0!important;flex-shrink:1!important;
	border-radius:4px!important;position:relative!important;overflow:hidden!important;
	background:var(--ez-line)!important;z-index:1!important;
	transform-origin:right center!important;
}
#ezty .ezty-progress-sep.is-done{
	background:linear-gradient(90deg,#031f8a,#0a3d91)!important;
}
#ezty .ezty-progress-sep.is-done::after{
	content:""!important;position:absolute!important;inset:0!important;
	background:linear-gradient(90deg,transparent,rgba(255,255,255,.7),transparent)!important;
	background-size:200% 100%!important;animation:eztyShimmer 1.6s linear infinite!important;
}
#ezty .ezty-progress-sep.is-to-active{
	background:linear-gradient(90deg,#031f8a 0%,#059669 100%)!important;
}
#ezty .ezty-progress-sep.is-to-active::after{
	content:""!important;position:absolute!important;top:0!important;bottom:0!important;right:0!important;
	width:40%!important;
	background:linear-gradient(90deg,transparent,#fff,transparent)!important;
	animation:eztyFlow 1.4s linear infinite!important;
}
@keyframes eztyFlow{
	from{transform:translateX(120%)}
	to{transform:translateX(-180%)}
}

@media(max-width:640px){
	#ezty .ezty-progress{
		overflow-x:auto!important;-webkit-overflow-scrolling:touch!important;justify-content:flex-start!important;
		gap:8px!important;padding:12px!important;scrollbar-width:none!important;
	}
	#ezty .ezty-progress::-webkit-scrollbar{display:none!important}
	#ezty .ezty-progress-sep{display:none!important}
	#ezty .ezty-progress-item{
		flex:0 0 auto!important;flex-direction:row!important;gap:8px!important;
		padding:8px 12px!important;border-radius:999px!important;
		background:var(--ez-soft)!important;border:1px solid var(--ez-line)!important;font-size:11.5px!important;
	}
	#ezty .ezty-progress-item.is-done{
		background:rgba(3,31,138,.06)!important;border-color:rgba(3,31,138,.18)!important;
	}
	#ezty .ezty-progress-item.is-active{
		background:var(--ez-ok-bg)!important;border-color:var(--ez-ok-bd)!important;
	}
	#ezty .ezty-progress-item .ezty-badge{width:30px!important;height:30px!important;border-radius:9px!important}
	#ezty .ezty-progress-item .ezty-badge svg{width:15px!important;height:15px!important}
	#ezty .ezty-progress-item.is-active .ezty-badge{
		width:34px!important;height:34px!important;border-radius:11px!important;animation:none!important;
		border-width:2px!important;
	}
	#ezty .ezty-progress-item.is-active .ezty-badge svg{width:16px!important;height:16px!important}
	#ezty .ezty-progress-item.is-active .ezty-plabel{padding:0!important;border:0!important;background:transparent!important}
}

/* actions */
#ezty .ezty-actions{
	display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;
	gap:10px!important;margin:0 0 18px!important;width:100%!important;
}
@media(max-width:640px){#ezty .ezty-actions{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
#ezty .ezty-actions a,#ezty .ezty-actions button{
	display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;
	gap:10px!important;min-height:92px!important;padding:16px 8px!important;width:100%!important;
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	font-size:12.5px!important;font-weight:700!important;color:var(--ez-text)!important;text-align:center!important;
	transition:border-color .2s,box-shadow .2s,transform .15s!important;
}
#ezty .ezty-actions a:hover,#ezty .ezty-actions button:hover{
	border-color:rgba(3,31,138,.35)!important;box-shadow:0 8px 24px rgba(3,31,138,.1)!important;
	transform:translateY(-2px)!important;color:var(--ez-navy)!important;
}
#ezty .ez-ico-wrap{
	width:40px!important;height:40px!important;border-radius:12px!important;
	background:var(--ez-grad)!important;color:#fff!important;
	display:flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important;
}
#ezty .ez-ico-wrap svg{width:20px!important;height:20px!important;stroke:#fff!important;color:#fff!important}

/* hero */
#ezty .ezty-hero{
	position:relative!important;overflow:hidden!important;
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:20px!important;
	padding:0!important;margin:0 0 16px!important;text-align:center!important;
}
#ezty .ezty-hero-top{background:var(--ez-grad)!important;padding:28px 20px 36px!important;color:#fff!important}
#ezty .ezty-mark{
	width:72px!important;height:72px!important;margin:0 auto 14px!important;border-radius:50%!important;
	background:rgba(255,255,255,.2)!important;border:2px solid rgba(255,255,255,.5)!important;
	display:flex!important;align-items:center!important;justify-content:center!important;color:#fff!important;
	animation:eztyPop .55s cubic-bezier(.34,1.56,.64,1) both;
	box-shadow:0 8px 24px rgba(0,0,0,.12)!important;
}
#ezty .ezty-mark.is-user{
	background:rgba(255,255,255,.95)!important;color:var(--ez-navy)!important;
	border:3px solid rgba(255,255,255,.9)!important;
	box-shadow:0 10px 28px rgba(0,0,0,.15)!important;
}
#ezty .ezty-mark.is-user svg{stroke:var(--ez-navy)!important;color:var(--ez-navy)!important}
#ezty .ezty-mark.is-fail{
	background:rgba(254,226,226,.95)!important;color:#dc2626!important;border-color:#fecaca!important;
}
#ezty .ezty-mark.is-fail svg{stroke:#dc2626!important}
#ezty .ezty-mark svg{width:32px!important;height:32px!important;stroke:#fff!important}
#ezty .ezty-title{
	margin:0 0 8px!important;font-size:clamp(1.15rem,3vw,1.45rem)!important;font-weight:800!important;
	color:#fff!important;line-height:1.4!important;
}
#ezty .ezty-lead{margin:0 auto!important;max-width:420px!important;color:rgba(255,255,255,.9)!important;font-size:.9rem!important;line-height:1.85!important}
#ezty .ezty-hero-body{padding:0 20px 24px!important;margin-top:-18px!important}
#ezty .ezty-chip{
	display:inline-flex!important;align-items:center!important;gap:10px!important;
	padding:12px 18px!important;border-radius:999px!important;font-size:.88rem!important;font-weight:700!important;
	background:var(--ez-white)!important;color:var(--ez-navy)!important;border:1px solid var(--ez-line)!important;
	box-shadow:0 8px 24px rgba(3,31,138,.12)!important;
}
#ezty .ezty-chip code{font-family:inherit!important;direction:ltr!important;font-weight:800!important;background:none!important;border:0!important;padding:0!important;color:var(--ez-navy)!important}
#ezty .ezty-copy{
	width:32px!important;height:32px!important;border-radius:10px!important;
	background:var(--ez-soft)!important;color:var(--ez-navy)!important;
	display:inline-flex!important;align-items:center!important;justify-content:center!important;
}
#ezty .ezty-copy svg{width:15px!important;height:15px!important}

/* calm */
#ezty .ezty-calm{
	display:flex!important;gap:14px!important;align-items:flex-start!important;
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	padding:16px 18px!important;margin:0 0 16px!important;font-size:.86rem!important;color:var(--ez-mute)!important;line-height:1.8!important;
}
#ezty .ezty-calm-ico{
	width:42px!important;height:42px!important;border-radius:12px!important;flex-shrink:0!important;
	background:var(--ez-grad)!important;color:#fff!important;
	display:flex!important;align-items:center!important;justify-content:center!important;
}
#ezty .ezty-calm-ico svg{width:20px!important;height:20px!important;stroke:#fff!important}
#ezty .ezty-calm b{display:block!important;color:var(--ez-navy)!important;font-weight:800!important;margin:0 0 4px!important;font-size:.92rem!important}

/* grid */
#ezty .ezty-grid{
	display:grid!important;grid-template-columns:1.1fr .9fr!important;
	gap:12px!important;margin:0 0 16px!important;width:100%!important;
}
@media(max-width:720px){#ezty .ezty-grid{grid-template-columns:1fr!important}}
#ezty .ezty-card{
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	padding:20px!important;margin:0!important;
}
#ezty .ezty-card-title{
	display:flex!important;align-items:center!important;gap:10px!important;
	margin:0 0 14px!important;padding:0 0 12px!important;
	font-size:.92rem!important;font-weight:800!important;color:var(--ez-navy)!important;
	border-bottom:1px solid var(--ez-line)!important;
}
#ezty .ezty-card-title .ez-ico-wrap{width:32px!important;height:32px!important;border-radius:10px!important}
#ezty .ezty-card-title .ez-ico-wrap svg{width:16px!important;height:16px!important}

#ezty .ezty-meta{
	display:grid!important;grid-template-columns:1fr 1fr!important;gap:8px!important;
}
@media(max-width:480px){#ezty .ezty-meta{grid-template-columns:1fr!important}}
#ezty .ezty-meta > div{
	background:var(--ez-soft)!important;border:1px solid var(--ez-line)!important;border-radius:12px!important;padding:12px!important;
}
#ezty .ezty-meta .ezty-k{margin:0 0 4px!important;font-size:.7rem!important;color:var(--ez-mute)!important;font-weight:600!important}
#ezty .ezty-meta .ezty-v{margin:0!important;font-size:.88rem!important;font-weight:700!important;color:var(--ez-text)!important;word-break:break-word!important}
#ezty .ezty-meta .ezty-v.is-ltr{direction:ltr!important;display:inline-block!important}
#ezty .ezty-meta .ezty-v.is-ok{color:var(--ez-ok)!important}
#ezty .ezty-meta .ezty-v.is-price{color:var(--ez-navy)!important;font-size:.95rem!important}

/* timeline — framed icons */
#ezty .ezty-timeline{list-style:none!important;margin:0!important;padding:0!important}
#ezty .ezty-timeline li{
	position:relative!important;padding:0 48px 18px 0!important;margin:0!important;
	font-size:.84rem!important;color:var(--ez-mute)!important;line-height:1.7!important;list-style:none!important;
}
#ezty .ezty-timeline li:last-child{padding-bottom:0!important}
#ezty .ezty-timeline li::after{
	content:""!important;position:absolute!important;right:15px!important;top:34px!important;bottom:0!important;
	width:2px!important;background:var(--ez-line)!important;
}
#ezty .ezty-timeline li:last-child::after{display:none!important}
#ezty .ezty-timeline .ezty-dot{
	position:absolute!important;right:0!important;top:0!important;
	width:32px!important;height:32px!important;border-radius:10px!important;
	background:var(--ez-grad)!important;color:#fff!important;border:1px solid transparent!important;
	display:flex!important;align-items:center!important;justify-content:center!important;
	box-shadow:0 4px 12px rgba(3,31,138,.2)!important;
}
#ezty .ezty-timeline .ezty-dot svg{width:16px!important;height:16px!important;stroke:#fff!important;color:#fff!important}
#ezty .ezty-timeline strong{
	display:block!important;color:var(--ez-text)!important;font-weight:800!important;
	font-size:.88rem!important;margin:0 0 4px!important;padding-top:4px!important;
}

/* address card */
#ezty .ezty-address{
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	padding:20px!important;margin:0 0 16px!important;
}
#ezty .ezty-address-title{
	display:flex!important;align-items:center!important;gap:10px!important;
	margin:0 0 14px!important;padding:0 0 12px!important;
	font-size:.92rem!important;font-weight:800!important;color:var(--ez-navy)!important;
	border-bottom:1px solid var(--ez-line)!important;
}
#ezty .ezty-address-grid{
	display:grid!important;grid-template-columns:1.2fr .8fr!important;gap:12px!important;
}
@media(max-width:640px){#ezty .ezty-address-grid{grid-template-columns:1fr!important}}
#ezty .ezty-address-block{
	background:var(--ez-soft)!important;border:1px solid var(--ez-line)!important;border-radius:14px!important;
	padding:16px!important;
}
#ezty .ezty-address-row{
	display:flex!important;align-items:flex-start!important;gap:12px!important;margin:0 0 12px!important;
}
#ezty .ezty-address-row:last-child{margin-bottom:0!important}
#ezty .ezty-address-row .ez-ico-wrap{
	width:36px!important;height:36px!important;border-radius:10px!important;
}
#ezty .ezty-address-row .ez-ico-wrap svg{width:17px!important;height:17px!important}
#ezty .ezty-address-row .ezty-al{margin:0 0 2px!important;font-size:.7rem!important;color:var(--ez-mute)!important;font-weight:600!important}
#ezty .ezty-address-row .ezty-av{margin:0!important;font-size:.9rem!important;font-weight:700!important;color:var(--ez-text)!important;line-height:1.6!important}
#ezty .ezty-address-row .ezty-av.is-ltr{direction:ltr!important;display:inline-block!important}

/* support */
#ezty .ezty-support{
	display:flex!important;flex-wrap:wrap!important;align-items:center!important;justify-content:space-between!important;
	gap:14px!important;margin:0 0 16px!important;padding:18px 20px!important;
	background:var(--ez-grad)!important;border-radius:16px!important;color:#fff!important;
}
#ezty .ezty-support-text{flex:1 1 200px!important}
#ezty .ezty-st-title{margin:0 0 4px!important;font-size:.95rem!important;font-weight:800!important;color:#fff!important}
#ezty .ezty-st-desc{margin:0!important;font-size:.82rem!important;opacity:.9!important;line-height:1.7!important;color:rgba(255,255,255,.9)!important}
#ezty .ezty-support-btn{
	display:inline-flex!important;align-items:center!important;gap:10px!important;
	padding:12px 18px!important;border-radius:12px!important;flex-shrink:0!important;
	background:#25D366!important;color:#fff!important;font-weight:700!important;font-size:.88rem!important;
}
#ezty .ezty-support-btn:hover{background:#1ebe57!important;color:#fff!important}
#ezty .ezty-support-btn svg{width:18px!important;height:18px!important;stroke:#fff!important}

/* trust */
#ezty .ezty-trust{
	display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:10px!important;margin:0!important;
}
@media(max-width:560px){#ezty .ezty-trust{grid-template-columns:1fr!important}}
#ezty .ezty-trust > div{
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	padding:18px 14px!important;text-align:center!important;
}
#ezty .ezty-trust .ez-ico-wrap{margin:0 auto 10px!important}
#ezty .ezty-trust .ezty-tt{margin:0 0 3px!important;font-size:.86rem!important;font-weight:800!important}
#ezty .ezty-trust .ezty-ts{margin:0!important;font-size:.74rem!important;color:var(--ez-mute)!important}

/* WC order details keep visible */
html body.woocommerce-order-received .woocommerce-order-details{
	background:var(--ez-white)!important;border:1px solid var(--ez-line)!important;border-radius:16px!important;
	padding:22px!important;margin:0 auto 14px!important;box-shadow:none!important;max-width:980px!important;
}
html body.woocommerce-order-received .woocommerce-order-details > h2,
html body.woocommerce-order-received .woocommerce-order-details__title{
	display:flex!important;align-items:center!important;gap:10px!important;
	font-size:.95rem!important;font-weight:800!important;color:var(--ez-navy)!important;
	margin:0 0 14px!important;padding:0 0 12px!important;border-bottom:1px solid var(--ez-line)!important;background:none!important;
}
html body.woocommerce-order-received .woocommerce-order-details__title .ez-ico-wrap{
	width:32px!important;height:32px!important;border-radius:10px!important;
	background:linear-gradient(135deg,#031f8a 0%,#0a3d91 45%,#011663 100%)!important;
	color:#fff!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important;
}
html body.woocommerce-order-received .woocommerce-order-details__title .ez-ico-wrap svg{
	width:16px!important;height:16px!important;stroke:#fff!important;display:block!important;
}
html body.woocommerce-order-received table.shop_table,
html body.woocommerce-order-received table.woocommerce-table--order-details{
	border:1px solid var(--ez-line)!important;border-radius:14px!important;overflow:hidden!important;width:100%!important;
	border-collapse:separate!important;border-spacing:0!important;background:#fff!important;
	box-shadow:0 1px 2px rgba(15,23,42,.03)!important;
}
html body.woocommerce-order-received table.shop_table th,
html body.woocommerce-order-received table.shop_table td,
html body.woocommerce-order-received table.woocommerce-table--order-details th,
html body.woocommerce-order-received table.woocommerce-table--order-details td{
	padding:14px 16px!important;border:0!important;border-bottom:1px solid var(--ez-line)!important;
	font-size:.9rem!important;vertical-align:middle!important;background:transparent!important;
}
html body.woocommerce-order-received table.shop_table thead th,
html body.woocommerce-order-received table.woocommerce-table--order-details thead th{
	background:linear-gradient(180deg,#f7f9fc,#f1f4fa)!important;color:var(--ez-navy)!important;
	font-weight:800!important;font-size:.8rem!important;letter-spacing:.02em!important;
}
html body.woocommerce-order-received table.shop_table tbody tr:last-child td,
html body.woocommerce-order-received table.woocommerce-table--order-details tbody tr:last-child td{
	border-bottom:1px solid var(--ez-line)!important;
}
html body.woocommerce-order-received table.shop_table tbody .product-name a,
html body.woocommerce-order-received table.woocommerce-table--order-details .product-name a{
	color:var(--ez-text)!important;font-weight:700!important;text-decoration:none!important;
}
html body.woocommerce-order-received table.shop_table tbody .product-name a:hover{
	color:var(--ez-navy)!important;
}
html body.woocommerce-order-received table.shop_table .product-quantity,
html body.woocommerce-order-received table.woocommerce-table--order-details .product-quantity{
	display:none!important;
}
html body.woocommerce-order-received .ezty-qty{
	display:inline-block!important;margin-right:8px!important;padding:3px 10px!important;
	border-radius:999px!important;font-size:.78rem!important;font-weight:700!important;
	background:rgba(3,31,138,.07)!important;color:var(--ez-navy)!important;
	border:1px solid rgba(3,31,138,.12)!important;white-space:nowrap!important;
	vertical-align:middle!important;
}
html body.woocommerce-order-received table.shop_table tbody .product-name,
html body.woocommerce-order-received table.woocommerce-table--order-details tbody .product-name{
	line-height:1.7!important;
}
html body.woocommerce-order-received table.shop_table tfoot th,
html body.woocommerce-order-received table.shop_table tfoot td,
html body.woocommerce-order-received table.woocommerce-table--order-details tfoot th,
html body.woocommerce-order-received table.woocommerce-table--order-details tfoot td{
	background:var(--ez-soft)!important;font-weight:700!important;padding:12px 16px!important;
	border-bottom:1px solid var(--ez-line)!important;
}
html body.woocommerce-order-received table.shop_table tfoot tr:last-child th,
html body.woocommerce-order-received table.shop_table tfoot tr:last-child td,
html body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr:last-child th,
html body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr:last-child td{
	background:rgba(3,31,138,.04)!important;color:var(--ez-navy)!important;
	font-weight:800!important;font-size:.95rem!important;border-bottom:0!important;
}
html body.woocommerce-order-received table.shop_table .woocommerce-Price-amount,
html body.woocommerce-order-received table.woocommerce-table--order-details .woocommerce-Price-amount{
	font-weight:700!important;color:var(--ez-navy)!important;direction:ltr!important;display:inline-block!important;
}
html body.woocommerce-order-received .responsive-table{overflow-x:auto!important;-webkit-overflow-scrolling:touch!important}

#eztyToast{
	position:fixed!important;bottom:24px!important;left:50%!important;
	transform:translateX(-50%) translateY(12px)!important;
	background:#0f172a!important;color:#fff!important;padding:12px 20px!important;border-radius:12px!important;
	font-size:.84rem!important;font-weight:600!important;z-index:999999!important;
	opacity:0!important;pointer-events:none!important;transition:opacity .25s,transform .25s!important;
}
#eztyToast.is-show{opacity:1!important;transform:translateX(-50%) translateY(0)!important}

@media print{
	body *{visibility:hidden!important}
	#ezty .ezty-sheet,#ezty .ezty-sheet *{visibility:visible!important}
	#ezty .ezty-sheet{position:absolute!important;left:0!important;top:0!important;width:100%!important;padding:24px!important;background:#fff!important}
	#ezty .ezty-hide{display:none!important}
}
</style>
<script id="ezty-js">
(function(){
	"use strict";
	function toLatin(s){
		return String(s||"")
			.replace(/۰/g,"0").replace(/۱/g,"1").replace(/۲/g,"2").replace(/۳/g,"3").replace(/۴/g,"4")
			.replace(/۵/g,"5").replace(/۶/g,"6").replace(/۷/g,"7").replace(/۸/g,"8").replace(/۹/g,"9");
	}
	function repairSvg(root){
		if(!root) return;
		var nodes = root.querySelectorAll("svg, svg *");
		for(var i=0;i<nodes.length;i++){
			var el=nodes[i], attrs=el.attributes;
			if(!attrs) continue;
			for(var a=0;a<attrs.length;a++){
				var n=attrs[a].name, v=attrs[a].value, nv=toLatin(v);
				if(nv!==v) el.setAttribute(n,nv);
				// also fix attribute names like x۱ → x1
				var nn=toLatin(n);
				if(nn!==n){ el.setAttribute(nn,nv); el.removeAttribute(n); }
			}
		}
	}
	function fixLinks(){
		var links=document.querySelectorAll("a[href*='wa.me']");
		for(var i=0;i<links.length;i++){
			var h=links[i].getAttribute("href")||"", f=toLatin(h);
			if(f!==h) links[i].setAttribute("href",f);
		}
	}
	function injectOrderDetailsIcon(){
		var title = document.querySelector(".woocommerce-order-details__title, .woocommerce-order-details > h2");
		if(!title || title.querySelector(".ez-ico-wrap")) return;
		var src = document.getElementById("eztyOrderIcon");
		if(!src) return;
		var wrap = document.createElement("span");
		wrap.className = "ez-ico-wrap";
		wrap.innerHTML = src.innerHTML;
		title.insertBefore(wrap, title.firstChild);
		repairSvg(title);
	}
	function run(){
		repairSvg(document.getElementById("ezty"));
		fixLinks();
		injectOrderDetailsIcon();
	}
	if(document.readyState==="loading") document.addEventListener("DOMContentLoaded", run);
	else run();
	setTimeout(run,50); setTimeout(run,300); setTimeout(run,1000);

	window.eztyCopyOrder=function(){
		var el=document.getElementById("eztyOrderNo");
		if(!el) return;
		var text=toLatin((el.textContent||"").trim());
		var toast=document.getElementById("eztyToast");
		function show(){
			if(!toast) return;
			toast.textContent="شماره سفارش کپی شد";
			toast.className="is-show";
			setTimeout(function(){toast.className="";},1800);
		}
		function fb(){
			var ta=document.createElement("textarea");
			ta.value=text;ta.style.cssText="position:fixed;opacity:0;left:-9999px";
			document.body.appendChild(ta);ta.select();
			try{document.execCommand("copy");show();}catch(e){}
			document.body.removeChild(ta);
		}
		if(navigator.clipboard&&navigator.clipboard.writeText) navigator.clipboard.writeText(text).then(show).catch(fb);
		else fb();
	};
})();
</script>
	<?php
}

/* ---- Render ---- */
add_action( 'woocommerce_before_thankyou', 'ezty_render_hero', 5 );
function ezty_render_hero( $order_id ) {
	if ( ! $order_id ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$num     = $order->get_order_number();
	$email   = $order->get_billing_email();
	$phone   = $order->get_billing_phone();
	$total   = $order->get_formatted_order_total();
	$method  = $order->get_payment_method_title();
	$status  = wc_get_order_status_name( $order->get_status() );
	$name    = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$shop    = wc_get_page_permalink( 'shop' );
	$account = wc_get_page_permalink( 'myaccount' );
	$date    = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y/m/d H:i' ) : '';
	$ship    = $order->get_shipping_method();
	$failed  = $order->has_status( 'failed' );
	$wa      = 'https://wa.me/98919842069?text=' . rawurlencode( 'سلام، درباره سفارش #' . $num . ' سوال داشتم.' );
	$first   = $order->get_billing_first_name() ?: ( $name ?: 'شما' );

	// Address fields
	$addr_name = $name;
	$addr_lines = array_filter( array(
		$order->get_billing_address_1(),
		$order->get_billing_address_2(),
		trim( $order->get_billing_city() . ( $order->get_billing_state() ? '، ' . $order->get_billing_state() : '' ) ),
		$order->get_billing_postcode(),
	) );
	$addr_text = implode( "\n", $addr_lines );

	$ico = function ( $name ) {
		return ezty_icon( $name );
	};
	?>
	<div id="ezty">
		<div class="ezty-sheet">

			<div class="ezty-progress ezty-hide ezty-anim" aria-label="روند خرید">
				<span class="ezty-progress-item is-done">
					<span class="ezty-badge"><?php echo $ico( 'cart' ); ?></span>
					<span class="ezty-plabel">سبد خرید</span>
				</span>
				<span class="ezty-progress-sep is-done"></span>
				<span class="ezty-progress-item is-done">
					<span class="ezty-badge"><?php echo $ico( 'map-pin' ); ?></span>
					<span class="ezty-plabel">اطلاعات</span>
				</span>
				<span class="ezty-progress-sep is-done"></span>
				<span class="ezty-progress-item is-done">
					<span class="ezty-badge"><?php echo $ico( 'wallet' ); ?></span>
					<span class="ezty-plabel">پرداخت</span>
				</span>
				<span class="ezty-progress-sep is-done is-to-active"></span>
				<span class="ezty-progress-item is-active">
					<span class="ezty-badge"><?php echo $ico( 'award' ); ?></span>
					<span class="ezty-plabel">تکمیل</span>
				</span>
			</div>

			<div class="ezty-actions ezty-hide ezty-anim ezty-anim-d1">
				<a href="<?php echo esc_url( $shop ); ?>">
					<span class="ez-ico-wrap"><?php echo $ico( 'cart' ); ?></span>
					<span>ادامه خرید</span>
				</a>
				<button type="button" onclick="window.print()">
					<span class="ez-ico-wrap"><?php echo $ico( 'print' ); ?></span>
					<span>چاپ رسید</span>
				</button>
				<a href="<?php echo esc_url( $account ); ?>">
					<span class="ez-ico-wrap"><?php echo $ico( 'user' ); ?></span>
					<span>پیگیری سفارش</span>
				</a>
				<a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="ez-ico-wrap"><?php echo $ico( 'headset' ); ?></span>
					<span>پشتیبانی</span>
				</a>
			</div>

			<section class="ezty-hero ezty-anim ezty-anim-d2">
				<div class="ezty-hero-top">
					<div class="ezty-mark<?php echo $failed ? ' is-fail' : ' is-user'; ?>">
						<?php echo $failed ? $ico( 'x-circle' ) : $ico( 'user' ); ?>
					</div>
					<?php if ( $failed ) : ?>
						<div class="ezty-title">پرداخت هنوز کامل نشده</div>
						<p class="ezty-lead">نگران نباشید؛ سفارش ذخیره شده و هر زمان آماده بودید می‌توانید پرداخت را ادامه دهید.</p>
					<?php else : ?>
						<div class="ezty-title"><?php echo esc_html( $first ); ?> عزیز، سفارش شما ثبت شد</div>
						<p class="ezty-lead">از اعتمادتان سپاسگزاریم. تیم ما سفارش را بررسی می‌کند و از این لحظه همه‌چیز تحت کنترل است.</p>
					<?php endif; ?>
				</div>
				<div class="ezty-hero-body">
					<div class="ezty-chip">
						<span>شماره سفارش</span>
						<code id="eztyOrderNo"><?php echo esc_html( $num ); ?></code>
						<button type="button" class="ezty-copy ezty-hide" onclick="eztyCopyOrder()" aria-label="کپی" title="کپی">
							<?php echo $ico( 'copy' ); ?>
						</button>
					</div>
				</div>
			</section>

			<?php if ( ! $failed ) : ?>
			<div class="ezty-calm ezty-hide ezty-anim ezty-anim-d3">
				<span class="ezty-calm-ico"><?php echo $ico( 'shield' ); ?></span>
				<div>
					<b>انتخاب آگاهانه‌ای داشتید</b>
					محصولات ایزی‌لنز با کنترل کیفیت و پشتیبانی تخصصی همراه شماست. اگر سوالی پیش آمد، کافی‌ست شماره سفارش را ارسال کنید.
				</div>
			</div>
			<?php endif; ?>

			<div class="ezty-grid ezty-anim ezty-anim-d3">
				<div class="ezty-card">
					<div class="ezty-card-title">
						<span class="ez-ico-wrap"><?php echo $ico( 'receipt' ); ?></span>
						جزئیات سفارش
					</div>
					<div class="ezty-meta">
						<div><p class="ezty-k">شماره</p><p class="ezty-v is-ltr"><?php echo esc_html( $num ); ?></p></div>
						<div><p class="ezty-k">مبلغ</p><p class="ezty-v is-price"><?php echo wp_kses_post( $total ); ?></p></div>
						<div><p class="ezty-k">روش پرداخت</p><p class="ezty-v"><?php echo esc_html( $method ?: '—' ); ?></p></div>
						<div><p class="ezty-k">وضعیت</p><p class="ezty-v is-ok"><?php echo esc_html( $status ); ?></p></div>
						<?php if ( $date ) : ?><div><p class="ezty-k">زمان ثبت</p><p class="ezty-v is-ltr"><?php echo esc_html( ezty_fa( $date ) ); ?></p></div><?php endif; ?>
						<?php if ( $ship ) : ?><div><p class="ezty-k">روش ارسال</p><p class="ezty-v"><?php echo esc_html( $ship ); ?></p></div><?php endif; ?>
						<?php if ( $phone ) : ?><div><p class="ezty-k">موبایل</p><p class="ezty-v is-ltr"><?php echo esc_html( ezty_fa( $phone ) ); ?></p></div><?php endif; ?>
						<?php if ( $email ) : ?><div><p class="ezty-k">ایمیل</p><p class="ezty-v is-ltr"><?php echo esc_html( $email ); ?></p></div><?php endif; ?>
					</div>
				</div>

				<div class="ezty-card">
					<div class="ezty-card-title">
						<span class="ez-ico-wrap"><?php echo $ico( 'activity' ); ?></span>
						مسیر پیشِ‌رو
					</div>
					<ol class="ezty-timeline">
						<li>
							<span class="ezty-dot"><?php echo $ico( 'clipboard-check' ); ?></span>
							<strong>بررسی تخصصی</strong>
							سفارش و مشخصات توسط تیم ما بازبینی می‌شود.
						</li>
						<li>
							<span class="ezty-dot"><?php echo $ico( 'package' ); ?></span>
							<strong>آماده‌سازی</strong>
							بسته‌بندی دقیق و آماده‌سازی برای ارسال.
						</li>
						<li>
							<span class="ezty-dot"><?php echo $ico( 'send' ); ?></span>
							<strong>ارسال و اطلاع‌رسانی</strong>
							وضعیت را از حساب کاربری و پیامک دنبال کنید.
						</li>
						<li>
							<span class="ezty-dot"><?php echo $ico( 'message-circle' ); ?></span>
							<strong>همراهی بعد از خرید</strong>
							هر سوالی بود، با شماره سفارش در واتساپ پیام دهید.
						</li>
					</ol>
				</div>
			</div>

			<?php if ( $addr_name || $addr_text || $phone || $email ) : ?>
			<div class="ezty-address ezty-anim ezty-anim-d4">
				<div class="ezty-address-title">
					<span class="ez-ico-wrap"><?php echo $ico( 'map-pin' ); ?></span>
					آدرس صورتحساب
				</div>
				<div class="ezty-address-grid">
					<div class="ezty-address-block">
						<?php if ( $addr_name ) : ?>
						<div class="ezty-address-row">
							<span class="ez-ico-wrap"><?php echo $ico( 'user' ); ?></span>
							<div>
								<p class="ezty-al">نام گیرنده</p>
								<p class="ezty-av"><?php echo esc_html( $addr_name ); ?></p>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $addr_text ) : ?>
						<div class="ezty-address-row">
							<span class="ez-ico-wrap"><?php echo $ico( 'map-pin' ); ?></span>
							<div>
								<p class="ezty-al">آدرس</p>
								<p class="ezty-av"><?php echo nl2br( esc_html( $addr_text ) ); ?></p>
							</div>
						</div>
						<?php endif; ?>
					</div>
					<div class="ezty-address-block">
						<?php if ( $phone ) : ?>
						<div class="ezty-address-row">
							<span class="ez-ico-wrap"><?php echo $ico( 'phone' ); ?></span>
							<div>
								<p class="ezty-al">موبایل</p>
								<p class="ezty-av is-ltr"><?php echo esc_html( ezty_fa( $phone ) ); ?></p>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $email ) : ?>
						<div class="ezty-address-row">
							<span class="ez-ico-wrap"><?php echo $ico( 'mail' ); ?></span>
							<div>
								<p class="ezty-al">ایمیل</p>
								<p class="ezty-av is-ltr"><?php echo esc_html( $email ); ?></p>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<div class="ezty-support ezty-hide ezty-anim ezty-anim-d4">
			<div class="ezty-support-text">
				<p class="ezty-st-title">نیاز به راهنمایی دارید؟</p>
				<p class="ezty-st-desc">پاسخ‌گویی سریع؛ فقط شماره سفارش را بفرستید تا سریع‌تر کمکتان کنیم.</p>
			</div>
			<a class="ezty-support-btn" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo $ico( 'whatsapp' ); ?>
				گفتگو در واتساپ
			</a>
		</div>

		<div class="ezty-trust ezty-hide ezty-anim ezty-anim-d5">
			<div>
				<div class="ez-ico-wrap"><?php echo $ico( 'shield' ); ?></div>
				<p class="ezty-tt">اصالت و کیفیت</p>
				<p class="ezty-ts">محصولات کنترل‌شده و قابل اطمینان</p>
			</div>
			<div>
				<div class="ez-ico-wrap"><?php echo $ico( 'eye' ); ?></div>
				<p class="ezty-tt">پیگیری شفاف</p>
				<p class="ezty-ts">از ثبت سفارش تا لحظه تحویل</p>
			</div>
			<div>
				<div class="ez-ico-wrap"><?php echo $ico( 'headset' ); ?></div>
				<p class="ezty-tt">پشتیبانی واقعی</p>
				<p class="ezty-ts">همراه شما بعد از خرید</p>
			</div>
		</div>
	</div>
	<span id="eztyOrderIcon" hidden aria-hidden="true"><?php echo $ico( 'package' ); ?></span>
	<div id="eztyToast" role="status" aria-live="polite"></div>
	<?php
}

add_filter( 'woocommerce_thankyou_order_received_text', function () {
	return '';
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	if ( ! function_exists( 'wc_get_order' ) ) {
		return;
	}
	add_meta_box( 'ezty_notif_box', 'اطلاع‌رسانی EzLens', 'ezty_admin_metabox', 'shop_order', 'side', 'default' );
	if ( class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) ) {
		add_meta_box( 'ezty_notif_box', 'اطلاع‌رسانی EzLens', 'ezty_admin_metabox', 'woocommerce_page_wc-orders', 'side', 'default' );
	}
} );

function ezty_admin_metabox( $post_or_order ) {
	$order_id = 0;
	if ( $post_or_order instanceof WP_Post ) {
		$order_id = $post_or_order->ID;
	} elseif ( is_object( $post_or_order ) && method_exists( $post_or_order, 'get_id' ) ) {
		$order_id = $post_or_order->get_id();
	}
	if ( ! $order_id ) {
		echo '<p>—</p>';
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$notified = $order->get_meta( '_ezty_notified' );
	$at       = $order->get_meta( '_ezty_notified_at' );
	echo '<div style="font-size:13px;line-height:1.8">';
	if ( $notified === 'yes' ) {
		echo '<p style="margin:0;color:#059669;font-weight:700">✓ ارسال شد</p>';
		if ( $at ) {
			echo '<p style="margin:4px 0 0;color:#64748b;font-size:12px">' . esc_html( ezty_fa( $at ) ) . '</p>';
		}
	} else {
		$url = admin_url( 'admin-post.php?action=ezty_manual_notify&order_id=' . $order_id . '&_wpnonce=' . wp_create_nonce( 'ezty_manual_notify_' . $order_id ) );
		echo '<a class="button button-primary" href="' . esc_url( $url ) . '" onclick="return confirm(\'ارسال؟\')">ارسال دستی</a>';
	}
	echo '</div>';
}

add_action( 'admin_post_ezty_manual_notify', function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'x' );
	}
	$order_id = absint( $_GET['order_id'] ?? 0 );
	if ( ! $order_id || ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ?? '' ), 'ezty_manual_notify_' . $order_id ) ) {
		wp_die( 'x' );
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_die( 'x' );
	}
	$order->delete_meta_data( '_ezty_notified' );
	$order->save();
	ezty_do_notify( $order );
	wp_safe_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
	exit;
} );