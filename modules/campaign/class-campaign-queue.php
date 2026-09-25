<?php
/**
 * صف و اجرای batch کمپین — فاز ۲
 * @version 2.7.0-phase2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Campaign_Queue {

	private static $instance = null;
	private $hook           = 'ezlens_campaign_queue_tick';
	private $batch_hook     = 'ezlens_campaign_process_batch';

	public static function get_instance() {
		return self::$instance ? self::$instance : ( self::$instance = new self() );
	}

	private function __construct() {
		add_filter( 'cron_schedules', array( $this, 'schedules' ) );
		add_action( $this->hook, array( $this, 'run' ) );
		add_action( $this->batch_hook, array( $this, 'run_batch' ) );
		add_action( 'wp_loaded', array( $this, 'schedule' ) );
		// اگر Action Scheduler (ووکامرس) باشد، همان batch را آنجا هم ثبت می‌کنیم
		add_action( 'action_scheduler_init', array( $this, 'register_as_hooks' ) );
	}

	public function schedules( $s ) {
		$s['ezlens_1min'] = array(
			'interval' => 60,
			'display'  => 'EzLens every minute',
		);
		return $s;
	}

	public function schedule() {
		if ( ! wp_next_scheduled( $this->hook ) ) {
			wp_schedule_event( time() + 30, 'ezlens_1min', $this->hook );
		}
	}

	public function unschedule() {
		wp_clear_scheduled_hook( $this->hook );
		wp_clear_scheduled_hook( $this->batch_hook );
	}

	public function register_as_hooks() {
		// هوک AS با همان callback batch
		if ( function_exists( 'as_has_scheduled_action' ) ) {
			// فقط ثبت callback؛ زمان‌بندی per-campaign در schedule_batch
		}
	}

	/**
	 * زمان‌بندی یک batch برای کمپین.
	 * ترجیح: Action Scheduler → در غیر این صورت WP-Cron single event.
	 */
	public static function schedule_batch( $campaign_id, $delay = 5 ) {
		$campaign_id = (int) $campaign_id;
		$delay       = max( 1, (int) $delay );
		$hook        = 'ezlens_campaign_process_batch';
		$args        = array( $campaign_id );

		if ( function_exists( 'as_enqueue_async_action' ) && function_exists( 'as_has_scheduled_action' ) ) {
			// جلوگیری از صف تکراری زیاد برای همان کمپین
			if ( ! as_has_scheduled_action( $hook, $args, 'ezlens-campaign' ) ) {
				if ( $delay <= 2 && function_exists( 'as_enqueue_async_action' ) ) {
					as_enqueue_async_action( $hook, $args, 'ezlens-campaign' );
				} elseif ( function_exists( 'as_schedule_single_action' ) ) {
					as_schedule_single_action( time() + $delay, $hook, $args, 'ezlens-campaign' );
				}
			}
			return;
		}

		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_single_event( time() + $delay, $hook, $args );
		}
		// روی لوکال بدون real-cron: spawn غیرمسدود برای پیشبرد صف
		if ( function_exists( 'spawn_cron' ) ) {
			spawn_cron( time() );
		}
	}

	/**
	 * تیک دقیقه‌ای: کمپین‌های زمان‌بندی‌شده + ادامه sending.
	 */
	public function run() {
		global $wpdb;
		$table = $wpdb->prefix . 'ezlens_campaigns';
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) {
			return;
		}

		$now = current_time( 'mysql' );
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE status='scheduled' AND scheduled_at IS NOT NULL AND scheduled_at<=%s ORDER BY scheduled_at ASC LIMIT 5",
				$now
			)
		);
		foreach ( (array) $ids as $id ) {
			$result = EzLens_Auth_Campaign::get_instance()->send_campaign( (int) $id, true );
			if ( class_exists( 'EzLens_Auth_Audit' ) ) {
				EzLens_Auth_Audit::get_instance()->log( 'campaign.scheduled_run', array( 'result' => $result ), 'campaign', (int) $id );
			}
		}

		// ستون updated_at ممکن است روی هاست قدیمی نباشد
		$has_updated = false;
		$cols = $wpdb->get_col( "SHOW COLUMNS FROM {$table}", 0 );
		if ( is_array( $cols ) ) {
			$has_updated = in_array( 'updated_at', $cols, true );
		}
		$order = $has_updated ? 'ORDER BY updated_at ASC, created_at ASC' : 'ORDER BY created_at ASC';
		$sending = $wpdb->get_col( "SELECT id FROM {$table} WHERE status='sending' {$order} LIMIT 5" );
		foreach ( (array) $sending as $id ) {
			self::schedule_batch( (int) $id, 2 );
		}
	}

	public function run_batch( $campaign_id ) {
		$campaign_id = (int) $campaign_id;
		$result      = EzLens_Auth_Campaign::get_instance()->send_campaign( $campaign_id, true );
		if ( class_exists( 'EzLens_Auth_Audit' ) ) {
			EzLens_Auth_Audit::get_instance()->log( 'campaign.batch_run', array( 'result' => $result ), 'campaign', $campaign_id );
		}
		// اگر هنوز pending مانده، batch بعدی
		if ( is_array( $result ) && ! empty( $result['queued'] ) ) {
			self::schedule_batch( $campaign_id, 3 );
		} elseif ( is_array( $result ) && ! empty( $result['pending'] ) && (int) $result['pending'] > 0 ) {
			self::schedule_batch( $campaign_id, 3 );
		}
	}
}
