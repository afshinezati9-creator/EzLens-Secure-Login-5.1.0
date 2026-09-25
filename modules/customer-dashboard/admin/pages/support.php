<?php
/**
 * پشتیبانی حرفه‌ای ادمین + پیام مستقیم به مشتری
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$nonce   = wp_create_nonce( 'ezcd_admin' );
$view    = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'tickets';
if ( ! in_array( $view, array( 'tickets', 'compose' ), true ) ) {
	$view = 'tickets';
}
$status  = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'all';
$search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$tickets = class_exists( 'EzLens_CD_Support_Bridge' )
	? EzLens_CD_Support_Bridge::admin_list( 'all' === $status ? '' : $status, $search, 60, 0 )
	: array();
$counts  = array( 'all' => 0, 'open' => 0, 'replied' => 0, 'closed' => 0 );
if ( class_exists( 'EzLens_CD_Support_Bridge' ) ) {
	$all = EzLens_CD_Support_Bridge::admin_list( '', '', 200, 0 );
	$counts['all'] = count( $all );
	foreach ( $all as $row ) {
		$st = strtolower( (string) ( $row->status ?? '' ) );
		if ( 'open' === $st ) {
			$counts['open']++;
		} elseif ( in_array( $st, array( 'replied', 'answered' ), true ) ) {
			$counts['replied']++;
		} elseif ( in_array( $st, array( 'closed', 'resolved' ), true ) ) {
			$counts['closed']++;
		}
	}
}
?>
<div class="wrap ezcd-admin ezcd-as" dir="rtl" id="ezcd-admin-support">
	<div class="ezcd-as-head">
		<div>
			<h1>پشتیبانی</h1>
			<p class="description">تیکت‌های داشبورد مشتری — پاسخ، پیوست، ایمیل/پیامک و پیام مستقیم به مشتری</p>
		</div>
	</div>

	<nav class="ezcd-as-views">
		<a class="ezcd-as-view<?php echo 'tickets' === $view ? ' is-on' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-support&view=tickets' ) ); ?>">لیست تیکت‌ها</a>
		<a class="ezcd-as-view<?php echo 'compose' === $view ? ' is-on' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-support&view=compose' ) ); ?>">پیام به مشتری</a>
	</nav>

	<?php if ( 'compose' === $view ) : ?>
		<div class="ezcd-as-card" style="max-width:720px">
			<h2 style="margin-top:0">ارسال پیام پشتیبانی به مشتری</h2>
			<p class="description">پیام به‌صورت تیکت در داشبورد مشتری (بخش پشتیبانی) نمایش داده می‌شود.</p>
			<form id="ezcd-compose-form">
				<p>
					<label>جستجوی مشتری<br>
						<input type="search" id="ezcd-compose-search" class="regular-text" placeholder="نام، ایمیل یا موبایل…" autocomplete="off">
					</label>
				</p>
				<div id="ezcd-compose-results" class="ezcd-compose-results"></div>
				<input type="hidden" name="user_id" id="ezcd-compose-user" value="">
				<p id="ezcd-compose-selected" class="description" style="display:none"></p>
				<p><label>موضوع<br><input type="text" name="subject" class="widefat" required placeholder="مثلاً: پیگیری سفارش شما"></label></p>
				<p><label>متن پیام<br><textarea name="message" class="widefat" rows="5" required placeholder="پیام شما…"></textarea></label></p>
				<p><label>پیوست (حداکثر ۲۰ مگ)<br><input type="file" name="attachment" accept="image/*,.pdf"></label></p>
				<div class="ezcd-as-notify">
					<label><input type="checkbox" name="notify_email" value="1" checked> ارسال ایمیل</label>
					<label><input type="checkbox" name="notify_sms" value="1" checked> ارسال پیامک</label>
				</div>
				<p>
					<button type="submit" class="button button-primary">ارسال به داشبورد مشتری</button>
					<span id="ezcd-compose-msg" class="ezcd-admin-msg"></span>
				</p>
			</form>
		</div>
	<?php else : ?>

	<nav class="ezcd-as-filters">
		<?php
		$filters = array(
			'all'     => 'همه',
			'open'    => 'باز',
			'replied' => 'پاسخ‌داده‌شده',
			'closed'  => 'بسته',
		);
		foreach ( $filters as $k => $lab ) :
			$url = admin_url( 'admin.php?page=ezlens-cd-support&view=tickets&status=' . $k );
			if ( $search ) {
				$url = add_query_arg( 's', $search, $url );
			}
			?>
			<a class="ezcd-as-chip<?php echo $status === $k ? ' is-on' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $lab ); ?>
				<span><?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( (string) ( $counts[ $k ] ?? 0 ) ) : (string) ( $counts[ $k ] ?? 0 ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="get" class="ezcd-as-search">
		<input type="hidden" name="page" value="ezlens-cd-support">
		<input type="hidden" name="view" value="tickets">
		<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="جستجوی نام، ایمیل یا موضوع…">
		<button type="submit" class="button button-primary">جستجو</button>
	</form>

	<div class="ezcd-as-grid">
		<div class="ezcd-as-card">
			<table class="wp-list-table widefat striped ezcd-as-table">
				<thead>
					<tr>
						<th>شناسه</th>
						<th>مشتری</th>
						<th>موضوع</th>
						<th>وضعیت</th>
						<th>به‌روزرسانی</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $tickets ) ) : ?>
					<tr><td colspan="6">تیکتی یافت نشد.</td></tr>
				<?php else : ?>
					<?php foreach ( $tickets as $t ) :
						$tid  = (int) $t->id;
						$name = isset( $t->display_name ) ? $t->display_name : ( '#' . (int) $t->user_id );
						?>
						<tr>
							<td><code>#<?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( (string) $tid ) : (string) $tid ); ?></code></td>
							<td><?php echo esc_html( $name ); ?></td>
							<td><?php echo esc_html( $t->subject ?: '—' ); ?></td>
							<td><span class="ezcd-as-status"><?php echo esc_html( EzLens_CD_Support_Bridge::status_label( $t->status ) ); ?></span></td>
							<td><?php echo esc_html( function_exists( 'ezcd_jalali' ) ? ezcd_jalali( $t->updated_at ) : $t->updated_at ); ?></td>
							<td><button type="button" class="button button-small ezcd-as-open" data-id="<?php echo esc_attr( (string) $tid ); ?>">مشاهده</button></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="ezcd-as-card ezcd-as-detail" id="ezcd-ticket-detail">
			<div class="ezcd-as-placeholder">
				<p>یک تیکت را از لیست انتخاب کنید تا جزئیات، پاسخ و اطلاع‌رسانی اینجا باز شود.</p>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
<style>
.ezcd-as-views{display:flex;gap:8px;margin:12px 0 16px}
.ezcd-as-view{padding:9px 16px;border-radius:10px;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;color:#475569;font-weight:700;font-size:13px}
.ezcd-as-view.is-on,.ezcd-as-view:hover{background:linear-gradient(135deg,#031f8a,#2563eb);color:#fff;border-color:transparent}
.ezcd-as-head{margin-bottom:8px}
.ezcd-as-filters{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0}
.ezcd-as-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;color:#475569;font-weight:700;font-size:13px;transition:.15s}
.ezcd-as-chip span{background:#fff;border-radius:999px;padding:2px 8px;font-size:11px;color:#031f8a}
.ezcd-as-chip.is-on,.ezcd-as-chip:hover{background:linear-gradient(135deg,#031f8a,#2563eb);color:#fff;border-color:transparent}
.ezcd-as-chip.is-on span,.ezcd-as-chip:hover span{background:rgba(255,255,255,.2);color:#fff}
.ezcd-as-search{display:flex;gap:8px;margin-bottom:16px}
.ezcd-as-search input[type=search]{flex:1;max-width:360px;border-radius:10px}
.ezcd-as-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:16px;align-items:start}
@media(max-width:1100px){.ezcd-as-grid{grid-template-columns:1fr}}
.ezcd-as-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.ezcd-as-status{display:inline-block;padding:3px 10px;border-radius:999px;background:#ecfdf5;color:#047857;font-size:11px;font-weight:700}
.ezcd-as-placeholder{padding:40px 16px;text-align:center;color:#94a3b8}
.ezcd-as-thread{max-height:340px;overflow:auto;padding:12px;background:#f8fafc;border-radius:12px;margin:12px 0;border:1px solid #e2e8f0}
.ezcd-as-bubble{padding:10px 12px;border-radius:12px;margin:8px 0;max-width:92%;background:#fff;border:1px solid #e2e8f0}
.ezcd-as-bubble.is-staff{background:linear-gradient(135deg,#031f8a,#1d4ed8);color:#fff;border:none;margin-right:auto}
.ezcd-as-bubble .meta{font-size:11px;opacity:.75;margin-bottom:4px}
.ezcd-as-actions{display:flex;flex-wrap:wrap;gap:6px;margin:8px 0 12px}
.ezcd-as-notify{display:flex;flex-direction:column;gap:6px;margin:8px 0;padding:10px 12px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0}
.ezcd-compose-results{max-height:220px;overflow:auto;border:1px solid #e2e8f0;border-radius:10px;margin:8px 0 12px;display:none}
.ezcd-compose-row{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid #f1f5f9;cursor:pointer}
.ezcd-compose-row:hover{background:#f8fafc}
.ezcd-compose-row.is-on{background:#eff6ff}
</style>
<script>
(function(){
	var nonce = '<?php echo esc_js( $nonce ); ?>';
	var detail = document.getElementById('ezcd-ticket-detail');
	function loadTicket(id){
		if(!detail) return;
		detail.innerHTML = '<p style="padding:24px;text-align:center;color:#94a3b8">در حال بارگذاری…</p>';
		var body = new FormData();
		body.append('action','ezcd_admin_support_get');
		body.append('nonce', nonce);
		body.append('ticket_id', id);
		fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
			if(!res || !res.success){ detail.innerHTML = '<p class="ezcd-admin-msg is-err">'+(res&&res.data&&res.data.message||'خطا')+'</p>'; return; }
			detail.innerHTML = res.data.html;
			bindDetail(id);
		});
	}
	function bindDetail(id){
		var form = detail.querySelector('#ezcd-admin-reply-form');
		if(form){
			form.addEventListener('submit', function(e){
				e.preventDefault();
				var fd = new FormData(form);
				fd.append('action','ezcd_admin_support_reply');
				fd.append('nonce', nonce);
				fd.append('ticket_id', id);
				var btn = form.querySelector('[type=submit]');
				if(btn) btn.disabled = true;
				fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(res){
					if(btn) btn.disabled = false;
					var m = detail.querySelector('.ezcd-reply-msg');
					if(m){ m.textContent = (res&&res.data&&res.data.message)|| (res&&res.success?'ارسال شد':'خطا'); m.className='ezcd-admin-msg '+(res&&res.success?'is-ok':'is-err'); }
					if(res && res.success) loadTicket(id);
				});
			});
		}
		detail.querySelectorAll('[data-status]').forEach(function(btn){
			btn.addEventListener('click', function(){
				var body = new FormData();
				body.append('action','ezcd_admin_support_status');
				body.append('nonce', nonce);
				body.append('ticket_id', id);
				body.append('status', btn.getAttribute('data-status'));
				fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
					if(res && res.success) loadTicket(id);
					else alert((res&&res.data&&res.data.message)||'خطا');
				});
			});
		});
	}
	document.querySelectorAll('.ezcd-as-open').forEach(function(btn){
		btn.addEventListener('click', function(){ loadTicket(btn.getAttribute('data-id')); });
	});

	/* Compose: search customers + send */
	var search = document.getElementById('ezcd-compose-search');
	var results = document.getElementById('ezcd-compose-results');
	var userInput = document.getElementById('ezcd-compose-user');
	var selected = document.getElementById('ezcd-compose-selected');
	var timer = null;
	if(search && results){
		search.addEventListener('input', function(){
			clearTimeout(timer);
			var q = search.value.trim();
			if(q.length < 2){ results.style.display='none'; results.innerHTML=''; return; }
			timer = setTimeout(function(){
				fetch(ajaxurl + '?action=ezcd_admin_search&q=' + encodeURIComponent(q), {credentials:'same-origin'})
					.then(function(r){return r.json();})
					.then(function(res){
						if(!res || !res.success || !res.data || !res.data.length){
							results.innerHTML = '<div class="ezcd-compose-row">نتیجه‌ای نیست</div>';
							results.style.display = 'block';
							return;
						}
						results.innerHTML = res.data.map(function(u){
							return '<div class="ezcd-compose-row" data-id="'+u.id+'" data-name="'+String(u.name||'').replace(/"/g,'&quot;')+'"><span><strong>'+u.name+'</strong><br><small>'+u.email+(u.phone?' · '+u.phone:'')+'</small></span><span class="button button-small">انتخاب</span></div>';
						}).join('');
						results.style.display = 'block';
						results.querySelectorAll('.ezcd-compose-row[data-id]').forEach(function(row){
							row.addEventListener('click', function(){
								userInput.value = row.getAttribute('data-id');
								selected.style.display = 'block';
								selected.textContent = 'انتخاب‌شده: ' + row.getAttribute('data-name') + ' (#' + row.getAttribute('data-id') + ')';
								results.querySelectorAll('.ezcd-compose-row').forEach(function(r){ r.classList.remove('is-on'); });
								row.classList.add('is-on');
							});
						});
					});
			}, 280);
		});
	}
	var compose = document.getElementById('ezcd-compose-form');
	if(compose){
		compose.addEventListener('submit', function(e){
			e.preventDefault();
			if(!userInput.value){ alert('ابتدا مشتری را انتخاب کنید'); return; }
			var fd = new FormData(compose);
			fd.append('action','ezcd_admin_support_compose');
			fd.append('nonce', nonce);
			fd.set('user_id', userInput.value);
			var btn = compose.querySelector('[type=submit]');
			if(btn) btn.disabled = true;
			fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(res){
				if(btn) btn.disabled = false;
				var m = document.getElementById('ezcd-compose-msg');
				m.textContent = (res&&res.data&&res.data.message)|| (res&&res.success?'ارسال شد':'خطا');
				m.className = 'ezcd-admin-msg '+(res&&res.success?'is-ok':'is-err');
				if(res && res.success){ compose.reset(); userInput.value=''; selected.style.display='none'; }
			});
		});
	}
})();
</script>
