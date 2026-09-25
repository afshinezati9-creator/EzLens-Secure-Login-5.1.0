<?php
/**
 * Loader for active Purchase Process scripts.
 *
 * IMPORTANT: Never auto-deactivate scripts on load conflict / missing file.
 * Auto-deactivate caused "activate then refresh → inactive" on production.
 *
 * @package EzLens_Secure_Login
 * @subpackage Purchase_Process
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Purchase_Process_Loader {

	private static $instance = null;
	private $loaded = false;

	/** @var string[] functions loaded by snippets in this request */
	private $loaded_functions = array();

	/** @var string[] human-readable skip reasons (this request) */
	private $skip_notices = array();

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_active_files' ), 20 );
		add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
	}

	private function ensure_database_ready() {
		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			$install_file = dirname( __FILE__ ) . '/class-install.php';
			if ( file_exists( $install_file ) ) {
				require_once $install_file;
			}
		}

		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return false;
		}

		if ( EzLens_Purchase_Process_Install::is_ready() ) {
			return true;
		}

		error_log( 'EzLens Purchase Process: DB not ready, trying force_create_table...' );
		$result = EzLens_Purchase_Process_Install::force_create_table();
		return (bool) $result;
	}

	private function get_storage_dir() {
		if ( class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return EzLens_Purchase_Process_Install::get_storage_dir();
		}
		return trailingslashit( dirname( __FILE__ ) ) . 'storage/';
	}

	/**
	 * Extract global function names from PHP source (including inside function_exists guards).
	 *
	 * @param string $code
	 * @return string[]
	 */
	private function extract_function_names( $code ) {
		$names = array();
		if ( preg_match_all( '/\bfunction\s+&?([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $code, $m ) ) {
			$names = $m[1];
		}
		return array_values( array_unique( array_filter( $names ) ) );
	}

	/**
	 * Log skip reason without changing DB status.
	 *
	 * @param int    $id
	 * @param string $reason
	 */
	private function skip_item( $id, $reason ) {
		$msg = 'EzLens Purchase Process: skip #' . (int) $id . ' — ' . $reason;
		error_log( $msg );
		$this->skip_notices[] = $msg;
		// Keep last errors for admin (max 10)
		$prev = get_option( 'ezlens_pp_loader_skips', array() );
		if ( ! is_array( $prev ) ) {
			$prev = array();
		}
		array_unshift( $prev, array( 'time' => time(), 'id' => (int) $id, 'reason' => $reason ) );
		$prev = array_slice( $prev, 0, 10 );
		update_option( 'ezlens_pp_loader_skips', $prev, false );
	}

	/**
	 * @deprecated Kept for BC — no longer used to flip status.
	 */
	private function deactivate_item( $table, $id, $reason ) {
		$this->skip_item( $id, $reason . ' (status NOT changed)' );
	}

	public function render_admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( $page !== 'ezlens-purchase-process' && strpos( $page, 'ezlens-purchase' ) === false ) {
			return;
		}
		$skips = get_option( 'ezlens_pp_loader_skips', array() );
		if ( empty( $skips ) || ! is_array( $skips ) ) {
			return;
		}
		// Only show recent (last 10 minutes)
		$recent = array();
		$now    = time();
		foreach ( $skips as $row ) {
			if ( empty( $row['time'] ) || ( $now - (int) $row['time'] ) > 600 ) {
				continue;
			}
			$recent[] = $row;
		}
		if ( empty( $recent ) ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p><strong>فرآیند خرید — هشدار بارگذاری:</strong></p><ul style="list-style:disc;margin:8px 0 8px 20px;">';
		foreach ( $recent as $row ) {
			echo '<li>' . esc_html( '#' . (int) $row['id'] . ' — ' . (string) $row['reason'] ) . '</li>';
		}
		echo '</ul><p>وضعیت «فعال» در دیتابیس تغییر نکرده است. فایل را در <code>storage/</code> بررسی کنید یا تداخل با Code Snippets را رفع کنید.</p></div>';
	}

	public function load_active_files() {
		if ( $this->loaded ) {
			return;
		}
		$this->loaded = true;

		if ( ! $this->ensure_database_ready() ) {
			error_log( 'EzLens Purchase Process: DB not ready; loader stopped.' );
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ezlens_purchase_scripts';

		$items = $wpdb->get_results(
			"SELECT id, title, filename, status FROM `{$table}` WHERE status = 1 ORDER BY id ASC"
		);

		if ( null === $items && ! empty( $wpdb->last_error ) ) {
			$lower            = strtolower( $table );
			$wpdb->last_error = '';
			$items            = $wpdb->get_results(
				"SELECT id, title, filename, status FROM `{$lower}` WHERE status = 1 ORDER BY id ASC"
			);
			if ( is_array( $items ) && ! empty( $items ) ) {
				$table = $lower;
			}
		}

		if ( null === $items && ! empty( $wpdb->last_error ) ) {
			error_log( 'EzLens Purchase Process: loader DB error — ' . $wpdb->last_error );
			return;
		}

		if ( empty( $items ) ) {
			return;
		}

		$storage_dir = trailingslashit( $this->get_storage_dir() );

		foreach ( $items as $item ) {
			$filename = basename( sanitize_file_name( $item->filename ) );

			if ( ! preg_match( '/^[a-zA-Z0-9._-]+\.php$/', $filename ) ) {
				$this->skip_item( $item->id, 'Invalid filename: ' . $filename );
				continue;
			}

			$file_path = $storage_dir . $filename;

			if ( ! file_exists( $file_path ) ) {
				// Do NOT deactivate — keep UI status; admin must upload file
				$this->skip_item(
					$item->id,
					'Physical file missing in storage: ' . $filename . ' (looked in ' . $storage_dir . ')'
				);
				continue;
			}

			$real_storage = realpath( $storage_dir );
			$real_file    = realpath( $file_path );

			if ( false === $real_storage || false === $real_file ) {
				$this->skip_item( $item->id, 'Invalid realpath for ' . $filename );
				continue;
			}

			$storage_prefix  = trailingslashit( wp_normalize_path( $real_storage ) );
			$normalized_file = wp_normalize_path( $real_file );

			if ( 0 !== strpos( $normalized_file, $storage_prefix ) ) {
				$this->skip_item( $item->id, 'File outside storage dir: ' . $filename );
				continue;
			}

			if ( ! is_readable( $real_file ) ) {
				$this->skip_item( $item->id, 'File not readable: ' . $filename );
				continue;
			}

			$code = @file_get_contents( $real_file );
			if ( false === $code ) {
				$this->skip_item( $item->id, 'Cannot read file: ' . $filename );
				continue;
			}

			$fn_names = $this->extract_function_names( $code );
			$conflicts = array();

			foreach ( $fn_names as $fn ) {
				if ( function_exists( $fn ) || isset( $this->loaded_functions[ $fn ] ) ) {
					$conflicts[] = $fn;
				}
			}

			if ( ! empty( $conflicts ) ) {
				// Safe skip: product_page wraps all in function_exists — include_once is still OK
				// only if EVERY conflicting name already exists (already loaded elsewhere)
				$all_exist = true;
				foreach ( $fn_names as $fn ) {
					if ( ! function_exists( $fn ) && ! isset( $this->loaded_functions[ $fn ] ) ) {
						$all_exist = false;
						break;
					}
				}

				if ( $all_exist ) {
					// Already provided by another source (snippet / earlier file) — keep active
					error_log(
						'EzLens Purchase Process: #' . (int) $item->id .
						' functions already defined; skip include, keep status=1 (' . $filename . ')'
					);
					continue;
				}

				// Partial conflict: include_once may still work because of function_exists guards.
				// Try include_once; do not deactivate on conflict.
				error_log(
					'EzLens Purchase Process: partial function overlap for #' . (int) $item->id .
					' (' . implode( ', ', array_slice( $conflicts, 0, 5 ) ) . '...) — trying include_once'
				);
			}

			$before      = get_defined_functions();
			$before_user = isset( $before['user'] ) ? $before['user'] : array();

			try {
				include_once $real_file;
			} catch ( Throwable $e ) {
				$this->skip_item(
					$item->id,
					'Load error in "' . $filename . '": ' . $e->getMessage()
				);
				continue;
			}

			$after      = get_defined_functions();
			$after_user = isset( $after['user'] ) ? $after['user'] : array();
			$new_fns    = array_diff( $after_user, $before_user );
			foreach ( $new_fns as $fn ) {
				$this->loaded_functions[ $fn ] = $filename;
			}
			foreach ( $fn_names as $fn ) {
				$this->loaded_functions[ $fn ] = $filename;
			}
		}
	}
}

EzLens_Purchase_Process_Loader::get_instance();
