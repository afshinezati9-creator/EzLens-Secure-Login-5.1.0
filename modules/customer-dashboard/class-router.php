<?php
/**
 * Customer Dashboard router — Phase 2 (reliable content takeover)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Router {

	/** @var self|null */
	private static $instance = null;

	/** @var bool */
	private $shell_rendered = false;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ), 9 );
		add_filter( 'woocommerce_get_query_vars', array( $this, 'query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'filter_wc_menu' ), 99 );
		add_filter( 'woocommerce_locate_template', array( $this, 'locate_template' ), 50, 3 );

		// Primary takeover: replace entire account content for every endpoint
		add_action( 'woocommerce_account_content', array( $this, 'hijack_content' ), 1 );

		$eps = array(
			'prescriptions',
			'wallet', 'charity',
			'gift-cards',
			'support',
			'security',
			'wishlist',
			'coupons',
			'referral',
			'reviews',
		);
		foreach ( $eps as $ep ) {
			add_action( 'woocommerce_account_' . $ep . '_endpoint', array( $this, 'render_endpoint' ), 5 );
		}
	}

	public function enabled() {
		return (int) get_option( 'ezlens_cd_replace_my_account', 1 ) === 1
			&& is_user_logged_in()
			&& function_exists( 'is_account_page' )
			&& is_account_page();
	}

	public function add_endpoints() {
		foreach ( array(
			'prescriptions',
			'wallet',
			'charity',
			'gift-cards',
			'support',
			'security',
			'wishlist',
			'coupons',
			'referral',
			'reviews',
		) as $ep ) {
			add_rewrite_endpoint( $ep, EP_ROOT | EP_PAGES );
		}
	}

	public function query_vars( $vars ) {
		foreach ( array(
			'prescriptions',
			'wallet',
			'charity',
			'gift-cards',
			'support',
			'security',
			'wishlist',
			'coupons',
			'referral',
			'reviews',
		) as $ep ) {
			$vars[ $ep ] = $ep;
		}
		return $vars;
	}

	public function filter_wc_menu( $items ) {
		if ( (int) get_option( 'ezlens_cd_replace_my_account', 1 ) !== 1 ) {
			return $items;
		}
		return array();
	}

	/**
	 * Resolve current section key from WC query.
	 */
	public function resolve_section() {
		$map = array(
			'orders'          => 'orders',
			'view-order'      => 'view-order',
			'edit-address'    => 'addresses',
			'edit-account'    => 'account',
			'payment-methods' => 'account',
			'wishlist'        => 'wishlist',
			'security'        => 'security',
			'prescriptions'   => 'prescriptions',
			'wallet'          => 'wallet',
			'charity'         => 'charity',
			'gift-cards'      => 'gift-cards',
			'support'         => 'support',
			'coupons'         => 'coupons',
			'referral'        => 'referral',
			'reviews'         => 'reviews',
			'customer-logout' => 'logout',
		);

		$ep = '';
		if ( function_exists( 'WC' ) && WC()->query ) {
			$ep = (string) WC()->query->get_current_endpoint();
		}
		if ( ! $ep && function_exists( 'ezcd_current_endpoint' ) ) {
			$ep = (string) ezcd_current_endpoint();
			if ( 'overview' === $ep ) {
				$ep = '';
			}
		}
		// Fallback: parse request path /my-account/{endpoint}
		if ( ! $ep && ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
			$path = trailingslashit( $path ? $path : '' );
			foreach ( array_keys( $map ) as $key ) {
				if ( false !== strpos( $path, '/' . $key . '/' ) || preg_match( '#/' . preg_quote( $key, '#' ) . '/?$#', $path ) ) {
					$ep = $key;
					break;
				}
			}
		}
		if ( ! $ep ) {
			return 'overview';
		}
		return isset( $map[ $ep ] ) ? $map[ $ep ] : $ep;
	}

	/**
	 * Strip default WC account body and print our shell once.
	 */
	public function hijack_content() {
		if ( (int) get_option( 'ezlens_cd_replace_my_account', 1 ) !== 1 ) {
			return;
		}
		// مهمان در my-account: همان ورود EzLens (نه فرم پیش‌فرض ووکامرس)
		if ( ! is_user_logged_in() ) {
			if ( $this->shell_rendered ) {
				return;
			}
			$this->shell_rendered = true;
			remove_all_actions( 'woocommerce_account_content' );
			echo '<div class="ezcd-guest-login" style="max-width:480px;margin:24px auto;">';
			if ( shortcode_exists( 'minimal_auth' ) ) {
				echo do_shortcode( '[minimal_auth]' );
			} elseif ( class_exists( 'EzLens_Auth_Shortcodes_Auth' ) ) {
				echo EzLens_Auth_Shortcodes_Auth::render_minimal_auth();
			} else {
				echo '<p style="text-align:center;">برای ورود به <a href="' . esc_url( home_url( '/login/' ) ) . '">صفحه ورود</a> بروید.</p>';
			}
			echo '</div>';
			return;
		}
		if ( $this->shell_rendered ) {
			// Prevent default WC output after us
			remove_all_actions( 'woocommerce_account_content' );
			return;
		}

		// Stop every other callback on this hook (default orders list, dashboard text, …)
		remove_all_actions( 'woocommerce_account_content' );

		$section = $this->resolve_section();
		$this->render_shell( $section );
	}

	public function locate_template( $template, $template_name, $template_path ) {
		if ( (int) get_option( 'ezlens_cd_replace_my_account', 1 ) !== 1 || ! is_user_logged_in() ) {
			return $template;
		}
		if ( strpos( $template_name, 'myaccount/' ) !== 0 ) {
			return $template;
		}

		$dir = dirname( __FILE__ ) . '/templates/';
		$map = array(
			'myaccount/navigation.php' => 'wc-navigation.php',
			'myaccount/my-account.php' => 'wc-my-account.php',
			// dashboard/orders/etc. content is handled by hijack_content —
			// still map to empty bridges so theme templates do not leak.
			'myaccount/dashboard.php'          => 'wc-empty.php',
			'myaccount/orders.php'             => 'wc-empty.php',
			'myaccount/view-order.php'         => 'wc-empty.php',
			'myaccount/form-edit-address.php'  => 'wc-empty.php',
			'myaccount/form-edit-account.php'  => 'wc-empty.php',
		);
		if ( isset( $map[ $template_name ] ) ) {
			$custom = $dir . $map[ $template_name ];
			if ( is_readable( $custom ) ) {
				return $custom;
			}
		}
		return $template;
	}

	public function render_endpoint() {
		// Custom endpoints: hijack_content usually already rendered.
		if ( $this->shell_rendered ) {
			return;
		}
		$this->render_shell( $this->resolve_section() );
	}

	public function render_shell( $current = 'overview' ) {
		if ( $this->shell_rendered ) {
			return;
		}
		$this->shell_rendered = true;
		$path = dirname( __FILE__ ) . '/templates/shell.php';
		if ( ! is_readable( $path ) ) {
			echo '<p style="padding:20px;border:1px solid #e8edf5;border-radius:12px;">قالب داشبورد EzLens یافت نشد.</p>';
			return;
		}
		$current = sanitize_key( $current );
		if ( ! $current ) {
			$current = 'overview';
		}
		include $path;
	}

	public static function flush_rules() {
		$self = self::get_instance();
		$self->add_endpoints();
		flush_rewrite_rules( false );
	}
}
