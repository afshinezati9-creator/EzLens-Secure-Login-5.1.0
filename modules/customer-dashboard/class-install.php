<?php
/**
 * Customer Dashboard — bootstrap & install (v1.0.0 release)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Customer_Dashboard_Install {

	/** @var self|null */
	private static $instance = null;

	const OPTION_VERSION = 'ezlens_cd_db_version';
	const DB_VERSION     = '1.1.1';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ), 25 );
		add_action( 'init', array( $this, 'maybe_install' ), 4 );
	}

	public static function install() {
		$self = self::get_instance();
		$self->seed_options();
		$self->register_caps();
		update_option( self::OPTION_VERSION, self::DB_VERSION, false );
	}

	public function maybe_install() {
		$ver = get_option( self::OPTION_VERSION, '' );
		if ( $ver !== self::DB_VERSION ) {
			$this->seed_options();
			$this->register_caps();
			$this->run_schema();
			delete_option( 'ezlens_cd_flush_rules' );
			update_option( self::OPTION_VERSION, self::DB_VERSION, false );
		}
	}

	/**
	 * Ensure all module tables exist (idempotent via dbDelta).
	 */
	public function run_schema() {
		if ( class_exists( 'EzLens_CD_Prescriptions' ) ) {
			EzLens_CD_Prescriptions::maybe_create_table();
		}
		if ( class_exists( 'EzLens_CD_Support_Bridge' ) ) {
			EzLens_CD_Support_Bridge::maybe_create_fallback_tables();
		}
		if ( class_exists( 'EzLens_CD_Notes' ) ) {
			EzLens_CD_Notes::maybe_create_table();
		}
		if ( class_exists( 'EzLens_CD_Mass_Actions' ) ) {
			EzLens_CD_Mass_Actions::maybe_create_table();
			EzLens_CD_Mass_Actions::maybe_create_queue();
		}
		if ( class_exists( 'EzLens_CD_Wallet' ) ) {
			EzLens_CD_Wallet::maybe_create_table();
		}
		if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			EzLens_CD_Wallet_Deposits::maybe_create_table();
		}
		if ( class_exists( 'EzLens_CD_Gift_Cards' ) ) {
			EzLens_CD_Gift_Cards::maybe_create_table();
		}
		if ( class_exists( 'EzLens_CD_Charity' ) ) {
			EzLens_CD_Charity::maybe_create_tables();
		}
	}

	public function boot() {
		$dir = $this->dir();

		$files = array(
			$dir . 'includes/class-helpers.php',
			$dir . 'includes/class-admin-ajax.php',
			$dir . 'includes/class-api.php',
			$dir . 'includes/class-cache.php',
			$dir . 'includes/class-mass-actions.php',
			$dir . 'includes/class-messaging-bridge.php',
			$dir . 'includes/class-admin-customers.php',
			$dir . 'includes/class-notes.php',
			$dir . 'includes/class-reviews.php',
			$dir . 'includes/class-support-bridge.php',
			$dir . 'includes/class-referral.php',
			$dir . 'includes/class-coupons.php',
			$dir . 'includes/class-gift-cards.php',
			$dir . 'includes/class-wallet.php',
			$dir . 'includes/class-wallet-deposits.php',
			$dir . 'includes/class-wallet-online-pay.php',
			$dir . 'includes/class-charity.php',
			$dir . 'includes/class-prescriptions.php',
			$dir . 'includes/class-sections.php',
			$dir . 'includes/class-overview.php',
			$dir . 'includes/class-orders.php',
			$dir . 'includes/class-addresses.php',
			$dir . 'includes/class-account.php',
			$dir . 'includes/class-security.php',
			$dir . 'includes/class-wishlist.php',
			$dir . 'includes/class-ajax.php',
			$dir . 'class-menu.php',
			$dir . 'class-router.php',
			$dir . 'class-assets.php',
			$dir . 'class-compatibility.php',
			$dir . 'class-admin.php',
			$dir . 'class-loader.php',
		);

		foreach ( $files as $file ) {
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}

		$this->run_schema();
		if ( class_exists( 'EzLens_CD_Referral' ) ) {
			EzLens_CD_Referral::bootstrap();
		}
		if ( class_exists( 'EzLens_CD_Loader' ) ) {
			EzLens_CD_Loader::get_instance();
		}
	}

	/**
	 * Default section toggles — all on for mapped sections.
	 */
	private function seed_options() {
		$sections = array(
			'overview'       => 1,
			'orders'         => 1,
			'prescriptions'  => 1,
			'wishlist'       => 1,
			'addresses'      => 1,
			'reviews'        => 1,
			'coupons'        => 1,
			'gift_cards'     => 1,
			'wallet'         => 1,
			'support'        => 1,
			'account'        => 1,
			'security'       => 1,
			'referral'       => 1,
		);

		foreach ( $sections as $key => $val ) {
			$opt = 'ezlens_cd_section_' . $key;
			if ( false === get_option( $opt, false ) && null === get_option( $opt, null ) ) {
				// only set if never existed — use add_option
			}
			if ( get_option( $opt, null ) === null ) {
				add_option( $opt, $val, '', false );
			}
		}

		if ( get_option( 'ezlens_cd_replace_my_account', null ) === null ) {
			add_option( 'ezlens_cd_replace_my_account', 1, '', false );
		}
		if ( get_option( 'ezlens_cd_sections', null ) === null ) {
			add_option( 'ezlens_cd_sections', $sections, '', false );
		}
		if ( get_option( 'ezlens_cd_module_version', null ) === null ) {
			add_option( 'ezlens_cd_module_version', self::DB_VERSION, '', false );
		} else {
			update_option( 'ezlens_cd_module_version', self::DB_VERSION, false );
		}
	}

	private function register_caps() {
		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}
		$caps = array(
			'ezlens_manage_customers',
			'ezlens_view_prescriptions',
			'ezlens_manage_gift_cards',
			'ezlens_send_mass_message',
		);
		foreach ( $caps as $cap ) {
			if ( ! $role->has_cap( $cap ) ) {
				$role->add_cap( $cap );
			}
		}
	}

	public function dir() {
		return trailingslashit( dirname( __FILE__ ) );
	}

	public function url() {
		if ( defined( 'EZLAUTH_PLUGIN_URL' ) ) {
			return trailingslashit( EZLAUTH_PLUGIN_URL . 'modules/customer-dashboard' );
		}
		return plugin_dir_url( __FILE__ );
	}
}

EzLens_Customer_Dashboard_Install::get_instance();
