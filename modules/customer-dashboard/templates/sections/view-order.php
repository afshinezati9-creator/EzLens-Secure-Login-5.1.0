<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$order_id = 0;
if ( ! empty( $GLOBALS['ezcd_order_id'] ) ) {
	$order_id = absint( $GLOBALS['ezcd_order_id'] );
} elseif ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'view-order' ) ) {
	global $wp;
	$order_id = isset( $wp->query_vars['view-order'] ) ? absint( $wp->query_vars['view-order'] ) : 0;
} elseif ( isset( $_GET['order_id'] ) ) {
	$order_id = absint( $_GET['order_id'] );
}
$order = $order_id ? wc_get_order( $order_id ) : false;
if ( ! $order || (int) $order->get_user_id() !== get_current_user_id() ) {
	echo '<div class="ezcd-empty"><p>سفارش یافت نشد.</p></div>';
	return;
}
$timeline = EzLens_CD_Orders::timeline( $order );
$back     = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '#';
?>
<div class="ezcd-view-order">
	<a class="ezcd-back" href="<?php echo esc_url( $back ); ?>">← بازگشت به سفارش‌ها</a>
	<div class="ezcd-order-card">
		<div class="ezcd-order-top">
			<div>
				<div class="ezcd-order-id">سفارش <?php echo esc_html( ezcd_fa( '#' . $order->get_order_number() ) ); ?></div>
				<div class="ezcd-order-date"><?php echo esc_html( ezcd_fa( wc_format_datetime( $order->get_date_created(), 'Y/m/d H:i' ) ) ); ?></div>
			</div>
			<span class="ezcd-order-status st-<?php echo esc_attr( EzLens_CD_Orders::status_class( $order ) ); ?>"><?php echo esc_html( EzLens_CD_Orders::status_label( $order ) ); ?></span>
		</div>
		<div class="ezcd-timeline">
			<?php foreach ( $timeline as $step ) : ?>
				<div class="ezcd-tl-step<?php echo $step['done'] ? ' is-done' : ''; ?><?php echo $step['active'] ? ' is-active' : ''; ?>">
					<span class="ezcd-tl-dot"></span>
					<span class="ezcd-tl-label"><?php echo esc_html( $step['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ezcd-section-block">
		<h2 class="ezcd-h2">اقلام سفارش</h2>
		<div class="ezcd-table-wrap">
			<table class="ezcd-table">
				<thead><tr><th>محصول</th><th>تعداد</th><th>مبلغ</th></tr></thead>
				<tbody>
				<?php foreach ( $order->get_items() as $item ) :
					$qty = $item->get_quantity();
					?>
					<tr>
						<td><?php echo esc_html( $item->get_name() ); ?></td>
						<td><?php echo esc_html( ezcd_fa( (string) $qty ) . ' عدد' ); ?></td>
						<td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr><th colspan="2">جمع کل</th><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
				</tfoot>
			</table>
		</div>
	</div>

	<div class="ezcd-order-actions" style="margin-top:16px">
		<?php if ( EzLens_CD_Orders::can_reorder( $order ) ) : ?>
			<button type="button" class="ezcd-btn ezcd-btn-primary ezcd-reorder" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">سفارش مجدد</button>
		<?php endif; ?>
		<?php
		$invoice = $order->get_checkout_order_received_url();
		?>
		<a class="ezcd-btn ezcd-btn-soft" href="<?php echo esc_url( $invoice ); ?>" target="_blank">صفحه رسید</a>
	</div>
</div>
