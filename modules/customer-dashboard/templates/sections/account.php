<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$d = EzLens_CD_Account::get_data();
?>
<div class="ezcd-account-form">
	<form class="ezcd-form ezcd-card-panel" id="ezcd-account-form">
		<p class="ezcd-hint">اطلاعات تماس برای پیگیری سفارش استفاده می‌شود. کد ملی اختیاری است.</p>
		<div class="ezcd-form-row">
			<label>نام<input type="text" name="first_name" value="<?php echo esc_attr( $d['first_name'] ); ?>" placeholder="نام"></label>
			<label>نام خانوادگی<input type="text" name="last_name" value="<?php echo esc_attr( $d['last_name'] ); ?>" placeholder="نام خانوادگی"></label>
		</div>
		<label>نام نمایشی<input type="text" name="display_name" value="<?php echo esc_attr( $d['display_name'] ); ?>"></label>
		<label>ایمیل<input type="email" name="email" value="<?php echo esc_attr( $d['email'] ); ?>" dir="ltr"></label>
		<label>موبایل (شناسه حساب — غیرقابل تغییر)
			<input type="text" name="phone" value="<?php echo esc_attr( $d['phone'] ); ?>" dir="ltr" readonly class="ezcd-input-locked">
			<span class="ezcd-verified">قفل‌شده</span>
		</label>
		<label>کد ملی (اختیاری)
			<input type="text" name="national_id" value="<?php echo esc_attr( $d['national_id'] ?? '' ); ?>" placeholder="۰۰۱۲۳۴۵۶۷۸" maxlength="10" dir="ltr" inputmode="numeric">
		</label>
		<button type="submit" class="ezcd-btn ezcd-btn-grad">ذخیره تغییرات</button>
		<p class="ezcd-form-msg" hidden></p>
	</form>
</div>
