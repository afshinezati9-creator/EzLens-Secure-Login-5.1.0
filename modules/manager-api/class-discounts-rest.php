<?php
/**
 * Manager Discounts & Gifts REST — WC coupons + CD gift cards.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Discounts_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_shop_coupons' );
	}

	private static function ensure_gift() {
		$path = defined( 'EZLAUTH_MODULES_DIR' )
			? EZLAUTH_MODULES_DIR . 'customer-dashboard/includes/class-gift-cards.php'
			: dirname( __FILE__, 3 ) . '/customer-dashboard/includes/class-gift-cards.php';
		if ( is_readable( $path ) ) {
			require_once $path;
		}
		if ( class_exists( 'EzLens_CD_Gift_Cards' ) ) {
			EzLens_CD_Gift_Cards::maybe_create_table();
		}
	}

	public static function register_routes() {
		// Coupons
		register_rest_route( self::NS, '/manager/discounts/coupons', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_coupons' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_coupon' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/discounts/coupons/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_coupon' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_coupon' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );

		// Gift cards
		register_rest_route( self::NS, '/manager/discounts/gifts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_gifts' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_gift' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/discounts/gifts/(?P<id>\d+)/cancel', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'cancel_gift' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/discounts/stats', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'stats' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
	}

	private static function to_jalali( $mysql ) {
		$ts = is_numeric( $mysql ) ? (int) $mysql : strtotime( (string) $mysql );
		if ( ! $ts ) {
			return (string) $mysql;
		}
		if ( class_exists( 'EzLens_Inbox' ) && method_exists( 'EzLens_Inbox', 'to_jalali' ) ) {
			return EzLens_Inbox::to_jalali( $ts );
		}
		return date_i18n( 'Y-m-d H:i', $ts );
	}

	private static function map_coupon( $coupon ) {
		if ( ! $coupon || ! is_a( $coupon, 'WC_Coupon' ) ) {
			return null;
		}
		$expires = $coupon->get_date_expires();
		$type    = $coupon->get_discount_type();
		$type_label = 'percent' === $type ? 'درصدی' : ( 'fixed_cart' === $type ? 'مبلغ ثابت سبد' : ( 'fixed_product' === $type ? 'مبلغ ثابت محصول' : $type ) );
		return array(
			'id'              => $coupon->get_id(),
			'code'            => $coupon->get_code(),
			'discount_type'   => $type,
			'type_label'      => $type_label,
			'amount'          => (float) $coupon->get_amount(),
			'description'     => $coupon->get_description(),
			'usage_count'     => (int) $coupon->get_usage_count(),
			'usage_limit'     => $coupon->get_usage_limit() ? (int) $coupon->get_usage_limit() : 0,
			'usage_limit_per_user' => $coupon->get_usage_limit_per_user() ? (int) $coupon->get_usage_limit_per_user() : 0,
			'minimum_amount'  => (float) $coupon->get_minimum_amount(),
			'maximum_amount'  => (float) $coupon->get_maximum_amount(),
			'individual_use'  => $coupon->get_individual_use(),
			'free_shipping'   => $coupon->get_free_shipping(),
			'date_expires'    => $expires ? $expires->date( 'Y-m-d H:i:s' ) : '',
			'date_expires_fa' => $expires ? self::to_jalali( $expires->getTimestamp() ) : '',
			'status'          => get_post_status( $coupon->get_id() ),
			'date_created'    => $coupon->get_date_created() ? $coupon->get_date_created()->date( 'Y-m-d H:i:s' ) : '',
			'date_created_fa' => $coupon->get_date_created() ? self::to_jalali( $coupon->get_date_created()->getTimestamp() ) : '',
		);
	}

	public static function list_coupons( WP_REST_Request $request ) {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			return new WP_Error( 'no_wc', 'ووکامرس فعال نیست', array( 'status' => 500 ) );
		}
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$args   = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => $per,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		if ( $search !== '' ) {
			$args['s'] = $search;
		}
		$q = new WP_Query( $args );
		$items = array();
		foreach ( $q->posts as $p ) {
			$c = new WC_Coupon( $p->ID );
			$mapped = self::map_coupon( $c );
			if ( $mapped ) {
				$items[] = $mapped;
			}
		}
		return rest_ensure_response(
			array(
				'ok'    => true,
				'items' => $items,
				'total' => (int) $q->found_posts,
				'page'  => $page,
				'pages' => max( 1, (int) $q->max_num_pages ),
			)
		);
	}

	public static function create_coupon( WP_REST_Request $request ) {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			return new WP_Error( 'no_wc', 'ووکامرس فعال نیست', array( 'status' => 500 ) );
		}
		$code = sanitize_text_field( $request->get_param( 'code' ) ?: '' );
		$code = wc_format_coupon_code( $code );
		if ( $code === '' ) {
			return new WP_Error( 'code', 'کد تخفیف لازم است', array( 'status' => 400 ) );
		}
		// uniqueness
		$existing = new WC_Coupon( $code );
		if ( $existing->get_id() ) {
			return new WP_Error( 'exists', 'این کد از قبل وجود دارد', array( 'status' => 400 ) );
		}

		$coupon = new WC_Coupon();
		$coupon->set_code( $code );
		$type = sanitize_key( $request->get_param( 'discount_type' ) ?: 'percent' );
		if ( ! in_array( $type, array( 'percent', 'fixed_cart', 'fixed_product' ), true ) ) {
			$type = 'percent';
		}
		$coupon->set_discount_type( $type );
		$coupon->set_amount( (float) $request->get_param( 'amount' ) );
		if ( null !== $request->get_param( 'description' ) ) {
			$coupon->set_description( sanitize_text_field( $request->get_param( 'description' ) ) );
		}
		if ( $request->get_param( 'usage_limit' ) ) {
			$coupon->set_usage_limit( absint( $request->get_param( 'usage_limit' ) ) );
		}
		if ( $request->get_param( 'usage_limit_per_user' ) ) {
			$coupon->set_usage_limit_per_user( absint( $request->get_param( 'usage_limit_per_user' ) ) );
		}
		if ( $request->get_param( 'minimum_amount' ) ) {
			$coupon->set_minimum_amount( (string) $request->get_param( 'minimum_amount' ) );
		}
		if ( $request->get_param( 'free_shipping' ) ) {
			$coupon->set_free_shipping( true );
		}
		if ( $request->get_param( 'individual_use' ) ) {
			$coupon->set_individual_use( true );
		}
		$exp = sanitize_text_field( $request->get_param( 'date_expires' ) ?: '' );
		if ( $exp !== '' ) {
			$coupon->set_date_expires( $exp );
		}
		$id = $coupon->save();
		if ( ! $id ) {
			return new WP_Error( 'save', 'ذخیره کوپن ناموفق', array( 'status' => 500 ) );
		}
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'کد تخفیف ایجاد شد',
				'coupon'  => self::map_coupon( new WC_Coupon( $id ) ),
			)
		);
	}

	public static function update_coupon( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		$coupon = new WC_Coupon( $id );
		if ( ! $coupon->get_id() ) {
			return new WP_Error( 'not_found', 'کوپن یافت نشد', array( 'status' => 404 ) );
		}
		if ( null !== $request->get_param( 'amount' ) ) {
			$coupon->set_amount( (float) $request->get_param( 'amount' ) );
		}
		if ( null !== $request->get_param( 'discount_type' ) ) {
			$coupon->set_discount_type( sanitize_key( $request->get_param( 'discount_type' ) ) );
		}
		if ( null !== $request->get_param( 'description' ) ) {
			$coupon->set_description( sanitize_text_field( $request->get_param( 'description' ) ) );
		}
		if ( null !== $request->get_param( 'usage_limit' ) ) {
			$coupon->set_usage_limit( absint( $request->get_param( 'usage_limit' ) ) );
		}
		if ( null !== $request->get_param( 'status' ) ) {
			$st = sanitize_key( $request->get_param( 'status' ) );
			if ( in_array( $st, array( 'publish', 'draft' ), true ) ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => $st ) );
			}
		}
		$coupon->save();
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'به‌روز شد',
				'coupon'  => self::map_coupon( new WC_Coupon( $id ) ),
			)
		);
	}

	public static function delete_coupon( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		$r  = wp_delete_post( $id, true );
		if ( ! $r ) {
			return new WP_Error( 'delete', 'حذف ناموفق', array( 'status' => 400 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'حذف شد' ) );
	}

	private static function map_gift( $row ) {
		$purchaser = '';
		$redeemer  = '';
		if ( ! empty( $row->purchased_by ) ) {
			$u = get_userdata( (int) $row->purchased_by );
			$purchaser = $u ? $u->display_name : ( '#' . $row->purchased_by );
		}
		if ( ! empty( $row->redeemed_by ) ) {
			$u = get_userdata( (int) $row->redeemed_by );
			$redeemer = $u ? $u->display_name : ( '#' . $row->redeemed_by );
		}
		return array(
			'id'             => (int) $row->id,
			'code'           => (string) $row->code,
			'amount'         => (int) $row->amount,
			'status'         => (string) $row->status,
			'purchased_by'   => (int) $row->purchased_by,
			'purchaser_name' => $purchaser,
			'redeemed_by'    => (int) ( $row->redeemed_by ?? 0 ),
			'redeemer_name'  => $redeemer,
			'recipient_name' => (string) ( $row->recipient_name ?? '' ),
			'message'        => (string) ( $row->message ?? '' ),
			'created_at'     => (string) $row->created_at,
			'created_fa'     => self::to_jalali( $row->created_at ),
			'redeemed_at'    => (string) ( $row->redeemed_at ?? '' ),
			'redeemed_fa'    => self::to_jalali( $row->redeemed_at ?? '' ),
		);
	}

	public static function list_gifts( WP_REST_Request $request ) {
		self::ensure_gift();
		if ( ! class_exists( 'EzLens_CD_Gift_Cards' ) ) {
			return new WP_Error( 'no_gift', 'ماژول کارت هدیه بارگذاری نشده', array( 'status' => 500 ) );
		}
		global $wpdb;
		$table  = EzLens_CD_Gift_Cards::table();
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$status = sanitize_key( $request->get_param( 'status' ) ?: 'all' );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );

		$where  = array( '1=1' );
		$params = array();
		if ( $status && $status !== 'all' ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}
		if ( $search !== '' ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(code LIKE %s OR recipient_name LIKE %s)';
			$params[] = $like;
			$params[] = $like;
		}
		$where_sql = implode( ' AND ', $where );
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );
		$offset    = ( $page - 1 ) * $per;
		$list_sql  = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
		$list_args = array_merge( $params, array( $per, $offset ) );
		$rows      = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ) );
		$items     = array();
		foreach ( (array) $rows as $row ) {
			$items[] = self::map_gift( $row );
		}
		return rest_ensure_response(
			array(
				'ok'    => true,
				'items' => $items,
				'total' => $total,
				'page'  => $page,
				'pages' => max( 1, (int) ceil( $total / max( 1, $per ) ) ),
			)
		);
	}

	/**
	 * Admin-issued gift card (no wallet debit).
	 */
	public static function create_gift( WP_REST_Request $request ) {
		self::ensure_gift();
		if ( ! class_exists( 'EzLens_CD_Gift_Cards' ) ) {
			return new WP_Error( 'no_gift', 'ماژول کارت هدیه بارگذاری نشده', array( 'status' => 500 ) );
		}
		global $wpdb;
		$amount = absint( preg_replace( '/\D+/', '', (string) $request->get_param( 'amount' ) ) );
		if ( $amount < 1000 ) {
			return new WP_Error( 'min', 'حداقل مبلغ ۱٬۰۰۰ تومان', array( 'status' => 400 ) );
		}
		$code = sanitize_text_field( $request->get_param( 'code' ) ?: '' );
		if ( $code === '' ) {
			$code = EzLens_CD_Gift_Cards::generate_code();
		} else {
			$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $code ) );
		}
		$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . EzLens_CD_Gift_Cards::table() . ' WHERE code = %s', $code ) );
		if ( $exists ) {
			return new WP_Error( 'exists', 'این کد از قبل وجود دارد', array( 'status' => 400 ) );
		}
		$ok = $wpdb->insert(
			EzLens_CD_Gift_Cards::table(),
			array(
				'code'           => $code,
				'amount'         => $amount,
				'status'         => 'active',
				'purchased_by'   => get_current_user_id(),
				'recipient_name' => sanitize_text_field( $request->get_param( 'recipient_name' ) ?: '' ),
				'message'        => sanitize_textarea_field( $request->get_param( 'message' ) ?: '' ),
				'created_at'     => current_time( 'mysql' ),
			)
		);
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ایجاد کارت ناموفق', array( 'status' => 500 ) );
		}
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . EzLens_CD_Gift_Cards::table() . ' WHERE id = %d', $wpdb->insert_id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'کارت هدیه صادر شد',
				'gift'    => self::map_gift( $row ),
			)
		);
	}

	public static function cancel_gift( WP_REST_Request $request ) {
		self::ensure_gift();
		global $wpdb;
		$id  = (int) $request['id'];
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . EzLens_CD_Gift_Cards::table() . ' WHERE id = %d', $id ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'کارت یافت نشد', array( 'status' => 404 ) );
		}
		if ( 'redeemed' === $row->status ) {
			return new WP_Error( 'redeemed', 'کارت استفاده‌شده قابل لغو نیست', array( 'status' => 400 ) );
		}
		$wpdb->update(
			EzLens_CD_Gift_Cards::table(),
			array( 'status' => 'cancelled' ),
			array( 'id' => $id )
		);
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . EzLens_CD_Gift_Cards::table() . ' WHERE id = %d', $id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'کارت لغو شد',
				'gift'    => self::map_gift( $row ),
			)
		);
	}

	public static function stats( WP_REST_Request $request ) {
		self::ensure_gift();
		$coupon_count = 0;
		if ( post_type_exists( 'shop_coupon' ) ) {
			$counts = wp_count_posts( 'shop_coupon' );
			$coupon_count = (int) ( $counts->publish ?? 0 );
		}
		$gift_active = 0;
		$gift_redeemed = 0;
		if ( class_exists( 'EzLens_CD_Gift_Cards' ) ) {
			global $wpdb;
			$t = EzLens_CD_Gift_Cards::table();
			$gift_active = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE status = %s", 'active' ) );
			$gift_redeemed = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE status = %s", 'redeemed' ) );
		}
		return rest_ensure_response(
			array(
				'ok'            => true,
				'coupons'       => $coupon_count,
				'gifts_active'  => $gift_active,
				'gifts_redeemed'=> $gift_redeemed,
			)
		);
	}
}

EzLens_Manager_Discounts_REST::init();
