<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dir = dirname( __FILE__ );
$files = array(
	'class-auth-rest.php',
	'class-orders-rest.php',
	'class-customers-rest.php',
	'class-comments-rest.php',
	'class-support-rest.php',
	'class-messages-rest.php',
	'class-campaigns-rest.php',
	'class-wallet-rest.php',
	'class-charity-rest.php',
	'class-discounts-rest.php',
	'class-mass-rest.php',
	'class-purchase-rest.php',
	'class-stats-rest.php',
	'class-inbox-rest.php',
	'class-requests-rest.php',
	'class-posts-rest.php',
);
foreach ( $files as $file ) {
	$path = $dir . '/' . $file;
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}
