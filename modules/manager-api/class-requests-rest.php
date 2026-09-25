<?php
/**
 * Manager Requests REST — EI Form Builder (ei_requests).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Requests_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' );
	}

	public static function register_routes() {
		register_rest_route( self::NS, '/manager/requests', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'list_requests' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/requests/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_request' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_request' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/requests/(?P<id>\d+)/status', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'update_status' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/requests/(?P<id>\d+)/notes', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_notes' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'add_note' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
	}

	private static function tables_ok() {
		global $wpdb;
		$t = $wpdb->prefix . 'ei_requests';
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t;
	}

	private static function form_title( $form_id ) {
		global $wpdb;
		$title = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT title FROM {$wpdb->prefix}ei_forms WHERE id = %d",
				(int) $form_id
			)
		);
		return $title ? (string) $title : '—';
	}

	private static function attachments_for( $request_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ei_attachments';
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $found !== $table ) {
			return array();
		}
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, file_name, file_path, file_size, mime_type FROM {$table} WHERE request_id = %d",
				(int) $request_id
			),
			ARRAY_A
		);
		$out = array();
		$upload = wp_upload_dir();
		foreach ( (array) $rows as $r ) {
			$path = isset( $r['file_path'] ) ? (string) $r['file_path'] : '';
			$url  = '';
			if ( $path && strpos( $path, 'http' ) === 0 ) {
				$url = $path;
			} elseif ( $path && ! empty( $upload['baseurl'] ) ) {
				$url = trailingslashit( $upload['baseurl'] ) . ltrim( str_replace( (string) $upload['basedir'], '', $path ), '/\\' );
			}
			$out[] = array(
				'id'        => (int) $r['id'],
				'name'      => (string) ( $r['file_name'] ?? '' ),
				'url'       => $url,
				'size'      => (int) ( $r['file_size'] ?? 0 ),
				'mime_type' => (string) ( $r['mime_type'] ?? '' ),
			);
		}
		return $out;
	}

	private static function map_row( $row, $with_attachments = false ) {
		$form_data = $row['form_data'] ?? '';
		if ( is_string( $form_data ) && $form_data !== '' ) {
			$decoded = json_decode( $form_data, true );
			if ( is_array( $decoded ) ) {
				$form_data = $decoded;
			}
		}
		$item = array(
			'id'         => (int) $row['id'],
			'form_id'    => (int) $row['form_id'],
			'form_title' => self::form_title( (int) $row['form_id'] ),
			'name'       => (string) ( $row['name'] ?? '' ),
			'email'      => (string) ( $row['email'] ?? '' ),
			'phone'      => (string) ( $row['phone'] ?? '' ),
			'message'    => (string) ( $row['message'] ?? '' ),
			'status'     => (string) ( $row['status'] ?? 'new' ),
			'ip'         => (string) ( $row['ip'] ?? '' ),
			'created_at' => (string) ( $row['created_at'] ?? '' ),
			'form_data'  => $form_data,
		);
		if ( $with_attachments ) {
			$item['attachments'] = self::attachments_for( (int) $row['id'] );
		}
		return $item;
	}

	private static function notes_key( $id ) {
		return 'ezlens_ei_request_notes_' . (int) $id;
	}

	public static function list_requests( WP_REST_Request $request ) {
		if ( ! self::tables_ok() ) {
			return new WP_Error(
				'ei_missing',
				'جدول درخواست‌های EI یافت نشد. افزونه فرم‌ساز EI را فعال کنید.',
				array( 'status' => 503 )
			);
		}

		global $wpdb;
		$table    = $wpdb->prefix . 'ei_requests';
		$page     = max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) );
		$per_page = max( 1, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 10 ) ) );
		$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );
		$status   = sanitize_text_field( (string) $request->get_param( 'status' ) );
		$form_id  = (int) ( $request->get_param( 'form_id' ) ?: 0 );
		$form_title = sanitize_text_field( (string) $request->get_param( 'form_title' ) );
		$date_from = sanitize_text_field( (string) $request->get_param( 'date_from' ) );
		$date_to   = sanitize_text_field( (string) $request->get_param( 'date_to' ) );
		$offset   = ( $page - 1 ) * $per_page;

		$where  = '1=1';
		$params = array();

		if ( $search !== '' ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= ' AND ( name LIKE %s OR email LIKE %s OR phone LIKE %s OR message LIKE %s )';
			array_push( $params, $like, $like, $like, $like );
		}
		if ( $status !== '' && $status !== 'all' ) {
			$where .= ' AND status = %s';
			$params[] = $status;
		}
		if ( $form_id > 0 ) {
			$where .= ' AND form_id = %d';
			$params[] = $form_id;
		}
		if ( $form_title !== '' ) {
			$where .= " AND form_id IN (SELECT id FROM {$wpdb->prefix}ei_forms WHERE title LIKE %s)";
			$params[] = '%' . $wpdb->esc_like( $form_title ) . '%';
		}
		if ( $date_from !== '' ) {
			$where .= ' AND created_at >= %s';
			$params[] = $date_from . ( strlen( $date_from ) <= 10 ? ' 00:00:00' : '' );
		}
		if ( $date_to !== '' ) {
			$where .= ' AND created_at <= %s';
			$params[] = $date_to . ( strlen( $date_to ) <= 10 ? ' 23:59:59' : '' );
		}

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		$total = empty( $params )
			? (int) $wpdb->get_var( $count_sql )
			: (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$list_sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d";
		$list_params = array_merge( $params, array( $per_page, $offset ) );
		$rows = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ), ARRAY_A );

		$items = array();
		foreach ( (array) $rows as $row ) {
			$items[] = self::map_row( $row, false );
		}

		return rest_ensure_response(
			array(
				'ok'          => true,
				'items'       => $items,
				'total'       => $total,
				'total_pages' => (int) max( 1, ceil( max( 1, $total ) / $per_page ) ),
				'page'        => $page,
				'per_page'    => $per_page,
			)
		);
	}

	public static function get_request( WP_REST_Request $request ) {
		if ( ! self::tables_ok() ) {
			return new WP_Error( 'ei_missing', 'جدول EI یافت نشد', array( 'status' => 503 ) );
		}
		global $wpdb;
		$id  = (int) $request['id'];
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ei_requests WHERE id = %d", $id ),
			ARRAY_A
		);
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'درخواست یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'item' => self::map_row( $row, true ) ) );
	}

	public static function delete_request( WP_REST_Request $request ) {
		if ( ! self::tables_ok() ) {
			return new WP_Error( 'ei_missing', 'جدول EI یافت نشد', array( 'status' => 503 ) );
		}
		global $wpdb;
		$id = (int) $request['id'];
		$wpdb->delete( $wpdb->prefix . 'ei_attachments', array( 'request_id' => $id ) );
		$ok = $wpdb->delete( $wpdb->prefix . 'ei_requests', array( 'id' => $id ) );
		delete_option( self::notes_key( $id ) );
		return rest_ensure_response( array( 'ok' => (bool) $ok ) );
	}

	public static function update_status( WP_REST_Request $request ) {
		if ( ! self::tables_ok() ) {
			return new WP_Error( 'ei_missing', 'جدول EI یافت نشد', array( 'status' => 503 ) );
		}
		global $wpdb;
		$id     = (int) $request['id'];
		$status = sanitize_text_field( (string) $request->get_param( 'status' ) );
		if ( $status === '' ) {
			return new WP_Error( 'invalid', 'وضعیت خالی است', array( 'status' => 400 ) );
		}
		$wpdb->update(
			$wpdb->prefix . 'ei_requests',
			array( 'status' => $status ),
			array( 'id' => $id )
		);
		return self::get_request( $request );
	}

	public static function list_notes( WP_REST_Request $request ) {
		$id    = (int) $request['id'];
		$notes = get_option( self::notes_key( $id ), array() );
		if ( ! is_array( $notes ) ) {
			$notes = array();
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => array_values( $notes ) ) );
	}

	public static function add_note( WP_REST_Request $request ) {
		$id   = (int) $request['id'];
		$text = sanitize_textarea_field( (string) $request->get_param( 'text' ) );
		if ( $text === '' ) {
			return new WP_Error( 'invalid', 'متن یادداشت خالی است', array( 'status' => 400 ) );
		}
		$user  = wp_get_current_user();
		$note  = array(
			'id'         => time() + wp_rand( 1, 99 ),
			'text'       => $text,
			'author'     => $user && $user->ID ? $user->display_name : 'مدیر',
			'created_at' => current_time( 'mysql' ),
		);
		$notes = get_option( self::notes_key( $id ), array() );
		if ( ! is_array( $notes ) ) {
			$notes = array();
		}
		$notes[] = $note;
		update_option( self::notes_key( $id ), $notes, false );
		return rest_ensure_response( array( 'ok' => true, 'note' => $note ) );
	}
}

EzLens_Manager_Requests_REST::init();
