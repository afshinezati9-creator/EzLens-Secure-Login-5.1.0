<?php
/**
 * Simplified my-account wrapper (logged-in)
 *
 * @see woocommerce/templates/myaccount/my-account.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * My Account navigation + content.
 * Navigation is emptied; content handles endpoints / dashboard.
 */
do_action( 'woocommerce_account_navigation' );
?>
<div class="woocommerce-MyAccount-content ezcd-wc-content">
	<?php do_action( 'woocommerce_account_content' ); ?>
</div>
