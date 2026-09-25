<?php
/**
 * Virtual gift cards
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Gift_Cards {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_gift_cards';
	}

	public static function maybe_create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(32) NOT NULL,
			amount decimal(18,0) NOT NULL DEFAULT 0,
			status varchar(16) NOT NULL DEFAULT 'active',
			purchased_by bigint(20) unsigned NOT NULL DEFAULT 0,
			redeemed_by bigint(20) unsigned NOT NULL DEFAULT 0,
			recipient_name varchar(191) NOT NULL DEFAULT '',
			message text NULL,
			created_at datetime NOT NULL,
			redeemed_at datetime NULL,
			PRIMARY KEY (id),
			UNIQUE KEY code (code),
			KEY purchased_by (purchased_by),
			KEY redeemed_by (redeemed_by),
			KEY status (status)
		) {$charset};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function generate_code() {
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code  = 'EZ';
		for ( $i = 0; $i < 10; $i++ ) {
			$code .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		return $code;
	}

	/**
	 * Purchase gift card from wallet balance.
	 *
	 * @return array|WP_Error {id, code}
	 */
	public static function purchase( $amount, $recipient_name = '', $message = '', $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$amount  = absint( $amount );
		if ( $amount < 10000 ) {
			return new WP_Error( 'min', 'حداقل مبلغ کارت هدیه ۱۰٬۰۰۰ تومان است' );
		}
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'وارد شوید' );
		}
		$debit = EzLens_CD_Wallet::add_entry( $user_id, $amount, 'debit', 'gift_purchase', 'خرید کارت هدیه' );
		if ( is_wp_error( $debit ) ) {
			return $debit;
		}
		$code = self::generate_code();
		// ensure unique
		for ( $i = 0; $i < 5; $i++ ) {
			$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::table() . ' WHERE code = %s', $code ) );
			if ( ! $exists ) {
				break;
			}
			$code = self::generate_code();
		}
		$ok = $wpdb->insert(
			self::table(),
			array(
				'code'           => $code,
				'amount'         => $amount,
				'status'         => 'active',
				'purchased_by'   => $user_id,
				'recipient_name' => sanitize_text_field( $recipient_name ),
				'message'        => sanitize_textarea_field( $message ),
				'created_at'     => current_time( 'mysql' ),
			)
		);
		if ( ! $ok ) {
			// refund wallet
			EzLens_CD_Wallet::add_entry( $user_id, $amount, 'credit', 'other', 'برگشت خرید کارت هدیه ناموفق' );
			return new WP_Error( 'db', 'صدور کارت ممکن نشد' );
		}
		return array(
			'id'   => (int) $wpdb->insert_id,
			'code' => $code,
		);
	}

	/**
	 * Redeem code into wallet of current user.
	 *
	 * @return true|WP_Error
	 */
	public static function redeem( $code, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$code    = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $code ) );
		if ( ! $user_id || ! $code ) {
			return new WP_Error( 'invalid', 'کد نامعتبر است' );
		}
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE code = %s', $code ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'کد کارت هدیه یافت نشد' );
		}
		if ( 'active' !== $row->status ) {
			return new WP_Error( 'used', 'این کد قبلاً استفاده شده است' );
		}
		if ( (int) $row->purchased_by === $user_id ) {
			return new WP_Error( 'self', 'کارت خریداری‌شده توسط خودتان قابل استفاده در همین حساب نیست' );
		}
		$wpdb->update(
			self::table(),
			array(
				'status'      => 'redeemed',
				'redeemed_by' => $user_id,
				'redeemed_at' => current_time( 'mysql' ),
			),
			array( 'id' => (int) $row->id )
		);
		$r = EzLens_CD_Wallet::add_entry(
			$user_id,
			(int) $row->amount,
			'credit',
			'gift_redeem',
			'شارژ از کارت هدیه ' . $code,
			'gift_card',
			(int) $row->id
		);
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		return true;
	}

	public static function purchased_by( $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$rows    = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::table() . ' WHERE purchased_by = %d ORDER BY id DESC LIMIT 50',
			$user_id
		) );
		return is_array( $rows ) ? $rows : array();
	}

	public static function redeemed_by( $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$rows    = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::table() . ' WHERE redeemed_by = %d ORDER BY id DESC LIMIT 50',
			$user_id
		) );
		return is_array( $rows ) ? $rows : array();
	}
}
