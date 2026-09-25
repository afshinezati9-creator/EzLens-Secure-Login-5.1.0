<?php
/**
 * Wallet top-up deposit requests (gateway / card / bank)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Wallet_Deposits {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_wallet_deposits';
	}

	public static function maybe_create_table() {
		global $wpdb;
		$t = self::table();
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			"CREATE TABLE {$t} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				amount decimal(18,0) NOT NULL DEFAULT 0,
				method varchar(32) NOT NULL DEFAULT 'card',
				ref_code varchar(100) NOT NULL DEFAULT '',
				receipt_id bigint(20) unsigned NOT NULL DEFAULT 0,
				status varchar(16) NOT NULL DEFAULT 'pending',
				admin_note text NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY user_id (user_id),
				KEY status (status)
			) {$charset};"
		);
	}

	public static function methods() {
		$m = array(
			'card'   => 'کارت به کارت / فیش',
			'bank'   => 'اینترنت‌بانک',
			'online' => 'درگاه پرداخت',
		);
		if ( class_exists( 'EzLens_Auth_Settings' ) ) {
			$flags = array(
				'online' => 'wallet_payment_online_enabled',
				'card'   => 'wallet_payment_card_enabled',
				'bank'   => 'wallet_payment_bank_enabled',
			);
			foreach ( $flags as $method => $key ) {
				if ( '1' !== (string) EzLens_Auth_Settings::get( $key ) ) {
					unset( $m[ $method ] );
				}
			}
		}
		return apply_filters( 'ezcd_wallet_deposit_methods', $m );
	}

	public static function status_label( $s ) {
		$map = array(
			'pending'  => 'در انتظار بررسی',
			'approved' => 'تأیید و شارژ شد',
			'rejected' => 'رد شده',
		);
		return isset( $map[ $s ] ) ? $map[ $s ] : $s;
	}

	public static function for_user( $user_id = 0, $limit = 20 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$rows    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' WHERE user_id = %d ORDER BY id DESC LIMIT %d',
				$user_id,
				$limit
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function pending( $limit = 50 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' WHERE status = %s ORDER BY id DESC LIMIT %d',
				'pending',
				$limit
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return int|WP_Error
	 */
	public static function create( $data, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$amount  = isset( $data['amount'] ) ? absint( preg_replace( '/\D+/', '', (string) $data['amount'] ) ) : 0;
		// support Persian digits
		if ( ! $amount && isset( $data['amount'] ) ) {
			$fa = array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' );
			$en = array( '0','1','2','3','4','5','6','7','8','9' );
			$amount = absint( preg_replace( '/\D+/', '', str_replace( $fa, $en, (string) $data['amount'] ) ) );
		}
		if ( $amount < 10000 ) {
			return new WP_Error( 'amount', 'حداقل مبلغ شارژ ۱۰٬۰۰۰ تومان است' );
		}
		$method = isset( $data['method'] ) ? sanitize_key( $data['method'] ) : 'card';
		if ( ! array_key_exists( $method, self::methods() ) ) {
			$method = 'card';
		}
		$ref = isset( $data['ref_code'] ) ? sanitize_text_field( $data['ref_code'] ) : '';
		$receipt_id = isset( $data['receipt_id'] ) ? absint( $data['receipt_id'] ) : 0;
		if ( 'card' === $method ) {
			if ( $ref === '' ) {
				return new WP_Error( 'ref', 'شماره رسید / کد پیگیری الزامی است' );
			}
			if ( $receipt_id < 1 ) {
				return new WP_Error( 'receipt', 'آپلود فیش واریز الزامی است' );
			}
		}
		if ( 'bank' === $method ) {
			if ( $ref === '' && $receipt_id < 1 ) {
				return new WP_Error( 'ref', 'شناسه پرداخت یا اسکرین پرداخت را ارسال کنید' );
			}
		}
		$now = current_time( 'mysql' );
		$ok  = $wpdb->insert(
			self::table(),
			array(
				'user_id'    => $user_id,
				'amount'     => $amount,
				'method'     => $method,
				'ref_code'   => $ref,
				'receipt_id' => $receipt_id,
				'status'     => 'pending',
				'created_at' => $now,
				'updated_at' => $now,
			)
		);
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ثبت درخواست ممکن نشد' );
		}
		$id = (int) $wpdb->insert_id;
		// Admin notice transient
		$pending_flag = (int) get_option( 'ezlens_cd_wallet_pending_count', 0 );
		update_option( 'ezlens_cd_wallet_pending_count', $pending_flag + 1, false );
		do_action( 'ezcd_wallet_deposit_created', $id, $user_id, $amount );
		return $id;
	}

	/**
	 * @return true|WP_Error
	 */
	public static function approve( $id, $admin_note = '' ) {
		global $wpdb;
		$id  = absint( $id );
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
		if ( ! $row || 'pending' !== $row->status ) {
			return new WP_Error( 'invalid', 'درخواست معتبر نیست' );
		}
		$r = EzLens_CD_Wallet::add_entry( (int) $row->user_id, (int) $row->amount, 'credit', 'admin', 'شارژ کیف پول #' . $id );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$wpdb->update(
			self::table(),
			array(
				'status'     => 'approved',
				'admin_note' => sanitize_textarea_field( $admin_note ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id )
		);
		self::recount_pending();
		return true;
	}

	public static function reject( $id, $admin_note = '' ) {
		global $wpdb;
		$id = absint( $id );
		$wpdb->update(
			self::table(),
			array(
				'status'     => 'rejected',
				'admin_note' => sanitize_textarea_field( $admin_note ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id )
		);
		self::recount_pending();
		return true;
	}

	public static function recount_pending() {
		global $wpdb;
		$n = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE status = %s', 'pending' ) );
		update_option( 'ezlens_cd_wallet_pending_count', $n, false );
	}
}
