<?php
/**
 * Compatibility: legacy panel, Woodmart, admin status (Phase 1)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Compatibility {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'override_legacy_shortcode' ), 20 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'wp_head', array( $this, 'hide_default_nav_css' ), 40 );
		add_action( 'template_redirect', array( $this, 'maybe_start_latin_buffer' ), 0 );
		add_action( 'init', array( $this, 'maybe_flush' ), 99 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 25 );
		add_action( 'admin_notices', array( $this, 'admin_notice_once' ) );
	}

	public function override_legacy_shortcode() {
		remove_shortcode( 'modern_user_panel' );
		add_shortcode( 'modern_user_panel', array( $this, 'legacy_shortcode' ) );
	}

	public function legacy_shortcode() {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		if ( class_exists( 'EzLens_CD_Router' ) && (int) get_option( 'ezlens_cd_replace_my_account', 1 ) === 1 ) {
			ob_start();
			EzLens_CD_Router::get_instance()->render_shell( 'overview' );
			return ob_get_clean();
		}
		if ( current_user_can( 'manage_options' ) ) {
			return '<div class="ezcd-admin-notice" style="padding:12px 16px;border:1px solid #e8edf5;border-radius:12px;background:#fff;">شورت‌کد <code>[modern_user_panel]</code> بازنشسته است. از صفحه حساب کاربری ووکامرس استفاده کنید.</div>';
		}
		return '';
	}

	public function body_class( $classes ) {
		if ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() ) {
			$classes[] = 'ezcd-account';
			if ( function_exists( 'ezcd_current_endpoint' ) ) {
				$classes[] = 'ezcd-ep-' . sanitize_html_class( ezcd_current_endpoint() );
			}
		}
		return $classes;
	}

	public function hide_default_nav_css() {
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
			return;
		}
		if ( (int) get_option( 'ezlens_cd_replace_my_account', 1 ) !== 1 ) {
			return;
		}
		// منوی پیش‌فرض ووکامرس/وودمارت + عرض کامل — فقط یک سایدبار CD
		echo '<style id="ezcd-hide-wc-nav">
		body.woocommerce-account .woocommerce-MyAccount-navigation,
		body.ezcd-account .woocommerce-MyAccount-navigation,
		body.woocommerce-account .wd-my-account-sidebar,
		body.ezcd-account .wd-my-account-sidebar,
		body.woocommerce-account .wd-nav-my-account,
		body.ezcd-account .wd-nav-my-account,
		body.woocommerce-account .woocommerce-account-nav,
		body.ezcd-account aside.woocommerce-MyAccount-navigation,
		body.ezcd-account .col-md-3 > .woocommerce-MyAccount-navigation,
		body.ezcd-account .col-lg-3 > .woocommerce-MyAccount-navigation{
			display:none!important;visibility:hidden!important;width:0!important;height:0!important;overflow:hidden!important;margin:0!important;padding:0!important
		}
		body.ezcd-account .woocommerce-MyAccount-content,
		body.woocommerce-account .woocommerce-MyAccount-content{
			width:100%!important;max-width:100%!important;float:none!important;padding:0!important;margin:0!important
		}
		body.ezcd-account .woocommerce,
		body.woocommerce-account .woocommerce{
			max-width:100%!important;width:100%!important
		}
		@media(min-width:901px){
			body.ezcd-account .container,
			body.ezcd-account .wd-content-layout,
			body.ezcd-account .content-layout-wrapper,
			body.ezcd-account .wd-page-content,
			body.ezcd-account .site-content,
			body.ezcd-account .entry-content{
				max-width:1400px!important;width:100%!important
			}
			body.ezcd-account .ezcd{
				max-width:1360px!important;width:100%!important;margin-left:auto!important;margin-right:auto!important
			}
			body.ezcd-account .ezcd-main{flex:1 1 auto!important;min-width:0!important}
			/* جلوگیری از دوگانه شدن سایدبار CD */
			body.ezcd-account .ezcd > .ezcd-sidebar ~ .ezcd-sidebar{display:none!important}
		}
		</style>';
	}

	public function maybe_flush() {
		if ( '1' === get_option( 'ezlens_cd_flush_rules', '' ) ) {
			return;
		}
		if ( class_exists( 'EzLens_CD_Router' ) ) {
			EzLens_CD_Router::flush_rules();
		}
		update_option( 'ezlens_cd_flush_rules', '1', false );
	}

	public function admin_menu() {
		// Menus owned exclusively by EzLens_CD_Admin under parent ezlens-auth.
		return;
	}

	public function render_status_page() {
		$loaded   = class_exists( 'EzLens_CD_Router' );
		$ver      = get_option( 'ezlens_cd_db_version', '—' );
		$rep      = (int) get_option( 'ezlens_cd_replace_my_account', 1 );
		$dir      = dirname( __FILE__ );
		$ok_shell = is_readable( $dir . '/templates/shell.php' );
		$account  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
		?>
		<div class="wrap" dir="rtl">
			<h1>داشبورد مشتری (فاز ۱)</h1>
			<p>پنل کامل مدیریت مشتریان در فازهای بعد می‌آید. وضعیت نصب فاز ۱:</p>
			<table class="widefat striped" style="max-width:640px">
				<tbody>
					<tr><td>کلاس Router</td><td><?php echo $loaded ? '✅ لود شده' : '❌ لود نشده'; ?></td></tr>
					<tr><td>نسخه ماژول</td><td><code><?php echo esc_html( (string) $ver ); ?></code></td></tr>
					<tr><td>جایگزینی my-account</td><td><?php echo $rep ? '✅ فعال' : '❌ خاموش'; ?></td></tr>
					<tr><td>قالب shell.php</td><td><?php echo $ok_shell ? '✅ موجود' : '❌ نیست'; ?></td></tr>
					<tr><td>مسیر ماژول</td><td><code><?php echo esc_html( $dir ); ?></code></td></tr>
					<tr><td>صفحه حساب</td><td><a href="<?php echo esc_url( $account ); ?>" target="_blank"><?php echo esc_html( $account ); ?></a></td></tr>
				</tbody>
			</table>
			<p style="margin-top:16px"><strong>اگر فرانت هنوز قدیمی است:</strong> خط
			<code>EZLAUTH_MODULES_DIR . 'customer-dashboard/class-install.php'</code>
			باید داخل <code>$core_files</code> در فایل اصلی پلاگین باشد، بعد پیوندهای یکتا را ذخیره کنید و کش را پاک کنید.</p>
		</div>
		<?php
	}

	public function admin_notice_once() {
		return; // disabled — hub lives under EzLens Auth menu
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( ! class_exists( 'EzLens_CD_Router' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || strpos( (string) $screen->id, 'ezlens' ) === false ) {
			return;
		}
		$url = admin_url( 'admin.php?page=ezlens-cd-status' );
		echo '<div class="notice notice-success is-dismissible"><p>ماژول <strong>داشبورد مشتری فاز ۱</strong> فعال است. <a href="' . esc_url( $url ) . '">وضعیت نصب</a></p></div>';
	}

	/**
	 * افزونه تقویم پارسی / تبدیل ارقام، اعداد داخل SVG و JS را خراب می‌کند.
	 * فقط در حساب کاربری و تسویه، ارقام فارسی را در SVG و <script> به لاتین برمی‌گردانیم.
	 */
	public function maybe_start_latin_buffer() {
		$need = false;
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$need = true;
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			$need = true;
		}
		if ( ! $need ) {
			return;
		}
		ob_start( array( $this, 'restore_latin_in_critical' ) );
	}

	public function restore_latin_in_critical( $html ) {
		if ( ! is_string( $html ) || $html === '' ) {
			return $html;
		}
		$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
		$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
		// SVG attributes must be Latin digits
		$html = preg_replace_callback(
			'/<svg\b[^>]*>.*?<\/svg>/is',
			function ( $m ) use ( $fa, $en ) {
				return str_replace( $fa, $en, $m[0] );
			},
			$html
		);
		// Inline scripts must stay Latin or JS breaks (U+06F2 etc.)
		$html = preg_replace_callback(
			'/<script\b[^>]*>.*?<\/script>/is',
			function ( $m ) use ( $fa, $en ) {
				return str_replace( $fa, $en, $m[0] );
			},
			$html
		);
		return $html;
	}

}
