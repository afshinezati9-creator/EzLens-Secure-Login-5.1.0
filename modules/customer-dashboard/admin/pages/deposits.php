<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$pending = EzLens_CD_Wallet_Deposits::pending( 50 );
$all     = isset( $_GET['all'] ) ? true : false;
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>شارژ کیف پول مشتریان</h1>
	<?php if ( ! empty( $_GET['ok'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p>انجام شد.</p></div>
	<?php endif; ?>
	<p class="description">درخواست‌های در انتظار بررسی — پس از تأیید، موجودی کیف پول مشتری افزایش می‌یابد.</p>
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>مشتری</th>
				<th>مبلغ</th>
				<th>روش</th>
				<th>پیگیری</th>
				<th>رسید</th>
				<th>زمان</th>
				<th>اقدام</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $pending ) ) : ?>
			<tr><td colspan="8">درخواست معلقی نیست.</td></tr>
		<?php else : ?>
			<?php foreach ( $pending as $d ) :
				$u = get_userdata( (int) $d->user_id );
				$receipt = $d->receipt_id ? wp_get_attachment_url( (int) $d->receipt_id ) : '';
				?>
				<tr>
					<td><?php echo esc_html( (string) $d->id ); ?></td>
					<td>
						<?php if ( $u ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $u->ID ) ); ?>">
								<?php echo esc_html( $u->display_name ); ?>
							</a>
						<?php else : ?>
							#<?php echo esc_html( (string) $d->user_id ); ?>
						<?php endif; ?>
					</td>
					<td><strong><?php echo esc_html( number_format_i18n( (int) $d->amount ) ); ?></strong></td>
					<td><?php
						$ml = class_exists( 'EzLens_CD_Wallet_Deposits' ) ? EzLens_CD_Wallet_Deposits::methods() : array();
						echo esc_html( isset( $ml[ $d->method ] ) ? $ml[ $d->method ] : $d->method );
					?></td>
					<td><code><?php echo esc_html( $d->ref_code ?: '—' ); ?></code></td>
					<td><?php if ( $receipt ) : ?><a href="<?php echo esc_url( $receipt ); ?>" target="_blank">مشاهده</a><?php else : ?>—<?php endif; ?></td>
					<td><?php echo esc_html( $d->created_at ); ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
							<input type="hidden" name="action" value="ezcd_deposit_approve">
							<input type="hidden" name="id" value="<?php echo esc_attr( $d->id ); ?>">
							<?php wp_nonce_field( 'ezcd_deposit_action' ); ?>
							<button class="button button-primary">تأیید شارژ</button>
						</form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
							<input type="hidden" name="action" value="ezcd_deposit_reject">
							<input type="hidden" name="id" value="<?php echo esc_attr( $d->id ); ?>">
							<?php wp_nonce_field( 'ezcd_deposit_action' ); ?>
							<button class="button">رد</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:24px;max-width:640px">
		<input type="hidden" name="action" value="ezcd_save_bank_info">
		<?php wp_nonce_field( 'ezcd_save_bank_info' ); ?>
		<h2>اطلاعات کارت / حساب برای نمایش به مشتری</h2>
		<textarea name="bank_info" rows="5" class="large-text"><?php echo esc_textarea( get_option( 'ezlens_cd_wallet_bank_info', '' ) ); ?></textarea>
		<?php submit_button( 'ذخیره اطلاعات بانکی' ); ?>
	</form>
</div>
