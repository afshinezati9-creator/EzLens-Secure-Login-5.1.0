<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cases     = EzLens_CD_Charity::public_cases();
$donations = EzLens_CD_Charity::user_donations();
$updates   = EzLens_CD_Charity::updates_for_user();
$total     = EzLens_CD_Charity::user_total();
$balance   = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance() : 0;
$needs     = EzLens_CD_Charity::need_labels();
$tab       = isset( $_GET['ctab'] ) ? sanitize_key( $_GET['ctab'] ) : 'help';
if ( ! in_array( $tab, array( 'help', 'mine', 'impact' ), true ) ) {
	$tab = 'help';
}
?>
<div class="ezcd-charity" data-ctab="<?php echo esc_attr( $tab ); ?>">
	<div class="ezcd-charity-hero">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'heart' ); ?><?php if ( ! ezcd_icon( 'heart' ) ) { echo ezcd_icon( 'gift' ); } ?></span>
		<div>
			<strong>هم‌یاری بینایی</strong>
			<p class="ezcd-muted">بعضی‌ها فقط به‌خاطر هزینه، از عینک، لنز یا درمان چشم محروم می‌مانند. کمک شما مستقیم و شفاف به همین نیاز می‌رسد — بدون هیاهو، با احترام به عزت افراد.</p>
		</div>
	</div>

	<div class="ezcd-charity-summary">
		<div class="ezcd-ch-pill">
			<span class="ezcd-muted">مجموع کمک‌های شما</span>
			<strong><?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( wp_strip_all_tags( EzLens_CD_Charity::format_amount( $total ) ) ) : EzLens_CD_Charity::format_amount( $total ) ); ?></strong>
		</div>
		<div class="ezcd-ch-pill soft">
			<span class="ezcd-muted">موجودی کیف پول</span>
			<strong><?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $balance ) ) : number_format_i18n( (int) $balance ) ); ?> تومان</strong>
		</div>
	</div>

	<nav class="ezcd-rx-tabs ezcd-ch-tabs" role="tablist">
		<button type="button" class="ezcd-rx-tab<?php echo 'help' === $tab ? ' is-active' : ''; ?>" data-ctab="help">کمک می‌کنم</button>
		<button type="button" class="ezcd-rx-tab<?php echo 'mine' === $tab ? ' is-active' : ''; ?>" data-ctab="mine">کمک‌های من</button>
		<button type="button" class="ezcd-rx-tab<?php echo 'impact' === $tab ? ' is-active' : ''; ?>" data-ctab="impact">گزارش اثر</button>
	</nav>

	<!-- HELP -->
	<div class="ezcd-rx-panel<?php echo 'help' !== $tab ? ' is-hidden' : ''; ?>" data-cpanel="help">
		<?php if ( empty( $cases ) ) : ?>
			<div class="ezcd-empty ezcd-empty-rich">
				<p>الان مورد فعالی برای نمایش عمومی نیست. می‌توانید کمک عمومی ثبت کنید تا در اولین نیاز واقعی هزینه شود.</p>
			</div>
		<?php else : ?>
			<div class="ezcd-ch-cases">
				<?php foreach ( $cases as $case ) :
					$goal   = (int) $case->goal_amount;
					$raised = (int) $case->raised_amount;
					$pct    = $goal > 0 ? min( 100, (int) round( 100 * $raised / $goal ) ) : 0;
					$need   = $needs[ $case->need_type ] ?? $case->need_type;
					?>
					<article class="ezcd-ch-case">
						<div class="ezcd-ch-case-top">
							<span class="ezcd-pill is-ok"><?php echo esc_html( $need ); ?></span>
							<?php if ( 'funded' === $case->status ) : ?>
								<span class="ezcd-pill">تأمین شد</span>
							<?php endif; ?>
						</div>
						<strong class="ezcd-ch-title"><?php echo esc_html( $case->title ); ?></strong>
						<?php if ( $case->summary ) : ?>
							<p class="ezcd-muted"><?php echo esc_html( $case->summary ); ?></p>
						<?php endif; ?>
						<?php if ( $goal > 0 ) : ?>
							<div class="ezcd-ch-bar"><span style="width:<?php echo esc_attr( (string) $pct ); ?>%"></span></div>
							<div class="ezcd-ch-meta">
								<span><?php echo esc_html( ezcd_fa( number_format_i18n( $raised ) ) ); ?> از <?php echo esc_html( ezcd_fa( number_format_i18n( $goal ) ) ); ?> تومان</span>
								<span><?php echo esc_html( ezcd_fa( (string) $pct ) ); ?>٪</span>
							</div>
						<?php endif; ?>
						<button type="button" class="ezcd-btn ezcd-btn-grad ezcd-ch-pick" data-case="<?php echo esc_attr( $case->id ); ?>" data-title="<?php echo esc_attr( $case->title ); ?>">
							انتخاب برای کمک
						</button>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form class="ezcd-form ezcd-card-panel" id="ezcd-charity-form" style="margin-top:16px">
			<input type="hidden" name="case_id" id="ezcd-ch-case-id" value="0">
			<h2 class="ezcd-h2">ثبت کمک</h2>
			<p class="ezcd-hint" id="ezcd-ch-case-label">کمک عمومی (به اولین نیاز تأییدشده اختصاص داده می‌شود)</p>
			<label>مبلغ (تومان)
				<input type="text" name="amount" inputmode="numeric" dir="ltr" placeholder="مثلاً ۵۰۰۰۰" required>
			</label>
			<div class="ezcd-ch-quick">
				<?php foreach ( array( 50000, 100000, 200000, 500000 ) as $q ) : ?>
					<button type="button" class="ezcd-btn ezcd-btn-ghost ezcd-ch-amt" data-amt="<?php echo esc_attr( (string) $q ); ?>"><?php echo esc_html( ezcd_fa( number_format_i18n( $q ) ) ); ?></button>
				<?php endforeach; ?>
			</div>
			<label class="ezcd-check">
				<input type="checkbox" name="is_anonymous" value="1">
				<span class="ezcd-check-ui"></span>
				<span class="ezcd-check-txt">می‌خواهم نامم در گزارش‌های عمومی دیده نشود</span>
			</label>
			<label>پیام اختیاری (فقط برای تیم ما)
				<textarea name="message" rows="2" placeholder="اگر نکته‌ای هست بنویسید — اجباری نیست"></textarea>
			</label>
			<p class="ezcd-hint">مبلغ از <strong>کیف پول</strong> شما کسر می‌شود. اگر موجودی کم است، اول از بخش کیف پول شارژ کنید.</p>
			<button type="submit" class="ezcd-btn ezcd-btn-grad">ثبت کمک با احترام</button>
		</form>
	</div>

	<!-- MINE -->
	<div class="ezcd-rx-panel<?php echo 'mine' !== $tab ? ' is-hidden' : ''; ?>" data-cpanel="mine">
		<?php if ( empty( $donations ) ) : ?>
			<div class="ezcd-empty ezcd-empty-rich">
				<p>هنوز کمکی ثبت نکرده‌اید. هر مبلغی، حتی کوچک، می‌تواند مسیر کسی را روشن‌تر کند.</p>
				<button type="button" class="ezcd-btn ezcd-btn-grad" data-ctab="help">شروع کمک</button>
			</div>
		<?php else : ?>
			<div class="ezcd-ch-list">
				<?php foreach ( $donations as $d ) : ?>
					<div class="ezcd-ch-item">
						<div>
							<strong><?php echo esc_html( ezcd_fa( number_format_i18n( (int) $d->amount ) ) ); ?> تومان</strong>
							<div class="ezcd-muted">
								<?php echo $d->case_title ? esc_html( $d->case_title ) : 'کمک عمومی'; ?>
								· <?php echo esc_html( ezcd_fa( $d->created_at ) ); ?>
								<?php if ( $d->is_anonymous ) : ?> · ناشناس<?php endif; ?>
							</div>
						</div>
						<span class="ezcd-pill is-ok">ثبت شد</span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- IMPACT -->
	<div class="ezcd-rx-panel<?php echo 'impact' !== $tab ? ' is-hidden' : ''; ?>" data-cpanel="impact">
		<?php if ( empty( $updates ) ) : ?>
			<div class="ezcd-empty ezcd-empty-rich">
				<p>به‌محض هزینه شدن کمک‌تان، اینجا می‌بینید صرف چه شده و پیام قدردانی می‌رسد. شفافیت، بخشی از احترام به شماست.</p>
			</div>
		<?php else : ?>
			<div class="ezcd-ch-impact">
				<?php foreach ( $updates as $u ) :
					$url = $u->attachment_id ? wp_get_attachment_url( (int) $u->attachment_id ) : '';
					?>
					<article class="ezcd-ch-update">
						<div class="ezcd-ch-update-top">
							<strong><?php echo esc_html( $u->case_title ?: 'گزارش اثر' ); ?></strong>
							<span class="ezcd-muted"><?php echo esc_html( ezcd_fa( $u->created_at ) ); ?></span>
						</div>
						<?php if ( $u->beneficiary_label ) : ?>
							<p><span class="ezcd-muted">دریافت‌کننده (با حفظ حریم):</span> <?php echo esc_html( $u->beneficiary_label ); ?></p>
						<?php endif; ?>
						<?php if ( $u->purpose ) : ?>
							<p><?php echo esc_html( $u->purpose ); ?></p>
						<?php endif; ?>
						<p class="ezcd-ch-spent">مبلغ گزارش‌شده: <strong><?php echo esc_html( ezcd_fa( number_format_i18n( (int) $u->spent_amount ) ) ); ?> تومان</strong></p>
						<?php if ( $u->thank_you ) : ?>
							<blockquote class="ezcd-ch-thanks"><?php echo esc_html( $u->thank_you ); ?></blockquote>
						<?php endif; ?>
						<?php if ( $url ) : ?>
							<p><a class="ezcd-link" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">مشاهده پیوست / مدرک</a></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
