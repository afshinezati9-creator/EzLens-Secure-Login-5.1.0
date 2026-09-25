<?php
/**
 * Account profile edit
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Account {

	public static function get_data( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return array();
		}
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'digits_phone', true );
		}
		return array(
			'first_name'   => $user->first_name,
			'last_name'    => $user->last_name,
			'display_name' => $user->display_name,
			'email'        => $user->user_email,
			'phone'        => $phone,
			'national_id'  => get_user_meta( $user_id, 'ezcd_national_id', true ),
		);
	}

	/**
	 * @param array $data
	 * @return true|WP_Error
	 */
	public static function save( $data, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'وارد شوید' );
		}
		$first = isset( $data['first_name'] ) ? sanitize_text_field( wp_unslash( $data['first_name'] ) ) : '';
		$last  = isset( $data['last_name'] ) ? sanitize_text_field( wp_unslash( $data['last_name'] ) ) : '';
		$disp  = isset( $data['display_name'] ) ? sanitize_text_field( wp_unslash( $data['display_name'] ) ) : '';
		$email = isset( $data['email'] ) ? sanitize_email( wp_unslash( $data['email'] ) ) : '';
		$phone = isset( $data['phone'] ) ? sanitize_text_field( wp_unslash( $data['phone'] ) ) : '';
		$nid   = isset( $data['national_id'] ) ? preg_replace( '/\D+/', '', (string) wp_unslash( $data['national_id'] ) ) : '';
		// Persian digits in national id
		$nid = str_replace(
			array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
			$nid
		);

		if ( $email && ! is_email( $email ) ) {
			return new WP_Error( 'email', 'ایمیل معتبر نیست' );
		}
		if ( $email ) {
			$exists = email_exists( $email );
			if ( $exists && (int) $exists !== $user_id ) {
				return new WP_Error( 'email', 'این ایمیل قبلاً ثبت شده' );
			}
		}
		if ( $nid !== '' && strlen( $nid ) !== 10 ) {
			return new WP_Error( 'nid', 'کد ملی باید ۱۰ رقم باشد' );
		}

		$update = array( 'ID' => $user_id );
		if ( $first !== '' ) {
			$update['first_name'] = $first;
		}
		if ( $last !== '' ) {
			$update['last_name'] = $last;
		}
		if ( $disp !== '' ) {
			$update['display_name'] = $disp;
		}
		if ( $email ) {
			$update['user_email'] = $email;
		}
		$r = wp_update_user( $update );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		// Phone is identity — do not allow change from dashboard.
		unset( $phone );
		if ( $first !== '' ) {
			update_user_meta( $user_id, 'billing_first_name', $first );
		}
		if ( $last !== '' ) {
			update_user_meta( $user_id, 'billing_last_name', $last );
		}
		if ( $nid !== '' ) {
			update_user_meta( $user_id, 'ezcd_national_id', $nid );
		} else {
			delete_user_meta( $user_id, 'ezcd_national_id' );
		}
		return true;
	}
}
