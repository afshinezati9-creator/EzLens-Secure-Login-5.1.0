<?php
/**
 * Customer Dashboard — Admin CRM (Phase 6)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Admin {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_ezcd_save_sections', array( $this, 'save_sections' ) );
		add_action( 'wp_ajax_ezcd_admin_save_sections', array( $this, 'ajax_save_sections' ) );
		add_action( 'wp_ajax_ezcd_admin_order_action', array( $this, 'ajax_order_action' ) );
		add_action( 'wp_ajax_ezcd_admin_rx_delete', array( $this, 'ajax_rx_delete' ) );
		add_action( 'wp_ajax_ezcd_admin_review_action', array( $this, 'ajax_review_action' ) );
		add_action( 'wp_ajax_ezcd_admin_charity_case', array( $this, 'ajax_charity_case' ) );
		add_action( 'wp_ajax_ezcd_admin_charity_impact', array( $this, 'ajax_charity_impact' ) );
		add_action( 'admin_post_ezcd_add_note', array( $this, 'add_note' ) );
		add_action( 'admin_post_ezcd_wallet_adjust', array( $this, 'wallet_adjust' ) );
		add_action( 'wp_ajax_ezcd_admin_search', array( $this, 'ajax_search' ) );
		add_action( 'wp_ajax_ezcd_admin_support_get', array( $this, 'ajax_support_get' ) );
		add_action( 'wp_ajax_ezcd_admin_support_reply', array( $this, 'ajax_support_reply' ) );
		add_action( 'wp_ajax_ezcd_admin_support_status', array( $this, 'ajax_support_status' ) );
		add_action( 'wp_ajax_ezcd_admin_support_compose', array( $this, 'ajax_support_compose' ) );
		add_action( 'wp_ajax_ezcd_admin_customer_profile', array( $this, 'ajax_customer_profile' ) );
		add_action( 'wp_ajax_ezcd_admin_customer_update', array( $this, 'ajax_customer_update' ) );
		add_action( 'wp_ajax_ezcd_admin_customer_create', array( $this, 'ajax_customer_create' ) );
		add_action( 'wp_ajax_ezcd_admin_customer_note', array( $this, 'ajax_customer_note' ) );
		add_action( 'wp_ajax_ezcd_admin_customer_wallet', array( $this, 'ajax_customer_wallet' ) );

		add_action( 'admin_post_ezcd_mass_send', array( $this, 'mass_send' ) );
		add_action( 'admin_post_ezcd_mass_to_campaign_list', array( $this, 'mass_to_campaign_list' ) );
		add_action( 'admin_post_ezcd_deposit_approve', array( $this, 'deposit_approve' ) );
		add_action( 'admin_post_ezcd_deposit_reject', array( $this, 'deposit_reject' ) );
		add_action( 'admin_post_ezcd_save_bank_info', array( $this, 'save_bank_info' ) );
		add_action( 'admin_notices', array( $this, 'deposit_notice' ) );
		add_action( 'admin_post_ezcd_direct_message', array( $this, 'direct_message' ) );
	}

	public function menu() {
		$parent = 'ezlens-auth';

		// Ensure we only hang under EzLens Auth plugin menu (never top-level / WooCommerce).
		add_submenu_page(
			$parent,
			'داشبورد مشتری',
			'<span class="ezlens-menu-icon" data-icon="home"></span> داشبورد مشتری',
			'manage_woocommerce',
			'ezlens-cd-status',
			array( $this, 'render_status' )
		);
		add_submenu_page(
			$parent,
			'مشتریان',
			'<span class="ezlens-menu-icon" data-icon="users"></span>&nbsp;&nbsp;مشتریان',
			'manage_woocommerce',
			'ezlens-cd-customers',
			array( $this, 'render_list' )
		);
		// Hidden profile page (no menu label) — cleaned in menu_cleanup.
		add_submenu_page(
			$parent,
			'پروفایل مشتری',
			'پروفایل مشتری',
			'manage_woocommerce',
			'ezlens-cd-customer',
			array( $this, 'render_profile' )
		);
		add_submenu_page(
			$parent,
			'سفارش‌های مشتریان',
			'<span class="ezlens-menu-icon" data-icon="package"></span>&nbsp;&nbsp;سفارش‌ها',
			'manage_woocommerce',
			'ezlens-cd-orders',
			array( $this, 'render_orders' )
		);
		add_submenu_page(
			$parent,
			'پرونده بیماران',
			'<span class="ezlens-menu-icon" data-icon="eye"></span>&nbsp;&nbsp;نسخه‌ها',
			'manage_woocommerce',
			'ezlens-cd-prescriptions',
			array( $this, 'render_prescriptions' )
		);
		add_submenu_page(
			$parent,
			'نظرات مشتریان',
			'<span class="ezlens-menu-icon" data-icon="message-circle"></span>&nbsp;&nbsp;نظرات',
			'manage_woocommerce',
			'ezlens-cd-reviews',
			array( $this, 'render_reviews' )
		);
		add_submenu_page(
			$parent,
			'تخفیف و کارت هدیه',
			'<span class="ezlens-menu-icon" data-icon="gift"></span>&nbsp;&nbsp;تخفیف و هدیه',
			'manage_woocommerce',
			'ezlens-cd-rewards',
			array( $this, 'render_rewards' )
		);
		add_submenu_page(
			$parent,
			'شارژ کیف پول',
			'<span class="ezlens-menu-icon" data-icon="wallet"></span>&nbsp;&nbsp;شارژ کیف پول',
			'manage_woocommerce',
			'ezlens-cd-deposits',
			array( $this, 'render_deposits' )
		);
		add_submenu_page(
			$parent,
			'پشتیبانی',
			'<span class="ezlens-menu-icon" data-icon="headset"></span>&nbsp;&nbsp;پشتیبانی',
			'manage_woocommerce',
			'ezlens-cd-support',
			array( $this, 'render_support' )
		);
		add_submenu_page(
			$parent,
			'هم‌یاری بینایی',
			'<span class="ezlens-menu-icon" data-icon="heart"></span>&nbsp;&nbsp;هم‌یاری بینایی',
			'manage_woocommerce',
			'ezlens-cd-charity',
			array( $this, 'render_charity' )
		);
		add_submenu_page(
			$parent,
			'پیام جمعی',
			'<span class="ezlens-menu-icon" data-icon="send"></span>&nbsp;&nbsp;پیام جمعی',
			'manage_woocommerce',
			'ezlens-cd-mass',
			array( $this, 'render_mass' )
		);
		add_submenu_page(
			$parent,
			'تنظیمات داشبورد مشتری',
			'<span class="ezlens-menu-icon" data-icon="settings"></span>&nbsp;&nbsp;تنظیمات داشبورد',
			'manage_woocommerce',
			'ezlens-cd-settings',
			array( $this, 'render_settings' )
		);

		add_action( 'admin_menu', array( $this, 'menu_cleanup' ), 999 );
		add_action( 'admin_head', array( $this, 'menu_icons_css' ) );
	}

	/**
	 * Hide profile slug from submenu (keeps page reachable via deep link).
	 * Also remove any duplicate ezlens-cd-* entries under other parents.
	 */
	public function menu_cleanup() {
		// حذف پشتیبانی قدیمی پلاگین — فقط پشتیبانی جدید داشبورد بماند
		remove_submenu_page( 'ezlens-auth', 'ezlens-auth-support' );

		global $submenu;
		if ( empty( $submenu ) || ! is_array( $submenu ) ) {
			return;
		}
		foreach ( $submenu as $parent => $items ) {
			if ( ! is_array( $items ) ) {
				continue;
			}
			foreach ( $items as $i => $item ) {
				$slug = isset( $item[2] ) ? $item[2] : '';
				// Hide empty profile entry under plugin menu
				if ( 'ezlens-auth' === $parent && 'ezlens-cd-customer' === $slug ) {
					unset( $submenu[ $parent ][ $i ] );
					continue;
				}
				// Remove CD pages that were wrongly attached outside EzLens Auth
				if ( 'ezlens-auth' !== $parent && is_string( $slug ) && strpos( $slug, 'ezlens-cd-' ) === 0 ) {
					unset( $submenu[ $parent ][ $i ] );
				}
			}
			if ( isset( $submenu[ $parent ] ) ) {
				$submenu[ $parent ] = array_values( $submenu[ $parent ] );
			}
		}
	}

	
	/**
	 * Ensure CD submenu icons (heart, headset, …) render in WP admin menu.
	 */
	public function menu_icons_css() {
		$base = '';
		if ( defined( 'EZLAUTH_PLUGIN_URL' ) ) {
			$base = trailingslashit( EZLAUTH_PLUGIN_URL ) . 'assets/icons/modern/';
		} else {
			$base = plugins_url( 'assets/icons/modern/', dirname( dirname( __FILE__ ) ) . '/ezlens-secure-login.php' );
		}
		$icons = array( 'home', 'users', 'package', 'eye', 'message-circle', 'gift', 'wallet', 'headset', 'heart', 'send', 'settings', 'support', 'layout-dashboard' );
		echo "<style id=\"ezcd-menu-icons\">\n";
		echo ".ezlens-menu-icon{display:inline-block;width:18px;height:18px;vertical-align:middle;margin-left:4px;background-color:currentColor;";
		echo "-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;}\n";
		foreach ( $icons as $name ) {
			$url = esc_url( $base . $name . '.svg' );
			echo ".ezlens-menu-icon[data-icon=\"{$name}\"]{-webkit-mask-image:url('{$url}');mask-image:url('{$url}');}\n";
		}
		echo "</style>\n";
	}

	public function assets( $hook ) {
		if ( strpos( $hook, 'ezlens-cd-' ) === false ) {
			return;
		}
		$dir  = plugin_dir_url( __FILE__ );
		$base = dirname( __FILE__ ) . '/admin/assets/';
		$css  = $base . 'admin.css';
		$js   = $base . 'admin.js';
		$ver  = file_exists( $css ) ? filemtime( $css ) : '1.0';
		wp_enqueue_style( 'ezcd-admin', $dir . 'admin/assets/admin.css', array(), $ver );
		if ( file_exists( $js ) ) {
			$jver = filemtime( $js );
			wp_enqueue_script( 'ezcd-admin', $dir . 'admin/assets/admin.js', array(), $jver, true );
			wp_localize_script(
				'ezcd-admin',
				'ezcdAdmin',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ezcd_admin' ),
				)
			);
		}
	}


	public function render_orders() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/orders.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_prescriptions() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/prescriptions.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_reviews() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/reviews.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_rewards() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/rewards.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	private function guard() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
	}

	public function render_list() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		$file = dirname( __FILE__ ) . '/admin/pages/list.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_profile() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		$file = dirname( __FILE__ ) . '/admin/pages/profile.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_support() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/support.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_charity() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/charity.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_deposits() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		$file = dirname( __FILE__ ) . '/admin/pages/deposits.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_mass() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		$file = dirname( __FILE__ ) . '/admin/pages/mass.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		$file = dirname( __FILE__ ) . '/admin/pages/settings.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}

	public function render_status() {
		$this->guard();
		$file = dirname( __FILE__ ) . '/admin/pages/status.php';
		if ( is_readable( $file ) ) {
			include $file;
			return;
		}
		echo '<div class="wrap"><p>status.php missing</p></div>';
	}

	public function ajax_save_sections() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'دسترسی' ) );
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$sections = isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['sections'] ) ) : array();
		$all = array( 'overview', 'orders', 'prescriptions', 'wishlist', 'addresses', 'reviews', 'coupons', 'gift_cards', 'wallet', 'charity', 'support', 'account', 'security', 'referral' );
		$out = array();
		foreach ( $all as $k ) {
			$out[ $k ] = ( 'overview' === $k || in_array( $k, $sections, true ) ) ? 1 : 0;
		}
		update_option( 'ezlens_cd_sections', $out, false );
		update_option( 'ezlens_cd_replace_my_account', ! empty( $_POST['replace_my_account'] ) ? 1 : 0, false );
		wp_send_json_success( array( 'message' => 'تنظیمات ذخیره شد' ) );
	}

	public function ajax_order_action() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$act = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : '';
		$order = $id && function_exists( 'wc_get_order' ) ? wc_get_order( $id ) : null;
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => 'سفارش یافت نشد' ) );
		}
		if ( 'complete' === $act ) {
			$order->update_status( 'completed', 'EzLens CD admin' );
		} elseif ( 'cancel' === $act ) {
			$order->update_status( 'cancelled', 'EzLens CD admin' );
		} elseif ( 'processing' === $act ) {
			$order->update_status( 'processing', 'EzLens CD admin' );
		} else {
			wp_send_json_error( array( 'message' => 'عملیات نامعتبر' ) );
		}
		wp_send_json_success( array( 'message' => 'وضعیت به‌روز شد', 'status' => $order->get_status() ) );
	}

	public function ajax_rx_delete() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		global $wpdb;
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$table = $wpdb->prefix . 'ezlens_cd_prescriptions';
		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		wp_send_json_success( array( 'message' => 'نسخه حذف شد' ) );
	}

	public function ajax_charity_case() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$r  = EzLens_CD_Charity::save_case( wp_unslash( $_POST ), $id );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'مورد ذخیره شد', 'id' => (int) $r ) );
	}

	public function ajax_charity_impact() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$att = 0;
		if ( ! empty( $_FILES['attachment'] ) && ! empty( $_FILES['attachment']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( 'attachment', 0 );
			if ( ! is_wp_error( $aid ) ) {
				$att = (int) $aid;
			}
		}
		$data = wp_unslash( $_POST );
		$data['attachment_id'] = $att;
		$r = EzLens_CD_Charity::add_update( $data );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'گزارش اثر ثبت شد', 'id' => (int) $r ) );
	}

	public function ajax_review_action() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$act = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : '';
		if ( ! $id ) {
			wp_send_json_error();
		}
		if ( 'approve' === $act ) {
			wp_set_comment_status( $id, 'approve' );
		} elseif ( 'hold' === $act ) {
			wp_set_comment_status( $id, 'hold' );
		} elseif ( 'trash' === $act ) {
			wp_trash_comment( $id );
		} elseif ( 'reply' === $act ) {
			$content = isset( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
			$parent = get_comment( $id );
			if ( ! $parent || ! $content ) {
				wp_send_json_error( array( 'message' => 'متن پاسخ لازم است' ) );
			}
			wp_insert_comment(
				array(
					'comment_post_ID'      => (int) $parent->comment_post_ID,
					'comment_parent'       => $id,
					'comment_content'      => $content,
					'user_id'              => get_current_user_id(),
					'comment_author'       => wp_get_current_user()->display_name,
					'comment_author_email' => wp_get_current_user()->user_email,
					'comment_approved'     => 1,
				)
			);
		} else {
			wp_send_json_error();
		}
		wp_send_json_success( array( 'message' => 'انجام شد' ) );
	}

	public function save_sections() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_save_sections' );
		$all = array(
			'overview', 'orders', 'prescriptions', 'wishlist', 'addresses',
			'reviews', 'coupons', 'gift_cards', 'wallet', 'support',
			'account', 'security', 'referral',
		);
		$posted = isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['sections'] ) ) : array();
		$save   = array();
		foreach ( $all as $key ) {
			$save[ $key ] = in_array( $key, $posted, true ) ? 1 : 0;
		}
		// overview always on
		$save['overview'] = 1;
		update_option( 'ezlens_cd_sections', $save, false );
		$replace = isset( $_POST['replace_my_account'] ) ? 1 : 0;
		update_option( 'ezlens_cd_replace_my_account', $replace, false );
		wp_safe_redirect( admin_url( 'admin.php?page=ezlens-cd-settings&saved=1' ) );
		exit;
	}

	public function add_note() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_add_note' );
		$cid  = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$note = isset( $_POST['note'] ) ? wp_unslash( $_POST['note'] ) : '';
		$r    = EzLens_CD_Notes::add( $cid, $note );
		$url  = admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $cid . '&tab=notes' );
		if ( is_wp_error( $r ) ) {
			$url = add_query_arg( 'err', rawurlencode( $r->get_error_message() ), $url );
		} else {
			$url = add_query_arg( 'saved', '1', $url );
		}
		wp_safe_redirect( $url );
		exit;
	}

	public function wallet_adjust() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_wallet_adjust' );
		$cid    = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$amount = isset( $_POST['amount'] ) ? absint( $_POST['amount'] ) : 0;
		$type   = isset( $_POST['entry_type'] ) && 'debit' === $_POST['entry_type'] ? 'debit' : 'credit';
		$note   = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : 'تعدیل ادمین';
		$url    = admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $cid . '&tab=wallet' );
		if ( ! class_exists( 'EzLens_CD_Wallet' ) ) {
			wp_safe_redirect( add_query_arg( 'err', 'wallet', $url ) );
			exit;
		}
		$r = EzLens_CD_Wallet::add_entry( $cid, $amount, $type, 'admin', $note );
		if ( is_wp_error( $r ) ) {
			wp_safe_redirect( add_query_arg( 'err', rawurlencode( $r->get_error_message() ), $url ) );
		} else {
			wp_safe_redirect( add_query_arg( 'saved', '1', $url ) );
		}
		exit;
	}

	
	public function deposit_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$n = (int) get_option( 'ezlens_cd_wallet_pending_count', 0 );
		if ( $n < 1 ) {
			return;
		}
		$url = admin_url( 'admin.php?page=ezlens-cd-deposits' );
		echo '<div class="notice notice-warning"><p><strong>کیف پول:</strong> ' . esc_html( (string) $n ) . ' درخواست شارژ در انتظار بررسی است. <a href="' . esc_url( $url ) . '">مشاهده</a></p></div>';
	}

	public function deposit_approve() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_deposit_action' );
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		EzLens_CD_Wallet_Deposits::approve( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=ezlens-cd-deposits&ok=1' ) );
		exit;
	}

	public function deposit_reject() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_deposit_action' );
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		EzLens_CD_Wallet_Deposits::reject( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=ezlens-cd-deposits&ok=1' ) );
		exit;
	}

	public function save_bank_info() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_save_bank_info' );
		$info = isset( $_POST['bank_info'] ) ? wp_kses_post( wp_unslash( $_POST['bank_info'] ) ) : '';
		update_option( 'ezlens_cd_wallet_bank_info', $info, false );
		wp_safe_redirect( admin_url( 'admin.php?page=ezlens-cd-deposits&ok=1' ) );
		exit;
	}

	
	/**
	 * پل: کاربران انتخاب‌شده در پیام جمعی → لیست کمپین.
	 */
	public function mass_to_campaign_list() {
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_mass_to_campaign_list' );
		$raw = isset( $_POST['user_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['user_ids'] ) ) : '';
		$ids = array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $raw ) ) );
		$name = isset( $_POST['list_name'] ) ? sanitize_text_field( wp_unslash( $_POST['list_name'] ) ) : '';
		$redirect = admin_url( 'admin.php?page=ezlens-cd-mass' );
		if ( ! class_exists( 'EzLens_Auth_Campaign' ) ) {
			wp_safe_redirect( add_query_arg( 'list_err', rawurlencode( 'ماژول کمپین در دسترس نیست' ), $redirect ) );
			exit;
		}
		$campaign = EzLens_Auth_Campaign::get_instance();
		if ( ! method_exists( $campaign, 'create_list_from_user_ids' ) ) {
			wp_safe_redirect( add_query_arg( 'list_err', rawurlencode( 'هسته کمپین را به نسخه فاز ۲ به‌روز کنید' ), $redirect ) );
			exit;
		}
		$result = $campaign->create_list_from_user_ids( $ids, $name );
		if ( empty( $result['success'] ) ) {
			wp_safe_redirect( add_query_arg( 'list_err', rawurlencode( $result['message'] ?? 'خطا' ), $redirect ) );
			exit;
		}
		wp_safe_redirect(
			add_query_arg(
				array(
					'list_ok'  => 1,
					'list_msg' => rawurlencode( $result['message'] ?? 'لیست ساخته شد' ),
					'camp'     => 1,
				),
				$redirect
			)
		);
		exit;
	}

	public function mass_send() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_mass_send' );
		$audience = isset( $_POST['audience'] ) ? sanitize_key( $_POST['audience'] ) : 'selected';
		$channel  = isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'sms';
		$subject  = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		if ( $message === '' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=ezlens-cd-mass&err=' . rawurlencode( 'متن پیام الزامی است' ) ) );
			exit;
		}

		$ids = array();
		if ( 'selected' === $audience ) {
			if ( isset( $_POST['user_ids'] ) && is_array( $_POST['user_ids'] ) ) {
				$ids = array_filter( array_map( 'absint', wp_unslash( $_POST['user_ids'] ) ) );
			}
			$ids_text = isset( $_POST['user_ids_text'] ) ? sanitize_text_field( wp_unslash( $_POST['user_ids_text'] ) ) : '';
			if ( $ids_text === '' && isset( $_POST['user_ids'] ) && is_string( $_POST['user_ids'] ) ) {
				$ids_text = sanitize_text_field( wp_unslash( $_POST['user_ids'] ) );
			}
			if ( $ids_text !== '' ) {
				$ids = array_merge( $ids, array_filter( array_map( 'absint', preg_split( '/[\s,;]+/', $ids_text ) ) ) );
			}
			$ids = array_values( array_unique( $ids ) );
		} else {
			$ids = EzLens_CD_Mass_Actions::audience( $audience );
		}

		if ( empty( $ids ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=ezlens-cd-mass&err=' . rawurlencode( 'هیچ مخاطبی انتخاب نشده' ) ) );
			exit;
		}

		$result = EzLens_CD_Mass_Actions::send_to_users( $ids, $channel, $subject, $message );
		EzLens_CD_Mass_Actions::log(
			'mass_message',
			$channel,
			count( $ids ),
			isset( $result['success'] ) ? $result['success'] : 0,
			isset( $result['fail'] ) ? $result['fail'] : 0,
			array(
				'audience' => $audience,
				'subject'  => $subject,
			)
		);

		wp_safe_redirect(
			admin_url(
				'admin.php?page=ezlens-cd-mass&done=1&ok=' . absint( $result['success'] ) . '&fail=' . absint( $result['fail'] )
			)
		);
		exit;
	}

	public function direct_message() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}
		check_admin_referer( 'ezcd_direct_message' );
		$cid     = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$channel = isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'sms';
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$url     = admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $cid . '&tab=overview' );
		if ( ! $cid || $message === '' ) {
			wp_safe_redirect( add_query_arg( 'err', rawurlencode( 'پیام نامعتبر' ), $url ) );
			exit;
		}
		$result = EzLens_CD_Mass_Actions::send_to_users( array( $cid ), $channel, $subject, $message );
		if ( $result['success'] > 0 ) {
			wp_safe_redirect( add_query_arg( 'saved', '1', $url ) );
		} else {
			$err = ! empty( $result['errors'][0] ) ? $result['errors'][0] : 'ارسال ناموفق';
			wp_safe_redirect( add_query_arg( 'err', rawurlencode( $err ), $url ) );
		}
		exit;
	}

	public function ajax_search() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		$s = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$r = EzLens_CD_Admin_Customers::query( array( 'search' => $s, 'per_page' => 10 ) );
		$out = array();
		foreach ( $r['items'] as $u ) {
			$out[] = array(
				'id'    => $u->ID,
				'name'  => $u->display_name,
				'email' => $u->user_email,
				'phone' => EzLens_CD_Admin_Customers::phone( $u->ID ),
			);
		}
		wp_send_json_success( $out );
	}

	public function ajax_support_get() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$tid    = absint( $_POST['ticket_id'] ?? 0 );
		$ticket = EzLens_CD_Support_Bridge::get_ticket( $tid, 0 );
		if ( ! $ticket ) {
			wp_send_json_error( array( 'message' => 'تیکت یافت نشد' ) );
		}
		$messages = EzLens_CD_Support_Bridge::get_messages( $tid );
		$user     = get_userdata( (int) $ticket->user_id );
		ob_start();
		?>
		<div>
			<strong style="font-size:15px"><?php echo esc_html( $ticket->subject ?: ( '#' . $tid ) ); ?></strong>
			<div class="description" style="margin:6px 0 10px">
				<?php echo esc_html( $user ? $user->display_name : '' ); ?>
				· #<?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( (string) $tid ) : (string) $tid ); ?>
				· <?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $ticket->created_at ) : $ticket->created_at ); ?>
			</div>
			<div class="ezcd-as-actions">
				<span class="ezcd-as-status"><?php echo esc_html( EzLens_CD_Support_Bridge::status_label( $ticket->status ) ); ?></span>
				<button type="button" class="button button-small" data-status="closed">بستن</button>
				<button type="button" class="button button-small" data-status="open">باز کردن</button>
				<button type="button" class="button button-small" data-status="replied">پاسخ‌داده‌شده</button>
			</div>
		</div>
		<div class="ezcd-as-thread">
			<?php foreach ( $messages as $m ) :
				$staff = EzLens_CD_Support_Bridge::is_staff_message( $m );
				$text  = EzLens_CD_Support_Bridge::message_text( $m );
				$att   = EzLens_CD_Support_Bridge::attachment_url( $m );
				$when  = $m->created_at ?? '';
				?>
				<div class="ezcd-as-bubble <?php echo $staff ? 'is-staff' : ''; ?>">
					<div class="meta"><?php echo $staff ? 'پشتیبانی' : 'مشتری'; ?> · <?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $when ) : $when ); ?></div>
					<div><?php echo nl2br( esc_html( $text ) ); ?></div>
					<?php if ( $att ) : ?><div style="margin-top:6px"><a href="<?php echo esc_url( $att ); ?>" target="_blank" rel="noopener">پیوست</a></div><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<form id="ezcd-admin-reply-form" enctype="multipart/form-data">
			<p><label>پاسخ<br><textarea name="message" class="widefat" rows="4" required placeholder="پاسخ خود را بنویسید…"></textarea></label></p>
			<p><label>پیوست (حداکثر ۲۰ مگابایت)<br><input type="file" name="attachment" accept="image/*,.pdf"></label></p>
			<div class="ezcd-as-notify">
				<label><input type="checkbox" name="notify_email" value="1" checked> ارسال ایمیل به مشتری</label>
				<label><input type="checkbox" name="notify_sms" value="1" checked> ارسال پیامک به مشتری</label>
			</div>
			<p><button type="submit" class="button button-primary">ارسال پاسخ</button> <span class="ezcd-reply-msg ezcd-admin-msg"></span></p>
		</form>
		<?php
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	public function ajax_support_reply() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$tid = absint( $_POST['ticket_id'] ?? 0 );
		$msg = isset( $_POST['message'] ) ? (string) wp_unslash( $_POST['message'] ) : '';
		$attachment_id = 0;
		if ( ! empty( $_FILES['attachment']['name'] ) ) {
			$max = function_exists( 'ezcd_max_upload_bytes' ) ? ezcd_max_upload_bytes() : 20 * 1024 * 1024;
			if ( ! empty( $_FILES['attachment']['size'] ) && (int) $_FILES['attachment']['size'] > $max ) {
				wp_send_json_error( array( 'message' => 'حجم فایل بیش از ۲۰ مگابایت است' ) );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( 'attachment', 0 );
			if ( ! is_wp_error( $aid ) ) {
				$attachment_id = (int) $aid;
			}
		}
		$ticket = EzLens_CD_Support_Bridge::get_ticket( $tid, 0 );
		if ( ! $ticket ) {
			wp_send_json_error( array( 'message' => 'تیکت یافت نشد' ) );
		}
		$core = EzLens_CD_Support_Bridge::core();
		if ( $core && method_exists( $core, 'add_message' ) ) {
			$core->add_message( $tid, get_current_user_id(), 'admin', $msg, $attachment_id ?: null, false );
			EzLens_CD_Support_Bridge::set_status( $tid, 'replied' );
		} else {
			EzLens_CD_Support_Bridge::add_message( $tid, $msg, $attachment_id, true );
		}
		$do_email = ! empty( $_POST['notify_email'] );
		$do_sms   = ! empty( $_POST['notify_sms'] );
		EzLens_CD_Support_Bridge::notify_customer_reply( $tid, $msg, $do_sms, $do_email );
		wp_send_json_success( array( 'message' => 'پاسخ ثبت شد' . ( ( $do_sms || $do_email ) ? ' و اطلاع‌رسانی انجام شد' : '' ) ) );
	}

	public function ajax_support_status() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$tid = absint( $_POST['ticket_id'] ?? 0 );
		$st  = sanitize_key( $_POST['status'] ?? 'open' );
		if ( ! EzLens_CD_Support_Bridge::set_status( $tid, $st ) ) {
			wp_send_json_error( array( 'message' => 'به‌روزرسانی وضعیت ممکن نشد' ) );
		}
		wp_send_json_success( array( 'message' => 'وضعیت به‌روز شد' ) );
	}

	
	public function ajax_support_compose() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message = isset( $_POST['message'] ) ? (string) wp_unslash( $_POST['message'] ) : '';
		$attachment_id = 0;
		if ( ! empty( $_FILES['attachment']['name'] ) ) {
			$max = function_exists( 'ezcd_max_upload_bytes' ) ? ezcd_max_upload_bytes() : 20 * 1024 * 1024;
			if ( ! empty( $_FILES['attachment']['size'] ) && (int) $_FILES['attachment']['size'] > $max ) {
				wp_send_json_error( array( 'message' => 'حجم فایل بیش از ۲۰ مگابایت است' ) );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( 'attachment', 0 );
			if ( ! is_wp_error( $aid ) ) {
				$attachment_id = (int) $aid;
			}
		}
		$r = EzLens_CD_Support_Bridge::create_for_user(
			$user_id,
			$subject,
			$message,
			$attachment_id,
			! empty( $_POST['notify_email'] ),
			! empty( $_POST['notify_sms'] )
		);
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'پیام در پشتیبانی مشتری ثبت شد', 'ticket_id' => (int) $r ) );
	}

	public function ajax_customer_profile() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		$tab     = sanitize_key( $_POST['tab'] ?? 'overview' );
		$bundle  = EzLens_CD_Admin_Customers::profile_bundle( $user_id );
		if ( ! $bundle ) {
			wp_send_json_error( array( 'message' => 'مشتری یافت نشد' ) );
		}
		$user = $bundle['user'];
		$tabs = array(
			'overview' => 'خلاصه',
			'orders'   => 'سفارش‌ها',
			'rx'       => 'نسخه‌ها',
			'wallet'   => 'کیف پول',
			'tickets'  => 'تیکت‌ها',
			'account'  => 'ویرایش',
			'notes'    => 'یادداشت',
		);
		ob_start();
		?>
		<div class="ezcd-cust-head">
			<strong style="font-size:16px"><?php echo esc_html( $user->display_name ); ?></strong>
			<div class="description">#<?php echo esc_html( (string) $user_id ); ?> · <?php echo esc_html( $user->user_email ); ?> · <code dir="ltr"><?php echo esc_html( $bundle['phone'] ); ?></code></div>
		</div>
		<nav class="ezcd-cust-tabs">
			<?php foreach ( $tabs as $k => $l ) : ?>
				<button type="button" class="ezcd-cust-tab<?php echo $tab === $k ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></button>
			<?php endforeach; ?>
		</nav>
		<div class="ezcd-cust-panel">
		<?php if ( 'overview' === $tab ) : ?>
			<p>سفارش‌ها: <strong><?php echo esc_html( number_format_i18n( $bundle['stats']['count'] ) ); ?></strong>
			· خرید: <strong><?php echo function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $bundle['stats']['spent'] ) ) : esc_html( (string) $bundle['stats']['spent'] ); ?></strong>
			· کیف پول: <strong><?php echo esc_html( number_format_i18n( (int) $bundle['wallet'] ) ); ?></strong>
			· تیکت: <strong><?php echo esc_html( number_format_i18n( count( $bundle['tickets'] ) ) ); ?></strong></p>
			<p class="description">عضویت: <?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $user->user_registered ) : $user->user_registered ); ?></p>
		<?php elseif ( 'orders' === $tab ) : ?>
			<?php if ( empty( $bundle['orders'] ) ) : ?><p>سفارشی نیست.</p><?php else : ?>
			<table class="widefat striped"><thead><tr><th>شماره</th><th>وضعیت</th><th>مبلغ</th><th>تاریخ</th></tr></thead><tbody>
			<?php foreach ( $bundle['orders'] as $o ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( $o->get_edit_order_url() ); ?>">#<?php echo esc_html( $o->get_order_number() ); ?></a></td>
					<td><?php echo esc_html( wc_get_order_status_name( $o->get_status() ) ); ?></td>
					<td><?php echo wp_kses_post( $o->get_formatted_order_total() ); ?></td>
					<td><?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $o->get_date_created() ? $o->get_date_created()->date( 'Y-m-d H:i:s' ) : '' ) : '' ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody></table>
			<?php endif; ?>
		<?php elseif ( 'rx' === $tab ) : ?>
			<?php if ( empty( $bundle['prescriptions'] ) ) : ?><p>نسخه‌ای نیست.</p><?php else : ?>
			<ul><?php foreach ( $bundle['prescriptions'] as $rx ) : ?>
				<li>#<?php echo esc_html( (string) ( $rx->id ?? '' ) ); ?> — <?php echo esc_html( $rx->title ?? $rx->type ?? '' ); ?>
				· <?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $rx->created_at ?? '' ) : '' ); ?></li>
			<?php endforeach; ?></ul>
			<?php endif; ?>
		<?php elseif ( 'wallet' === $tab ) : ?>
			<p>موجودی: <strong><?php echo esc_html( number_format_i18n( (int) $bundle['wallet'] ) ); ?></strong> تومان</p>
			<form id="ezcd-cust-wallet-form">
				<p><label>مبلغ<br><input type="number" name="amount" min="1" required></label>
				<label>نوع <select name="type"><option value="credit">افزایش</option><option value="debit">کاهش</option></select></label>
				<label>یادداشت <input type="text" name="note" class="regular-text"></label>
				<button class="button button-primary">اعمال</button></p>
			</form>
			<?php if ( ! empty( $bundle['wallet_hist'] ) ) : ?>
			<table class="widefat striped"><thead><tr><th>نوع</th><th>مبلغ</th><th>زمان</th></tr></thead><tbody>
			<?php foreach ( array_slice( $bundle['wallet_hist'], 0, 10 ) as $row ) : ?>
				<tr><td><?php echo 'credit' === $row->entry_type ? '+' : '−'; ?></td>
				<td><?php echo esc_html( number_format_i18n( (int) $row->amount ) ); ?></td>
				<td><?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $row->created_at ) : $row->created_at ); ?></td></tr>
			<?php endforeach; ?>
			</tbody></table>
			<?php endif; ?>
		<?php elseif ( 'tickets' === $tab ) : ?>
			<?php if ( empty( $bundle['tickets'] ) ) : ?><p>تیکتی نیست.</p><?php else : ?>
			<ul><?php foreach ( $bundle['tickets'] as $tk ) : ?>
				<li>#<?php echo esc_html( (string) $tk->id ); ?> — <?php echo esc_html( $tk->subject ?? '' ); ?>
				(<?php echo esc_html( EzLens_CD_Support_Bridge::status_label( $tk->status ?? '' ) ); ?>)
				· <?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $tk->updated_at ?? $tk->created_at ?? '' ) : '' ); ?></li>
			<?php endforeach; ?></ul>
			<?php endif; ?>
		<?php elseif ( 'account' === $tab ) : ?>
			<form id="ezcd-cust-edit-form">
				<p><label>نام نمایشی<br><input type="text" name="display_name" class="widefat" value="<?php echo esc_attr( $user->display_name ); ?>"></label></p>
				<p><label>ایمیل<br><input type="email" name="email" class="widefat" value="<?php echo esc_attr( $user->user_email ); ?>"></label></p>
				<p><label>موبایل<br><input type="text" name="phone" class="widefat" dir="ltr" value="<?php echo esc_attr( $bundle['phone'] !== '—' ? $bundle['phone'] : '' ); ?>"></label></p>
				<p><button type="submit" class="button button-primary">ذخیره</button> <span class="ezcd-edit-msg ezcd-admin-msg"></span></p>
			</form>
		<?php elseif ( 'notes' === $tab ) : ?>
			<form id="ezcd-cust-note-form"><p><textarea name="note" class="widefat" rows="3" required placeholder="یادداشت داخلی…"></textarea></p>
			<p><button class="button button-primary">افزودن</button></p></form>
			<?php if ( empty( $bundle['notes'] ) ) : ?><p>یادداشتی نیست.</p><?php else : ?>
			<ul><?php foreach ( $bundle['notes'] as $n ) : ?>
				<li><?php echo esc_html( $n->note ); ?>
				<div class="description"><?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $n->created_at ) : $n->created_at ); ?></div></li>
			<?php endforeach; ?></ul>
			<?php endif; ?>
		<?php endif; ?>
		</div>
		<?php
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	public function ajax_customer_update() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		if ( ! get_userdata( $user_id ) ) {
			wp_send_json_error( array( 'message' => 'کاربر یافت نشد' ) );
		}
		$upd = array( 'ID' => $user_id );
		if ( isset( $_POST['display_name'] ) ) {
			$upd['display_name'] = sanitize_text_field( wp_unslash( $_POST['display_name'] ) );
		}
		if ( isset( $_POST['email'] ) && is_email( wp_unslash( $_POST['email'] ) ) ) {
			$upd['user_email'] = sanitize_email( wp_unslash( $_POST['email'] ) );
		}
		$r = wp_update_user( $upd );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		if ( isset( $_POST['phone'] ) ) {
			update_user_meta( $user_id, 'billing_phone', sanitize_text_field( wp_unslash( $_POST['phone'] ) ) );
		}
		wp_send_json_success( array( 'message' => 'ذخیره شد' ) );
	}

	public function ajax_customer_create() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$name  = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
		$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$pass  = (string) wp_unslash( $_POST['password'] ?? '' );
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'ایمیل نامعتبر' ) );
		}
		if ( email_exists( $email ) ) {
			wp_send_json_error( array( 'message' => 'این ایمیل قبلاً ثبت شده' ) );
		}
		if ( $pass === '' ) {
			$pass = wp_generate_password( 12, true );
		}
		$phone_n = preg_replace( '/\D+/', '', (string) $phone );
		if ( strpos( $phone_n, '98' ) === 0 && strlen( $phone_n ) >= 12 ) {
			$phone_n = '0' . substr( $phone_n, 2 );
		}
		if ( preg_match( '/^09\d{9}$/', $phone_n ) ) {
			$login = $phone_n;
		} else {
			$login = sanitize_user( current( explode( '@', $email ) ), true );
			if ( username_exists( $login ) ) {
				$login .= wp_rand( 10, 99 );
			}
		}
		if ( username_exists( $login ) ) {
			wp_send_json_error( array( 'message' => 'این شماره موبایل قبلاً به‌عنوان نام کاربری ثبت شده است' ) );
		}
		$uid = wp_create_user( $login, $pass, $email );
		if ( is_wp_error( $uid ) ) {
			wp_send_json_error( array( 'message' => $uid->get_error_message() ) );
		}
		wp_update_user( array( 'ID' => $uid, 'display_name' => $name ? $name : $login, 'role' => 'customer' ) );
		if ( $phone ) {
			update_user_meta( $uid, 'billing_phone', $phone );
		}
		wp_send_json_success( array( 'message' => 'مشتری ایجاد شد', 'user_id' => (int) $uid ) );
	}

	public function ajax_customer_note() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		$note    = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
		if ( ! $note || ! class_exists( 'EzLens_CD_Notes' ) ) {
			wp_send_json_error( array( 'message' => 'یادداشت نامعتبر' ) );
		}
		EzLens_CD_Notes::add( $user_id, $note );
		wp_send_json_success( array( 'message' => 'ثبت شد' ) );
	}

	public function ajax_customer_wallet() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_admin', 'nonce' );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		$amount  = absint( $_POST['amount'] ?? 0 );
		$type    = sanitize_key( $_POST['type'] ?? 'credit' );
		$note    = sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) );
		if ( ! class_exists( 'EzLens_CD_Wallet' ) || $amount < 1 ) {
			wp_send_json_error( array( 'message' => 'مبلغ نامعتبر' ) );
		}
		$r = EzLens_CD_Wallet::add_entry( $user_id, $amount, $type === 'debit' ? 'debit' : 'credit', 'admin', $note );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'کیف پول به‌روز شد' ) );
	}
}
