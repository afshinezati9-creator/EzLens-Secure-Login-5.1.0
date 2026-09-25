<?php
/**
 * Overview / dashboard home data
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Overview {

	/**
	 * Order status counts for current user.
	 *
	 * @return array{processing:int,completed:int,on-hold:int,pending:int,cancelled:int,refunded:int,total:int}
	 */
	public static function order_counts( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$counts  = array(
			'processing' => 0,
			'completed'  => 0,
			'on-hold'    => 0,
			'pending'    => 0,
			'cancelled'  => 0,
			'refunded'   => 0,
			'failed'     => 0,
			'total'      => 0,
		);
		if ( ! $user_id || ! function_exists( 'wc_get_orders' ) ) {
			return $counts;
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'limit'       => -1,
				'return'      => 'ids',
				'status'      => array_keys( wc_get_order_statuses() ),
			)
		);
		$counts['total'] = count( $orders );
		foreach ( $orders as $oid ) {
			$order = wc_get_order( $oid );
			if ( ! $order ) {
				continue;
			}
			$st = $order->get_status(); // without wc-
			if ( isset( $counts[ $st ] ) ) {
				$counts[ $st ]++;
			}
		}
		return $counts;
	}

	/**
	 * Active = processing + on-hold + pending
	 */
	public static function cards( $user_id = 0 ) {
		$c = self::order_counts( $user_id );
		return array(
			array(
				'key'   => 'current',
				'label' => 'جاری',
				'count' => $c['processing'] + $c['on-hold'] + $c['pending'],
				'icon'  => 'package',
				'href'  => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '#',
			),
			array(
				'key'   => 'completed',
				'label' => 'تحویل‌شده',
				'count' => $c['completed'],
				'icon'  => 'check-circle',
				'href'  => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '#',
			),
			array(
				'key'   => 'cancelled',
				'label' => 'لغوشده',
				'count' => $c['cancelled'] + $c['failed'],
				'icon'  => 'x-circle',
				'href'  => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '#',
			),
			array(
				'key'   => 'refunded',
				'label' => 'مرجوعی',
				'count' => $c['refunded'],
				'icon'  => 'refresh-cw',
				'href'  => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '#',
			),
		);
	}

	/**
	 * Recent completed products for "buy again" carousel (max 8).
	 *
	 * @return WC_Product[]
	 */
	public static function frequent_products( $user_id = 0, $limit = 8 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! function_exists( 'wc_get_orders' ) ) {
			return array();
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'status'      => array( 'wc-completed', 'wc-processing' ),
				'limit'       => 20,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
		$ids = array();
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$pid = $item->get_product_id();
				if ( $pid ) {
					$ids[ $pid ] = isset( $ids[ $pid ] ) ? $ids[ $pid ] + $item->get_quantity() : $item->get_quantity();
				}
			}
		}
		arsort( $ids );
		$out = array();
		foreach ( array_keys( $ids ) as $pid ) {
			$p = wc_get_product( $pid );
			if ( $p && $p->is_purchasable() ) {
				$out[] = $p;
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * Cart has items?
	 */
	public static function cart_count() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return 0;
		}
		return (int) WC()->cart->get_cart_contents_count();
	}
}
