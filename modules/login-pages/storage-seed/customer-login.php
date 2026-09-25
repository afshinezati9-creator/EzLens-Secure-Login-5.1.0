<?php
/**
 * ورود مشتری — [minimal_auth]
 * منطق = frontend/pages/login.php | ظاهر = تم داشبورد مشتری
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$tpl = dirname( __DIR__ ) . '/templates/customer-login.php';
if ( is_readable( $tpl ) ) {
	include $tpl;
	return;
}
// fallback مستقیم
$core = defined( 'EZLAUTH_FRONTEND_DIR' ) ? EZLAUTH_FRONTEND_DIR . 'pages/login.php' : '';
if ( $core && is_readable( $core ) ) {
	include $core;
}
