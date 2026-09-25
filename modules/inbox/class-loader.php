<?php
/**
 * بارگذار ماژول صندوق ایمیل‌ها — فقط کلاس + AJAX
 * ثبت منو در dashboard.php انجام می‌شود.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ezlens_inbox_load_class() {
	if ( ! class_exists( 'EzLens_Inbox' ) ) {
		$f = dirname( __FILE__ ) . '/includes/class-inbox.php';
		if ( is_readable( $f ) ) {
			require_once $f;
		}
	}
	if ( ! class_exists( 'EzLens_Inbox_Ajax' ) ) {
		$f = dirname( __FILE__ ) . '/includes/class-ajax.php';
		if ( is_readable( $f ) ) {
			require_once $f;
		}
	}
}

add_action( 'plugins_loaded', 'ezlens_inbox_load_class', 5 );
add_action( 'init', 'ezlens_inbox_load_class', 1 );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( empty( $_GET['page'] ) || $_GET['page'] !== 'ezlens-inbox' ) {
		return;
	}
	ezlens_inbox_load_class();
	$base = plugin_dir_url( dirname( __FILE__ ) . '/../ezlens-secure-login.php' );
	// fallback relative to this module
	$mod_url = plugins_url( '/', dirname( __FILE__ ) . '/class-loader.php' );
	$css = dirname( __FILE__ ) . '/admin/assets/css/inbox-admin.css';
	$js  = dirname( __FILE__ ) . '/admin/assets/js/inbox-admin.js';
	if ( is_readable( $css ) ) {
		wp_enqueue_style( 'ezlens-inbox-admin', $mod_url . 'admin/assets/css/inbox-admin.css', array(), filemtime( $css ) );
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script( 'ezlens-inbox-admin', $mod_url . 'admin/assets/js/inbox-admin.js', array( 'jquery' ), filemtime( $js ), true );
		wp_localize_script(
			'ezlens-inbox-admin',
			'ezInbox',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'ezlens_inbox_nonce' ),
			)
		);
	}
} );
