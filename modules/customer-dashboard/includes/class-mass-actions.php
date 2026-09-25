<?php
/**
 * Mass / bulk customer actions + activity log
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Mass_Actions {

	public static function log_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_activity_log';
	}

	public static function maybe_create_table() {
		global $wpdb;
		$table   = self::log_table();
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				admin_id bigint(20) unsigned NOT NULL DEFAULT 0,
				action_type varchar(32) NOT NULL DEFAULT '',
				channel varchar(16) NOT NULL DEFAULT '',
				target_count int unsigned NOT NULL DEFAULT 0,
				success_count int unsigned NOT NULL DEFAULT 0,
				fail_count int unsigned NOT NULL DEFAULT 0,
				payload longtext NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY created_at (created_at)
			) {$charset};"
		);
	}

	public static function log( $action_type, $channel, $target, $success, $fail, $payload = array() ) {
		global $wpdb;
		$wpdb->insert(
			self::log_table(),
			array(
				'admin_id'      => get_current_user_id(),
				'action_type'   => sanitize_key( $action_type ),
				'channel'       => sanitize_key( $channel ),
				'target_count'  => absint( $target ),
				'success_count' => absint( $success ),
				'fail_count'    => absint( $fail ),
				'payload'       => wp_json_encode( $payload ),
				'created_at'    => current_time( 'mysql' ),
			)
		);
	}

	public static function queue_recent( $limit = 20 ) {
		global $wpdb;
		$table = self::queue_table();
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status IN ('pending','failed') ORDER BY id DESC LIMIT %d",
				absint( $limit )
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function recent_logs( $limit = 20 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::log_table() . ' ORDER BY id DESC LIMIT %d',
				absint( $limit )
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param int[]  $user_ids
	 * @param string $channel sms|email|both
	 * @return array{success:int,fail:int,errors:array}
	 */
	public static function send_to_users( $user_ids, $channel, $subject, $message ) {
		$user_ids = array_filter( array_map( 'absint', (array) $user_ids ) );
		$channel  = in_array( $channel, array( 'sms', 'email', 'both' ), true ) ? $channel : 'sms';
		$subject  = sanitize_text_field( $subject );
		$message  = sanitize_textarea_field( $message );
		$success  = 0;
		$fail     = 0;
		$errors   = array();

		foreach ( $user_ids as $uid ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				$fail++;
				$errors[] = "کاربر #{$uid} یافت نشد";
				continue;
			}
			$ok_any = false;

			if ( 'sms' === $channel || 'both' === $channel ) {
				$phone = EzLens_CD_Messaging_Bridge::user_phone( $uid );
				if ( ! $phone ) {
					$fail++;
					$errors[] = $user->display_name . ': بدون موبایل';
				} else {
					$r = EzLens_CD_Messaging_Bridge::send_sms( $phone, $message );
					if ( is_wp_error( $r ) ) {
						// Queue internally even if gateway missing
						self::queue_item( $uid, 'sms', $phone, $subject, $message, $r->get_error_message() );
						$fail++;
						$errors[] = $user->display_name . ' (SMS): ' . $r->get_error_message();
					} else {
						$success++;
						$ok_any = true;
					}
				}
			}

			if ( 'email' === $channel || 'both' === $channel ) {
				$r = EzLens_CD_Messaging_Bridge::send_email( $user->user_email, $subject ? $subject : 'پیام از ایزی‌لنز', $message );
				if ( is_wp_error( $r ) ) {
					self::queue_item( $uid, 'email', $user->user_email, $subject, $message, $r->get_error_message() );
					$fail++;
					$errors[] = $user->display_name . ' (ایمیل): ' . $r->get_error_message();
				} else {
					$success++;
					$ok_any = true;
				}
			}

			unset( $ok_any );
		}

		self::log(
			'bulk_message',
			$channel,
			count( $user_ids ),
			$success,
			$fail,
			array(
				'subject' => $subject,
				'message' => mb_substr( $message, 0, 200 ),
				'errors'  => array_slice( $errors, 0, 10 ),
			)
		);

		return array(
			'success' => $success,
			'fail'    => $fail,
			'errors'  => $errors,
		);
	}

	/**
	 * Lightweight outbox when gateway unavailable.
	 */
	public static function queue_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_message_queue';
	}

	public static function maybe_create_queue() {
		global $wpdb;
		$table   = self::queue_table();
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				channel varchar(16) NOT NULL DEFAULT 'sms',
				recipient varchar(191) NOT NULL DEFAULT '',
				subject varchar(255) NOT NULL DEFAULT '',
				message text NOT NULL,
				status varchar(16) NOT NULL DEFAULT 'pending',
				error_text text NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY status (status)
			) {$charset};"
		);
	}

	public static function queue_item( $user_id, $channel, $recipient, $subject, $message, $error = '' ) {
		global $wpdb;
		self::maybe_create_queue();
		$wpdb->insert(
			self::queue_table(),
			array(
				'user_id'    => absint( $user_id ),
				'channel'    => sanitize_key( $channel ),
				'recipient'  => sanitize_text_field( $recipient ),
				'subject'    => sanitize_text_field( $subject ),
				'message'    => sanitize_textarea_field( $message ),
				'status'     => 'pending',
				'error_text' => sanitize_textarea_field( $error ),
				'created_at' => current_time( 'mysql' ),
			)
		);
	}

	public static function pending_queue( $limit = 50 ) {
		global $wpdb;
		self::maybe_create_queue();
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::queue_table() . ' WHERE status = %s ORDER BY id DESC LIMIT %d',
				'pending',
				absint( $limit )
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Audience presets.
	 *
	 * @return int[] user ids
	 */
	public static function audience( $preset ) {
		$preset = sanitize_key( $preset );
		if ( 'all_customers' === $preset ) {
			$q = new WP_User_Query(
				array(
					'role__in' => array( 'customer', 'subscriber' ),
					'fields'   => 'ID',
					'number'   => 500,
				)
			);
			return array_map( 'intval', (array) $q->get_results() );
		}
		if ( 'with_orders' === $preset && function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'limit'  => 200,
					'status' => array( 'wc-completed', 'wc-processing' ),
					'return' => 'ids',
				)
			);
			$ids = array();
			foreach ( $orders as $oid ) {
				$o = wc_get_order( $oid );
				if ( $o && $o->get_user_id() ) {
					$ids[ $o->get_user_id() ] = $o->get_user_id();
				}
			}
			return array_values( $ids );
		}
		if ( 'no_orders' === $preset ) {
			$all = self::audience( 'all_customers' );
			$with = self::audience( 'with_orders' );
			return array_values( array_diff( $all, $with ) );
		}
		return array();
	}
}
