<?php
/**
 * شورت‌کد پنل کاربری — فاز ۱: منسوخ در برابر Customer Dashboard
 * @version 3.1.0-phase1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Shortcodes_Panel {

	/**
	 * [modern_user_panel] — با CD فعال به my-account هدایت می‌شود.
	 */
	public static function render_user_panel() {
		// فاز ۱: داشبورد مشتری جایگزین پنل قدیمی
		if ( function_exists( 'ezlens_cd_is_active' ) && ezlens_cd_is_active() ) {
			$target = function_exists( 'wc_get_page_permalink' )
				? wc_get_page_permalink( 'myaccount' )
				: home_url( '/my-account/' );
			if ( ! is_user_logged_in() ) {
				$target = wp_login_url( $target );
			}
			if ( ! headers_sent() ) {
				wp_safe_redirect( $target );
				exit;
			}
			return '<div class="ezlens-legacy-panel-redirect" style="text-align:center;padding:48px 16px;font-family:Tahoma,sans-serif;">'
				. '<p style="margin:0 0 12px;color:#475569;">پنل قدیمی به حساب کاربری جدید منتقل شده است.</p>'
				. '<a class="button" href="' . esc_url( $target ) . '" style="display:inline-block;padding:10px 20px;background:#031f8a;color:#fff;border-radius:8px;text-decoration:none;">ورود به حساب کاربری</a>'
				. '</div>';
		}

		if ( class_exists( 'EzLens_Auth_Settings' ) && ! EzLens_Auth_Settings::is_page_enabled( 'user-panel' ) ) {
			return self::get_disabled_message( 'پنل کاربری', 'پنل کاربری غیرفعال است.' );
		}

		if ( ! is_user_logged_in() ) {
			$login_url = function_exists( 'wc_get_page_permalink' )
				? wc_get_page_permalink( 'myaccount' )
				: wp_login_url( get_permalink() );
			return '<div class="ezu-login-required" style="text-align:center;padding:60px 20px;font-family:IRANYekan,Tahoma,sans-serif;">'
				. '<h2 style="font-size:22px;font-weight:700;color:#0f172a;margin:0 0 8px;">برای مشاهده پنل وارد شوید</h2>'
				. '<p style="color:#64748b;margin:0 0 20px;">لطفاً وارد حساب کاربری خود شوید.</p>'
				. '<a href="' . esc_url( $login_url ) . '" style="display:inline-block;padding:12px 32px;background:#2b6cb0;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">ورود به حساب کاربری</a>'
				. '</div>';
		}

		ob_start();
		if ( defined( 'EZLAUTH_FRONTEND_DIR' ) && is_readable( EZLAUTH_FRONTEND_DIR . 'pages/user-panel.php' ) ) {
			include EZLAUTH_FRONTEND_DIR . 'pages/user-panel.php';
		} else {
			echo '<p>قالب پنل یافت نشد. ماژول داشبورد مشتری را فعال کنید.</p>';
		}
		return ob_get_clean();
	}

	private static function get_disabled_message( $title, $msg ) {
		return '<div class="ezlens-disabled-page" style="text-align:center;padding:40px;"><p>' . esc_html( $msg ) . '</p></div>';
	}

	public static function ajax_save_profile() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'لطفاً وارد شوید.' ) );
		}
		$user_id = get_current_user_id();
		$fn      = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
		$ln      = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
		$phone   = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ?? '' ) );
		if ( $fn === '' || $ln === '' ) {
			wp_send_json_error( array( 'message' => 'نام و نام خانوادگی الزامی است.' ) );
		}
		if ( $phone && ! preg_match( '/^09\d{9}$/', $phone ) ) {
			wp_send_json_error( array( 'message' => 'موبایل نامعتبر است.' ) );
		}
		wp_update_user(
			array(
				'ID'           => $user_id,
				'first_name'   => $fn,
				'last_name'    => $ln,
				'display_name' => trim( $fn . ' ' . $ln ),
			)
		);
		update_user_meta( $user_id, 'billing_first_name', $fn );
		update_user_meta( $user_id, 'billing_last_name', $ln );
		if ( $phone ) {
			update_user_meta( $user_id, 'billing_phone', $phone );
		}
		wp_send_json_success( array( 'message' => 'پروفایل ذخیره شد.' ) );
	}

	public static function ajax_save_address() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'لطفاً وارد شوید.' ) );
		}
		$user_id = get_current_user_id();
		$keys    = array( 'billing_first_name', 'billing_last_name', 'billing_state', 'billing_city', 'billing_address_1', 'billing_address_2', 'billing_postcode', 'billing_phone' );
		foreach ( $keys as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				update_user_meta( $user_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		wp_send_json_success( array( 'message' => 'آدرس ذخیره شد.' ) );
	}
}
