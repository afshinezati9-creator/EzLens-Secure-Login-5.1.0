<?php
/**
 * اتوماسیون یادآوری تعویض لنز — فاز ۷ (اسکلت قابل‌فعال‌سازی)
 *
 * متای پیشنهادی کاربر:
 * - _ezlens_lens_next_date  (Y-m-d)
 * - _ezlens_lens_reminded_for (Y-m-d) آخرین تاریخی که برایش یادآوری زده شد
 *
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Automation_Lens_Reminder {

	const HOOK = 'ezlens_cd_lens_reminder_tick';

	public static function init() {
		if ( ! apply_filters( 'ezlens_cd_lens_reminder_enabled', false ) ) {
			// پیش‌فرض خاموش تا تنظیمات صریح
			add_action( 'init', array( __CLASS__, 'maybe_unschedule' ) );
			return;
		}
		add_action( self::HOOK, array( __CLASS__, 'run' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'schedule' ) );
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK );
		}
	}

	public static function maybe_unschedule() {
		// وقتی فیچر خاموش است کرون را نگه نمی‌داریم
		if ( wp_next_scheduled( self::HOOK ) ) {
			wp_clear_scheduled_hook( self::HOOK );
		}
	}

	/**
	 * کاربران با next_date در بازه N روز آینده
	 */
	public static function run() {
		$days = (int) apply_filters( 'ezlens_cd_lens_reminder_window_days', 7 );
		$days = max( 1, min( 30, $days ) );
		$today = current_time( 'Y-m-d' );
		$until = gmdate( 'Y-m-d', strtotime( $today . " +{$days} days" ) );

		$q = new WP_User_Query(
			array(
				'number'     => 100,
				'meta_query' => array(
					array(
						'key'     => '_ezlens_lens_next_date',
						'value'   => array( $today, $until ),
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					),
				),
				'fields'     => 'ID',
			)
		);
		$ids = $q->get_results();
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $uid ) {
			$uid  = (int) $uid;
			$next = get_user_meta( $uid, '_ezlens_lens_next_date', true );
			$done = get_user_meta( $uid, '_ezlens_lens_reminded_for', true );
			if ( $done && $done === $next ) {
				continue;
			}
			$user = get_userdata( $uid );
			if ( ! $user ) {
				continue;
			}
			$phone = get_user_meta( $uid, 'billing_phone', true );
			$name  = $user->first_name ?: $user->display_name;
			$body  = apply_filters(
				'ezlens_cd_lens_reminder_message',
				sprintf( '%s عزیز، تا تاریخ %s زمان تعویض لنز نزدیک است. از حساب کاربری می‌توانید سفارش مجدد دهید.', $name, $next ),
				$uid,
				$next
			);

			$sent = false;
			if ( $phone && class_exists( 'EzLens_Auth_Messaging' ) ) {
				$m = EzLens_Auth_Messaging::get_instance();
				if ( method_exists( $m, 'send_sms' ) ) {
					$r = $m->send_sms( $phone, $body );
					$sent = ! empty( $r['success'] );
				} elseif ( method_exists( $m, 'send' ) ) {
					$r = $m->send( 'sms', $phone, $body );
					$sent = ! empty( $r['success'] );
				}
			}
			$sent = (bool) apply_filters( 'ezlens_cd_lens_reminder_send', $sent, $uid, $phone, $body );

			if ( $sent ) {
				update_user_meta( $uid, '_ezlens_lens_reminded_for', $next );
				do_action( 'ezlens_cd_lens_reminder_sent', $uid, $next );
			}
		}
	}
}

EzLens_CD_Automation_Lens_Reminder::init();
