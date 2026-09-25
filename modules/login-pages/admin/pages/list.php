<?php
/**
 * لیست صفحات ورود — همیشه سه صفحه هسته را نشان می‌دهد
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

if ( class_exists( 'EzLens_Login_Pages_Install' ) ) {
	EzLens_Login_Pages_Install::force_create_table();
	EzLens_Login_Pages_Install::seed_defaults();
}

$cores = array(
	'customer-login.php' => array(
		'title'       => 'ورود مشتری',
		'description' => 'شورت‌کد [minimal_auth] — ورود و ثبت‌نام',
		'shortcode'   => '[minimal_auth]',
		'setting'     => 'enable_customer_login',
	),
	'admin-login.php'    => array(
		'title'       => 'ورود مدیر',
		'description' => 'شورت‌کد [admin_login_page]',
		'shortcode'   => '[admin_login_page]',
		'setting'     => 'enable_admin_login',
	),
	'lost-password.php'  => array(
		'title'       => 'فراموشی رمز',
		'description' => 'شورت‌کد [ezlens_lost_password]',
		'shortcode'   => '[ezlens_lost_password]',
		'setting'     => 'enable_lost_password',
	),
);

$table = class_exists( 'EzLens_Login_Pages_Install' )
	? EzLens_Login_Pages_Install::resolve_table_name()
	: $wpdb->prefix . 'ezlens_login_scripts';

$db_rows = array();
$tables  = $wpdb->get_col( 'SHOW TABLES' );
$tables_l = array_map( 'strtolower', is_array( $tables ) ? $tables : array() );
$table_ok = in_array( strtolower( $table ), $tables_l, true );

if ( $table_ok ) {
	$rows = $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY id ASC" );
	if ( is_array( $rows ) ) {
		foreach ( $rows as $r ) {
			$db_rows[ basename( $r->filename ) ] = $r;
		}
	}
}

$storage = class_exists( 'EzLens_Login_Pages_Install' )
	? EzLens_Login_Pages_Install::get_storage_dir()
	: ( defined( 'EZLAUTH_MODULES_DIR' ) ? EZLAUTH_MODULES_DIR . 'login-pages/storage/' : '' );

// ساخت لیست نمایش: هسته + سفارشی
$items = array();
foreach ( $cores as $fn => $meta ) {
	$row = isset( $db_rows[ $fn ] ) ? $db_rows[ $fn ] : null;
	$status = 1;
	if ( $row ) {
		$status = (int) $row->status;
	} elseif ( class_exists( 'EzLens_Auth_Settings' ) ) {
		$st = EzLens_Auth_Settings::get( $meta['setting'] );
		if ( $st !== null && $st !== '' ) {
			$status = ( $st === '1' || $st === 1 ) ? 1 : 0;
		}
	}
	$path = $storage ? trailingslashit( $storage ) . $fn : '';
	$items[] = (object) array(
		'id'          => $row ? (int) $row->id : 0,
		'title'       => $meta['title'],
		'filename'    => $fn,
		'description' => $meta['description'],
		'shortcode'   => $meta['shortcode'],
		'status'      => $status,
		'is_core'     => true,
		'file_ok'     => ( $path && is_readable( $path ) && filesize( $path ) > 50 ),
		'updated_at'  => $row->updated_at ?? '',
	);
}

// فایل‌های سفارشی از DB
foreach ( $db_rows as $fn => $row ) {
	if ( isset( $cores[ $fn ] ) ) {
		continue;
	}
	$items[] = (object) array(
		'id'          => (int) $row->id,
		'title'       => $row->title,
		'filename'    => $fn,
		'description' => $row->description,
		'shortcode'   => '',
		'status'      => (int) $row->status,
		'is_core'     => false,
		'file_ok'     => is_readable( trailingslashit( $storage ) . $fn ),
		'updated_at'  => $row->updated_at ?? '',
	);
}

$total_all      = count( $items );
$total_active   = count( array_filter( $items, function ( $i ) { return $i->status; } ) );
$total_inactive = $total_all - $total_active;

$add_url = admin_url( 'admin.php?page=ezlens-login-pages-edit' );
$nonce   = wp_create_nonce( 'ezpurchase_nonce' );

$icon = function ( $name ) {
	$path = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';
	if ( is_readable( $path ) ) {
		return file_get_contents( $path );
	}
	return '';
};
?>
<div class="wrap ezlp-admin" dir="rtl">
	<header class="ezlp-hero">
		<div>
			<h1>صفحات ورود</h1>
			<p>ورود مشتری، ورود مدیر و فراموشی رمز — یکپارچه با تنظیمات اصلی پلاگین و ویرایشگر کد مثل فرآیند خرید.</p>
		</div>
		<div class="ezlp-hero-actions">
			<a class="ezlp-btn ezlp-btn-light" href="<?php echo esc_url( $add_url ); ?>">افزودن فایل</a>
			<button type="button" class="ezlp-btn ezlp-btn-ghost" id="ezlp-rebuild">بازسازی جدول و seed</button>
		</div>
	</header>

	<div class="ezlp-stats">
		<div class="ezlp-stat"><strong><?php echo (int) $total_all; ?></strong><span>کل صفحات</span></div>
		<div class="ezlp-stat on"><strong><?php echo (int) $total_active; ?></strong><span>فعال</span></div>
		<div class="ezlp-stat off"><strong><?php echo (int) $total_inactive; ?></strong><span>غیرفعال</span></div>
		<div class="ezlp-stat"><strong><?php echo $table_ok ? 'OK' : '—'; ?></strong><span>جدول</span></div>
	</div>

	<?php if ( ! $table_ok ) : ?>
		<div class="notice notice-warning"><p>جدول دیتابیس هنوز تشخیص داده نشد. روی «بازسازی جدول و seed» بزنید. نمایش زیر از فایل‌های هسته است.</p></div>
	<?php endif; ?>

	<table class="ezlp-table">
		<thead>
			<tr>
				<th>صفحه</th>
				<th>فایل</th>
				<th>شورت‌کد</th>
				<th>فایل فیزیکی</th>
				<th>وضعیت</th>
				<th>عملیات</th>
			</tr>
		</thead>
		<tbody id="ezlp-tbody">
		<?php foreach ( $items as $item ) :
			$edit = admin_url( 'admin.php?page=ezlens-login-pages-edit' . ( $item->id ? '&id=' . $item->id : '&file=' . rawurlencode( $item->filename ) ) );
			?>
			<tr data-id="<?php echo (int) $item->id; ?>" data-file="<?php echo esc_attr( $item->filename ); ?>">
				<td>
					<strong><?php echo esc_html( $item->title ); ?></strong>
					<?php if ( $item->is_core ) : ?><span class="ezlp-badge">هسته</span><?php endif; ?>
					<div class="ezlp-desc"><?php echo esc_html( $item->description ); ?></div>
				</td>
				<td><code><?php echo esc_html( $item->filename ); ?></code></td>
				<td><?php echo $item->shortcode ? '<code>' . esc_html( $item->shortcode ) . '</code>' : '—'; ?></td>
				<td><?php echo $item->file_ok ? '<span class="ezlp-ok">موجود</span>' : '<span class="ezlp-miss">نیاز به seed</span>'; ?></td>
				<td>
					<label class="ezlp-switch">
						<input type="checkbox" class="ezlp-toggle" <?php checked( $item->status ); ?>
							data-id="<?php echo (int) $item->id; ?>"
							data-file="<?php echo esc_attr( $item->filename ); ?>">
						<span class="ezlp-slider"></span>
					</label>
				</td>
				<td>
					<a class="ezlp-btn ezlp-btn-sm" href="<?php echo esc_url( $edit ); ?>">ویرایش کد</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>

<style>
.ezlp-admin{max-width:1100px;margin:8px 12px 40px 0;font-family:Tahoma,IRANYekan,sans-serif}
.ezlp-hero{display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;align-items:flex-end;
background:linear-gradient(135deg,#031f8a,#1e4fd6 60%,#3b82f6);color:#fff;border-radius:16px;padding:20px 22px;margin-bottom:16px;
box-shadow:0 12px 32px rgba(3,31,138,.22)}
.ezlp-hero h1{margin:0 0 6px;color:#fff!important;font-size:20px}
.ezlp-hero p{margin:0;opacity:.92;font-size:13px;max-width:520px;line-height:1.7}
.ezlp-hero-actions{display:flex;gap:8px;flex-wrap:wrap}
.ezlp-btn{display:inline-flex;align-items:center;padding:8px 14px;border-radius:10px;border:none;cursor:pointer;font-weight:700;font-size:13px;text-decoration:none}
.ezlp-btn-light{background:#fff;color:#031f8a}
.ezlp-btn-ghost{background:rgba(255,255,255,.15);color:#fff}
.ezlp-btn-sm{background:linear-gradient(135deg,#1e4fd6,#031f8a);color:#fff!important;padding:6px 12px}
.ezlp-stats{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap}
.ezlp-stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;min-width:90px;box-shadow:0 4px 14px rgba(15,23,42,.04)}
.ezlp-stat strong{display:block;font-size:20px;color:#031f8a}
.ezlp-stat span{font-size:12px;color:#64748b}
.ezlp-stat.on strong{color:#059669}
.ezlp-stat.off strong{color:#b45309}
.ezlp-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 6px 20px rgba(15,23,42,.04)}
.ezlp-table th{text-align:right;padding:12px;background:#f8fafc;font-size:12px;color:#64748b;border-bottom:1px solid #e2e8f0}
.ezlp-table td{padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:13px;vertical-align:middle}
.ezlp-desc{font-size:12px;color:#64748b;margin-top:4px}
.ezlp-badge{display:inline-block;margin-right:6px;padding:1px 8px;border-radius:999px;background:#e0e7ff;color:#031f8a;font-size:10px;font-weight:700}
.ezlp-ok{color:#059669;font-weight:700;font-size:12px}
.ezlp-miss{color:#b45309;font-weight:700;font-size:12px}
.ezlp-switch{position:relative;display:inline-block;width:44px;height:24px}
.ezlp-switch input{opacity:0;width:0;height:0}
.ezlp-slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:999px;transition:.2s}
.ezlp-slider:before{position:absolute;content:"";height:18px;width:18px;right:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
.ezlp-switch input:checked + .ezlp-slider{background:#031f8a}
.ezlp-switch input:checked + .ezlp-slider:before{transform:translateX(-20px)}
</style>

<script>
(function($){
	var nonce = <?php echo wp_json_encode( $nonce ); ?>;
	$('#ezlp-rebuild').on('click', function(){
		var $b=$(this).prop('disabled',true).text('…');
		$.post(ajaxurl,{action:'ezpurchase_force_install',nonce:nonce},function(res){
			alert((res&&res.data&&res.data.message)||(res&&res.success?'انجام شد':'خطا'));
			location.reload();
		}).fail(function(){ alert('خطای ارتباط'); $b.prop('disabled',false).text('بازسازی جدول و seed'); });
	});
	$('.ezlp-toggle').on('change', function(){
		var $t=$(this), id=$t.data('id'), file=$t.data('file'), on=$t.is(':checked')?1:0;
		if(!id){
			// فقط settings
			$.post(ajaxurl,{action:'ezloginpages_toggle_by_file',nonce:nonce,filename:file,status:on},function(res){
				if(!res||!res.success){ $t.prop('checked',!on); alert((res&&res.data&&res.data.message)||'خطا'); }
			});
			return;
		}
		$.post(ajaxurl,{action:'ezpurchase_toggle_status',nonce:nonce,id:id,status:on},function(res){
			if(!res||!res.success){ $t.prop('checked',!on); alert((res&&res.data&&res.data.message)||'خطا'); }
		});
	});
})(jQuery);
</script>
