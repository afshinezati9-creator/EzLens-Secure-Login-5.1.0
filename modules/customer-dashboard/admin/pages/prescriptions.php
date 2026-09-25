<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
$table = $wpdb->prefix . 'ezlens_cd_prescriptions';
$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 80" );
$nonce = wp_create_nonce( 'ezcd_admin' );
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>پرونده‌ها و نسخه‌های بیماران</h1>
	<p class="description">جدیدترین نسخه‌های ثبت‌شده توسط مشتریان — حذف AJAX.</p>
	<table class="wp-list-table widefat striped ezcd-admin-table">
		<thead>
			<tr>
				<th>ID</th>
				<th>مشتری</th>
				<th>نوع</th>
				<th>پزشک</th>
				<th>صدور</th>
				<th>پیوست</th>
				<th>اقدام</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $rows ) ) : ?>
			<tr><td colspan="7">نسخه‌ای ثبت نشده.</td></tr>
		<?php else : ?>
			<?php foreach ( $rows as $row ) :
				$u = get_userdata( (int) $row->user_id );
				$url = $row->attachment_id ? wp_get_attachment_url( (int) $row->attachment_id ) : '';
				?>
				<tr data-id="<?php echo esc_attr( $row->id ); ?>">
					<td><?php echo esc_html( (string) $row->id ); ?></td>
					<td>
						<?php if ( $u ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $u->ID ) ); ?>"><?php echo esc_html( $u->display_name ); ?></a>
						<?php else : ?>#<?php echo esc_html( (string) $row->user_id ); ?><?php endif; ?>
					</td>
					<td><?php echo esc_html( $row->rx_type ); ?></td>
					<td><?php echo esc_html( $row->doctor_name ?: '—' ); ?></td>
					<td><?php echo esc_html( $row->issued_at ?: '—' ); ?></td>
					<td><?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>" target="_blank">فایل</a><?php else : ?>—<?php endif; ?></td>
					<td><button type="button" class="button ezcd-rx-del" data-id="<?php echo esc_attr( $row->id ); ?>">حذف</button></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
<script>
(function(){
	var nonce='<?php echo esc_js( $nonce ); ?>';
	document.querySelectorAll('.ezcd-rx-del').forEach(function(btn){
		btn.addEventListener('click', function(){
			if(!confirm('حذف نسخه؟')) return;
			var body=new FormData();
			body.append('action','ezcd_admin_rx_delete');
			body.append('nonce',nonce);
			body.append('id',btn.getAttribute('data-id'));
			fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
				if(res&&res.success){ var tr=btn.closest('tr'); if(tr) tr.remove(); }
				else alert('خطا');
			});
		});
	});
})();
</script>
