<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'current';
if ( ! in_array( $tab, array( 'current', 'completed', 'cancelled', 'refunded' ), true ) ) {
	$tab = 'current';
}
$tabs = array(
	'current'   => array( 'label' => 'جاری', 'icon' => 'package', 'empty' => 'هنوز سفارشی در جریان نیست. وقتی خریدی ثبت کنید، از اینجا قدم‌به‌قدم وضعیتش را می‌بینید.' ),
	'completed' => array( 'label' => 'تحویل‌شده', 'icon' => 'check-circle', 'empty' => 'هنوز سفارشی تحویل نشده. بعد از دریافت، اینجا آرشیو خریدهای موفق‌تان می‌ماند.' ),
	'cancelled' => array( 'label' => 'لغوشده', 'icon' => 'x-circle', 'empty' => 'سفارش لغوشده‌ای ندارید — و این خبر خوبی است.' ),
	'refunded'  => array( 'label' => 'مرجوعی', 'icon' => 'refresh-cw', 'empty' => 'مرجوعی ثبت‌شده‌ای نیست. اگر لازم شد، پشتیبانی کمکتان می‌کند.' ),
);
$orders = EzLens_CD_Orders::get_orders( $tab );
$base   = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '';
$shop   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<div class="ezcd-orders">
	<div class="ezcd-tabs ezcd-tabs-rich">
		<?php foreach ( $tabs as $k => $meta ) :
			$url = add_query_arg( 'tab', $k, $base );
			?>
			<a class="ezcd-tab ezcd-tab-<?php echo esc_attr( $k ); ?><?php echo $tab === $k ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>" data-ezcd-nav="orders" data-tab="<?php echo esc_attr( $k ); ?>">
				<span class="ezcd-tab-ico"><?php echo ezcd_icon( $meta['icon'] ); ?></span>
				<span class="ezcd-tab-lbl"><?php echo esc_html( $meta['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<?php if ( empty( $orders ) ) : ?>
		<div class="ezcd-empty ezcd-empty-rich">
			<div class="ezcd-empty-ico ezcd-empty-<?php echo esc_attr( $tab ); ?>"><?php echo ezcd_icon( $tabs[ $tab ]['icon'] ); ?></div>
			<p><?php echo esc_html( $tabs[ $tab ]['empty'] ); ?></p>
			<?php if ( 'current' === $tab || 'completed' === $tab ) : ?>
				<a class="ezcd-btn ezcd-btn-grad" href="<?php echo esc_url( $shop ); ?>">مشاهده فروشگاه</a>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="ezcd-order-list">
			<?php foreach ( $orders as $order ) :
				$view = $order->get_view_order_url();
				$cls  = EzLens_CD_Orders::status_class( $order );
				?>
				<article class="ezcd-order-card">
					<div class="ezcd-order-top">
						<div>
							<div class="ezcd-order-id">سفارش <?php echo esc_html( ezcd_fa( '#' . $order->get_order_number() ) ); ?></div>
							<div class="ezcd-order-date"><?php echo esc_html( ezcd_fa( wc_format_datetime( $order->get_date_created(), 'Y/m/d H:i' ) ) ); ?></div>
						</div>
						<span class="ezcd-order-status st-<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( EzLens_CD_Orders::status_label( $order ) ); ?></span>
					</div>
					<div class="ezcd-order-mid">
						<span><?php echo esc_html( ezcd_fa( (string) $order->get_item_count() ) ); ?> قلم</span>
						<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
					</div>
					<div class="ezcd-order-actions">
						<a class="ezcd-btn ezcd-btn-ghost" href="<?php echo esc_url( $view ); ?>" data-ezcd-nav="orders">جزئیات</a>
						<?php if ( EzLens_CD_Orders::can_reorder( $order ) ) : ?>
							<button type="button" class="ezcd-btn ezcd-btn-grad ezcd-reorder" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">سفارش مجدد</button>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
