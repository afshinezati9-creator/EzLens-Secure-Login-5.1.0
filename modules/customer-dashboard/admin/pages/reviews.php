<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$comments = get_comments(
	array(
		'status' => 'all',
		'type'   => 'review',
		'number' => 50,
	)
);
if ( empty( $comments ) ) {
	$comments = get_comments( array( 'status' => 'all', 'number' => 50, 'post_type' => 'product' ) );
}
$nonce = wp_create_nonce( 'ezcd_admin' );
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>نظرات مشتریان</h1>
	<table class="wp-list-table widefat striped ezcd-admin-table">
		<thead>
			<tr>
				<th>محصول</th>
				<th>نویسنده</th>
				<th>متن</th>
				<th>وضعیت</th>
				<th>اقدام</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $comments ) ) : ?>
			<tr><td colspan="5">نظری نیست.</td></tr>
		<?php else : ?>
			<?php foreach ( $comments as $c ) : ?>
				<tr data-id="<?php echo esc_attr( $c->comment_ID ); ?>">
					<td><a href="<?php echo esc_url( get_edit_post_link( $c->comment_post_ID ) ); ?>"><?php echo esc_html( get_the_title( $c->comment_post_ID ) ); ?></a></td>
					<td><?php echo esc_html( $c->comment_author ); ?></td>
					<td><?php echo esc_html( wp_trim_words( $c->comment_content, 18 ) ); ?></td>
					<td><?php echo esc_html( $c->comment_approved ); ?></td>
					<td>
						<button type="button" class="button button-primary ezcd-rev" data-do="approve" data-id="<?php echo esc_attr( $c->comment_ID ); ?>">تأیید</button>
						<button type="button" class="button ezcd-rev" data-do="hold" data-id="<?php echo esc_attr( $c->comment_ID ); ?>">معلق</button>
						<button type="button" class="button ezcd-rev-reply" data-id="<?php echo esc_attr( $c->comment_ID ); ?>">پاسخ</button>
						<button type="button" class="button ezcd-rev" data-do="trash" data-id="<?php echo esc_attr( $c->comment_ID ); ?>">حذف</button>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
<script>
(function(){
	var nonce='<?php echo esc_js( $nonce ); ?>';
	function act(id, doo, content){
		var body=new FormData();
		body.append('action','ezcd_admin_review_action');
		body.append('nonce',nonce);
		body.append('id',id);
		body.append('do',doo);
		if(content) body.append('content',content);
		return fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();});
	}
	document.querySelectorAll('.ezcd-rev').forEach(function(btn){
		btn.addEventListener('click', function(){
			act(btn.getAttribute('data-id'), btn.getAttribute('data-do')).then(function(res){
				if(res&&res.success){ if(btn.getAttribute('data-do')==='trash'){ var tr=btn.closest('tr'); if(tr) tr.remove(); } else alert(res.data.message); }
				else alert('خطا');
			});
		});
	});
	document.querySelectorAll('.ezcd-rev-reply').forEach(function(btn){
		btn.addEventListener('click', function(){
			var txt = prompt('پاسخ شما:');
			if(!txt) return;
			act(btn.getAttribute('data-id'), 'reply', txt).then(function(res){ alert((res&&res.data&&res.data.message)||'انجام شد'); });
		});
	});
})();
</script>
