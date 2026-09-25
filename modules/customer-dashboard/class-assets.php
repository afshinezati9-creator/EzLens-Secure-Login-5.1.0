<?php
/**
 * Customer Dashboard front assets
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Assets {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 30 );
	}

	public function enqueue() {
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}

		$base = $this->base_url();
		$css_file = dirname( __FILE__ ) . '/assets/css/customer-dashboard.css';
		$js_file  = dirname( __FILE__ ) . '/assets/js/customer-dashboard.js';
		/* نسخه بر اساس زمان فایل تا کش مرورگر/LiteSpeed نماند */
		$ver = ( is_readable( $css_file ) ? (string) filemtime( $css_file ) : '1' )
			. '.' . ( is_readable( $js_file ) ? (string) filemtime( $js_file ) : '1' );

		wp_enqueue_style(
			'ezcd-dashboard',
			$base . 'assets/css/customer-dashboard.css',
			array(),
			$ver
		);

		// Leaflet always on account (SPA may open map without full reload)
		wp_enqueue_style( 'ezcd-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'ezcd-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true );

		$deps = array();
		if ( wp_script_is( 'ezcd-leaflet', 'enqueued' ) ) {
			$deps[] = 'ezcd-leaflet';
		}

		wp_enqueue_script(
			'ezcd-dashboard',
			$base . 'assets/js/customer-dashboard.js',
			$deps,
			$ver,
			true
		);

		wp_localize_script(
			'ezcd-dashboard',
			'ezcdData',
			array(
				'ajax'     => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ezcd_nonce' ),
				'endpoint' => function_exists( 'ezcd_current_endpoint' ) ? ezcd_current_endpoint() : 'overview',
				'i18n'     => array(
					'saving'  => 'در حال ذخیره…',
					'saved'   => 'ذخیره شد',
					'error'   => 'خطا رخ داد',
					'cart'    => 'رفتن به سبد',
				),
			)
		);
	}

	private function base_url() {
		if ( defined( 'EZLAUTH_PLUGIN_URL' ) ) {
			return trailingslashit( EZLAUTH_PLUGIN_URL . 'modules/customer-dashboard' );
		}
		return trailingslashit( plugins_url( '', dirname( __FILE__ ) . '/class-install.php' ) );
	}
}
