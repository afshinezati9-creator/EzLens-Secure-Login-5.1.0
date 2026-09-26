<?php
/**
 * Wallet online top-up via WooCommerce payment gateways (ZarinPal preferred).
 * Creates a real WC order, redirects to gateway, credits wallet on payment success.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find best available gateway (ZarinPal first, then any enabled gateway).
 *
 * @return WC_Payment_Gateway|null
 */
function ezcd_find_zarinpal_gateway() {
	if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
		return null;
	}
	$all = WC()->payment_gateways()->payment_gateways();
	if ( ! is_array( $all ) ) {
		return null;
	}
	$preferred = array( 'WC_ZarinPal', 'zarinpal', 'WC_Zarinpal', 'zarinpalgateway', 'WC_Gateway_ZarinPal', 'wc_zarinpal' );
	foreach ( $preferred as $k ) {
		if ( isset( $all[ $k ] ) && 'yes' === $all[ $k ]->enabled ) {
			return $all[ $k ];
		}
	}
	foreach ( $all as $id => $gw ) {
		if ( empty( $gw->enabled ) || 'yes' !== $gw->enabled ) {
			continue;
		}
		$t = strtolower( $id . ' ' . ( $gw->title ?? '' ) . ' ' . ( $gw->method_title ?? '' ) );
		if ( false !== strpos( $t, 'zarin' ) || false !== strpos( $t, 'زرین' ) ) {
			return $gw;
		}
	}
	// Fallback: first enabled gateway
	foreach ( $all as $gw ) {
		if ( isset( $gw->enabled ) && 'yes' === $gw->enabled ) {
			return $gw;
		}
	}
	return null;
}

/**
 * Create wallet top-up order and return pay URL.
 *
 * @param int $user_id
 * @param int $amount  Amount in store currency units (Toman).
 * @return array|WP_Error { order_id, redirect }
 */
function ezcd_create_wallet_topup_order( $user_id, $amount ) {
	if ( class_exists( 'EzLens_Auth_Settings' ) && '1' !== (string) EzLens_Auth_Settings::get( 'wallet_payment_online_enabled' ) ) {
		return new WP_Error( 'disabled', 'پرداخت آنلاین کیف پول در حال حاضر فعال نیست' );
	}
	$user_id = absint( $user_id );
	$amount  = absint( $amount );
	if ( $user_id < 1 || $amount < 10000 ) {
		return new WP_Error( 'amount', 'حداقل مبلغ ۱۰٬۰۰۰ تومان است' );
	}
	if ( ! function_exists( 'wc_create_order' ) ) {
		return new WP_Error( 'wc', 'ووکامرس فعال نیست' );
	}

	$order = wc_create_order( array( 'customer_id' => $user_id ) );
	if ( is_wp_error( $order ) ) {
		return $order;
	}

	$item = new WC_Order_Item_Fee();
	$item->set_name( 'شارژ کیف پول' );
	$item->set_amount( $amount );
	$item->set_total( $amount );
	$order->add_item( $item );

	$gw = ezcd_find_zarinpal_gateway();
	if ( $gw ) {
		$order->set_payment_method( $gw );
		$order->set_payment_method_title( $gw->get_title() );
	}

	$order->set_currency( get_woocommerce_currency() );
	$order->calculate_totals( false );
	if ( (float) $order->get_total() < 1 ) {
		$order->set_total( $amount );
	}

	$order->update_meta_data( '_ezcd_wallet_topup', 1 );
	$order->update_meta_data( '_ezcd_wallet_amount', $amount );
	$order->update_meta_data( '_ezcd_wallet_user', $user_id );
	$order->update_meta_data( '_ezcd_wallet_needs_credit', 1 );
	$wallet_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'wallet' ) : home_url( '/my-account/wallet/' );
	$order->update_meta_data( '_ezcd_wallet_return', $wallet_url );
	$order->set_status( 'pending', 'شارژ کیف پول' );
	$order->save();

	if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
		EzLens_CD_Wallet_Deposits::create(
			array(
				'amount'   => $amount,
				'method'   => 'online',
				'ref_code' => 'order:' . $order->get_id(),
			)
		);
	}

	$pay_url = $order->get_checkout_payment_url( true );

	// Prefer direct gateway redirect when process_payment is available
	if ( $gw && is_callable( array( $gw, 'process_payment' ) ) ) {
		try {
			// Some gateways read $_POST['payment_method']
			$_POST['payment_method'] = $gw->id;
			$result = $gw->process_payment( $order->get_id() );
			if ( is_array( $result ) && ! empty( $result['redirect'] ) ) {
				$pay_url = $result['redirect'];
			}
		} catch ( Exception $e ) {
			// keep order-pay URL
		}
	}

	return array(
		'order_id' => $order->get_id(),
		'redirect' => $pay_url,
	);
}

/**
 * Online top-up AJAX is handled by EzLens_CD_Ajax::handle_wallet_deposit().
 * Keeping a single handler prevents duplicate wp_ajax callbacks and ensures
 * the standard login + nonce checks are always applied.
 */
/**
 * Credit wallet after successful payment (idempotent).
 *
 * @param int $order_id Order ID.
 */
