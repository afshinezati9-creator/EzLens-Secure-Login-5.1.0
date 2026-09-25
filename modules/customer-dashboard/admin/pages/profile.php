<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
$tab     = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
$allowed = array( 'overview', 'orders', 'prescriptions', 'wallet', 'tickets', 'notes' );
if ( ! in_array( $tab, $allowed, true ) ) {
	$tab = 'overview';
}
$bundle = EzLens_CD_Admin_Customers::profile_bundle( $user_id );
if ( ! $bundle ) {
	echo '<div class="wrap"><h1>مشتری یافت نشد</h1></div>';
	return;
}
$u = $bundle['user'];
$base = admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $user_id );
?>
<div class="wrap ezcd-admin" dir="rtl">
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-customers' ) ); ?>">← بازگشت به لیست</a></p>
	<h1><?php echo esc_html( $u->display_name ); ?>
		<span class="description" style="font-size:13px">#<?php echo esc_html( (string) $u->ID ); ?></span>
	</h1>

	<?php if ( ! empty( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p>ذخیره شد.</p></div>
	<?php endif; ?>
	<?php if ( ! empty( $_GET['err'] ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></p></div>
	<?php endif; ?>

	<div class="ezcd-admin-profile-head">
		<div>
			<div><strong>ایمیل:</strong> <?php echo esc_html( $u->user_email ); ?></div>
			<div><strong>موبایل:</strong> <code><?php echo esc_html( $bundle['phone'] ); ?></code></div>
			<div><strong>عضویت:</strong> <?php echo esc_html( $u->user_registered ); ?></div>
		</div>
		<div class="ezcd-admin-stats">
			<div class="ezcd-admin-stat"><span>سفارش</span><strong><?php echo esc_html( number_format_i18n( $bundle['stats']['count'] ) ); ?></strong></div>
			<div class="ezcd-admin-stat"><span>خرید</span><strong><?php echo function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $bundle['stats']['spent'] ) ) : esc_html( (string) $bundle['stats']['spent'] ); ?></strong></div>
			<div class="ezcd-admin-stat"><span>کیف پول</span><strong><?php echo esc_html( number_format_i18n( $bundle['wallet'] ) ); ?></strong></div>
		</div>
	</div>

	<nav class="nav-tab-wrapper ezcd-admin-tabs">
		<?php
		$tabs = array(
			'overview'      => 'خلاصه',
			'orders'        => 'سفارش‌ها',
			'prescriptions' => 'نسخه‌ها',
			'wallet'        => 'کیف پول',
			'tickets'       => 'تیکت‌ها',
			'notes'         => 'یادداشت‌ها',
		);
		foreach ( $tabs as $k => $label ) :
			?>
			<a class="nav-tab <?php echo $tab === $k ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $base . '&tab=' . $k ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<div class="ezcd-admin-tab-body">
		<?php if ( 'overview' === $tab ) : ?>
			<table class="form-table">
				<tr><th>نام نمایشی</th><td><?php echo esc_html( $u->display_name ); ?></td></tr>
				<tr><th>نام / نام خانوادگی</th><td><?php echo esc_html( trim( $u->first_name . ' ' . $u->last_name ) ); ?></td></tr>
				<tr><th>نقش‌ها</th><td><?php echo esc_html( implode( ', ', $u->roles ) ); ?></td></tr>
				<?php if ( ! empty( $bundle['profile'] ) ) : ?>
					<tr><th>پروفایل بینایی</th>
						<td>
							<?php
							$p = $bundle['profile'];
							echo esc_html( 'جنسیت: ' . ( $p['gender'] ?: '—' ) . ' | سال تولد: ' . ( $p['birth_year'] ?: '—' ) );
							if ( ! empty( $p['conditions'] ) ) {
								echo '<br>سوابق: ' . esc_html( implode( ', ', (array) $p['conditions'] ) );
							}
							?>
						</td>
					</tr>
				<?php endif; ?>
			</table>

			<hr>
			<h2>پیام مستقیم به مشتری</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ezcd_direct_message">
				<input type="hidden" name="customer_id" value="<?php echo esc_attr( $user_id ); ?>">
				<?php wp_nonce_field( 'ezcd_direct_message' ); ?>
				<p>
					<label><input type="radio" name="channel" value="sms" checked> SMS</label>
					<label style="margin-right:12px"><input type="radio" name="channel" value="email"> ایمیل</label>
				</p>
				<p><input type="text" name="subject" class="regular-text" placeholder="موضوع (ایمیل)"></p>
				<p><textarea name="message" rows="3" class="large-text" required placeholder="متن پیام…"></textarea></p>
				<p><button type="submit" class="button button-primary">ارسال</button></p>
			</form>
			<p>
				<a class="button" href="<?php echo esc_url( get_edit_user_link( $u->ID ) ); ?>" target="_blank">ویرایش کاربر وردپرس</a>
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
					<a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" target="_blank">مشاهده فرانت حساب</a>
				<?php endif; ?>
			</p>

		<?php elseif ( 'orders' === $tab ) : ?>
			<?php if ( empty( $bundle['orders'] ) ) : ?>
				<p>سفارشی ثبت نشده.</p>
			<?php else : ?>
				<table class="wp-list-table widefat striped">
					<thead><tr><th>شماره</th><th>وضعیت</th><th>مبلغ</th><th>تاریخ</th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $bundle['orders'] as $order ) : ?>
						<tr>
							<td>#<?php echo esc_html( $order->get_order_number() ); ?></td>
							<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
							<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
							<td><?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y/m/d H:i' ) : '' ); ?></td>
							<td><a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">مشاهده</a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		<?php elseif ( 'prescriptions' === $tab ) : ?>
			<?php if ( empty( $bundle['prescriptions'] ) ) : ?>
				<p>نسخه‌ای ثبت نشده.</p>
			<?php else : ?>
				<table class="wp-list-table widefat striped">
					<thead><tr><th>نوع</th><th>تاریخ</th><th>پزشک</th><th>OD</th><th>OS</th><th>PD</th></tr></thead>
					<tbody>
					<?php foreach ( $bundle['prescriptions'] as $rx ) : ?>
						<tr>
							<td><?php echo esc_html( EzLens_CD_Prescriptions::type_label( $rx->rx_type ) ); ?></td>
							<td><?php echo esc_html( $rx->issued_at ?: '—' ); ?></td>
							<td><?php echo esc_html( $rx->doctor_name ?: '—' ); ?></td>
							<td><?php echo esc_html( $rx->od_sph . ' / ' . $rx->od_cyl . ' × ' . $rx->od_axis ); ?></td>
							<td><?php echo esc_html( $rx->os_sph . ' / ' . $rx->os_cyl . ' × ' . $rx->os_axis ); ?></td>
							<td><?php echo esc_html( $rx->pd ?: '—' ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		<?php elseif ( 'wallet' === $tab ) : ?>
			<p>موجودی فعلی: <strong><?php echo esc_html( number_format_i18n( $bundle['wallet'] ) ); ?></strong> تومان</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ezcd-admin-inline-form">
				<input type="hidden" name="action" value="ezcd_wallet_adjust">
				<input type="hidden" name="customer_id" value="<?php echo esc_attr( $user_id ); ?>">
				<?php wp_nonce_field( 'ezcd_wallet_adjust' ); ?>
				<select name="entry_type">
					<option value="credit">افزایش (credit)</option>
					<option value="debit">کاهش (debit)</option>
				</select>
				<input type="number" name="amount" min="1" step="1000" placeholder="مبلغ" required>
				<input type="text" name="note" placeholder="توضیح" class="regular-text">
				<button type="submit" class="button button-primary">اعمال</button>
			</form>
			<?php if ( ! empty( $bundle['wallet_hist'] ) ) : ?>
				<table class="wp-list-table widefat striped" style="margin-top:16px">
					<thead><tr><th>نوع</th><th>مبلغ</th><th>دلیل</th><th>زمان</th></tr></thead>
					<tbody>
					<?php foreach ( $bundle['wallet_hist'] as $row ) : ?>
						<tr>
							<td><?php echo 'credit' === $row->entry_type ? '+' : '−'; ?></td>
							<td><?php echo esc_html( number_format_i18n( (int) $row->amount ) ); ?></td>
							<td><?php echo esc_html( EzLens_CD_Wallet::reason_label( $row->reason ) . ( $row->note ? ' — ' . $row->note : '' ) ); ?></td>
							<td><?php echo esc_html( $row->created_at ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		<?php elseif ( 'tickets' === $tab ) : ?>
			<?php if ( empty( $bundle['tickets'] ) ) : ?>
				<p>تیکتی نیست.</p>
			<?php else : ?>
				<table class="wp-list-table widefat striped">
					<thead><tr><th>ID</th><th>موضوع</th><th>وضعیت</th><th>به‌روزرسانی</th></tr></thead>
					<tbody>
					<?php foreach ( $bundle['tickets'] as $t ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $t->id ); ?></td>
							<td><?php echo esc_html( isset( $t->subject ) ? $t->subject : '—' ); ?></td>
							<td><?php echo esc_html( isset( $t->status ) ? EzLens_CD_Support_Bridge::status_label( $t->status ) : '—' ); ?></td>
							<td><?php echo esc_html( isset( $t->updated_at ) ? $t->updated_at : ( $t->created_at ?? '' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		<?php elseif ( 'notes' === $tab ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ezcd_add_note">
				<input type="hidden" name="customer_id" value="<?php echo esc_attr( $user_id ); ?>">
				<?php wp_nonce_field( 'ezcd_add_note' ); ?>
				<textarea name="note" rows="3" class="large-text" placeholder="یادداشت داخلی تیم…" required></textarea>
				<p><button type="submit" class="button button-primary">افزودن یادداشت</button></p>
			</form>
			<?php if ( empty( $bundle['notes'] ) ) : ?>
				<p>یادداشتی نیست.</p>
			<?php else : ?>
				<ul class="ezcd-admin-notes">
					<?php foreach ( $bundle['notes'] as $n ) :
						$admin = get_userdata( (int) $n->admin_id );
						?>
						<li>
							<div class="ezcd-admin-note-meta">
								<?php echo esc_html( $admin ? $admin->display_name : 'سیستم' ); ?> —
								<?php echo esc_html( $n->created_at ); ?>
							</div>
							<div><?php echo esc_html( $n->note ); ?></div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
