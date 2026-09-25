<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$coupons = function_exists( 'wc_get_coupons' ) ? array() : array();
if ( function_exists( 'get_posts' ) ) {
	$coupons = get_posts(
		array(
			'post_type'      => 'shop_coupon',
			'posts_per_page' => 40,
			'post_status'    => 'publish',
		)
	);
}
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>تخفیف‌ها و کارت هدیه</h1>
	<div class="ezcd-admin-grid">
		<div class="ezcd-admin-card">
			<h2>کدهای تخفیف ووکامرس</h2>
			<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=shop_coupon' ) ); ?>">ایجاد کد تخفیف</a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_coupon' ) ); ?>">همه کدها</a></p>
			<table class="wp-list-table widefat striped">
				<thead><tr><th>کد</th><th>مبلغ/درصد</th><th>مصرف</th></tr></thead>
				<tbody>
				<?php if ( empty( $coupons ) ) : ?>
					<tr><td colspan="3">کد تخفیفی نیست.</td></tr>
				<?php else : ?>
					<?php foreach ( $coupons as $c ) :
						$coupon = new WC_Coupon( $c->ID );
						?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_post_link( $c->ID ) ); ?>"><code><?php echo esc_html( $coupon->get_code() ); ?></code></a></td>
							<td><?php echo esc_html( $coupon->get_amount() . ( 'percent' === $coupon->get_discount_type() ? '%' : '' ) ); ?></td>
							<td><?php echo esc_html( (string) $coupon->get_usage_count() ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="ezcd-admin-card">
			<h2>کارت هدیه / کیف پول</h2>
			<p class="description">شارژهای در انتظار را از منوی «شارژ کیف پول» بررسی کنید. برای کمپین پیامکی از «پیام جمعی» استفاده کنید.</p>
			<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-deposits' ) ); ?>">درخواست‌های شارژ</a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-mass' ) ); ?>">کمپین پیامکی</a></p>
		</div>
	</div>
</div>
