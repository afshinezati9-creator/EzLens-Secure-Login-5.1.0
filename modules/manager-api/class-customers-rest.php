<?php
/**
 * Manager Customers REST — list WP users (not only WC customers).
 * OTP users often have role customer/subscriber; WC /customers may return empty.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Customers_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'list_users' );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/customers',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_customers' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/customers/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_customer' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/customers/(?P<id>\d+)/dossier',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_dossier' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	private static function phone_for( $user_id, $user ) {
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'user_phone', true );
		}
		if ( ! $phone && preg_match( '/^09\d{9}$/', $user->user_login ) ) {
			$phone = $user->user_login;
		}
		return (string) $phone;
	}

	private static function last_login_iso( $user_id ) {
		$last = get_user_meta( $user_id, 'ezlens_last_login', true );
		if ( ! $last ) {
			$last = get_user_meta( $user_id, 'last_login', true );
		}
		if ( ! $last ) {
			return null;
		}
		if ( is_numeric( $last ) ) {
			$ts = (int) $last;
			if ( $ts > 9999999999 ) {
				$ts = (int) ( $ts / 1000 );
			}
			return gmdate( 'c', $ts );
		}
		$t = strtotime( $last );
		return $t ? gmdate( 'c', $t ) : (string) $last;
	}

	private static function wallet_balance( $user_id ) {
		if ( class_exists( 'EzLens_CD_Wallet' ) && method_exists( 'EzLens_CD_Wallet', 'balance' ) ) {
			return (int) EzLens_CD_Wallet::balance( $user_id );
		}
		$v = get_user_meta( $user_id, 'ezlens_wallet_balance', true );
		return absint( $v );
	}

	private static function map_user( $user, $include_stats = true ) {
		$user_id = (int) $user->ID;
		$has_password = ! empty( $user->user_pass );
		if ( get_user_meta( $user_id, 'ezlens_otp_only', true ) === '1' ) {
			$has_password = false;
		}
		$orders_count = 0;
		$total_spent  = '0';
		if ( $include_stats ) {
			$orders_count = function_exists( 'wc_get_customer_order_count' ) ? (int) wc_get_customer_order_count( $user_id ) : 0;
			$total_spent  = function_exists( 'wc_get_customer_total_spent' ) ? (string) wc_get_customer_total_spent( $user_id ) : '0';
		}

		return array(
			'id'             => $user_id,
			'username'       => $user->user_login,
			'email'          => $user->user_email,
			'first_name'     => $user->first_name,
			'last_name'      => $user->last_name,
			'display_name'   => $user->display_name,
			'phone'          => self::phone_for( $user_id, $user ),
			'roles'          => array_values( $user->roles ),
			'registered'     => $user->user_registered,
			'last_login'     => self::last_login_iso( $user_id ),
			'wallet_balance' => self::wallet_balance( $user_id ),
			'orders_count'   => $orders_count,
			'total_spent'    => $total_spent,
			'has_password'   => $has_password,
			// WC-compatible shape for Flutter ManagerUser.fromWcJson
			'billing'        => array(
				'first_name' => $user->first_name,
				'last_name'  => $user->last_name,
				'phone'      => self::phone_for( $user_id, $user ),
				'email'      => $user->user_email,
			),
		);
	}

	/**
	 * List users for Manager app.
	 * Query: page, per_page, search, role (customer|subscriber|all)
	 */
	public static function list_customers( WP_REST_Request $request ) {
		$page     = max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) );
		$per_page = max( 1, min( 100, (int) ( $request->get_param( 'per_page' ) ?: 15 ) ) );
		$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );
		$summary  = (bool) $request->get_param( 'summary' );
		$include_stats = ! $summary;
		$role     = sanitize_key( (string) ( $request->get_param( 'role' ) ?: 'all' ) );
		$orderby  = sanitize_key( (string) ( $request->get_param( 'orderby' ) ?: 'registered' ) );
		$order    = strtoupper( (string) ( $request->get_param( 'order' ) ?: 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';

		$args = array(
			'number'  => $per_page,
			'paged'   => $page,
			'order'   => $order,
			'orderby' => in_array( $orderby, array( 'registered', 'login', 'nicename', 'email', 'display_name', 'ID' ), true )
				? $orderby
				: 'registered',
			'count_total' => true,
		);

		// Exclude pure administrators from customer list unless role=administrator
		if ( $role && $role !== 'all' ) {
			$args['role'] = $role;
		} else {
			// Customers + subscribers (OTP shoppers) — exclude admins/shop managers
			$args['role__in'] = array( 'customer', 'subscriber' );
		}

		if ( $search !== '' ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}

		$q = new WP_User_Query( $args );
		$users = $q->get_results();
		$total = (int) $q->get_total();
		$items = array();
		foreach ( (array) $users as $u ) {
			$items[] = self::map_user( $u, $include_stats );
		}

		// If role filter empty result but search looks like phone, try meta
		if ( empty( $items ) && $search !== '' && preg_match( '/^0?9\d{9}$/', preg_replace( '/\D/', '', $search ) ) ) {
			$phone = preg_replace( '/\D/', '', $search );
			if ( strlen( $phone ) === 10 ) {
				$phone = '0' . $phone;
			}
			$q2 = new WP_User_Query(
				array(
					'meta_query' => array(
						'relation' => 'OR',
						array( 'key' => 'billing_phone', 'value' => $phone, 'compare' => 'LIKE' ),
						array( 'key' => 'user_phone', 'value' => $phone, 'compare' => 'LIKE' ),
					),
					'number' => $per_page,
					'paged'  => $page,
				)
			);
			foreach ( (array) $q2->get_results() as $u ) {
				$items[] = self::map_user( $u, $include_stats );
			}
			$total = max( $total, (int) $q2->get_total() );
		}

		$response = rest_ensure_response(
			array(
				'ok'          => true,
				'items'       => $items,
				'total'       => $total,
				'total_pages' => $per_page > 0 ? (int) ceil( $total / $per_page ) : 1,
				'page'        => $page,
			)
		);
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) ( $per_page > 0 ? (int) ceil( $total / $per_page ) : 1 ) );
		return $response;
	}

	public static function get_customer( WP_REST_Request $request ) {
		$user_id = (int) $request['id'];
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'not_found', 'کاربر یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( self::map_user( $user ) );
	}

	public static function get_dossier( WP_REST_Request $request ) {
		$user_id = (int) $request['id'];
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'not_found', 'کاربر یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response(
			array(
				'ok'   => true,
				'user' => self::map_user( $user ),
			)
		);
	}
}

EzLens_Manager_Customers_REST::init();