function ezcd_wallet_credit_on_payment( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->get_meta( '_ezcd_wallet_topup' ) ) {
		return;
	}
	if ( $order->get_meta( '_ezcd_wallet_credited' ) ) {
		return;
	}

	// Only credit when paid / processing / completed
	$status = $order->get_status();
	$ok     = in_array( $status, array( 'processing', 'completed', 'on-hold' ), true );
	if ( ! $ok && ! $order->is_paid() ) {
		return;
	}

	$user_id = (int) $order->get_meta( '_ezcd_wallet_user' );
	$amount  = (int) $order->get_meta( '_ezcd_wallet_amount' );
	if ( ! $user_id ) {
		$user_id = (int) $order->get_user_id();
	}
	if ( $user_id < 1 || $amount < 1 ) {
		return;
	}
	if ( ! class_exists( 'EzLens_CD_Wallet' ) ) {
		return;
	}

	$entry = EzLens_CD_Wallet::add_entry(
		$user_id,
		$amount,
		'credit',
		'topup',
		'شارژ آنلاین سفارش #' . $order_id,
		'order',
		$order_id
	);
	if ( is_wp_error( $entry ) ) {
		return;
	}

	$order->update_meta_data( '_ezcd_wallet_credited', 1 );
	$order->update_meta_data( '_ezcd_wallet_needs_credit', 0 );
	$order->add_order_note( 'موجودی کیف پول به مبلغ ' . number_format_i18n( $amount ) . ' تومان شارژ شد.' );
	$order->save();

	// Mark matching deposit request approved
	if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) && method_exists( 'EzLens_CD_Wallet_Deposits', 'approve_by_ref' ) ) {
		EzLens_CD_Wallet_Deposits::approve_by_ref( 'order:' . $order_id, $user_id );
	} elseif ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
		global $wpdb;
		$table = EzLens_CD_Wallet_Deposits::table();
		$wpdb->update(
			$table,
			array(
				'status'      => 'approved',
				'reviewed_at' => current_time( 'mysql' ),
			),
			array(
				'user_id'  => $user_id,
				'ref_code' => 'order:' . $order_id,
				'status'   => 'pending',
			),
			array( '%s', '%s' ),
			array( '%d', '%s', '%s' )
		);
	}
}

add_action( 'woocommerce_payment_complete', 'ezcd_wallet_credit_on_payment', 20 );
add_action( 'woocommerce_order_status_completed', 'ezcd_wallet_credit_on_payment', 20 );
add_action( 'woocommerce_order_status_processing', 'ezcd_wallet_credit_on_payment', 20 );
add_action( 'woocommerce_thankyou', 'ezcd_wallet_credit_on_payment', 5 );

/* Style order-pay page for wallet top-ups */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ! is_wc_endpoint_url( 'order-pay' ) ) {
		return;
	}
	$css = '
	body.woocommerce-order-pay .woocommerce{max-width:32.5em;margin:2.5em auto;padding:0 1em}
	body.woocommerce-order-pay #order_review,body.woocommerce-order-pay .woocommerce-checkout-payment{
		background:#fff;border:0.0625em solid #e8edf5;border-radius:1em;padding:1.25em;box-shadow:0 0.5em 1.5em rgba(15,23,42,.06)
	}
	body.woocommerce-order-pay button#place_order,body.woocommerce-order-pay button[type=submit].button{
		width:100%!important;min-height:3.25em!important;border:0!important;border-radius:0.875em!important;
		background:linear-gradient(135deg,#031f8a,#011663)!important;color:#fff!important;font-weight:800!important
	}
	body.woocommerce-order-pay .woocommerce-info{border-radius:0.75em;border-right:0.25em solid #031f8a}
	';
	wp_register_style( 'ezcd-wallet-order-pay', false, array(), '1.0' );
	wp_enqueue_style( 'ezcd-wallet-order-pay' );
	wp_add_inline_style( 'ezcd-wallet-order-pay', $css );
}, 30 );


/**
 * Send customer back to wallet page after gateway.
 */
add_filter( 'woocommerce_get_return_url', function ( $url, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $url;
	}
	if ( ! $order->get_meta( '_ezcd_wallet_topup' ) ) {
		return $url;
	}
	$wallet = $order->get_meta( '_ezcd_wallet_return' );
	if ( ! $wallet ) {
		$wallet = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'wallet' ) : home_url( '/my-account/wallet/' );
	}
	$amt = (int) $order->get_meta( '_ezcd_wallet_amount' );
	$ok  = $order->is_paid() || in_array( $order->get_status(), array( 'processing', 'completed' ), true );
	$wallet = add_query_arg(
		array(
			'ezcd_topup' => $ok ? 'ok' : 'fail',
			'amt'        => $amt,
		),
		$wallet
	);
	return $wallet;
}, 20, 2 );

add_action( 'woocommerce_thankyou', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->get_meta( '_ezcd_wallet_topup' ) ) {
		return;
	}
	// Credit if paid
	if ( function_exists( 'ezcd_wallet_credit_on_payment' ) ) {
		ezcd_wallet_credit_on_payment( $order_id );
	}
	$wallet = $order->get_meta( '_ezcd_wallet_return' );
	if ( ! $wallet ) {
		$wallet = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'wallet' ) : home_url( '/my-account/wallet/' );
	}
	$amt = (int) $order->get_meta( '_ezcd_wallet_amount' );
	$ok  = $order->is_paid() || in_array( $order->get_status(), array( 'processing', 'completed' ), true );
	$dest = add_query_arg(
		array(
			'ezcd_topup' => $ok ? 'ok' : 'fail',
			'amt'        => $amt,
		),
		$wallet
	);
	if ( ! headers_sent() ) {
		wp_safe_redirect( $dest );
		exit;
	}
}, 1 );
