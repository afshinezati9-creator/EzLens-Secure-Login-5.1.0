<?php
/**
 * REST API for Customer Dashboard (app / external)
 * Namespace: ezlens/v1/cd
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_API {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		$ns = 'ezlens/v1';

		register_rest_route(
			$ns,
			'/cd/me',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'me' ),
				'permission_callback' => array( $this, 'must_login' ),
			)
		);

		register_rest_route(
			$ns,
			'/cd/orders',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'orders' ),
				'permission_callback' => array( $this, 'must_login' ),
			)
		);

		register_rest_route(
			$ns,
			'/cd/wallet',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'wallet' ),
				'permission_callback' => array( $this, 'must_login' ),
			)
		);

		register_rest_route(
			$ns,
			'/cd/prescriptions',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'prescriptions' ),
				'permission_callback' => array( $this, 'must_login' ),
			)
		);

		register_rest_route(
			$ns,
			'/cd/sections',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sections' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function must_login() {
		return is_user_logged_in();
	}

	public function me( WP_REST_Request $request ) {
		$user = wp_get_current_user();
		$uid  = $user->ID;
		return rest_ensure_response(
			array(
				'id'           => $uid,
				'display_name' => $user->display_name,
				'email'        => $user->user_email,
				'phone'        => class_exists( 'EzLens_CD_Admin_Customers' ) ? EzLens_CD_Admin_Customers::phone( $uid ) : '',
				'wallet'       => class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance( $uid ) : 0,
				'points'       => class_exists( 'EzLens_CD_Coupons' ) ? EzLens_CD_Coupons::points( $uid ) : 0,
			)
		);
	}

	public function orders( WP_REST_Request $request ) {
		$uid = get_current_user_id();
		$data = EzLens_CD_Cache::remember(
			'orders_' . $uid,
			function () use ( $uid ) {
				if ( ! function_exists( 'wc_get_orders' ) ) {
					return array();
				}
				$orders = wc_get_orders(
					array(
						'customer_id' => $uid,
						'limit'       => 20,
						'orderby'     => 'date',
						'order'       => 'DESC',
					)
				);
				$out = array();
				foreach ( $orders as $o ) {
					$out[] = array(
						'id'     => $o->get_id(),
						'number' => $o->get_order_number(),
						'status' => $o->get_status(),
						'total'  => $o->get_total(),
						'date'   => $o->get_date_created() ? $o->get_date_created()->date( 'c' ) : '',
					);
				}
				return $out;
			},
			180
		);
		return rest_ensure_response( $data );
	}

	public function wallet( WP_REST_Request $request ) {
		$uid = get_current_user_id();
		return rest_ensure_response(
			array(
				'balance' => class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance( $uid ) : 0,
				'history' => class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::history( $uid, 20 ) : array(),
			)
		);
	}

	public function prescriptions( WP_REST_Request $request ) {
		$uid = get_current_user_id();
		$list = class_exists( 'EzLens_CD_Prescriptions' ) ? EzLens_CD_Prescriptions::list_for_user( $uid ) : array();
		return rest_ensure_response( $list );
	}

	public function sections( WP_REST_Request $request ) {
		$sections = get_option( 'ezlens_cd_sections', array() );
		return rest_ensure_response( is_array( $sections ) ? $sections : array() );
	}
}
