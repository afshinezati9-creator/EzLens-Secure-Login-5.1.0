<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$balance  = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance() : 0;
$history  = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::history() : array();
$deposits = class_exists( 'EzLens_CD_Wallet_Deposits' ) ? EzLens_CD_Wallet_Deposits::for_user() : array();
$bank     = get_option( 'ezlens_cd_wallet_bank_info', '' );
$fa_bal   = function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $balance ) ) : number_format_i18n( (int) $balance );

// Query flags after gateway return
$topup_ok   = isset( $_GET['ezcd_topup'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['ezcd_topup'] ) );
$topup_fail = isset( $_GET['ezcd_topup'] ) && 'fail' === sanitize_key( wp_unslash( $_GET['ezcd_topup'] ) );
$topup_amt  = isset( $_GET['amt'] ) ? absint( $_GET['amt'] ) : 0;
?>
<div class="ezcd-wallet">
	<div class="ezcd-wallet-hero">
		<span class="ezcd-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( 'wallet' ) : ''; ?></span>
		<div>
			<p class="ezcd-muted">موجودی قابل استفاده</p>
			<strong class="ezcd-wallet-balance"><?php echo esc_html( $fa_bal ); ?> تومان</strong>
		</div>
	</div>

	<?php if ( $topup_ok ) : ?>
		<div class="ezcd-alert ezcd-alert-ok" role="status">
			<strong>شارژ موفق</strong>
			<p>پرداخت تأیید شد<?php echo $topup_amt ? ' و مبلغ ' . esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( $topup_amt ) ) : number_format_i18n( $topup_amt ) ) . ' تومان' : ''; ?> بدون نیاز به تأیید مدیر به کیف پول شما اضافه شد.</p>
		</div>
	<?php elseif ( $topup_fail ) : ?>
		<div class="ezcd-alert ezcd-alert-err" role="alert">
			<strong>پرداخت ناموفق یا ناتمام</strong>
			<p>اگر مبلغ از حسابتان کم شده، از بخش پشتیبانی تیکت بزنید تا پیگیری شود. شماره پیگیری درگاه یا ۴ رقم آخر کارت را در تیکت بنویسید.</p>
			<a class="ezcd-btn ezcd-btn-ghost" href="<?php echo esc_url( function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'support' ) : home_url( '/my-account/support/' ) ); ?>">ثبت تیکت پشتیبانی</a>
		</div>
	<?php endif; ?>

	<div class="ezcd-section-block ezcd-card-panel">
		<h2 class="ezcd-h2">شارژ کیف پول</h2>
		<?php if ( $bank ) : ?>
			<div class="ezcd-bank-box"><?php echo wp_kses_post( wpautop( $bank ) ); ?></div>
		<?php endif; ?>

		<form class="ezcd-form" id="ezcd-wallet-deposit-form" enctype="multipart/form-data" novalidate>
			<label class="ezcd-amt-label">مبلغ شارژ (تومان)
				<input type="text" name="amount" id="ezcd-dep-amount" inputmode="numeric" placeholder="مثلاً 500,000" required autocomplete="off" dir="ltr" class="ezcd-amt-input">
				<small class="ezcd-amt-hint" id="ezcd-dep-amount-hint">حداقل ۱۰٬۰۰۰ تومان</small>
				<small class="ezcd-amt-words" id="ezcd-dep-amount-words" aria-live="polite"></small>
			</label>

			<p class="ezcd-method-label">روش پرداخت را انتخاب کنید</p>
			<div class="ezcd-method-grid" role="radiogroup" aria-label="روش پرداخت">
				<button type="button" class="ezcd-method-card is-active" data-method="online" aria-pressed="true">
					<span class="ezcd-method-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( 'credit-card' ) : ''; ?></span>
					<strong>درگاه پرداخت</strong>
					<small>پرداخت آنلاین — شارژ خودکار بعد از موفقیت</small>
				</button>
				<button type="button" class="ezcd-method-card" data-method="bank" aria-pressed="false">
					<span class="ezcd-method-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( 'globe' ) : ''; ?></span>
					<strong>اینترنت‌بانک</strong>
					<small>اسکرین یا شناسه پرداخت بفرستید</small>
				</button>
				<button type="button" class="ezcd-method-card" data-method="card" aria-pressed="false">
					<span class="ezcd-method-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( 'receipt' ) : ''; ?></span>
					<strong>کارت به کارت / فیش</strong>
					<small>فیش واریز را آپلود کنید</small>
				</button>
			</div>
			<input type="hidden" name="method" id="ezcd-dep-method" value="online">

			<!-- Online panel -->
			<div class="ezcd-dep-panel is-open" data-dep-panel="online" id="ezcd-dep-panel-online">
				<div class="ezcd-dep-summary">
					<span>مبلغ قابل پرداخت</span>
					<strong id="ezcd-dep-online-amt">—</strong>
				</div>
				<p class="ezcd-hint">پس از پرداخت موفق، به همین صفحه برمی‌گردید و موجودی بدون تأیید مدیر شارژ می‌شود. اگر پرداخت ناموفق بود و پول کم شد، تیکت پشتیبانی بزنید.</p>
				<button type="submit" class="ezcd-btn ezcd-btn-grad" id="ezcd-dep-submit-online">پرداخت از درگاه</button>
			</div>

			<!-- Bank panel -->
			<div class="ezcd-dep-panel" data-dep-panel="bank" id="ezcd-dep-panel-bank" hidden>
				<p class="ezcd-hint">بعد از واریز اینترنت‌بانک، شناسه پیگیری یا اسکرین‌شات را بفرستید. پس از تأیید سریع مدیر، مبلغ به کیف پول اضافه می‌شود.</p>
				<label>شناسه / کد پیگیری پرداخت <span class="ezcd-req">*</span>
					<input type="text" name="ref_code" id="ezcd-dep-ref" placeholder="مثلاً شماره پیگیری بانک" autocomplete="off">
				</label>
				<label class="ezcd-dropzone" id="ezcd-deposit-drop-bank">
					<input type="file" name="receipt" id="ezcd-deposit-file-bank" accept="image/*,.pdf" hidden>
					<span class="ezcd-drop-title">آپلود اسکرین پرداخت (اختیاری ولی بهتر است)</span>
					<span class="ezcd-drop-name" id="ezcd-deposit-file-name-bank"></span>
				</label>
				<button type="submit" class="ezcd-btn ezcd-btn-grad" id="ezcd-dep-submit-bank">ثبت درخواست بررسی</button>
			</div>

			<!-- Card panel -->
			<div class="ezcd-dep-panel" data-dep-panel="card" id="ezcd-dep-panel-card" hidden>
				<p class="ezcd-hint">کارت‌به‌کارت کنید و فیش را بفرستید. بعد از پیگیری و تأیید، مبلغ به کیف پولتان اضافه می‌شود.</p>
				<label>شماره رسید / کد پیگیری <span class="ezcd-req">*</span>
					<input type="text" name="ref_code_card" id="ezcd-dep-ref-card" placeholder="شماره پیگیری یا زمان واریز" autocomplete="off">
				</label>
				<label class="ezcd-dropzone" id="ezcd-deposit-drop">
					<input type="file" name="receipt_card" id="ezcd-deposit-file" accept="image/*,.pdf" hidden>
					<span class="ezcd-drop-title">آپلود فیش واریز *</span>
					<span class="ezcd-drop-name" id="ezcd-deposit-file-name"></span>
				</label>
				<button type="submit" class="ezcd-btn ezcd-btn-grad" id="ezcd-dep-submit-card">ارسال فیش و ثبت درخواست</button>
			</div>

			<p class="ezcd-hint" id="ezcd-dep-msg" aria-live="polite"></p>
		</form>
	</div>

	<?php if ( $deposits ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">درخواست‌های شارژ</h2>
			<div class="ezcd-deposit-list">
				<?php foreach ( $deposits as $d ) :
					$st = isset( $d->status ) ? $d->status : 'pending';
					$methods = class_exists( 'EzLens_CD_Wallet_Deposits' ) ? EzLens_CD_Wallet_Deposits::methods() : array();
					$ml = isset( $methods[ $d->method ] ) ? $methods[ $d->method ] : $d->method;
					?>
					<div class="ezcd-deposit-item st-<?php echo esc_attr( $st ); ?>">
						<strong><?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $d->amount ) ) : number_format_i18n( (int) $d->amount ) ); ?> تومان</strong>
						<span><?php echo esc_html( $ml ); ?></span>
						<span class="ezcd-badge"><?php echo esc_html( EzLens_CD_Wallet_Deposits::status_label( $st ) ); ?></span>
						<?php if ( ! empty( $d->ref_code ) ) : ?>
							<small class="ezcd-muted">پیگیری: <?php echo esc_html( $d->ref_code ); ?></small>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $history ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">گردش حساب</h2>
			<div class="ezcd-ledger">
				<?php foreach ( $history as $h ) :
					$sign = ( 'credit' === $h->entry_type ) ? '+' : '−';
					$cls  = ( 'credit' === $h->entry_type ) ? 'is-credit' : 'is-debit';
					?>
					<div class="ezcd-ledger-row <?php echo esc_attr( $cls ); ?>">
						<span><?php echo esc_html( EzLens_CD_Wallet::reason_label( $h->reason ) ); ?></span>
						<strong><?php echo esc_html( $sign . ( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $h->amount ) ) : number_format_i18n( (int) $h->amount ) ) ); ?></strong>
						<small class="ezcd-muted"><?php echo esc_html( $h->created_at ); ?><?php echo $h->note ? ' — ' . esc_html( $h->note ) : ''; ?></small>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
