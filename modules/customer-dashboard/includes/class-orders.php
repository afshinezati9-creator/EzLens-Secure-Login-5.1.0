<?php
/**
 * Customer orders helpers
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Orders {

	/**
	 * @param string $tab current|completed|cancelled|refunded|all
	 * @return WC_Order[]
	 */
	public static function get_orders( $tab = 'current', $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! function_exists( 'wc_get_orders' ) ) {
			return array();
		}
		$map = array(
			'current'   => array( 'wc-processing', 'wc-on-hold', 'wc-pending' ),
			'completed' => array( 'wc-completed' ),
			'cancelled' => array( 'wc-cancelled', 'wc-failed' ),
			'refunded'  => array( 'wc-refunded' ),
			'all'       => array_keys( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array() ),
		);
		$status = isset( $map[ $tab ] ) ? $map[ $tab ] : $map['current'];
		return wc_get_orders(
			array(
				'customer_id' => $user_id,
				'status'      => $status,
				'limit'       => 30,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
	}

	public static function status_label( $order ) {
		if ( ! $order ) {
			return '';
		}
		$statuses = wc_get_order_statuses();
		$key      = 'wc-' . $order->get_status();
		return isset( $statuses[ $key ] ) ? $statuses[ $key ] : $order->get_status();
	}

	public static function status_class( $order ) {
		$st = $order->get_status();
		if ( in_array( $st, array( 'processing', 'on-hold', 'pending' ), true ) ) {
			return 'current';
		}
		if ( 'completed' === $st ) {
			return 'done';
		}
		if ( in_array( $st, array( 'cancelled', 'failed' ), true ) ) {
			return 'cancel';
		}
		if ( 'refunded' === $st ) {
			return 'refund';
		}
		return 'muted';
	}

	/**
	 * Simple timeline steps for order.
	 *
	 * @return array[] {label, done, active}
	 */
	public static function timeline( $order ) {
		$st = $order->get_status();
		$steps = array(
			array( 'key' => 'pending', 'label' => 'ثبت سفارش' ),
			array( 'key' => 'processing', 'label' => 'تأیید و آماده‌سازی' ),
			array( 'key' => 'shipped', 'label' => 'ارسال' ),
			array( 'key' => 'completed', 'label' => 'تحویل' ),
		);
		$order_map = array(
			'pending'    => 0,
			'on-hold'    => 1,
			'processing' => 1,
			'completed'  => 3,
			'cancelled'  => -1,
			'failed'     => -1,
			'refunded'   => -1,
		);
		$idx = isset( $order_map[ $st ] ) ? $order_map[ $st ] : 0;
		// If tracking number exists treat as shipped
		$track = $order->get_meta( '_tracking_number' );
		if ( ! $track ) {
			$track = $order->get_meta( 'tracking_number' );
		}
		if ( $track && $idx >= 1 && $idx < 3 ) {
			$idx = 2;
		}
		$out = array();
		foreach ( $steps as $i => $s ) {
			$out[] = array(
				'label'  => $s['label'],
				'done'   => ( $idx >= 0 && $i < $idx ),
				'active' => ( $idx >= 0 && $i === $idx ),
				'failed' => ( $idx < 0 ),
			);
		}
		return $out;
	}

	public static function can_reorder( $order ) {
		return $order && $order->get_item_count() > 0;
	}

	/**
	 * Add all order items to cart (simple products / variations).
	 *
	 * @return true|WP_Error
	 */
	public static function reorder_to_cart( $order_id ) {
		$order = wc_get_order( absint( $order_id ) );
		if ( ! $order || (int) $order->get_user_id() !== get_current_user_id() ) {
			return new WP_Error( 'forbidden', 'دسترسی غیرمجاز' );
		}
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return new WP_Error( 'cart', 'سبد در دسترس نیست' );
		}
		$added = 0;
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product || ! $product->is_purchasable() ) {
				continue;
			}
			$qty = max( 1, (int) $item->get_quantity() );
			$vid = $item->get_variation_id();
			$pid = $vid ? $vid : $item->get_product_id();
			$meta = array();
			// variation attributes
			if ( $vid ) {
				$variation = wc_get_product( $vid );
				if ( $variation ) {
					$meta = $variation->get_variation_attributes();
				}
			}
			$r = WC()->cart->add_to_cart( $item->get_product_id(), $qty, $vid, $meta );
			if ( $r ) {
				$added++;
			}
		}
		if ( ! $added ) {
			return new WP_Error( 'empty', 'هیچ کالایی قابل افزودن نبود' );
		}
		return true;
	}
}
