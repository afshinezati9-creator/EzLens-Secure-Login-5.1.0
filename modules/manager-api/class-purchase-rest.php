<?php
/**
 * Manager Purchase Process REST — manage storage scripts.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Purchase_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' );
	}

	private static function ensure() {
		$dir = defined( 'EZLAUTH_MODULES_DIR' )
			? EZLAUTH_MODULES_DIR . 'purchase-process/'
			: dirname( __FILE__, 3 ) . '/purchase-process/';
		foreach ( array( 'class-install.php', 'class-ajax.php', 'class-loader.php' ) as $f ) {
			$p = $dir . $f;
			if ( is_readable( $p ) ) {
				require_once $p;
			}
		}
		if ( class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			EzLens_Purchase_Process_Install::maybe_install();
		}
	}

	private static function table() {
		if ( class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return EzLens_Purchase_Process_Install::get_table_name();
		}
		global $wpdb;
		return $wpdb->prefix . 'ezlens_purchase_scripts';
	}

	private static function storage_dir() {
		if ( class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return EzLens_Purchase_Process_Install::get_storage_dir();
		}
		$dir = defined( 'EZLAUTH_MODULES_DIR' )
			? EZLAUTH_MODULES_DIR . 'purchase-process/storage/'
			: dirname( __FILE__, 3 ) . '/purchase-process/storage/';
		return $dir;
	}

	private static function file_path( $filename ) {
		$filename = basename( sanitize_file_name( $filename ) );
		if ( ! preg_match( '/\.php$/i', $filename ) ) {
			$filename .= '.php';
		}
		$dir = self::storage_dir();
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		return trailingslashit( $dir ) . $filename;
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

	private static function map_row( $row, $with_code = false ) {
		$item = array(
			'id'          => (int) $row->id,
			'title'       => (string) $row->title,
			'filename'    => (string) $row->filename,
			'description' => (string) ( $row->description ?? '' ),
			'status'      => (int) $row->status,
			'active'      => (int) $row->status === 1,
			'created_at'  => (string) ( $row->created_at ?? '' ),
			'created_fa'  => self::to_jalali( $row->created_at ?? '' ),
			'updated_at'  => (string) ( $row->updated_at ?? '' ),
			'updated_fa'  => self::to_jalali( $row->updated_at ?? '' ),
		);
		if ( $with_code ) {
			$path = self::file_path( $row->filename );
			$code = '';
			if ( is_readable( $path ) ) {
				$code = (string) file_get_contents( $path );
			}
			$item['code'] = $code;
			$item['file_exists'] = is_readable( $path );
		}
		return $item;
	}

	public static function register_routes() {
		register_rest_route( self::NS, '/manager/purchase/scripts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_scripts' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'save_script' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/purchase/scripts/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_script' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_script' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_script' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/purchase/scripts/(?P<id>\d+)/toggle', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'toggle_script' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/purchase/stats', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'stats' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
	}

	public static function list_scripts( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$table  = self::table();
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 30 ) ) );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$status = $request->get_param( 'status' ); // all|1|0

		$where  = array( '1=1' );
		$params = array();
		if ( $search !== '' ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(title LIKE %s OR filename LIKE %s OR description LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		if ( $status === '1' || $status === '0' || $status === 1 || $status === 0 ) {
			$where[]  = 'status = %d';
			$params[] = (int) $status;
		}
		$where_sql = implode( ' AND ', $where );
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

		$offset   = ( $page - 1 ) * $per;
		$list_sql = "SELECT id, title, filename, description, status, created_at, updated_at FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
		$list_args = array_merge( $params, array( $per, $offset ) );
		$rows = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ) );

		$items = array();
		foreach ( (array) $rows as $row ) {
			$items[] = self::map_row( $row, false );
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

	public static function get_script( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$id  = (int) $request['id'];
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'اسکریپت یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'script' => self::map_row( $row, true ) ) );
	}

	public static function save_script( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$table = self::table();

		$title       = sanitize_text_field( $request->get_param( 'title' ) ?: '' );
		$description = sanitize_textarea_field( $request->get_param( 'description' ) ?: '' );
		$filename_in = sanitize_text_field( $request->get_param( 'filename' ) ?: '' );
		$code        = (string) $request->get_param( 'code' );
		$activate    = $request->get_param( 'activate' ) || $request->get_param( 'status' ) ? 1 : 0;

		$title = trim( $title );
		if ( $title === '' ) {
			return new WP_Error( 'title', 'عنوان نمایشی الزامی است', array( 'status' => 400 ) );
		}
		$code = trim( $code );
		if ( $code === '' ) {
			return new WP_Error( 'code', 'کد PHP خالی است', array( 'status' => 400 ) );
		}
		if ( 0 !== strpos( $code, '<?php' ) ) {
			$code = "<?php\n" . $code;
		}

		$filename_in = trim( $filename_in );
		$filename_in = preg_replace( '/\.php$/i', '', $filename_in );
		$filename_in = sanitize_file_name( $filename_in );
		$filename_in = preg_replace( '/[^A-Za-z0-9_-]/', '', $filename_in );
		if ( $filename_in === '' ) {
			return new WP_Error( 'filename', 'نام فایل انگلیسی الزامی است', array( 'status' => 400 ) );
		}
		$filename = $filename_in . '.php';

		$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $table . ' WHERE filename = %s', $filename ) );
		if ( $exists ) {
			return new WP_Error( 'conflict', 'این نام فایل از قبل وجود دارد', array( 'status' => 400 ) );
		}

		$path = self::file_path( $filename );
		$saved = @file_put_contents( $path, $code, LOCK_EX );
		if ( false === $saved ) {
			return new WP_Error( 'write', 'خطا در نوشتن فایل storage', array( 'status' => 500 ) );
		}

		$now = current_time( 'mysql' );
		$ok  = $wpdb->insert(
			$table,
			array(
				'title'       => $title,
				'filename'    => $filename,
				'description' => $description,
				'status'      => $activate,
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);
		if ( ! $ok ) {
			@unlink( $path );
			return new WP_Error( 'db', 'ذخیره در دیتابیس ناموفق', array( 'status' => 500 ) );
		}
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $wpdb->insert_id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'اسکریپت ذخیره شد',
				'script'  => self::map_row( $row, true ),
			)
		);
	}

	public static function update_script( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$table = self::table();
		$id    = (int) $request['id'];
		$row   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $id ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'اسکریپت یافت نشد', array( 'status' => 404 ) );
		}

		$data = array( 'updated_at' => current_time( 'mysql' ) );
		if ( null !== $request->get_param( 'title' ) ) {
			$t = trim( sanitize_text_field( $request->get_param( 'title' ) ) );
			if ( $t === '' ) {
				return new WP_Error( 'title', 'عنوان خالی مجاز نیست', array( 'status' => 400 ) );
			}
			$data['title'] = $t;
		}
		if ( null !== $request->get_param( 'description' ) ) {
			$data['description'] = sanitize_textarea_field( $request->get_param( 'description' ) );
		}
		if ( null !== $request->get_param( 'status' ) || null !== $request->get_param( 'activate' ) ) {
			$st = $request->get_param( 'status' );
			if ( null === $st ) {
				$st = $request->get_param( 'activate' ) ? 1 : 0;
			}
			$data['status'] = (int) $st ? 1 : 0;
		}

		$code = $request->get_param( 'code' );
		if ( is_string( $code ) && trim( $code ) !== '' ) {
			$code = trim( $code );
			if ( 0 !== strpos( $code, '<?php' ) ) {
				$code = "<?php\n" . $code;
			}
			$path = self::file_path( $row->filename );
			$saved = @file_put_contents( $path, $code, LOCK_EX );
			if ( false === $saved ) {
				return new WP_Error( 'write', 'خطا در نوشتن فایل', array( 'status' => 500 ) );
			}
		}

		$wpdb->update( $table, $data, array( 'id' => $id ) );
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'به‌روز شد',
				'script'  => self::map_row( $row, true ),
			)
		);
	}

	public static function toggle_script( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$table = self::table();
		$id    = (int) $request['id'];
		$row   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $id ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'اسکریپت یافت نشد', array( 'status' => 404 ) );
		}
		$new = (int) $row->status === 1 ? 0 : 1;
		$wpdb->update(
			$table,
			array(
				'status'     => $new,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id )
		);
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => $new ? 'فعال شد' : 'غیرفعال شد',
				'script'  => self::map_row( $row, false ),
			)
		);
	}

	public static function delete_script( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$table = self::table();
		$id    = (int) $request['id'];
		$row   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $id ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'اسکریپت یافت نشد', array( 'status' => 404 ) );
		}
		$path = self::file_path( $row->filename );
		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		if ( is_file( $path ) ) {
			@unlink( $path );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'حذف شد' ) );
	}

	public static function stats( WP_REST_Request $request ) {
		self::ensure();
		global $wpdb;
		$table = self::table();
		$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$active = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 1" );
		return rest_ensure_response(
			array(
				'ok'       => true,
				'total'    => $total,
				'active'   => $active,
				'inactive' => max( 0, $total - $active ),
			)
		);
	}
}

EzLens_Manager_Purchase_REST::init();
