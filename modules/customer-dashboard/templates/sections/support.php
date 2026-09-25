<?php
/**
 * پشتیبانی مشتری — UI حرفه‌ای، تب‌های قرصی AJAX، تاریخ شمسی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ticket_id  = isset( $_GET['ticket'] ) ? absint( $_GET['ticket'] ) : ( ! empty( $GLOBALS['ezcd_ticket_id'] ) ? absint( $GLOBALS['ezcd_ticket_id'] ) : 0 );
$stab       = isset( $_GET['stab'] ) ? sanitize_key( $_GET['stab'] ) : ( $ticket_id ? 'thread' : 'list' );
if ( ! in_array( $stab, array( 'new', 'list', 'thread' ), true ) ) {
	$stab = 'list';
}
if ( $ticket_id ) {
	$stab = 'thread';
}

$tickets    = EzLens_CD_Support_Bridge::list_tickets();
$topics     = EzLens_CD_Support_Bridge::topics();
$priorities = EzLens_CD_Support_Bridge::priorities();
$open_count = EzLens_CD_Support_Bridge::open_count();
$phone      = '';
if ( class_exists( 'EzLens_Auth_Settings' ) ) {
	$phone = (string) EzLens_Auth_Settings::get( 'support_phone' );
}
?>
<div class="ezcd-sp" data-stab="<?php echo esc_attr( $stab ); ?>">

	<header class="ezcd-sp-hero">
		<div class="ezcd-sp-hero-glow" aria-hidden="true"></div>
		<div class="ezcd-sp-hero-icon" aria-hidden="true">
			<?php echo ezcd_icon( 'headset' ); ?><?php if ( ! ezcd_icon( 'headset' ) ) { echo ezcd_icon( 'message-circle' ); } ?>
		</div>
		<div class="ezcd-sp-hero-text">
			<h2 class="ezcd-sp-title">پشتیبانی</h2>
			<p class="ezcd-sp-lead">هر سوالی دارید همین‌جا بنویسید. پاسخ تیم فروشگاه در همین صفحه می‌آید — سریع، شفاف، محترمانه.</p>
			<?php if ( $phone ) : ?>
				<p class="ezcd-sp-phone"><span>تماس مستقیم</span> <strong dir="ltr"><?php echo esc_html( $phone ); ?></strong></p>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( 'thread' !== $stab ) : ?>
		<nav class="ezcd-sp-tabs" role="tablist" aria-label="بخش پشتیبانی">
			<button type="button" role="tab" class="ezcd-sp-tab<?php echo 'list' === $stab ? ' is-on' : ''; ?>" data-stab="list" aria-selected="<?php echo 'list' === $stab ? 'true' : 'false'; ?>">
				<span class="ezcd-sp-tab-ico"><?php echo ezcd_icon( 'list' ); ?></span>
				<span>درخواست‌های من</span>
				<?php if ( $open_count > 0 ) : ?>
					<em class="ezcd-sp-badge"><?php echo esc_html( ezcd_fa( (string) $open_count ) ); ?></em>
				<?php endif; ?>
			</button>
			<button type="button" role="tab" class="ezcd-sp-tab<?php echo 'new' === $stab ? ' is-on' : ''; ?>" data-stab="new" aria-selected="<?php echo 'new' === $stab ? 'true' : 'false'; ?>">
				<span class="ezcd-sp-tab-ico"><?php echo ezcd_icon( 'plus-circle' ); ?><?php if ( ! ezcd_icon( 'plus-circle' ) ) { echo ezcd_icon( 'plus' ); } ?></span>
				<span>درخواست جدید</span>
			</button>
		</nav>
	<?php endif; ?>

	<!-- LIST -->
	<section class="ezcd-sp-panel<?php echo 'list' === $stab ? ' is-on' : ''; ?>" data-spanel="list" <?php echo 'list' === $stab ? '' : 'hidden'; ?>>
		<?php if ( empty( $tickets ) ) : ?>
			<div class="ezcd-sp-empty">
				<div class="ezcd-sp-empty-ico"><?php echo ezcd_icon( 'message-circle' ); ?></div>
				<p>هنوز درخواستی ثبت نکرده‌اید.</p>
				<p class="ezcd-sp-empty-sub">هر زمان نیاز بود، از تب «درخواست جدید» بنویسید — ما اینجاییم.</p>
				<button type="button" class="ezcd-sp-btn ezcd-sp-btn-primary" data-stab="new">شروع گفتگو</button>
			</div>
		<?php else : ?>
			<?php
			$max_open = (int) apply_filters( 'ezcd_max_open_tickets', EzLens_CD_Support_Bridge::MAX_OPEN_TICKETS );
			if ( $max_open > 0 && $open_count >= $max_open ) :
				?>
				<div class="ezcd-sp-limit-note">
					حداکثر <?php echo esc_html( ezcd_fa( (string) $max_open ) ); ?> درخواست باز مجاز است. پس از پاسخ پشتیبانی می‌توانید درخواست تازه ثبت کنید.
				</div>
			<?php endif; ?>
			<div class="ezcd-sp-cards">
				<?php foreach ( $tickets as $t ) :
					$tid = (int) $t->id;
					$sub = ! empty( $t->subject ) ? $t->subject : ( 'گفتگو #' . $tid );
					$st  = isset( $t->status ) ? (string) $t->status : 'open';
					$pr  = isset( $t->priority ) ? (string) $t->priority : 'normal';
					$upd = $t->updated_at ?? ( $t->created_at ?? '' );
					$closed = in_array( strtolower( $st ), array( 'closed', 'resolved' ), true );
					$st_key = $closed ? 'done' : ( in_array( strtolower( $st ), array( 'replied', 'answered' ), true ) ? 'reply' : 'live' );
					?>
					<button type="button" class="ezcd-sp-card is-<?php echo esc_attr( $st_key ); ?>" data-ticket-id="<?php echo esc_attr( (string) $tid ); ?>">
						<span class="ezcd-sp-card-rail" aria-hidden="true"></span>
						<span class="ezcd-sp-card-ico" aria-hidden="true">
							<?php
							echo ezcd_icon( 'message-circle' );
							if ( ! ezcd_icon( 'message-circle' ) ) {
								echo ezcd_icon( 'mail' );
							}
							?>
						</span>
						<span class="ezcd-sp-card-body">
							<span class="ezcd-sp-card-top">
								<strong class="ezcd-sp-card-title"><?php echo esc_html( $sub ); ?></strong>
								<span class="ezcd-sp-status is-<?php echo esc_attr( $st_key ); ?>">
									<?php echo esc_html( EzLens_CD_Support_Bridge::status_label( $st ) ); ?>
								</span>
							</span>
							<span class="ezcd-sp-card-meta">
								<span class="ezcd-sp-card-id">#<?php echo esc_html( ezcd_fa( (string) $tid ) ); ?></span>
								<span class="ezcd-sp-dot" aria-hidden="true"></span>
								<span><?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $upd ) : ezcd_fa( $upd ) ); ?></span>
								<?php if ( in_array( $pr, array( 'high', 'urgent' ), true ) ) : ?>
									<span class="ezcd-sp-dot" aria-hidden="true"></span>
									<span class="ezcd-sp-prio is-<?php echo esc_attr( $pr ); ?>"><?php echo esc_html( $priorities[ $pr ] ?? $pr ); ?></span>
								<?php endif; ?>
							</span>
						</span>
						<span class="ezcd-sp-card-go" aria-hidden="true"><?php echo ezcd_icon( 'chevron-left' ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<!-- NEW -->
	<section class="ezcd-sp-panel<?php echo 'new' === $stab ? ' is-on' : ''; ?>" data-spanel="new" <?php echo 'new' === $stab ? '' : 'hidden'; ?>>
		<form class="ezcd-sp-form" id="ezcd-ticket-create-form" enctype="multipart/form-data" novalidate>
			<div class="ezcd-sp-field">
				<label for="ezcd-support-topic">موضوع</label>
				<select name="topic" id="ezcd-support-topic" required>
					<option value="">انتخاب کنید…</option>
					<?php foreach ( $topics as $tk => $tl ) : ?>
						<option value="<?php echo esc_attr( $tk ); ?>"><?php echo esc_html( $tl ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="ezcd-sp-field" id="ezcd-support-other-wrap" hidden>
				<label for="ezcd-topic-other">شرح موضوع</label>
				<input type="text" name="topic_other" id="ezcd-topic-other" placeholder="مثلاً: سوال درباره گارانتی">
			</div>
			<div class="ezcd-sp-row">
				<div class="ezcd-sp-field">
					<label for="ezcd-priority">اولویت</label>
					<select name="priority" id="ezcd-priority">
						<?php foreach ( $priorities as $pk => $pl ) : ?>
							<option value="<?php echo esc_attr( $pk ); ?>"><?php echo esc_html( $pl ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ezcd-sp-field ezcd-sp-grow">
					<label for="ezcd-subject">عنوان کوتاه</label>
					<input type="text" name="subject" id="ezcd-subject" required maxlength="120" placeholder="مثلاً: سفارش هنوز نرسیده">
				</div>
			</div>
			<div class="ezcd-sp-field">
				<label for="ezcd-message">شرح کامل</label>
				<textarea name="message" id="ezcd-message" rows="4" required placeholder="جزئیات بیشتر = پاسخ دقیق‌تر و سریع‌تر"></textarea>
			</div>
			<label class="ezcd-sp-drop" id="ezcd-support-drop">
				<input type="file" name="attachment" id="ezcd-support-file" accept="image/*,.pdf" hidden>
				<span class="ezcd-sp-drop-ico"><?php echo ezcd_icon( 'upload' ); ?></span>
				<span class="ezcd-sp-drop-title">پیوست اختیاری</span>
				<span class="ezcd-sp-drop-hint">تصویر یا PDF — حداکثر ۲۰ مگابایت</span>
				<span class="ezcd-sp-drop-name" id="ezcd-support-file-name"></span>
			</label>
			<button type="submit" class="ezcd-sp-btn ezcd-sp-btn-primary ezcd-sp-btn-block">ثبت درخواست</button>
		</form>
	</section>

	<!-- THREAD -->
	<section class="ezcd-sp-panel<?php echo 'thread' === $stab ? ' is-on' : ''; ?>" data-spanel="thread" <?php echo 'thread' === $stab ? '' : 'hidden'; ?>>
		<?php if ( $ticket_id ) :
			$ticket = EzLens_CD_Support_Bridge::get_ticket( $ticket_id );
			if ( ! $ticket ) :
				?>
				<div class="ezcd-sp-empty">
					<p>این گفتگو پیدا نشد.</p>
					<button type="button" class="ezcd-sp-btn ezcd-sp-btn-ghost" data-stab="list">بازگشت به لیست</button>
				</div>
			<?php else :
				$messages = EzLens_CD_Support_Bridge::get_messages( $ticket_id );
				$subject  = ! empty( $ticket->subject ) ? $ticket->subject : ( 'گفتگو #' . $ticket_id );
				$status   = isset( $ticket->status ) ? (string) $ticket->status : 'open';
				$closed   = in_array( strtolower( $status ), array( 'closed', 'resolved' ), true );
				?>
				<button type="button" class="ezcd-sp-back" data-stab="list">
					<span class="ezcd-sp-back-ico"><?php echo ezcd_icon( 'arrow-right' ); ?></span>
					بازگشت به لیست
				</button>
				<div class="ezcd-sp-thread-head">
					<div>
						<strong class="ezcd-sp-thread-title"><?php echo esc_html( $subject ); ?></strong>
						<div class="ezcd-sp-item-meta">
							شماره <?php echo esc_html( ezcd_fa( (string) $ticket_id ) ); ?>
							· <?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $ticket->created_at ?? '' ) : '' ); ?>
						</div>
					</div>
					<span class="ezcd-sp-status <?php echo $closed ? 'is-done' : 'is-live'; ?>">
						<?php echo esc_html( EzLens_CD_Support_Bridge::status_label( $status ) ); ?>
					</span>
				</div>
				<div class="ezcd-sp-thread" id="ezcd-ticket-thread">
					<?php if ( empty( $messages ) ) : ?>
						<p class="ezcd-sp-empty-sub">هنوز پیامی نیست.</p>
					<?php else :
						foreach ( $messages as $m ) :
							$staff = EzLens_CD_Support_Bridge::is_staff_message( $m );
							$text  = EzLens_CD_Support_Bridge::message_text( $m );
							$when  = $m->created_at ?? '';
							$att   = EzLens_CD_Support_Bridge::attachment_url( $m );
							?>
							<article class="ezcd-sp-msg <?php echo $staff ? 'is-staff' : 'is-me'; ?>">
								<div class="ezcd-sp-msg-meta">
									<span><?php echo $staff ? 'پشتیبانی' : 'شما'; ?></span>
									<?php if ( $when ) : ?>
										<time><?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $when ) : ezcd_fa( $when ) ); ?></time>
									<?php endif; ?>
								</div>
								<div class="ezcd-sp-bubble">
									<?php echo $text !== '' ? nl2br( esc_html( $text ) ) : ''; ?>
									<?php if ( $att ) : ?>
										<a class="ezcd-sp-att" href="<?php echo esc_url( $att ); ?>" target="_blank" rel="noopener">مشاهده پیوست</a>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach;
					endif; ?>
				</div>
				<?php if ( ! $closed ) : ?>
					<form class="ezcd-sp-form ezcd-sp-reply" id="ezcd-ticket-reply-form" data-ticket-id="<?php echo esc_attr( (string) $ticket_id ); ?>" enctype="multipart/form-data">
						<div class="ezcd-sp-field">
							<label for="ezcd-reply-msg">ادامه گفتگو</label>
							<textarea name="message" id="ezcd-reply-msg" rows="3" placeholder="پاسخ یا توضیح بیشتر…"></textarea>
						</div>
						<label class="ezcd-sp-drop ezcd-sp-drop-sm" id="ezcd-reply-drop">
							<input type="file" name="attachment" id="ezcd-reply-file" accept="image/*,.pdf" hidden>
							<span class="ezcd-sp-drop-title">پیوست (حداکثر ۲۰ مگ)</span>
							<span class="ezcd-sp-drop-name" id="ezcd-reply-file-name"></span>
						</label>
						<button type="submit" class="ezcd-sp-btn ezcd-sp-btn-primary">ارسال پیام</button>
					</form>
				<?php else : ?>
					<div class="ezcd-sp-closed-note">
						<p>این گفتگو بسته شده است. برای موضوع تازه یک درخواست جدید ثبت کنید.</p>
						<button type="button" class="ezcd-sp-btn ezcd-sp-btn-primary" data-stab="new">درخواست جدید</button>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</section>
</div>
