<?php
/**
 * لودر صفحات ورود — ویرایشگر یکپارچه مثل فرآیند خرید
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Login_Pages_Loader {

	private static $instance = null;

	/** نگاشت فایل هسته → شورت‌کد */
	const CORE_MAP = array(
		'customer-login.php' => 'minimal_auth',
		'admin-login.php'    => 'admin_login_page',
		'lost-password.php'  => 'ezlens_lost_password',
	);

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$base = dirname( __FILE__ ) . '/';
		require_once $base . 'class-install.php';
		require_once $base . 'class-ajax.php';

		add_action( 'init', array( 'EzLens_Login_Pages_Install', 'maybe_install' ), 4 );
		// seed هسته
		add_action( 'init', array( $this, 'seed_core_files' ), 5 );
		add_action( 'admin_init', array( $this, 'seed_core_files' ), 5 );
		EzLens_Login_Pages_Ajax::get_instance();

		add_action( 'init', array( $this, 'register_shortcodes' ), 20 );
		// فایل‌های سفارشی (غیر از سه هسته) مثل فرآیند خرید روی init لود می‌شوند
		add_action( 'init', array( $this, 'load_custom_active_files' ), 25 );

		add_action( 'admin_menu', array( $this, 'register_menu' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}

	private function storage_dir() {
		return EzLens_Login_Pages_Install::get_storage_dir();
	}

	private function table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_login_scripts';
	}

	public function seed_core_files() {
		if ( class_exists( 'EzLens_Login_Pages_Install' ) && method_exists( 'EzLens_Login_Pages_Install', 'seed_defaults' ) ) {
			return EzLens_Login_Pages_Install::seed_defaults();
		}
		return false;
	}

	/**
	 * ساخت PHP یکپارچه از defaults قدیمی (html+css+js)
	 */
	private function build_seed_php( $slug ) {
		$html = $css = $js = '';
		$base = defined( 'EZLAUTH_TEMPLATES_DIR' ) ? EZLAUTH_TEMPLATES_DIR . 'defaults/' : '';
		if ( $base ) {
			foreach ( array( 'html', 'css', 'js' ) as $t ) {
				$f = $base . $slug . '.' . $t;
				if ( is_readable( $f ) ) {
					$$t = file_get_contents( $f );
				}
			}
		}
		// مهاجرت option
		$opt = get_option( 'ezlens_auth_codes_' . $slug, array() );
		if ( is_array( $opt ) ) {
			if ( ! empty( $opt['html'] ) ) {
				$html = $opt['html'];
			}
			if ( ! empty( $opt['css'] ) ) {
				$css = $opt['css'];
			}
			if ( ! empty( $opt['js'] ) ) {
				$js = $opt['js'];
			}
		}
		$out  = "<?php\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n";
		$out .= "// EzLens Login Page: {$slug}\n";
		$out .= "\$ezlp_primary = class_exists( 'EzLens_Auth_Settings' ) ? ( EzLens_Auth_Settings::get( 'primary_color' ) ?: '#031f8a' ) : '#031f8a';\n";
		if ( $css !== '' ) {
			$out .= "?>\n<style id=\"ezlens-lp-{$slug}-css\">\n" . $css . "\n</style>\n<?php\n";
		}
		if ( $html !== '' ) {
			$out .= "?>\n" . $html . "\n<?php\n";
		} else {
			$out .= "// قالب خالی — می‌توانید HTML/PHP را اینجا بنویسید.\n";
			$out .= "echo '<div class=\"ezlens-login-placeholder\" style=\"padding:40px;text-align:center;font-family:Tahoma,sans-serif;\">صفحه {$slug}</div>';\n";
		}
		if ( $js !== '' ) {
			$out .= "?>\n<script id=\"ezlens-lp-{$slug}-js\">\n" . $js . "\n</script>\n<?php\n";
		}
		return $out;
	}

	public function register_shortcodes() {
		add_shortcode( 'minimal_auth', array( $this, 'sc_customer' ) );
		add_shortcode( 'admin_login_page', array( $this, 'sc_admin' ) );
		add_shortcode( 'ezlens_lost_password', array( $this, 'sc_lost' ) );
	}

	private function is_file_active( $filename ) {
		global $wpdb;
		$table = $this->table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return true;
		}
		$status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$table} WHERE filename=%s", $filename ) );
		if ( null === $status ) {
			return true;
		}
		return (int) $status === 1;
	}

	private function render_file( $filename ) {
		if ( ! $this->is_file_active( $filename ) ) {
			return '<div style="text-align:center;padding:40px;font-family:Tahoma,sans-serif;color:#64748b;">این صفحه ورود غیرفعال است.</div>';
		}
		$path = trailingslashit( $this->storage_dir() ) . $filename;
		if ( ! is_readable( $path ) ) {
			return $this->fallback_core( $filename );
		}
		ob_start();
		include $path;
		return ob_get_clean();
	}

	private function fallback_core( $filename ) {
		ob_start();
		if ( $filename === 'customer-login.php' && defined( 'EZLAUTH_FRONTEND_DIR' ) && is_readable( EZLAUTH_FRONTEND_DIR . 'pages/login.php' ) ) {
			include EZLAUTH_FRONTEND_DIR . 'pages/login.php';
		} elseif ( $filename === 'lost-password.php' && defined( 'EZLAUTH_FRONTEND_DIR' ) && is_readable( EZLAUTH_FRONTEND_DIR . 'pages/forgot-password.php' ) ) {
			include EZLAUTH_FRONTEND_DIR . 'pages/forgot-password.php';
		} elseif ( $filename === 'admin-login.php' && defined( 'EZLAUTH_TEMPLATES_DIR' ) && is_readable( EZLAUTH_TEMPLATES_DIR . 'admin-login.php' ) ) {
			include EZLAUTH_TEMPLATES_DIR . 'admin-login.php';
		} else {
			echo '<p>فایل قالب یافت نشد.</p>';
		}
		return ob_get_clean();
	}

	public function sc_customer() {
		if ( is_user_logged_in() && ! is_admin() ) {
			$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			return '<div style="text-align:center;padding:32px;font-family:Tahoma,sans-serif;"><a class="button" href="' . esc_url( $url ) . '">حساب کاربری</a></div>';
		}
		// قالب تم‌دار + منطق هسته (نه HTML قدیمی defaults)
		$tpl = dirname( __FILE__ ) . '/templates/customer-login.php';
		if ( is_readable( $tpl ) ) {
			ob_start();
			include $tpl;
			return ob_get_clean();
		}
		return $this->render_file( 'customer-login.php' );
	}

	public function sc_admin() {
		return $this->render_file( 'admin-login.php' );
	}

	public function sc_lost() {
		return $this->render_file( 'lost-password.php' );
	}

	/**
	 * فقط فایل‌های سفارشی (غیر از سه هسته) را روی init لود می‌کند
	 */
	public function load_custom_active_files() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		global $wpdb;
		$table = $this->table();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}
		$rows = $wpdb->get_results( "SELECT filename FROM {$table} WHERE status=1 ORDER BY id ASC" );
		if ( ! $rows ) {
			return;
		}
		$dir = trailingslashit( $this->storage_dir() );
		$core = array_keys( self::CORE_MAP );
		foreach ( $rows as $row ) {
			$fn = basename( $row->filename );
			if ( in_array( $fn, $core, true ) ) {
				continue; // هسته فقط با شورت‌کد
			}
			$path = $dir . $fn;
			if ( is_readable( $path ) ) {
				include_once $path;
			}
		}
	}

	public function register_menu() {
		// منو از dashboard.php اصلی هم ثبت می‌شود؛ اینجا زیرصفحه ویرایش مخفی برای add/edit
		add_submenu_page(
			null,
			'ویرایش صفحه ورود',
			'ویرایش صفحه ورود',
			'manage_options',
			'ezlens-login-pages-edit',
			array( $this, 'render_edit' )
		);
	}

	public function render_list() {
		$file = dirname( __FILE__ ) . '/admin/pages/list.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_edit() {
		$file = dirname( __FILE__ ) . '/admin/pages/edit.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function enqueue_admin( $hook ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( ! in_array( $page, array( 'ezlens-login-pages', 'ezlens-login-pages-edit', 'ezlens-auth-editor' ), true ) ) {
			return;
		}
		$css = dirname( __FILE__ ) . '/admin/assets/css/login-pages-admin.css';
		$js  = dirname( __FILE__ ) . '/admin/assets/js/login-pages-admin.js';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'ezlens-login-pages-admin', plugins_url( 'admin/assets/css/login-pages-admin.css', __FILE__ ), array(), filemtime( $css ) );
		$theme = dirname( __FILE__ ) . '/admin/assets/css/login-pages-theme.css';
		if ( is_readable( $theme ) ) {
			wp_enqueue_style( 'ezlens-login-pages-theme', plugins_url( 'admin/assets/css/login-pages-theme.css', __FILE__ ), array( 'ezlens-login-pages-admin' ), filemtime( $theme ) );
		}
		}
		if ( is_readable( $js ) ) {
			wp_enqueue_script( 'ezlens-login-pages-admin', plugins_url( 'admin/assets/js/login-pages-admin.js', __FILE__ ), array( 'jquery' ), filemtime( $js ), true );
			wp_localize_script(
				'ezlens-login-pages-admin',
				'ezpurchase',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'ezpurchase_nonce' ),
				)
			);
		}
		// سازگاری با JS فرآیند خرید اگر همان نام متغیر را انتظار دارد
		wp_localize_script(
			'ezlens-login-pages-admin',
			'ezpurchase',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ezpurchase_nonce' ),
			)
		);
	}
}

// bootstrap
EzLens_Login_Pages_Loader::get_instance();
