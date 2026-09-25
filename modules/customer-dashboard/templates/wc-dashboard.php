<?php
/**
 * WooCommerce dashboard.php override — Phase 1
 * Replaces default "Hello …" with EzLens shell.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'EzLens_CD_Router' ) ) {
	EzLens_CD_Router::get_instance()->render_shell( 'overview' );
} else {
	echo '<p>ماژول Customer Dashboard لود نشده است.</p>';
}
