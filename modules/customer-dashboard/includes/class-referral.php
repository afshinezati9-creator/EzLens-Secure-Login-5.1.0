<?php
/**
 * Referral / invite friends
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Referral {

	const CODE_META   = '_ezcd_referral_code';
	const BY_META     = '_ezcd_referred_by';
	const COUNT_META  = '_ezcd_referral_count';
	const LIST_META   = '_ezcd_referral_list';

	public static function code( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$code    = get_user_meta( $user_id, self::CODE_META, true );
		if ( $code ) {
			return strtoupper( $code );
		}
		$code = 'EZ' . strtoupper( substr( md5( 'ezcd' . $user_id . wp_salt() ), 0, 8 ) );
		update_user_meta( $user_id, self::CODE_META, $code );
		return $code;
	}

	public static function share_url( $user_id = 0 ) {
		$code = self::code( $user_id );
		$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
		// landing shop with ref
		$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		return add_query_arg( 'ref', $code, $shop );
	}

	public static function count( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		return (int) get_user_meta( $user_id, self::COUNT_META, true );
	}

	/**
	 * Capture ?ref= on front for logged-out visitors via cookie; apply on register.
	 */
	public static function bootstrap() {
		add_action( 'init', array( __CLASS__, 'capture_ref' ), 5 );
		add_action( 'user_register', array( __CLASS__, 'on_register' ), 20 );
	}

	public static function capture_ref() {
		if ( empty( $_GET['ref'] ) ) {
			return;
		}
		$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['ref'] ) ) );
		if ( strlen( $code ) < 4 ) {
			return;
		}
		if ( ! headers_sent() ) {
			setcookie( 'ezcd_ref', $code, time() + WEEK_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		}
		$_COOKIE['ezcd_ref'] = $code;
	}

	public static function on_register( $user_id ) {
		$code = '';
		if ( ! empty( $_COOKIE['ezcd_ref'] ) ) {
			$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_COOKIE['ezcd_ref'] ) ) );
		}
		if ( ! $code ) {
			return;
		}
		$owner = self::user_id_by_code( $code );
		if ( ! $owner || (int) $owner === (int) $user_id ) {
			return;
		}
		update_user_meta( $user_id, self::BY_META, $owner );
		$count = (int) get_user_meta( $owner, self::COUNT_META, true );
		update_user_meta( $owner, self::COUNT_META, $count + 1 );
		$list = get_user_meta( $owner, self::LIST_META, true );
		if ( ! is_array( $list ) ) {
			$list = array();
		}
		$list[] = array(
			'user_id' => (int) $user_id,
			'at'      => current_time( 'mysql' ),
		);
		update_user_meta( $owner, self::LIST_META, array_slice( $list, -50 ) );

		// Optional small reward to referrer (wallet)
		if ( class_exists( 'EzLens_CD_Wallet' ) ) {
			$reward = (int) apply_filters( 'ezcd_referral_reward_amount', 0 );
			if ( $reward > 0 ) {
				EzLens_CD_Wallet::add_entry( $owner, $reward, 'credit', 'referral', 'پاداش دعوت دوست', 'user', $user_id );
			}
		}
	}

	public static function user_id_by_code( $code ) {
		$users = get_users(
			array(
				'meta_key'   => self::CODE_META,
				'meta_value' => $code,
				'number'     => 1,
				'fields'     => 'ID',
			)
		);
		return ! empty( $users[0] ) ? (int) $users[0] : 0;
	}
}
