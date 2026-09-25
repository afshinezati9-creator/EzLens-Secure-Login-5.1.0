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
			'/manager/customers',
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( __CLASS__, 'create_customer' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/customers/(?P<id>\\d+)',
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( __CLASS__, 'update_customer' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/customers/(?P<id>\\d+)',
			array(
				'methods' => WP_REST_Server::DELETABLE,
				'callback' => array( __CLASS__, 'delete_customer' ),
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

	private static function map_user( $user, $include_stats = true, $summary = false ) {
		// Dashboard summary responses only need identity + last-login data.
		// Avoid building the full customer payload for this read-heavy path.
		if ( $summary ) {
			$user_id = (int) $user->ID;
			return array(
				'id'           => $user_id,
				'username'     => $user->user_login,
				'email'        => $user->user_email,
				'first_name'   => $user->first_name,
				'last_name'    => $user->last_name,
				'display_name' => $user->display_name,
				'roles'        => array_values( $user->roles ),
				'registered'   => $user->user_registered,
				'last_login'   => self::last_login_iso( $user_id ),
			);
		}

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

		$allowed_orderby = array( 'registered', 'login', 'nicename', 'email', 'display_name', 'ID' );
		$is_last_login_sort = ( 'last_login' === $orderby );

		$args = array(
			'number'      => $per_page,
			'paged'       => $page,
			'order'       => $order,
			'orderby'     => $is_last_login_sort ? 'meta_value_num' : (
				in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'registered'
			),
			'count_total' => true,
		);

		// Keep recent-login sorting server-side so Flutter can request only the
		// rows that the dashboard actually renders.
		if ( $is_last_login_sort ) {
			$args['meta_key'] = 'ezlens_last_login';
		}

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
		if ( $summary && ! empty( $users ) ) {
			// Prime user meta in one batch so last_login_iso() does not cause
			// one database lookup per user in the dashboard summary.
			update_meta_cache( 'user', wp_list_pluck( $users, 'ID' ) );
		}
		foreach ( (array) $users as $u ) {
			$items[] = self::map_user( $u, $include_stats, $summary );
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

	public static function create_customer( WP_REST_Request $request ) {
		$p = $request->get_json_params();
		$phone = preg_replace( '/\\D+/', '', (string) ( $p['phone'] ?? '' ) );
		if ( strlen( $phone ) === 10 && strpos( $phone, '9' ) === 0 ) $phone = '0' . $phone;
		$username = sanitize_user( (string) ( $p['username'] ?? $phone ), true );
		if ( $username === '' ) return new WP_Error( 'invalid_username', 'نام کاربری الزامی است', array( 'status' => 400 ) );
		$email = sanitize_email( (string) ( $p['email'] ?? '' ) );
		if ( $email === '' ) $email = $username . '@ezlens.ir';
		$id = wp_insert_user( array( 'user_login' => $username, 'user_pass' => (string) ( $p['password'] ?? wp_generate_password( 16, true, true ) ), 'user_email' => $email, 'first_name' => sanitize_text_field( (string) ( $p['first_name'] ?? '' ) ), 'last_name' => sanitize_text_field( (string) ( $p['last_name'] ?? '' ) ), 'role' => 'customer' ) );
		if ( is_wp_error( $id ) ) return $id;
		if ( $phone !== '' ) { update_user_meta( $id, 'billing_phone', $phone ); update_user_meta( $id, 'user_phone', $phone ); }
		return new WP_REST_Response( self::map_user( get_userdata( $id ) ), 201 );
	}

	public static function update_customer( WP_REST_Request $request ) {
		$id = (int) $request['id']; $user = get_userdata( $id );
		if ( ! $user ) return new WP_Error( 'not_found', 'کاربر یافت نشد', array( 'status' => 404 ) );
		$p = $request->get_json_params(); $args = array( 'ID' => $id );
		if ( array_key_exists( 'email', $p ) ) $args['user_email'] = sanitize_email( (string) $p['email'] );
		if ( array_key_exists( 'first_name', $p ) ) $args['first_name'] = sanitize_text_field( (string) $p['first_name'] );
		if ( array_key_exists( 'last_name', $p ) ) $args['last_name'] = sanitize_text_field( (string) $p['last_name'] );
		if ( ! empty( $p['password'] ) ) $args['user_pass'] = (string) $p['password'];
		$updated = wp_update_user( $args ); if ( is_wp_error( $updated ) ) return $updated;
		if ( array_key_exists( 'phone', $p ) ) { $phone = preg_replace( '/\\D+/', '', (string) $p['phone'] ); if ( strlen( $phone ) === 10 && strpos( $phone, '9' ) === 0 ) $phone = '0' . $phone; update_user_meta( $id, 'billing_phone', $phone ); update_user_meta( $id, 'user_phone', $phone ); }
		return rest_ensure_response( self::map_user( get_userdata( $id ) ) );
	}

	public static function delete_customer( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		if ( ! get_userdata( $id ) ) return new WP_Error( 'not_found', 'کاربر یافت نشد', array( 'status' => 404 ) );
		if ( $id === get_current_user_id() ) return new WP_Error( 'self_delete', 'حذف کاربر جاری مجاز نیست', array( 'status' => 400 ) );
		require_once ABSPATH . 'wp-admin/includes/user.php';
		if ( ! wp_delete_user( $id ) ) return new WP_Error( 'delete_failed', 'حذف کاربر انجام نشد', array( 'status' => 500 ) );
		return rest_ensure_response( array( 'ok' => true, 'id' => $id ) );
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
