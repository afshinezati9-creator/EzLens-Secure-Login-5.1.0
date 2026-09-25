<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'processing';
$map = array(
	'processing' => array( 'processing', 'on-hold', 'pending' ),
	'completed'  => array( 'completed' ),
	'cancelled'  => array( 'cancelled', 'failed' ),
	'refunded'   => array( 'refunded' ),
	'cart'       => array(), // abandoned via session not WC
);
if ( ! isset( $map[ $tab ] ) ) {
	$tab = 'processing';
}
$tabs = array(
	'processing' => 'جاری',
	'completed'  => 'پرداخت‌شده / تحویل',
	'cancelled'  => 'لغو شده',
	'refunded'   => 'مرجوعی',
);
$orders = array();
if ( function_exists( 'wc_get_orders' ) && 'cart' !== $tab ) {
	$orders = wc_get_orders(
		array(
			'limit'  => 40,
			'status' => $map[ $tab ],
			'orderby'=> 'date',
			'order'  => 'DESC',
		)
	);
}
$nonce = wp_create_nonce( 'ezcd_admin' );
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>سفارش‌های مشتریان</h1>
	<nav class="ezcd-admin-tabs">
		<?php foreach ( $tabs as $k => $label ) : ?>
			<a class="ezcd-admin-tab<?php echo $tab === $k ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-orders&tab=' . $k ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>
	<table class="wp-list-table widefat striped ezcd-admin-table" id="ezcd-orders-table">
		<thead>
			<tr>
				<th>شماره</th>
				<th>مشتری</th>
				<th>مبلغ</th>
				<th>وضعیت</th>
				<th>تاریخ</th>
				<th>اقدام</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $orders ) ) : ?>
			<tr><td colspan="6">موردی در این تب نیست.</td></tr>
		<?php else : ?>
			<?php foreach ( $orders as $order ) : ?>
				<tr data-id="<?php echo esc_attr( $order->get_id() ); ?>">
					<td><a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a></td>
					<td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td>
					<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
					<td class="ezcd-st"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
					<td><?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y/m/d H:i' ) : '' ); ?></td>
					<td class="ezcd-actions">
						<button type="button" class="button ezcd-oact" data-do="processing" data-id="<?php echo esc_attr( $order->get_id() ); ?>">در حال انجام</button>
						<button type="button" class="button button-primary ezcd-oact" data-do="complete" data-id="<?php echo esc_attr( $order->get_id() ); ?>">تکمیل</button>
						<button type="button" class="button ezcd-oact" data-do="cancel" data-id="<?php echo esc_attr( $order->get_id() ); ?>">لغو</button>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
<script>
(function(){
	var nonce = '<?php echo esc_js( $nonce ); ?>';
	document.querySelectorAll('.ezcd-oact').forEach(function(btn){
		btn.addEventListener('click', function(){
			var body = new FormData();
			body.append('action','ezcd_admin_order_action');
			body.append('nonce', nonce);
			body.append('order_id', btn.getAttribute('data-id'));
			body.append('do', btn.getAttribute('data-do'));
			btn.disabled = true;
			fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
				btn.disabled = false;
				if(!res || !res.success){ alert((res&&res.data&&res.data.message)||'خطا'); return; }
				var tr = btn.closest('tr');
				if(tr && tr.querySelector('.ezcd-st')) tr.querySelector('.ezcd-st').textContent = res.data.status || 'به‌روز شد';
				alert(res.data.message);
			});
		});
	});
})();
</script>
