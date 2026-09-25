<?php
/**
 * رندر خروجی شورت‌کد از فایل‌های storage (منطق OTP دست‌نخورده در JS/AJAX)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Login_Pages_Renderer {

	public static function render( $slug ) {
		$slug = sanitize_key( $slug );
		if ( ! EzLens_Login_Pages_Registry::is_valid( $slug ) ) {
			return '';
		}
		if ( ! EzLens_Login_Pages_Status::is_enabled( $slug ) ) {
			return '<div class="ezlens-login-disabled" style="text-align:center;padding:40px;font-family:Tahoma,sans-serif;color:#64748b;">این صفحه ورود فعلاً غیرفعال است.</div>';
		}

		$html = EzLens_Login_Pages_Storage::read( $slug, 'html' );
		$css  = EzLens_Login_Pages_Storage::read( $slug, 'css' );
		$js   = EzLens_Login_Pages_Storage::read( $slug, 'js' );

		// اگر HTML خالی بود — fallback به قالب PHP هسته (منطق کامل)
		if ( trim( $html ) === '' ) {
			return self::fallback_php( $slug );
		}

		$primary = class_exists( 'EzLens_Auth_Settings' ) ? ( EzLens_Auth_Settings::get( 'primary_color' ) ?: '#031f8a' ) : '#031f8a';
		$repl    = array(
			'{{primary_color}}' => esc_attr( $primary ),
			'{{site_name}}'     => esc_html( get_bloginfo( 'name' ) ),
			'{{ajax_url}}'      => esc_url( admin_url( 'admin-ajax.php' ) ),
			'{{home_url}}'      => esc_url( home_url( '/' ) ),
		);
		$html = strtr( $html, $repl );
		$css  = strtr( $css, $repl );
		$js   = strtr( $js, $repl );

		ob_start();
		if ( $css !== '' ) {
			echo '<style id="ezlens-lp-' . esc_attr( $slug ) . '-css">' . $css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo $html; // phpcs:ignore — قالب ادمین ذخیره‌شده
		if ( $js !== '' ) {
			echo '<script id="ezlens-lp-' . esc_attr( $slug ) . '-js">' . $js . '</script>'; // phpcs:ignore
		}
		return ob_get_clean();
	}

	private static function fallback_php( $slug ) {
		ob_start();
		if ( $slug === 'customer-login' && is_readable( EZLAUTH_FRONTEND_DIR . 'pages/login.php' ) ) {
			include EZLAUTH_FRONTEND_DIR . 'pages/login.php';
		} elseif ( $slug === 'lost-password' && is_readable( EZLAUTH_FRONTEND_DIR . 'pages/forgot-password.php' ) ) {
			include EZLAUTH_FRONTEND_DIR . 'pages/forgot-password.php';
		} elseif ( $slug === 'admin-login' && is_readable( EZLAUTH_TEMPLATES_DIR . 'admin-login.php' ) ) {
			include EZLAUTH_TEMPLATES_DIR . 'admin-login.php';
		} else {
			echo '<p>قالب یافت نشد.</p>';
		}
		return ob_get_clean();
	}
}
