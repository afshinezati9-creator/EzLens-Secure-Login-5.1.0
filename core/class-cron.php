<?php
/**
 * کرون روزانه Auth — فاز ۶: لاگ + OTP + نگهداری مقیاس
 * @version 2.4.0-phase6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Cron {

	private static $instance = null;
	private $hook_name       = 'ezlens_auth_daily_cleanup';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( $this->hook_name, array( $this, 'run_cleanup' ) );
		add_action( 'wp_loaded', array( $this, 'schedule_cleanup' ) );
	}

	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( $this->hook_name ) ) {
			$timestamp = strtotime( 'tomorrow 00:15:00' );
			wp_schedule_event( $timestamp, 'daily', $this->hook_name );
		}
	}

	public function unschedule_cleanup() {
		wp_clear_scheduled_hook( $this->hook_name );
	}

	public function run_cleanup() {
		if ( class_exists( 'EzLens_Auth_Maintenance' ) ) {
			return EzLens_Auth_Maintenance::run();
		}
		// fallback قدیمی
		$retention_days = class_exists( 'EzLens_Auth_Settings' ) ? ( (int) EzLens_Auth_Settings::get( 'log_retention_days' ) ?: 30 ) : 30;
		if ( $retention_days > 0 && class_exists( 'EzLens_Auth_Logger' ) ) {
			EzLens_Auth_Logger::clean_old( $retention_days );
		}
		if ( class_exists( 'EzLens_Auth_OTP' ) ) {
			EzLens_Auth_OTP::clean_expired();
		}
		return true;
	}

	public function is_scheduled() {
		return wp_next_scheduled( $this->hook_name ) !== false;
	}

	public function get_next_run_time() {
		$timestamp = wp_next_scheduled( $this->hook_name );
		return $timestamp ? date_i18n( 'Y/m/d H:i:s', $timestamp ) : '—';
	}
}
