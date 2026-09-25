<?php
/**
 * REST API for Product Options (EzLens Manager app + admin tools).
 * Namespace: ezlens/v1
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Product_Options_REST_API {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {

		// ===== لیست / ایجاد ویژگی =====
		register_rest_route( self::NS, '/product-options', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_templates' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_template' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );

		// ===== خواندن / ویرایش / حذف =====
		register_rest_route( self::NS, '/product-options/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_template' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_template' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_template' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );

		// ===== محصولات متصل به یک ویژگی =====
		register_rest_route( self::NS, '/product-options/(?P<id>\d+)/products', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'template_products' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );

		// ===== اسلات‌های یک محصول =====
		register_rest_route( self::NS, '/products/(?P<id>\d+)/option-slots', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_product_slots' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'save_product_slots' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );

		// ===== Ping (برای تست) =====
		register_rest_route( self::NS, '/ping', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function () {
				return rest_ensure_response( array(
					'ok'      => true,
					'module'  => class_exists( 'EzLens_Product_Options_Template_Manager' ) ? 'yes' : 'no',
					'version' => '1.0.0',
				) );
			},
			'permission_callback' => '__return_true',
		) );
	}

	public static function can_manage() {
		if ( current_user_can( 'manage_woocommerce' ) ) return true;
		if ( current_user_can( 'manage_options' ) )     return true;
		if ( current_user_can( 'edit_products' ) )      return true;

		return new WP_Error(
			'ezlens_forbidden',
			'دسترسی غیرمجاز.',
			array( 'status' => 401 )
		);
	}

	private static function manager() {
		if ( ! class_exists( 'EzLens_Product_Options_Template_Manager' ) ) {
			return null;
		}
		return EzLens_Product_Options_Template_Manager::get_instance();
	}

	private static function extract_code( $fields ) {
		$html = '';
		$css  = '';
		$js   = '';
		if ( is_array( $fields ) ) {
			$html = isset( $fields['_code_html'] ) ? (string) $fields['_code_html'] : '';
			$css  = isset( $fields['_code_css'] ) ? (string) $fields['_code_css'] : '';
			$js   = isset( $fields['_code_js'] ) ? (string) $fields['_code_js'] : '';
		}
		$code = $html;
		if ( $css !== '' || $js !== '' ) {
			$code .= "\n\n/**CSS**/\n" . $css . "\n\n/**JS**/\n" . $js;
		}
		return $code;
	}

	private static function parse_code( $code ) {
		$code  = is_string( $code ) ? $code : '';
		$parts = preg_split( '/\/\*\*CSS\*\*\/|\/\*\*JS\*\*\//', $code );
		return array(
			'html' => isset( $parts[0] ) ? trim( $parts[0] ) : '',
			'css'  => isset( $parts[1] ) ? trim( $parts[1] ) : '',
			'js'   => isset( $parts[2] ) ? trim( $parts[2] ) : '',
		);
	}

	private static function format_template( $row, $manager ) {
		if ( ! is_array( $row ) ) {
			return null;
		}
		$id     = isset( $row['id'] ) ? (int) $row['id'] : 0;
		$fields = isset( $row['fields'] ) && is_array( $row['fields'] ) ? $row['fields'] : array();
		$code   = self::extract_code( $fields );

		$connected = 0;
		if ( $manager && method_exists( $manager, 'get_connected_products_count' ) ) {
			$connected = (int) $manager->get_connected_products_count( $id );
		}

		return array(
			'id'              => $id,
			'title'           => isset( $row['title'] ) ? (string) $row['title'] : '',
			'description'     => isset( $row['description'] ) ? (string) $row['description'] : '',
			'status'          => isset( $row['status'] ) ? (string) $row['status'] : 'active',
			'code'            => $code,
			'fields_count'    => count( array_filter( $fields, function ( $f, $k ) {
				return is_array( $f ) && ( is_int( $k ) || ( is_string( $k ) && strpos( $k, '_code_' ) !== 0 ) );
			}, ARRAY_FILTER_USE_BOTH ) ),
			'connected_count' => $connected,
			'created_at'      => isset( $row['created_at'] ) ? (string) $row['created_at'] : '',
			'updated_at'      => isset( $row['updated_at'] ) ? (string) $row['updated_at'] : '',
		);
	}

	public static function list_templates( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager ) {
			return new WP_Error( 'no_module', 'ماژول ویژگی محصول در دسترس نیست', array( 'status' => 500 ) );
		}

		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = min( 100, max( 1, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$search = sanitize_text_field( (string) $request->get_param( 'search' ) );
		$status = sanitize_key( (string) ( $request->get_param( 'status' ) ?: 'all' ) );
		$offset = ( $page - 1 ) * $per;

		$list  = $manager->get_list( array(
			'status' => $status,
			'search' => $search,
			'limit'  => $per,
			'offset' => $offset,
		) );
		$items = isset( $list['items'] ) && is_array( $list['items'] ) ? $list['items'] : array();
		$total = isset( $list['total'] ) ? (int) $list['total'] : count( $items );

		$out = array();
		foreach ( $items as $row ) {
			$fmt = self::format_template( $row, $manager );
			if ( $fmt ) {
				$out[] = $fmt;
			}
		}

		return rest_ensure_response( array(
			'items'    => $out,
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per,
		) );
	}

	public static function get_template( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager ) {
			return new WP_Error( 'no_module', 'ماژول ویژگی محصول در دسترس نیست', array( 'status' => 500 ) );
		}
		$id  = (int) $request['id'];
		$row = $manager->get( $id );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'ویژگی یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( self::format_template( $row, $manager ) );
	}

	public static function create_template( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager ) {
			return new WP_Error( 'no_module', 'ماژول ویژگی محصول در دسترس نیست', array( 'status' => 500 ) );
		}
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		if ( $title === '' ) {
			return new WP_Error( 'required', 'نام ویژگی اجباری است', array( 'status' => 400 ) );
		}

		$existing = $manager->get_list( array( 'search' => $title, 'limit' => 50, 'offset' => 0 ) );
		$items    = isset( $existing['items'] ) ? $existing['items'] : array();
		foreach ( $items as $row ) {
			if ( isset( $row['title'] ) && mb_strtolower( trim( $row['title'] ) ) === mb_strtolower( trim( $title ) ) ) {
				return new WP_Error( 'duplicate', 'نام ویژگی تکراری است', array( 'status' => 409 ) );
			}
		}

		$description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$status      = sanitize_key( (string) ( $request->get_param( 'status' ) ?: 'active' ) );
		$code        = (string) $request->get_param( 'code' );
		$parsed      = self::parse_code( $code );
		$fields      = array(
			'_code_html' => $parsed['html'],
			'_code_css'  => $parsed['css'],
			'_code_js'   => $parsed['js'],
		);

		$result = $manager->create( array(
			'title'       => $title,
			'description' => $description,
			'status'      => $status,
			'fields'      => $fields,
			'code'        => $code,
		) );

		if ( empty( $result['success'] ) ) {
			return new WP_Error( 'create_failed', $result['message'] ?? 'خطا در ذخیره', array( 'status' => 500 ) );
		}

		$id  = (int) ( $result['id'] ?? 0 );
		$row = $manager->get( $id );
		return rest_ensure_response( array(
			'success' => true,
			'message' => 'ویژگی با موفقیت ذخیره شد',
			'item'    => self::format_template( $row, $manager ),
		) );
	}

	public static function update_template( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager ) {
			return new WP_Error( 'no_module', 'ماژول ویژگی محصول در دسترس نیست', array( 'status' => 500 ) );
		}
		$id  = (int) $request['id'];
		$row = $manager->get( $id );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'ویژگی یافت نشد', array( 'status' => 404 ) );
		}

		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		if ( $title === '' ) {
			return new WP_Error( 'required', 'نام ویژگی اجباری است', array( 'status' => 400 ) );
		}

		$existing = $manager->get_list( array( 'search' => $title, 'limit' => 50, 'offset' => 0 ) );
		$items    = isset( $existing['items'] ) ? $existing['items'] : array();
		foreach ( $items as $r ) {
			$rid = isset( $r['id'] ) ? (int) $r['id'] : 0;
			if ( $rid !== $id && isset( $r['title'] ) && mb_strtolower( trim( $r['title'] ) ) === mb_strtolower( trim( $title ) ) ) {
				return new WP_Error( 'duplicate', 'نام ویژگی تکراری است', array( 'status' => 409 ) );
			}
		}

		$description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$status      = sanitize_key( (string) ( $request->get_param( 'status' ) ?: 'active' ) );
		$code        = (string) $request->get_param( 'code' );
		$parsed      = self::parse_code( $code );

		$fields = isset( $row['fields'] ) && is_array( $row['fields'] ) ? $row['fields'] : array();
		$fields['_code_html'] = $parsed['html'];
		$fields['_code_css']  = $parsed['css'];
		$fields['_code_js']   = $parsed['js'];

		$result = $manager->update( $id, array(
			'title'       => $title,
			'description' => $description,
			'status'      => $status,
			'fields'      => $fields,
			'code'        => $code,
		) );

		if ( empty( $result['success'] ) ) {
			return new WP_Error( 'update_failed', $result['message'] ?? 'خطا در به‌روزرسانی', array( 'status' => 500 ) );
		}

		$row = $manager->get( $id );
		return rest_ensure_response( array(
			'success' => true,
			'message' => 'ویژگی با موفقیت ذخیره شد',
			'item'    => self::format_template( $row, $manager ),
		) );
	}

	public static function delete_template( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager ) {
			return new WP_Error( 'no_module', 'ماژول ویژگی محصول در دسترس نیست', array( 'status' => 500 ) );
		}
		$id     = (int) $request['id'];
		$result = $manager->delete( $id );
		if ( empty( $result['success'] ) && $result !== true ) {
			$msg = is_array( $result ) ? ( $result['message'] ?? 'حذف ناموفق' ) : 'حذف ناموفق';
			return new WP_Error( 'delete_failed', $msg, array( 'status' => 500 ) );
		}
		return rest_ensure_response( array(
			'success' => true,
			'message' => 'ویژگی حذف شد',
		) );
	}

	public static function template_products( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		$q  = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_ezlens_option_slots',
					'value'   => '"template_id":' . $id,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_ezlens_option_template_id',
					'value'   => $id,
					'compare' => '=',
				),
			),
		) );
		$products = array();
		foreach ( $q->posts as $pid ) {
			$products[] = array(
				'id'    => (int) $pid,
				'title' => get_the_title( $pid ),
				'edit'  => get_edit_post_link( $pid, 'raw' ),
			);
		}
		return rest_ensure_response( array( 'items' => $products, 'total' => count( $products ) ) );
	}

	public static function get_product_slots( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager || ! method_exists( $manager, 'get_slots_service' ) ) {
			return new WP_Error( 'no_module', 'ماژول در دسترس نیست', array( 'status' => 500 ) );
		}
		$product_id = (int) $request['id'];
		$slots      = $manager->get_slots_service()->get_slots( $product_id );
		$enriched   = array();
		foreach ( $slots as $slot ) {
			$tid = (int) ( $slot['template_id'] ?? 0 );
			$tpl = $tid ? $manager->get( $tid ) : null;
			$enriched[] = array(
				'template_id' => $tid,
				'title'       => $tpl['title'] ?? '',
				'label'       => (string) ( $slot['label'] ?? '' ),
				'placement'   => (string) ( $slot['placement'] ?? 'below_summary' ),
				'display'     => (string) ( $slot['display'] ?? 'inline' ),
				'order'       => (int) ( $slot['order'] ?? 0 ),
			);
		}
		return rest_ensure_response( array( 'slots' => $enriched ) );
	}

	public static function save_product_slots( WP_REST_Request $request ) {
		$manager = self::manager();
		if ( ! $manager || ! method_exists( $manager, 'get_slots_service' ) ) {
			return new WP_Error( 'no_module', 'ماژول در دسترس نیست', array( 'status' => 500 ) );
		}
		$product_id = (int) $request['id'];
		$raw        = $request->get_param( 'slots' );
		if ( ! is_array( $raw ) ) {
			return new WP_Error( 'invalid', 'فرمت slots نامعتبر است', array( 'status' => 400 ) );
		}
		$slots = array();
		$order = 1;
		foreach ( $raw as $s ) {
			if ( ! is_array( $s ) ) continue;
			$tid = absint( $s['template_id'] ?? 0 );
			if ( ! $tid ) continue;

			$placement = sanitize_key( $s['placement'] ?? 'below_summary' );
			$display   = sanitize_key( $s['display'] ?? 'inline' );
			$allowed_p = array( 'gallery_side', 'below_price', 'below_summary', 'full_width' );
			$allowed_d = array( 'inline', 'accordion', 'ajax_modal' );
			if ( ! in_array( $placement, $allowed_p, true ) ) $placement = 'below_summary';
			if ( ! in_array( $display, $allowed_d, true ) )   $display   = 'inline';

			$slots[] = array(
				'template_id' => $tid,
				'label'       => sanitize_text_field( $s['label'] ?? '' ),
				'placement'   => $placement,
				'display'     => $display,
				'order'       => $order++,
			);
		}
		$result = $manager->get_slots_service()->save_slots( $product_id, $slots, true );
		if ( is_array( $result ) && isset( $result['success'] ) && ! $result['success'] ) {
			return new WP_Error( 'save_failed', $result['message'] ?? 'خطا در ذخیره‌سازی', array( 'status' => 500 ) );
		}
		return rest_ensure_response( array(
			'success' => true,
			'message' => 'تنظیمات با موفقیت به‌روزرسانی شد',
			'slots'   => $slots,
		) );
	}
}

EzLens_Product_Options_REST_API::init();