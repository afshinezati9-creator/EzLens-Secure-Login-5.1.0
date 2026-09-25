<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$available = EzLens_CD_Coupons::available();
$used      = EzLens_CD_Coupons::used_codes();
$points    = EzLens_CD_Coupons::points();
?>
<div class="ezcd-coupons">
	<div class="ezcd-wallet-hero ezcd-points-hero">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'percent' ); ?></span>
		<div>
			<p class="ezcd-muted">امتیاز وفاداری</p>
			<strong class="ezcd-wallet-balance"><?php echo esc_html( ezcd_fa( (string) $points ) ); ?></strong>
			<p class="ezcd-muted">امتیازها به‌تدریج با خریدهایتان رشد می‌کند و برای پیشنهادهای ویژه استفاده می‌شود.</p>
		</div>
	</div>

	<div class="ezcd-section-block">
		<h2 class="ezcd-h2">کدهای فعال فروشگاه</h2>
		<?php if ( empty( $available ) ) : ?>
			<p class="ezcd-muted">الان کد عمومی فعالی برای نمایش نیست. اگر کدی دارید در سبد خرید وارد کنید.</p>
		<?php else : ?>
			<div class="ezcd-coupon-list">
				<?php foreach ( $available as $coupon ) : ?>
					<div class="ezcd-coupon-card">
						<code class="ezcd-code" data-copy="<?php echo esc_attr( $coupon->get_code() ); ?>"><?php echo esc_html( $coupon->get_code() ); ?></code>
						<div>
							<strong><?php echo esc_html( EzLens_CD_Coupons::discount_label( $coupon ) ); ?></strong>
							<?php if ( $coupon->get_description() ) : ?>
								<p class="ezcd-muted"><?php echo esc_html( $coupon->get_description() ); ?></p>
							<?php endif; ?>
						</div>
						<button type="button" class="ezcd-btn ezcd-btn-soft ezcd-copy-code" data-copy="<?php echo esc_attr( $coupon->get_code() ); ?>">کپی</button>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $used ) ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">کدهایی که قبلاً استفاده کرده‌اید</h2>
			<div class="ezcd-used-codes">
				<?php foreach ( $used as $code ) : ?>
					<span class="ezcd-pill is-muted"><?php echo esc_html( $code ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
