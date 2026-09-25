<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$paged   = max( 1, absint( $_GET['paged'] ?? 1 ) );
$result  = EzLens_CD_Admin_Customers::query(
	array(
		'search' => $search,
		'paged'  => $paged,
	)
);
$pages = max( 1, (int) ceil( $result['total'] / 20 ) );
$nonce = wp_create_nonce( 'ezcd_admin' );
$stats = EzLens_CD_Admin_Customers::global_stats();
?>
<div class="wrap ezcd-admin" dir="rtl" id="ezcd-customers-app">
	<h1>مشتریان</h1>
	<div class="ezcd-admin-stats" style="grid-template-columns:repeat(4,1fr)">
		<div class="ezcd-admin-stat is-blue"><strong><?php echo esc_html( number_format_i18n( $stats['customers'] ?? $result['total'] ) ); ?></strong><span>کل مشتریان</span></div>
		<div class="ezcd-admin-stat is-green"><strong><?php echo esc_html( number_format_i18n( 0 ) ); ?></strong><span>سفارش‌ها</span></div>
		<div class="ezcd-admin-stat is-amber"><strong><?php echo esc_html( number_format_i18n( 0 ) ); ?></strong><span>تیکت باز</span></div>
		<div class="ezcd-admin-stat"><strong><?php echo esc_html( number_format_i18n( $result['total'] ) ); ?></strong><span>نتیجه جستجو</span></div>
	</div>

	<div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:14px 0">
		<form method="get" style="display:flex;gap:8px;flex:1">
			<input type="hidden" name="page" value="ezlens-cd-customers">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" class="regular-text" placeholder="نام، ایمیل، موبایل…">
			<button class="button">جستجو</button>
		</form>
		<button type="button" class="button button-primary" id="ezcd-open-create">+ مشتری جدید</button>
	</div>

	<div class="ezcd-admin-customers-layout">
		<div class="ezcd-admin-card">
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th>مشتری</th>
						<th>ایمیل</th>
						<th>موبایل</th>
						<th>سفارش</th>
						<th>کیف پول</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $result['items'] ) ) : ?>
					<tr><td colspan="6">مشتری‌ای یافت نشد.</td></tr>
				<?php else : ?>
					<?php foreach ( $result['items'] as $u ) :
						$st = EzLens_CD_Admin_Customers::order_stats( $u->ID );
						$w  = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance( $u->ID ) : 0;
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $u->display_name ); ?></strong>
								<div class="description">#<?php echo esc_html( (string) $u->ID ); ?></div>
							</td>
							<td><?php echo esc_html( $u->user_email ); ?></td>
							<td><code dir="ltr"><?php echo esc_html( EzLens_CD_Admin_Customers::phone( $u->ID ) ); ?></code></td>
							<td><?php echo esc_html( number_format_i18n( $st['count'] ) ); ?></td>
							<td><?php echo esc_html( number_format_i18n( $w ) ); ?></td>
							<td><button type="button" class="button button-small ezcd-open-customer" data-id="<?php echo esc_attr( (string) $u->ID ); ?>">مشاهده</button></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
			<?php if ( $pages > 1 ) : ?>
				<div class="tablenav bottom"><div class="tablenav-pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '',
								'current' => $paged,
								'total'   => $pages,
							)
						)
					);
					?>
				</div></div>
			<?php endif; ?>
		</div>

		<div class="ezcd-admin-card" id="ezcd-customer-panel">
			<p class="description">روی «مشاهده» بزنید تا پروفایل کامل مشتری با تب‌های اجکسی اینجا باز شود.</p>
		</div>
	</div>

	<!-- Create modal -->
	<div id="ezcd-create-modal" class="ezcd-modal" hidden>
		<div class="ezcd-modal-card">
			<h2>افزودن مشتری دستی</h2>
			<form id="ezcd-create-customer-form">
				<p><label>نام نمایشی<br><input type="text" name="display_name" class="widefat" required></label></p>
				<p><label>ایمیل<br><input type="email" name="email" class="widefat" required></label></p>
				<p><label>موبایل<br><input type="text" name="phone" class="widefat" dir="ltr" placeholder="09xxxxxxxxx"></label></p>
				<p><label>رمز عبور (خالی = تصادفی)<br><input type="text" name="password" class="widefat" autocomplete="new-password"></label></p>
				<p>
					<button type="submit" class="button button-primary">ایجاد</button>
					<button type="button" class="button" id="ezcd-create-cancel">انصراف</button>
					<span id="ezcd-create-msg" class="ezcd-admin-msg"></span>
				</p>
			</form>
		</div>
	</div>
