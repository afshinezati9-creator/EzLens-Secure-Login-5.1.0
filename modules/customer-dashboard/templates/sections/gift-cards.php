<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$purchased = EzLens_CD_Gift_Cards::purchased_by();
$redeemed  = EzLens_CD_Gift_Cards::redeemed_by();
$balance   = EzLens_CD_Wallet::balance();
?>
<div class="ezcd-gift">
	<div class="ezcd-rx-intro">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'gift' ); ?></span>
		<div>
			<strong>کارت هدیه ایزی‌لنز</strong>
			<p class="ezcd-muted">برای عزیزانتان کد هدیه بسازید یا کدی که دریافت کرده‌اید را شارژ کنید.</p>
		</div>
	</div>

	<div class="ezcd-gift-grid">
		<form class="ezcd-form ezcd-card-form" id="ezcd-gift-redeem-form">
			<h2 class="ezcd-h2">دریافت کارت هدیه</h2>
			<p class="ezcd-muted">کد را وارد کنید تا مبلغ به کیف پولتان اضافه شود.</p>
			<label>کد کارت
				<input type="text" name="code" placeholder="مثلاً EZAB12CD34" required autocomplete="off">
			</label>
			<button type="submit" class="ezcd-btn ezcd-btn-primary">اعمال کد</button>
		</form>

		<form class="ezcd-form ezcd-card-form" id="ezcd-gift-buy-form">
			<h2 class="ezcd-h2">صدور کارت هدیه</h2>
			<p class="ezcd-muted">موجودی کیف پول: <strong><?php echo esc_html( ezcd_fa( number_format_i18n( $balance ) ) ); ?></strong> تومان</p>
			<label>مبلغ (تومان)
				<input type="number" name="amount" min="10000" step="1000" placeholder="۵۰۰۰۰۰" required>
			</label>
			<label>نام گیرنده (اختیاری)
				<input type="text" name="recipient_name">
			</label>
			<label>پیام روی کارت
				<textarea name="message" rows="2" placeholder="با آرزوی سلامتی چشم‌ها…"></textarea>
			</label>
			<button type="submit" class="ezcd-btn ezcd-btn-soft">صدور از موجودی کیف پول</button>
		</form>
	</div>

	<div class="ezcd-section-block">
		<h2 class="ezcd-h2">کارت‌های صادرشده توسط شما</h2>
		<?php if ( empty( $purchased ) ) : ?>
			<p class="ezcd-muted">هنوز کارتی صادر نکرده‌اید.</p>
		<?php else : ?>
			<div class="ezcd-gift-list">
				<?php foreach ( $purchased as $g ) : ?>
					<div class="ezcd-gift-item">
						<code class="ezcd-code" data-copy="<?php echo esc_attr( $g->code ); ?>"><?php echo esc_html( $g->code ); ?></code>
						<span><?php echo esc_html( ezcd_fa( number_format_i18n( (int) $g->amount ) ) ); ?> تومان</span>
						<span class="ezcd-pill <?php echo 'active' === $g->status ? 'is-ok' : 'is-muted'; ?>">
							<?php echo 'active' === $g->status ? 'فعال' : 'استفاده‌شده'; ?>
						</span>
						<button type="button" class="ezcd-btn ezcd-btn-soft ezcd-copy-code" data-copy="<?php echo esc_attr( $g->code ); ?>">کپی کد</button>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $redeemed ) ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">کارت‌هایی که دریافت کرده‌اید</h2>
			<div class="ezcd-gift-list">
				<?php foreach ( $redeemed as $g ) : ?>
					<div class="ezcd-gift-item">
						<code><?php echo esc_html( $g->code ); ?></code>
						<span><?php echo esc_html( ezcd_fa( number_format_i18n( (int) $g->amount ) ) ); ?> تومان</span>
						<span class="ezcd-pill is-ok">شارژ شد</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
