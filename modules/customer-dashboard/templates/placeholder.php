<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$current = isset( $current ) ? $current : 'overview';
$messages = array(
	'prescriptions' => 'پرونده بینایی و نسخه‌های چشم به‌زودی اینجا در دسترس شماست — امن و مرتب.',
	'reviews'       => 'مدیریت نظراتی که ثبت کرده‌اید به‌زودی فعال می‌شود.',
	'coupons'       => 'کدهای تخفیف و امتیازهای شما به‌زودی در این بخش جمع می‌شود.',
	'gift_cards'    => 'کارت هدیه برای عزیزانتان به‌زودی آماده می‌شود.',
	'gift-cards'    => 'کارت هدیه برای عزیزانتان به‌زودی آماده می‌شود.',
	'wallet'        => 'کیف پول و موجودی حساب به‌زودی همین‌جا قابل مشاهده است.',
	'support'       => 'پشتیبانی اختصاصی به‌زودی از داخل حسابتان در دسترس است.',
	'referral'      => 'دعوت دوستان و دریافت هدیه به‌زودی فعال می‌شود.',
);
$msg = isset( $messages[ $current ] ) ? $messages[ $current ] : 'این بخش به‌زودی برای شما فعال می‌شود.';
?>
<div class="ezcd-placeholder">
	<div class="ezcd-placeholder-icon" aria-hidden="true"><?php echo ezcd_icon( 'layout' ); ?></div>
	<p class="ezcd-placeholder-title">به‌زودی</p>
	<p class="ezcd-placeholder-text"><?php echo esc_html( $msg ); ?></p>
</div>
