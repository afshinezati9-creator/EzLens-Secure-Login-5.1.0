<?php
/**
 * Manager Charity REST — هم‌یاری بینایی
 *
 * Cases, donations, impact updates via EzLens_CD_Charity.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Charity_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_users' );
	}

	private static function ensure() {
		$paths = array();
		if ( defined( 'EZLAUTH_MODULES_DIR' ) ) {
			$paths[] = EZLAUTH_MODULES_DIR . 'customer-dashboard/includes/class-charity.php';
		}
		$paths[] = dirname( __FILE__, 3 ) . '/customer-dashboard/includes/class-charity.php';
		foreach ( $paths as $p ) {
			if ( is_readable( $p ) ) {
				require_once $p;
				break;
			}
		}
		if ( class_exists( 'EzLens_CD_Charity' ) ) {
			EzLens_CD_Charity::maybe_create_tables();
		}
	}

	public static function register_routes() {
		register_rest_route( self::NS, '/manager/charity/stats', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'stats' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/charity/cases', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_cases' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'save_case' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/charity/cases/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_case' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_case' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_case' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/charity/donations', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'list_donations' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/charity/impact', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'add_impact' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/charity/need-types', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'need_types' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
	}

	private static function to_jalali( $mysql ) {
		$ts = strtotime( (string) $mysql );
		if ( ! $ts ) {
			return (string) $mysql;
		}
		if ( class_exists( 'EzLens_Inbox' ) && method_exists( 'EzLens_Inbox', 'to_jalali' ) ) {
			return EzLens_Inbox::to_jalali( $ts );
		}
		return date_i18n( 'Y-m-d H:i', $ts );
	}

	private static function need_label( $key ) {
		$labels = class_exists( 'EzLens_CD_Charity' ) ? EzLens_CD_Charity::need_labels() : array();
		return isset( $labels[ $key ] ) ? $labels[ $key ] : $key;
	}

	private static function map_case( $c ) {
		if ( ! $c ) {
			return null;
		}
		$goal   = (int) $c->goal_amount;
		$raised = (int) $c->raised_amount;
		$pct    = $goal > 0 ? min( 100, (int) round( ( $raised / $goal ) * 100 ) ) : 0;
		$cover  = '';
		if ( ! empty( $c->cover_id ) ) {
			$cover = (string) wp_get_attachment_url( (int) $c->cover_id );
		}
		return array(
			'id'            => (int) $c->id,
			'title'         => (string) $c->title,
			'summary'       => (string) ( $c->summary ?? '' ),
			'need_type'     => (string) $c->need_type,
			'need_label'    => self::need_label( $c->need_type ),
			'goal_amount'   => $goal,
			'raised_amount' => $raised,
			'percent'       => $pct,
			'status'        => (string) $c->status,
			'is_public'     => ! empty( $c->is_public ),
			'cover_url'     => $cover,
			'created_at'    => (string) ( $c->created_at ?? '' ),
			'created_fa'    => self::to_jalali( $c->created_at ?? '' ),
			'updated_at'    => (string) ( $c->updated_at ?? '' ),
			'updated_fa'    => self::to_jalali( $c->updated_at ?? '' ),
		);
	}

	private static function map_donation( $d ) {
		$user = null;
		if ( ! empty( $d->user_id ) ) {
			$u = get_userdata( (int) $d->user_id );
			$phone = get_user_meta( (int) $d->user_id, 'billing_phone', true );
			if ( ! $phone ) {
				$phone = get_user_meta( (int) $d->user_id, 'user_phone', true );
			}
			$user = array(
				'id'           => (int) $d->user_id,
				'display_name' => $u ? $u->display_name : '',
				'email'        => $u ? $u->user_email : '',
				'login'        => $u ? $u->user_login : '',
				'phone'        => (string) $phone,
			);
		}
		return array(
			'id'           => (int) $d->id,
			'user_id'      => (int) $d->user_id,
			'case_id'      => (int) $d->case_id,
			'case_title'   => (string) ( $d->case_title ?? '' ),
			'amount'       => (int) $d->amount,
			'source'       => (string) ( $d->source ?? 'wallet' ),
			'is_anonymous' => ! empty( $d->is_anonymous ),
			'message'      => (string) ( $d->message ?? '' ),
			'status'       => (string) ( $d->status ?? 'confirmed' ),
			'created_at'   => (string) ( $d->created_at ?? '' ),
			'created_fa'   => self::to_jalali( $d->created_at ?? '' ),
			'user'         => $user,
		);
	}

	public static function stats( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Charity' ) ) {
			return new WP_Error( 'no_charity', 'ماژول هم‌یاری بارگذاری نشده', array( 'status' => 500 ) );
		}
		$s = EzLens_CD_Charity::stats();
		return rest_ensure_response( array( 'ok' => true, 'stats' => is_array( $s ) ? $s : array() ) );
	}

	public static function need_types( WP_REST_Request $request ) {
		self::ensure();
		$labels = class_exists( 'EzLens_CD_Charity' ) ? EzLens_CD_Charity::need_labels() : array();
		$items  = array();
		foreach ( $labels as $k => $v ) {
			$items[] = array( 'key' => $k, 'label' => $v );
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}

	public static function list_cases( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Charity' ) ) {
			return new WP_Error( 'no_charity', 'ماژول هم‌یاری بارگذاری نشده', array( 'status' => 500 ) );
		}
		$status = sanitize_key( $request->get_param( 'status' ) ?: 'all' );
		$limit  = max( 5, min( 100, (int) ( $request->get_param( 'per_page' ) ?: 50 ) ) );
		$rows   = EzLens_CD_Charity::admin_cases( $limit );
		$items  = array();
		foreach ( (array) $rows as $c ) {
			if ( $status !== 'all' && (string) $c->status !== $status ) {
				continue;
			}
			$items[] = self::map_case( $c );
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}

	public static function get_case( WP_REST_Request $request ) {
		self::ensure();
		$id = (int) $request['id'];
		$c  = EzLens_CD_Charity::get_case( $id );
		if ( ! $c ) {
			return new WP_Error( 'not_found', 'مورد یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'case' => self::map_case( $c ) ) );
	}

	public static function save_case( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Charity' ) ) {
			return new WP_Error( 'no_charity', 'ماژول هم‌یاری بارگذاری نشده', array( 'status' => 500 ) );
		}
		$data = array(
			'title'       => $request->get_param( 'title' ),
			'summary'     => $request->get_param( 'summary' ),
			'need_type'   => $request->get_param( 'need_type' ) ?: 'glasses',
			'goal_amount' => $request->get_param( 'goal_amount' ),
			'status'      => $request->get_param( 'status' ) ?: 'open',
			'is_public'   => $request->get_param( 'is_public' ) ? 1 : 0,
		);
		$r = EzLens_CD_Charity::save_case( $data, 0 );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$c = EzLens_CD_Charity::get_case( (int) $r );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'مورد ذخیره شد',
				'case'    => self::map_case( $c ),
			)
		);
	}

	public static function update_case( WP_REST_Request $request ) {
		self::ensure();
		$id   = (int) $request['id'];
		$data = array();
		foreach ( array( 'title', 'summary', 'need_type', 'goal_amount', 'status' ) as $k ) {
			if ( null !== $request->get_param( $k ) ) {
				$data[ $k ] = $request->get_param( $k );
			}
		}
		if ( null !== $request->get_param( 'is_public' ) ) {
			$data['is_public'] = $request->get_param( 'is_public' ) ? 1 : 0;
		}
		$r = EzLens_CD_Charity::save_case( $data, $id );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$c = EzLens_CD_Charity::get_case( $id );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'مورد به‌روز شد',
				'case'    => self::map_case( $c ),
			)
		);
	}

	public static function delete_case( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$id = (int) $request['id'];
		$n  = $wpdb->delete( EzLens_CD_Charity::cases_table(), array( 'id' => $id ), array( '%d' ) );
		if ( ! $n ) {
			return new WP_Error( 'delete_failed', 'حذف ناموفق', array( 'status' => 400 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'مورد حذف شد' ) );
	}

	public static function list_donations( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Charity' ) ) {
			return new WP_Error( 'no_charity', 'ماژول هم‌یاری بارگذاری نشده', array( 'status' => 500 ) );
		}
		$limit = max( 5, min( 100, (int) ( $request->get_param( 'per_page' ) ?: 40 ) ) );
		$rows  = EzLens_CD_Charity::admin_donations( $limit );
		$items = array();
		foreach ( (array) $rows as $d ) {
			$items[] = self::map_donation( $d );
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}

	public static function add_impact( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Charity' ) ) {
			return new WP_Error( 'no_charity', 'ماژول هم‌یاری بارگذاری نشده', array( 'status' => 500 ) );
		}
		$data = array(
			'donation_id'       => absint( $request->get_param( 'donation_id' ) ),
			'spent_amount'      => absint( $request->get_param( 'spent_amount' ) ),
			'beneficiary_label' => sanitize_text_field( $request->get_param( 'beneficiary_label' ) ?: '' ),
			'purpose'           => sanitize_textarea_field( $request->get_param( 'purpose' ) ?: '' ),
			'thank_you'         => sanitize_textarea_field( $request->get_param( 'thank_you' ) ?: '' ),
			'attachment_id'     => 0,
		);

		$b64      = $request->get_param( 'file_base64' );
		$filename = sanitize_file_name( $request->get_param( 'file_name' ) ?: 'impact.bin' );
		if ( is_string( $b64 ) && $b64 !== '' ) {
			$att = self::sideload_base64( $b64, $filename );
			if ( is_wp_error( $att ) ) {
				return $att;
			}
			$data['attachment_id'] = $att;
		}

		$r = EzLens_CD_Charity::add_update( $data );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'گزارش اثر ثبت شد',
				'id'      => (int) $r,
			)
		);
	}

	private static function sideload_base64( $b64, $filename ) {
		if ( strpos( $b64, ',' ) !== false ) {
			$b64 = explode( ',', $b64, 2 )[1];
		}
		$bin = base64_decode( $b64 );
		if ( false === $bin ) {
			return new WP_Error( 'bad_file', 'فایل نامعتبر', array( 'status' => 400 ) );
		}
		$upload = wp_upload_bits( $filename, null, $bin );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'], array( 'status' => 500 ) );
		}
		$filetype   = wp_check_filetype( $filename );
		$attachment = array(
			'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'application/octet-stream',
			'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $meta );
		return (int) $attach_id;
	}
}

EzLens_Manager_Charity_REST::init();
