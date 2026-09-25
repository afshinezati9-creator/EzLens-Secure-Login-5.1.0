<?php
/**
 * Customer wallet + ledger
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Wallet {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_wallet_ledger';
	}

	public static function maybe_create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			amount decimal(18,0) NOT NULL DEFAULT 0,
			entry_type varchar(16) NOT NULL DEFAULT 'credit',
			reason varchar(64) NOT NULL DEFAULT '',
			note text NULL,
			ref_type varchar(32) NOT NULL DEFAULT '',
			ref_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function balance( $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return 0;
		}
		$sum = $wpdb->get_var( $wpdb->prepare(
			'SELECT COALESCE(SUM(CASE WHEN entry_type = \'credit\' THEN amount ELSE -amount END),0) FROM ' . self::table() . ' WHERE user_id = %d',
			$user_id
		) );
		return (int) $sum;
	}

	/**
	 * @return object[]
	 */
	public static function history( $user_id = 0, $limit = 30 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$rows    = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::table() . ' WHERE user_id = %d ORDER BY id DESC LIMIT %d',
			$user_id,
			absint( $limit )
		) );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return int|WP_Error entry id
	 */
	public static function add_entry( $user_id, $amount, $type, $reason, $note = '', $ref_type = '', $ref_id = 0 ) {
		global $wpdb;
		$user_id = absint( $user_id );
		$amount  = absint( $amount );
		$type    = ( 'debit' === $type ) ? 'debit' : 'credit';
		if ( ! $user_id || $amount < 1 ) {
			return new WP_Error( 'invalid', 'مبلغ نامعتبر است' );
		}
		if ( 'debit' === $type && self::balance( $user_id ) < $amount ) {
			return new WP_Error( 'balance', 'موجودی کافی نیست' );
		}
		$ok = $wpdb->insert(
			self::table(),
			array(
				'user_id'    => $user_id,
				'amount'     => $amount,
				'entry_type' => $type,
				'reason'     => sanitize_key( $reason ),
				'note'       => sanitize_textarea_field( $note ),
				'ref_type'   => sanitize_key( $ref_type ),
				'ref_id'     => absint( $ref_id ),
				'created_at' => current_time( 'mysql' ),
			)
		);
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ثبت تراکنش ممکن نشد' );
		}
		return (int) $wpdb->insert_id;
	}

	public static function reason_label( $reason ) {
		$map = array(
			'gift_redeem'   => 'دریافت کارت هدیه',
			'gift_purchase' => 'خرید کارت هدیه',
			'referral'      => 'پاداش دعوت دوست',
			'admin'         => 'تعدیل توسط فروشگاه',
			'order'         => 'سفارش',
			'refund'        => 'بازگشت وجه',
			'other'         => 'سایر',
			'charity' => 'هم‌یاری بینایی',
			'topup'   => 'شارژ آنلاین',
			'deposit' => 'واریز',
		);
		return isset( $map[ $reason ] ) ? $map[ $reason ] : $reason;
	}

	public static function format_amount( $amount ) {
		$amount = (int) $amount;
		if ( function_exists( 'wc_price' ) ) {
			return wp_strip_all_tags( wc_price( $amount ) );
		}
		return number_format_i18n( $amount ) . ' تومان';
	}
}