</div>
<style>
.ezcd-admin-customers-layout{display:grid;grid-template-columns:1fr 1.1fr;gap:16px;align-items:start}
@media(max-width:1100px){.ezcd-admin-customers-layout{grid-template-columns:1fr}}
.ezcd-modal{position:fixed;inset:0;background:rgba(15,23,42,.45);display:flex;align-items:center;justify-content:center;z-index:100000}
.ezcd-modal[hidden]{display:none!important}
.ezcd-modal-card{background:#fff;border-radius:16px;padding:20px 22px;width:min(440px,92vw);box-shadow:0 20px 50px rgba(0,0,0,.2)}
.ezcd-cust-tabs{display:flex;flex-wrap:wrap;gap:6px;margin:12px 0}
.ezcd-cust-tab{border:1px solid #e2e8f0;background:#f8fafc;border-radius:999px;padding:6px 12px;cursor:pointer;font-size:12px;font-weight:600}
.ezcd-cust-tab.is-active{background:linear-gradient(135deg,#031f8a,#2563eb);color:#fff;border-color:transparent}
.ezcd-cust-panel{margin-top:10px}
</style>
<script>
(function(){
	var nonce = '<?php echo esc_js( $nonce ); ?>';
	var panel = document.getElementById('ezcd-customer-panel');
	var currentId = 0;

	function loadProfile(id, tab){
		currentId = id;
		panel.innerHTML = '<p>در حال بارگذاری…</p>';
		var body = new FormData();
		body.append('action','ezcd_admin_customer_profile');
		body.append('nonce', nonce);
		body.append('user_id', id);
		body.append('tab', tab || 'overview');
		fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
			if(!res||!res.success){ panel.innerHTML = '<p class="ezcd-admin-msg is-err">'+(res&&res.data&&res.data.message||'خطا')+'</p>'; return; }
			panel.innerHTML = res.data.html;
			bindProfile();
		});
	}
	function bindProfile(){
		panel.querySelectorAll('.ezcd-cust-tab').forEach(function(btn){
			btn.addEventListener('click', function(){
				loadProfile(currentId, btn.getAttribute('data-tab'));
			});
		});
		var edit = panel.querySelector('#ezcd-cust-edit-form');
		if(edit){
			edit.addEventListener('submit', function(e){
				e.preventDefault();
				var fd = new FormData(edit);
				fd.append('action','ezcd_admin_customer_update');
				fd.append('nonce', nonce);
				fd.append('user_id', currentId);
				fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(res){
					var m = panel.querySelector('.ezcd-edit-msg');
					if(m){ m.textContent = (res&&res.data&&res.data.message)||''; m.className='ezcd-admin-msg '+(res&&res.success?'is-ok':'is-err'); }
					if(res&&res.success) loadProfile(currentId, 'account');
				});
			});
		}
		var note = panel.querySelector('#ezcd-cust-note-form');
		if(note){
			note.addEventListener('submit', function(e){
				e.preventDefault();
				var fd = new FormData(note);
				fd.append('action','ezcd_admin_customer_note');
				fd.append('nonce', nonce);
				fd.append('user_id', currentId);
				fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(res){
					if(res&&res.success) loadProfile(currentId, 'notes');
					else alert((res&&res.data&&res.data.message)||'خطا');
				});
			});
		}
		var wal = panel.querySelector('#ezcd-cust-wallet-form');
		if(wal){
			wal.addEventListener('submit', function(e){
				e.preventDefault();
				var fd = new FormData(wal);
				fd.append('action','ezcd_admin_customer_wallet');
				fd.append('nonce', nonce);
				fd.append('user_id', currentId);
				fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(res){
					if(res&&res.success) loadProfile(currentId, 'wallet');
					else alert((res&&res.data&&res.data.message)||'خطا');
				});
			});
		}
	}

	document.querySelectorAll('.ezcd-open-customer').forEach(function(btn){
		btn.addEventListener('click', function(){ loadProfile(btn.getAttribute('data-id'), 'overview'); });
	});

	var modal = document.getElementById('ezcd-create-modal');
	document.getElementById('ezcd-open-create').addEventListener('click', function(){ modal.hidden = false; });
	document.getElementById('ezcd-create-cancel').addEventListener('click', function(){ modal.hidden = true; });
	document.getElementById('ezcd-create-customer-form').addEventListener('submit', function(e){
		e.preventDefault();
		var fd = new FormData(e.target);
		fd.append('action','ezcd_admin_customer_create');
		fd.append('nonce', nonce);
		fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(res){
			var m = document.getElementById('ezcd-create-msg');
			m.textContent = (res&&res.data&&res.data.message)||'';
			m.className = 'ezcd-admin-msg '+(res&&res.success?'is-ok':'is-err');
			if(res&&res.success){ setTimeout(function(){ location.reload(); }, 800); }
		});
	});
})();
</script>
