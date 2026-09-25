<?php
/**
 * کمپین و لیست‌ها + ارسال تکی
 * @version 3.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'EzLens_Auth_Campaign' ) ) {
	EzLens_Auth_Campaign::get_instance()->create_tables();
}
$msg_url  = admin_url( 'admin.php?page=ezlens-auth-settings&tab=messaging' );
$mass_url = admin_url( 'admin.php?page=ezlens-cd-mass' );
?>
<div class="wrap ezlens-campaign-wrap ezlens-ui" dir="rtl">
	<header class="ezc-hero">
		<div class="ezc-hero-text">
			<h1>کمپین و لیست‌ها</h1>
			<p>
				مرکز لیست‌ها، کمپین انبوه و <strong>ارسال تکی</strong>.
				پیام سریع به چند مشتری سایت:
				<a href="<?php echo esc_url( $mass_url ); ?>">پیام به مشتریان</a>
			</p>
		</div>
		<div class="ezc-hero-actions">
			<button type="button" class="button ezc-btn-primary campaign-tab-jump" data-tab="single">ارسال تکی</button>
			<button type="button" class="button campaign-tab-jump" data-tab="create">ایجاد کمپین</button>
			<button type="button" class="button campaign-tab-jump" data-tab="audience">مخاطبان</button>
		</div>
	</header>

	<nav class="ezlens-campaign-tabs" role="tablist">
		<button type="button" class="campaign-tab active" data-tab="dashboard">مرور</button>
		<button type="button" class="campaign-tab" data-tab="single">ارسال تکی</button>
		<button type="button" class="campaign-tab" data-tab="audience">مخاطبان</button>
		<button type="button" class="campaign-tab" data-tab="create">ایجاد</button>
		<button type="button" class="campaign-tab" data-tab="history">تاریخچه</button>
		<button type="button" class="campaign-tab" data-tab="settings">تنظیمات</button>
	</nav>

	<div class="ezlens-campaign-content" id="campaignContent">
		<div class="ezc-skeleton-block">
			<div class="ezc-sk-line"></div>
			<div class="ezc-sk-line short"></div>
		</div>
	</div>
</div>
<script>
jQuery(function($){
	$(document).on('click', '.campaign-tab-jump', function(e){
		e.preventDefault();
		var tab = $(this).data('tab');
		$('.ezlens-campaign-tabs .campaign-tab[data-tab="'+tab+'"]').trigger('click');
	});
});
</script>
