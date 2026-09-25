<?php
/**
 * سلامت صف کمپین + خلاصه نگهداری — فاز ۷
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Queue_Health {

	public static function snapshot() {
		global $wpdb;
		$campaigns = $wpdb->prefix . 'ezlens_campaigns';
		$recipients = $wpdb->prefix . 'ezlens_campaign_recipients';
		$out = array(
			'queue_tick_scheduled'  => (bool) wp_next_scheduled( 'ezlens_campaign_queue_tick' ),
			'queue_tick_next'       => wp_next_scheduled( 'ezlens_campaign_queue_tick' ),
			'maintenance_last'      => get_option( 'ezlens_maintenance_last', null ),
			'sms_low_balance'       => get_option( 'ezlens_sms_low_balance', null ),
			'sending_count'         => 0,
			'scheduled_count'       => 0,
			'pending_recipients'    => 0,
			'failed_recipients_24h' => 0,
			'action_scheduler'      => function_exists( 'as_has_scheduled_action' ),
		);

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $campaigns ) ) === $campaigns ) {
			$out['sending_count']   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$campaigns} WHERE status='sending'" );
			$out['scheduled_count'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$campaigns} WHERE status='scheduled'" );
		}
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $recipients ) ) === $recipients ) {
			$out['pending_recipients'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$recipients} WHERE status='pending'" );
			$out['failed_recipients_24h'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$recipients} WHERE status='failed' AND updated_at >= %s",
					gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS )
				)
			);
		}

		$out['status'] = 'ok';
		if ( ! $out['queue_tick_scheduled'] && ( $out['sending_count'] > 0 || $out['scheduled_count'] > 0 ) ) {
			$out['status'] = 'warn';
			$out['message'] = 'کمپین در صف است ولی cron تیک زمان‌بندی نشده.';
		}
		if ( $out['failed_recipients_24h'] > 50 ) {
			$out['status'] = 'warn';
			$out['message'] = 'تعداد خطای ارسال ۲۴ساعته بالاست.';
		}
		if ( ! empty( $out['sms_low_balance'] ) ) {
			$out['status'] = 'warn';
			$out['message'] = 'اعتبار SMS نزدیک به اتمام است.';
		}
		return $out;
	}

	public static function init() {
		add_action( 'wp_ajax_ezlens_queue_health', array( __CLASS__, 'ajax' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
	}

	public static function ajax() {
		check_ajax_referer( 'ezlens_auth_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		wp_send_json_success( self::snapshot() );
	}

	public static function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || strpos( (string) $screen->id, 'ezlens-auth' ) === false ) {
			return;
		}
		$s = self::snapshot();
		if ( ( $s['status'] ?? '' ) !== 'warn' ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>EzLens صف/سلامت:</strong> ' . esc_html( $s['message'] ?? 'هشدار' ) . '</p></div>';
	}
}

EzLens_Auth_Queue_Health::init();
