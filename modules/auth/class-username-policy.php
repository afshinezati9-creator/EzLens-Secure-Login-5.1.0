<?php
/**
 * Customer username = mobile only; lock changes for non-admins.
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'EzLens_Username_Policy' ) ) {
	return;
}

final class EzLens_Username_Policy {

	public static function init() {
		add_filter( 'wp_pre_insert_user_data', array( __CLASS__, 'lock_customer_login' ), 10, 4 );
		add_action( 'personal_options_update', array( __CLASS__, 'prevent_login_post' ), 1 );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'prevent_login_post' ), 1 );
		add_filter( 'manage_users_columns', array( __CLASS__, 'users_columns' ), 20 );
	}

	public static function normalize_mobile( $mobile ) {
		$mobile = is_string( $mobile ) ? $mobile : '';
		$mobile = preg_replace( '/\D+/', '', $mobile );
		if ( strpos( $mobile, '98' ) === 0 && strlen( $mobile ) >= 12 ) {
			$mobile = '0' . substr( $mobile, 2 );
		}
		if ( strpos( $mobile, '9' ) === 0 && strlen( $mobile ) === 10 ) {
			$mobile = '0' . $mobile;
		}
		return $mobile;
	}

	public static function is_valid_mobile( $mobile ) {
		return (bool) preg_match( '/^09\d{9}$/', self::normalize_mobile( $mobile ) );
	}

	/**
	 * @return int|WP_Error
	 */
	public static function create_customer_by_phone( $phone, $password = '', $email = '' ) {
		$phone = self::normalize_mobile( $phone );
		if ( ! self::is_valid_mobile( $phone ) ) {
			return new WP_Error( 'invalid_phone', 'شماره موبایل نامعتبر است.' );
		}

		$existing = get_user_by( 'login', $phone );
		if ( $existing ) {
			return (int) $existing->ID;
		}

		if ( class_exists( 'EzLens_Auth_Helper' ) && method_exists( 'EzLens_Auth_Helper', 'get_user_by_phone' ) ) {
			$by_phone = EzLens_Auth_Helper::get_user_by_phone( $phone );
			if ( $by_phone && ! empty( $by_phone->ID ) ) {
				return (int) $by_phone->ID;
			}
		}

		if ( $password === '' ) {
			$password = wp_generate_password( 16, true, true );
		}

		if ( $email === '' || ! is_email( $email ) ) {
			$email = $phone . '@ezlens.ir';
		}
		if ( email_exists( $email ) ) {
			$email = $phone . '.' . wp_generate_password( 4, false ) . '@ezlens.ir';
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $phone,
				'user_pass'    => $password,
				'user_email'   => $email,
				'display_name' => $phone,
				'role'         => 'customer',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, 'user_phone', $phone );
		update_user_meta( $user_id, 'billing_phone', $phone );

		return (int) $user_id;
	}

	public static function lock_customer_login( $data, $update, $user_id = 0, $userdata = array() ) {
		if ( empty( $update ) || empty( $user_id ) ) {
			return $data;
		}
		if ( ! is_array( $data ) ) {
			return $data;
		}
		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return $data;
		}
		$user = get_userdata( (int) $user_id );
		if ( ! $user ) {
			return $data;
		}
		$data['user_login'] = $user->user_login;
		return $data;
	}

	public static function prevent_login_post( $user_id ) {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			return;
		}
		$user = get_userdata( (int) $user_id );
		if ( $user && isset( $_POST['user_login'] ) ) {
			$_POST['user_login'] = $user->user_login;
		}
	}

	public static function users_columns( $columns ) {
		if ( is_array( $columns ) && isset( $columns['username'] ) ) {
			$columns['username'] = 'نام کاربری (موبایل)';
		}
		return $columns;
	}
}

EzLens_Username_Policy::init();

/**
 * Repair tool: /wp-admin/?ezlens_repair_username=USER_ID
 */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_GET['ezlens_repair_username'] ) ) {
		return;
	}
	$user_id = absint( $_GET['ezlens_repair_username'] );
	$user    = get_userdata( $user_id );
	if ( ! $user ) {
		wp_die( 'User not found' );
	}
	$phone = get_user_meta( $user_id, 'billing_phone', true );
	if ( ! $phone ) {
		$phone = get_user_meta( $user_id, 'user_phone', true );
	}
	$phone = EzLens_Username_Policy::normalize_mobile( $phone );
	if ( ! EzLens_Username_Policy::is_valid_mobile( $phone ) ) {
		if ( preg_match( '/^(09\d{9})@/', $user->user_email, $m ) ) {
			$phone = $m[1];
		}
	}
	if ( ! EzLens_Username_Policy::is_valid_mobile( $phone ) ) {
		wp_die( 'No valid phone for this user' );
	}
	if ( $user->user_login === $phone ) {
		wp_die( 'Already OK: ' . esc_html( $phone ) );
	}
	if ( username_exists( $phone ) ) {
		wp_die( 'Phone login already taken by another user' );
	}
	global $wpdb;
	$wpdb->update(
		$wpdb->users,
		array(
			'user_login'    => $phone,
			'user_nicename' => sanitize_title( $phone ),
		),
		array( 'ID' => $user_id ),
		array( '%s', '%s' ),
		array( '%d' )
	);
	clean_user_cache( $user_id );
	update_user_meta( $user_id, 'user_phone', $phone );
	update_user_meta( $user_id, 'billing_phone', $phone );
	wp_die( 'Repaired. New user_login = ' . esc_html( $phone ) );
} );
