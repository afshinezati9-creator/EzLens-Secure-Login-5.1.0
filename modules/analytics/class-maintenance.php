<?php
/**
 * نگهداری مقیاس — فاز ۶
 * آرشیو/پاک‌سازی لاگ، ترک ایمیل، گیرندگان کمپین تمام‌شده، تیکت‌های بسته‌شدهٔ قدیمی
 *
 * @package EzLens
 * @version 1.0.0-phase6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Maintenance {

	/**
	 * اجرای کامل نگهداری (روزانه)
	 *
	 * @return array گزارش شمارش حذف‌ها
	 */
	public static function run() {
		$report = array(
			'logs'             => 0,
			'campaign_tracks'  => 0,
			'campaign_recipients' => 0,
			'support_messages' => 0,
			'support_tickets'  => 0,
			'otp'              => false,
		);

		$log_days = (int) ( class_exists( 'EzLens_Auth_Settings' ) ? EzLens_Auth_Settings::get( 'log_retention_days' ) : 30 );
		if ( $log_days < 1 ) {
			$log_days = 30;
		}
		if ( $log_days > 3650 ) {
			$log_days = 365;
		}

		if ( class_exists( 'EzLens_Auth_Logger' ) && method_exists( 'EzLens_Auth_Logger', 'clean_old' ) ) {
			EzLens_Auth_Logger::clean_old( $log_days );
			$report['logs'] = $log_days; // روز نگهداری، نه تعداد
		}

		if ( class_exists( 'EzLens_Auth_OTP' ) && method_exists( 'EzLens_Auth_OTP', 'clean_expired' ) ) {
			EzLens_Auth_OTP::clean_expired();
			$report['otp'] = true;
		}

		$track_days = (int) apply_filters( 'ezlens_retention_campaign_tracks_days', 90 );
		$report['campaign_tracks'] = self::purge_campaign_tracks( $track_days );

		$recip_days = (int) apply_filters( 'ezlens_retention_campaign_recipients_days', 120 );
		$report['campaign_recipients'] = self::purge_finished_campaign_recipients( $recip_days );

		$ticket_days = (int) apply_filters( 'ezlens_retention_closed_tickets_days', 180 );
		$st = self::purge_closed_support( $ticket_days );
		$report['support_tickets']  = $st['tickets'];
		$report['support_messages'] = $st['messages'];

		// سقف هشدار اعتبار SMS (فقط option؛ UI در فاز بعد)
		self::maybe_flag_sms_balance();

		do_action( 'ezlens_maintenance_ran', $report );
		update_option( 'ezlens_maintenance_last', array(
			'at'     => current_time( 'mysql' ),
			'report' => $report,
		), false );

		return $report;
	}

	/**
	 * پاک کردن ترک باز شدن ایمیل قدیمی
	 */
	public static function purge_campaign_tracks( $days = 90 ) {
		global $wpdb;
		$days = max( 14, (int) $days );
		$table = $wpdb->prefix . 'ezlens_campaign_tracks';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return 0;
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$n = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE opened_at < DATE_SUB(%s, INTERVAL %d DAY)",
				current_time( 'mysql' ),
				$days
			)
		);
		return is_numeric( $n ) ? (int) $n : 0;
	}

	/**
	 * گیرندگان کمپین‌های تمام‌شده (sent/partial) قدیمی را حذف می‌کند.
	 * stats روی خود کمپین می‌ماند — گزارش خلاصه بدون اسکن میلیون‌ها ردیف.
	 */
	public static function purge_finished_campaign_recipients( $days = 120 ) {
		global $wpdb;
		$days = max( 30, (int) $days );
		$campaigns = $wpdb->prefix . 'ezlens_campaigns';
		$recipients = $wpdb->prefix . 'ezlens_campaign_recipients';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $recipients ) ) !== $recipients ) {
			return 0;
		}
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $campaigns ) ) !== $campaigns ) {
			return 0;
		}
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$campaigns} WHERE status IN ('sent','partial') AND sent_at IS NOT NULL AND sent_at < DATE_SUB(%s, INTERVAL %d DAY) LIMIT 200",
				current_time( 'mysql' ),
				$days
			)
		);
		if ( empty( $ids ) ) {
			return 0;
		}
		$deleted = 0;
		foreach ( $ids as $id ) {
			$n = $wpdb->delete( $recipients, array( 'campaign_id' => (int) $id ), array( '%d' ) );
			if ( is_numeric( $n ) ) {
				$deleted += (int) $n;
			}
		}
		return $deleted;
	}

	/**
	 * تیکت‌های closed قدیمی + پیام‌هایشان
	 */
	public static function purge_closed_support( $days = 180 ) {
		global $wpdb;
		$days = max( 30, (int) $days );
		$tickets  = $wpdb->prefix . 'ezlens_support_tickets';
		$messages = $wpdb->prefix . 'ezlens_support_messages';
		$out = array( 'tickets' => 0, 'messages' => 0 );
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tickets ) ) !== $tickets ) {
			return $out;
		}
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$tickets} WHERE status IN ('closed','resolved') AND updated_at < DATE_SUB(%s, INTERVAL %d DAY) LIMIT 300",
				current_time( 'mysql' ),
				$days
			)
		);
		if ( empty( $ids ) ) {
			return $out;
		}
		foreach ( $ids as $id ) {
			$id = (int) $id;
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $messages ) ) === $messages ) {
				$m = $wpdb->delete( $messages, array( 'ticket_id' => $id ), array( '%d' ) );
				if ( is_numeric( $m ) ) {
					$out['messages'] += (int) $m;
				}
			}
			$t = $wpdb->delete( $tickets, array( 'id' => $id ), array( '%d' ) );
			if ( is_numeric( $t ) ) {
				$out['tickets'] += (int) $t;
			}
		}
		return $out;
	}

	/**
	 * اگر اعتبار SMS از آستانه کمتر بود option هشدار ست می‌شود (بدون API اجباری).
	 */
	public static function maybe_flag_sms_balance() {
		$threshold = (int) apply_filters( 'ezlens_sms_low_balance_threshold', 100 );
		$balance   = apply_filters( 'ezlens_sms_balance', null );
		if ( $balance === null || ! is_numeric( $balance ) ) {
			return;
		}
		$balance = (float) $balance;
		if ( $balance <= $threshold ) {
			update_option(
				'ezlens_sms_low_balance',
				array(
					'balance'   => $balance,
					'threshold' => $threshold,
					'at'        => current_time( 'mysql' ),
				),
				false
			);
		} else {
			delete_option( 'ezlens_sms_low_balance' );
		}
	}
}
