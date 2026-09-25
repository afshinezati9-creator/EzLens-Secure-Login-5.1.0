<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$sections = get_option( 'ezlens_cd_sections', array() );
if ( ! is_array( $sections ) ) {
	$sections = array();
}
$labels = array(
	'overview'      => 'پیشخوان',
	'orders'        => 'سفارش‌ها',
	'prescriptions' => 'پرونده بیمار',
	'wishlist'      => 'علاقه‌مندی‌ها',
	'addresses'     => 'آدرس‌ها',
	'reviews'       => 'نظرات',
	'coupons'       => 'تخفیف‌ها',
	'gift_cards'    => 'کارت هدیه',
	'wallet'        => 'کیف پول',
	'charity'       => 'هم‌یاری بینایی',
	'support'       => 'پشتیبانی',
	'account'       => 'اطلاعات حساب',
	'security'      => 'امنیت',
	'referral'      => 'دعوت دوستان',
);
$replace = (int) get_option( 'ezlens_cd_replace_my_account', 1 );
$nonce   = wp_create_nonce( 'ezcd_admin' );
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>تنظیمات داشبورد مشتری</h1>
	<form id="ezcd-settings-form" class="ezcd-admin-card">
		<table class="form-table">
			<tr>
				<th>جایگزینی My Account</th>
				<td>
					<label class="ezcd-switch">
						<input type="checkbox" name="replace_my_account" value="1" <?php checked( $replace, 1 ); ?>>
						<span class="ezcd-switch-ui"></span>
						<span class="ezcd-switch-label">شِل EzLens به‌جای منوی پیش‌فرض ووکامرس</span>
					</label>
				</td>
			</tr>
			<tr>
				<th>بخش‌های فعال مشتری</th>
				<td>
					<div class="ezcd-section-toggles">
						<?php foreach ( $labels as $key => $label ) :
							$on  = ! isset( $sections[ $key ] ) || (int) $sections[ $key ] === 1;
							$dis = ( 'overview' === $key );
							?>
							<label class="ezcd-toggle-row ezcd-switch">
								<input type="checkbox" name="sections[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $on ); ?> <?php disabled( $dis ); ?>>
								<span class="ezcd-switch-ui"></span>
								<span class="ezcd-switch-label"><?php echo esc_html( $label ); ?><?php echo $dis ? ' (همیشه)' : ''; ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</td>
			</tr>
		</table>
		<p>
			<button type="submit" class="button button-primary">ذخیره تنظیمات</button>
			<span id="ezcd-settings-msg" class="ezcd-admin-msg"></span>
		</p>
	</form>
</div>
<script>
(function(){
	var form = document.getElementById('ezcd-settings-form');
	if(!form) return;
	form.addEventListener('submit', function(e){
		e.preventDefault();
		var body = new FormData(form);
		body.append('action','ezcd_admin_save_sections');
		body.append('nonce','<?php echo esc_js( $nonce ); ?>');
		var msg = document.getElementById('ezcd-settings-msg');
		fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
			msg.textContent = (res && res.data && res.data.message) || (res && res.success ? 'ذخیره شد' : 'خطا');
			msg.className = 'ezcd-admin-msg ' + (res && res.success ? 'is-ok' : 'is-err');
		});
	});
})();
</script>
