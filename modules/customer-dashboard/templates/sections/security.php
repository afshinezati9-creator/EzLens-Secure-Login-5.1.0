<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$last = EzLens_CD_Security::last_login();
?>
<div class="ezcd-security">
	<div class="ezcd-info-card">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'shield' ); ?></span>
		<div>
			<strong>ورود دو مرحله‌ای (OTP)</strong>
			<p class="ezcd-muted">ورود با پیامک از طریق سیستم امن EzLens فعال است.</p>
		</div>
	</div>
	<?php if ( $last ) : ?>
		<p class="ezcd-muted">آخرین ورود ثبت‌شده: <?php echo esc_html( ezcd_fa( $last ) ); ?></p>
	<?php endif; ?>

	<form class="ezcd-form" id="ezcd-password-form">
		<h2 class="ezcd-h2">تغییر رمز عبور</h2>
		<label>رمز فعلی<input type="password" name="current_password" autocomplete="current-password" required></label>
		<label>رمز جدید<input type="password" name="new_password" autocomplete="new-password" required></label>
		<label>تکرار رمز جدید<input type="password" name="confirm_password" autocomplete="new-password" required></label>
		<button type="submit" class="ezcd-btn ezcd-btn-primary">بروزرسانی رمز</button>
		<p class="ezcd-form-msg" hidden></p>
	</form>
	<p class="ezcd-muted" style="margin-top:12px"><?php echo esc_html( EzLens_CD_Security::sessions_note() ); ?></p>
</div>
