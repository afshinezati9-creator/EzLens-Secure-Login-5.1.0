<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$code  = EzLens_CD_Referral::code();
$url   = EzLens_CD_Referral::share_url();
$count = EzLens_CD_Referral::count();
?>
<div class="ezcd-referral">
	<div class="ezcd-rx-intro">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'users' ); ?></span>
		<div>
			<strong>دوستان را دعوت کنید</strong>
			<p class="ezcd-muted">لینک اختصاصی‌تان را بفرستید. وقتی دوستتان ثبت‌نام کند، در آمار دعوت‌ها دیده می‌شود.</p>
		</div>
	</div>

	<div class="ezcd-ref-card">
		<p class="ezcd-muted">کد معرف شما</p>
		<code class="ezcd-code ezcd-code-lg" data-copy="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $code ); ?></code>
		<button type="button" class="ezcd-btn ezcd-btn-grad ezcd-copy-code" data-copy="<?php echo esc_attr( $code ); ?>">کپی کد</button>
	</div>

	<div class="ezcd-ref-card">
		<p class="ezcd-muted">لینک دعوت</p>
		<input type="text" class="ezcd-ref-url" readonly value="<?php echo esc_attr( $url ); ?>" id="ezcd-ref-url">
		<button type="button" class="ezcd-btn ezcd-btn-ghost ezcd-copy-code" data-copy="<?php echo esc_attr( $url ); ?>">کپی لینک</button>
	</div>

	<div class="ezcd-stat-grid" style="grid-template-columns:1fr 1fr;max-width:360px">
		<div class="ezcd-stat-card ezcd-stat-completed">
			<span class="ezcd-stat-ico"><?php echo ezcd_icon( 'users' ); ?></span>
			<span class="ezcd-stat-num"><?php echo esc_html( ezcd_fa( (string) $count ) ); ?></span>
			<span class="ezcd-stat-lbl">دعوت موفق</span>
		</div>
	</div>

	<p class="ezcd-muted" style="margin-top:16px">اشتراک‌گذاری محترمانه لینک، بهترین راه معرفی فروشگاه است — بدون فشار و با اعتماد.</p>
</div>
