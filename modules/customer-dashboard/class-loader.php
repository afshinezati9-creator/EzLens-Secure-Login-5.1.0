<?php
/**
 * Customer Dashboard loader — wires Phase 1 services
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Loader {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		EzLens_CD_Router::get_instance();
		EzLens_CD_Assets::get_instance();
		EzLens_CD_Compatibility::get_instance();
		if ( class_exists( 'EzLens_CD_Ajax' ) ) {
			EzLens_CD_Ajax::get_instance();
		}
		if ( class_exists( 'EzLens_CD_Referral' ) ) {
			EzLens_CD_Referral::bootstrap();
		}
		if ( is_admin() && class_exists( 'EzLens_CD_Admin' ) ) {
			EzLens_CD_Admin::get_instance();
		}
		if ( is_admin() && class_exists( 'EzLens_CD_Admin_Ajax' ) ) {
			EzLens_CD_Admin_Ajax::get_instance();
		}
		if ( class_exists( 'EzLens_CD_API' ) ) {
			EzLens_CD_API::get_instance();
		}
		if ( class_exists( 'EzLens_CD_Addresses' ) && method_exists( 'EzLens_CD_Addresses', 'bootstrap_checkout' ) ) {
			EzLens_CD_Addresses::bootstrap_checkout();
		}
	}
}
