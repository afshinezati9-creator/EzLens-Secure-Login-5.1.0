<?php
/**
 * Account security (Phase 2 — basic)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Security {

	/**
	 * Last login meta if logged by plugin.
	 */
	public static function last_login( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$ts      = get_user_meta( $user_id, 'ezlens_last_login', true );
		if ( ! $ts ) {
			$ts = get_user_meta( $user_id, 'last_login', true );
		}
		return $ts ? $ts : '';
	}

	/**
	 * Change password.
	 *
	 * @return true|WP_Error
	 */
	public static function change_password( $current, $new, $confirm, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'auth', 'کاربر یافت نشد' );
		}
		if ( ! wp_check_password( $current, $user->user_pass, $user_id ) ) {
			return new WP_Error( 'current', 'رمز فعلی نادرست است' );
		}
		if ( strlen( $new ) < 8 ) {
			return new WP_Error( 'short', 'رمز جدید حداقل ۸ کاراکتر باشد' );
		}
		if ( $new !== $confirm ) {
			return new WP_Error( 'match', 'تکرار رمز مطابقت ندارد' );
		}
		wp_set_password( $new, $user_id );
		// re-login current session
		wp_set_auth_cookie( $user_id, true );
		return true;
	}

	public static function sessions_note() {
		return 'برای خروج از همه دستگاه‌ها، یک‌بار رمز را تغییر دهید یا از دکمه خروج استفاده کنید.';
	}
}
