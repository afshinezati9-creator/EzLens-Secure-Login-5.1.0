<?php
/**
 * تنظیمات کمپین — فاز ۳: فیلدهای محدود + ارجاع به پیام‌رسانی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$s = class_exists( 'EzLens_Auth_Settings' ) ? EzLens_Auth_Settings::get_all() : array();
$msg = admin_url( 'admin.php?page=ezlens-auth-settings&tab=messaging' );
?>
<div class="campaign-tab-content ezc-panel">
	<div class="ezc-panel-head">
		<div>
			<h2>تنظیمات ارسال کمپین</h2>
			<p class="ezc-muted">سرویس SMS/ایمیل از تب پیام‌رسانی پلاگین خوانده می‌شود.</p>
		</div>
		<a class="button button-primary ezc-btn-primary" href="<?php echo esc_url( $msg ); ?>">باز کردن پیام‌رسانی</a>
	</div>

	<div class="ezc-settings-card">
		<div class="ezc-field">
			<label for="campaign_max_recipients">حداکثر مخاطب در هر کمپین</label>
			<input type="number" id="campaign_max_recipients" name="campaign_max_recipients" min="100" max="50000"
				value="<?php echo esc_attr( $s['campaign_max_recipients'] ?? 5000 ); ?>">
		</div>
		<div class="ezc-field">
			<label for="campaign_batch_size">اندازه هر batch</label>
			<input type="number" id="campaign_batch_size" name="campaign_batch_size" min="1" max="200"
				value="<?php echo esc_attr( $s['campaign_batch_size'] ?? 25 ); ?>">
		</div>
		<div class="ezc-field">
			<label for="campaign_delay_ms">فاصله بین ارسال‌ها (ms)</label>
			<input type="number" id="campaign_delay_ms" name="campaign_delay_ms" min="0" max="5000"
				value="<?php echo esc_attr( $s['campaign_delay_ms'] ?? 0 ); ?>">
		</div>
		<div class="ezc-field ezc-field-check">
			<label>
				<input type="checkbox" name="campaign_track_enabled" value="1" <?php checked( ( $s['campaign_track_enabled'] ?? '' ), '1' ); ?>>
				رهگیری باز شدن ایمیل
			</label>
		</div>
		<div class="ezc-field ezc-field-check">
			<label>
				<input type="checkbox" name="campaign_unsubscribe_enabled" value="1" <?php checked( ( $s['campaign_unsubscribe_enabled'] ?? '' ), '1' ); ?>>
				لینک لغو اشتراک در ایمیل
			</label>
		</div>
		<p class="ezc-hint">پس از تغییر، از دکمه ذخیره همین تب (اگر در JS فعال است) یا «ذخیره همه تنظیمات» در صفحه پیام‌رسانی استفاده کنید.</p>
		<p>
			<button type="button" class="button button-primary ezc-btn-primary" id="campaign-save-settings">ذخیره تنظیمات کمپین</button>
			<span id="campaign-settings-status" class="ezc-muted" style="margin-right:10px;"></span>
		</p>
	</div>
</div>
