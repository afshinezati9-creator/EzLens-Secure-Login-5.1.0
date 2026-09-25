<?php
/**
 * فعال / غیرفعال بودن هر صفحه ورود
 * با Settings::is_page_enabled همگام می‌ماند
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Login_Pages_Status {

	const OPTION = 'ezlens_login_pages_enabled';

	public static function all() {
		$defaults = array(
			'customer-login' => 1,
			'admin-login'    => 1,
			'lost-password'  => 1,
		);
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( $defaults, $saved );
	}

	public static function is_enabled( $slug ) {
		// اولویت با Settings پلاگین اگر متد موجود باشد
		if ( class_exists( 'EzLens_Auth_Settings' ) && method_exists( 'EzLens_Auth_Settings', 'is_page_enabled' ) ) {
			return (bool) EzLens_Auth_Settings::is_page_enabled( $slug );
		}
		$all = self::all();
		return ! empty( $all[ $slug ] );
	}

	public static function set_enabled( $slug, $enabled ) {
		$slug = sanitize_key( $slug );
		if ( ! EzLens_Login_Pages_Registry::is_valid( $slug ) ) {
			return false;
		}
		$all = self::all();
		$all[ $slug ] = $enabled ? 1 : 0;
		update_option( self::OPTION, $all, false );

		// همگام با Settings: enable_customer_login / enable_admin_login / ...
		if ( class_exists( 'EzLens_Auth_Settings' ) && method_exists( 'EzLens_Auth_Settings', 'set' ) ) {
			$key = 'enable_' . str_replace( '-', '_', $slug );
			EzLens_Auth_Settings::set( $key, $enabled ? '1' : '0' );
		}
		return true;
	}
}
