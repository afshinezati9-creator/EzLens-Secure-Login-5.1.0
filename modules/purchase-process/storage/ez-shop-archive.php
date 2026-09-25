<?php
/**
 * عنوان نمایشی: ارتقاء فروشگاه ایزی‌لنز — فیلتر AJAX + تب‌ها + واتساپ
 * نام فایل: ezlens-shop-enhance
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ============================================================
 * ۱. توابع پایه
 * ============================================================ */
if ( ! function_exists( 'ez_is_shop_context' ) ) {
	function ez_is_shop_context() {
		return function_exists( 'is_shop' ) && (
			is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()
		);
	}
}

if ( ! function_exists( 'ez_convert_to_persian' ) ) {
	function ez_convert_to_persian( $text ) {
		$fa = array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' );
		$en = array( '0','1','2','3','4','5','6','7','8','9' );
		return str_replace( $en, $fa, (string) $text );
	}
}

add_filter( 'woocommerce_price',          'ez_convert_to_persian', 10, 1 );
add_filter( 'wc_price',                   'ez_convert_to_persian', 10, 1 );
add_filter( 'woocommerce_get_price_html', 'ez_convert_to_persian', 10, 1 );
add_filter( 'woocommerce_order_number', function( $n, $o ){ return ez_convert_to_persian( $n ); }, 10, 2 );
add_filter( 'woocommerce_get_stock_quantity', 'ez_convert_to_persian', 10, 1 );
add_filter( 'woocommerce_cart_item_quantity', 'ez_convert_to_persian', 10, 1 );

add_filter( 'the_content', function( $content ){
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_account_page() || is_checkout() || is_cart() ) ) {
		$content = ez_convert_to_persian( $content );
	}
	return $content;
}, 20 );

add_action( 'wp_head', function(){
	if ( ! ez_is_shop_context() ) return;
	if ( function_exists( 'is_shop' ) && is_shop() && ! is_paged() ) {
		echo '<meta name="description" content="فروشگاه تخصصی ایزی‌لنز؛ لنز طبی هارد و سافت، عدسی عینک، عینک طبی و اکسسوری با مشاوره تخصصی و ارسال سریع.">' . "\n";
	}
}, 5 );


/* ============================================================
 * ۱b. جلوگیری از FOUC + اسکلتون لود
 * ============================================================ */
add_filter( 'body_class', function( $classes ) {
	if ( function_exists( 'ez_is_shop_context' ) && ez_is_shop_context() ) {
		$classes[] = 'ezshop-booting';
	}
	return $classes;
} );

add_action( 'wp_head', function () {
	if ( ! function_exists( 'ez_is_shop_context' ) || ! ez_is_shop_context() ) {
		return;
	}
	?>
<style id="ezshop-boot-css">
/* تا آماده شدن UI سفارشی، تم پیش‌فرض دیده نشود */
body.ezshop-booting .filters-area,
body.ezshop-booting .wd-show-sidebar-btn,
body.ezshop-booting .wd-filter-buttons,
body.ezshop-booting .shop-loop-head,
body.ezshop-booting .wd-products-element,
body.ezshop-booting .woocommerce-pagination,
body.ezshop-booting .wd-pagination,
body.ezshop-booting .woocommerce-result-count,
body.ezshop-booting .woocommerce-ordering,
body.ezshop-booting .wd-shop-tools{
	opacity:0 !important;
	visibility:hidden !important;
	pointer-events:none !important;
}
body.ezshop-booting .wd-content-area{
	position:relative !important;
	min-height:60vh !important;
}
.ezshop-skeleton{
	display:none;
	width:100%;
	max-width:1500px;
	margin:0 auto;
	padding:12px 16px 32px;
	box-sizing:border-box;
	direction:rtl;
}
body.ezshop-booting .ezshop-skeleton{display:block !important}
.ezshop-sk-row{display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap}
.ezshop-sk-side{
	flex:0 0 280px;max-width:100%;
	background:#fff;border:1px solid #e8edf5;border-radius:16px;padding:16px;
	box-shadow:0 4px 16px -8px rgba(3,31,138,.08);
}
.ezshop-sk-main{flex:1 1 280px;min-width:0}
.ezshop-sk-line{
	height:12px;border-radius:8px;margin:0 0 12px;
	background:linear-gradient(90deg,#eef2f7 0%,#f8fafc 40%,#eef2f7 80%);
	background-size:200% 100%;
	animation:ezshopSk 1.2s ease-in-out infinite;
}
.ezshop-sk-line.lg{height:18px;width:40%}
.ezshop-sk-line.sm{height:10px;width:70%}
.ezshop-sk-tabs{display:flex;gap:8px;margin:0 0 16px;flex-wrap:wrap}
.ezshop-sk-tab{
	height:36px;width:88px;border-radius:10px;
	background:linear-gradient(90deg,#eef2f7 0%,#f8fafc 40%,#eef2f7 80%);
	background-size:200% 100%;animation:ezshopSk 1.2s ease-in-out infinite;
}
.ezshop-sk-grid{
	display:grid;
	grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
	gap:14px;
}
.ezshop-sk-card{
	background:#fff;border:1px solid #e8edf5;border-radius:16px;overflow:hidden;
	box-shadow:0 2px 10px -6px rgba(15,23,42,.08);
}
.ezshop-sk-thumb{
	aspect-ratio:1/1;
	background:linear-gradient(90deg,#e8edf5 0%,#f5f7fb 45%,#e8edf5 90%);
	background-size:200% 100%;animation:ezshopSk 1.2s ease-in-out infinite;
}
.ezshop-sk-card-body{padding:12px}
@keyframes ezshopSk{
	0%{background-position:100% 0}
	100%{background-position:-100% 0}
}
@media(max-width:900px){
	.ezshop-sk-side{display:none}
}
body.ezshop-ready .ezshop-skeleton{display:none !important}
body.ezshop-ready.ezshop-booting .shop-loop-head,
body.ezshop-ready.ezshop-booting .wd-products-element{
	opacity:1 !important;visibility:visible !important;pointer-events:auto !important;
}
</style>
	<?php
}, 1 );

add_action( 'wp_body_open', function () {
	if ( ! function_exists( 'ez_is_shop_context' ) || ! ez_is_shop_context() ) {
		return;
	}
	?>
	<div class="ezshop-skeleton" aria-hidden="true">
		<div class="ezshop-sk-line lg" style="margin-bottom:16px"></div>
		<div class="ezshop-sk-row">
			<div class="ezshop-sk-side">
				<div class="ezshop-sk-line lg"></div>
				<div class="ezshop-sk-line"></div>
				<div class="ezshop-sk-line sm"></div>
				<div class="ezshop-sk-line"></div>
				<div class="ezshop-sk-line sm"></div>
				<div class="ezshop-sk-line"></div>
				<div class="ezshop-sk-line sm"></div>
			</div>
			<div class="ezshop-sk-main">
				<div class="ezshop-sk-tabs">
					<div class="ezshop-sk-tab"></div><div class="ezshop-sk-tab"></div>
					<div class="ezshop-sk-tab"></div><div class="ezshop-sk-tab"></div>
					<div class="ezshop-sk-tab"></div>
				</div>
				<div class="ezshop-sk-grid">
					<?php for ( $i = 0; $i < 8; $i++ ) : ?>
					<div class="ezshop-sk-card">
						<div class="ezshop-sk-thumb"></div>
						<div class="ezshop-sk-card-body">
							<div class="ezshop-sk-line"></div>
							<div class="ezshop-sk-line sm"></div>
							<div class="ezshop-sk-line" style="width:50%;margin-top:10px"></div>
						</div>
					</div>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}, 5 );

/* اگر تم wp_body_open نداشت */
add_action( 'wp_footer', function () {
	if ( ! function_exists( 'ez_is_shop_context' ) || ! ez_is_shop_context() ) {
		return;
	}
	?>
	<script>
	(function(){
		if(document.querySelector('.ezshop-skeleton')) return;
		var s=document.createElement('div');
		s.className='ezshop-skeleton';
		s.setAttribute('aria-hidden','true');
		s.innerHTML='<div class="ezshop-sk-line lg" style="margin-bottom:16px"></div><div class="ezshop-sk-row"><div class="ezshop-sk-main"><div class="ezshop-sk-tabs"><div class="ezshop-sk-tab"></div><div class="ezshop-sk-tab"></div><div class="ezshop-sk-tab"></div></div><div class="ezshop-sk-grid"><div class="ezshop-sk-card"><div class="ezshop-sk-thumb"></div><div class="ezshop-sk-card-body"><div class="ezshop-sk-line"></div><div class="ezshop-sk-line sm"></div></div></div><div class="ezshop-sk-card"><div class="ezshop-sk-thumb"></div><div class="ezshop-sk-card-body"><div class="ezshop-sk-line"></div><div class="ezshop-sk-line sm"></div></div></div><div class="ezshop-sk-card"><div class="ezshop-sk-thumb"></div><div class="ezshop-sk-card-body"><div class="ezshop-sk-line"></div><div class="ezshop-sk-line sm"></div></div></div><div class="ezshop-sk-card"><div class="ezshop-sk-thumb"></div><div class="ezshop-sk-card-body"><div class="ezshop-sk-line"></div><div class="ezshop-sk-line sm"></div></div></div></div></div></div>';
		var main=document.querySelector('.wd-content-area')||document.querySelector('main')||document.body;
		if(main) main.insertBefore(s, main.firstChild);
	})();
	</script>
	<?php
}, 1 );

/* ============================================================
 * ۲. تزریق قالب‌ها
 * ============================================================ */
add_action( 'wp_footer', 'ezshop_inject_templates', 4 );
function ezshop_inject_templates() {
	if ( ! ez_is_shop_context() ) return;

	$term_id = 0;
	if ( is_product_category() || is_product_tag() ) {
		$t = get_queried_object();
		if ( $t && isset( $t->term_id ) ) $term_id = (int) $t->term_id;
	}

	$cat_label = $term_id ? 'زیر‌دسته‌ها' : 'دسته‌بندی';
	$data = array(
		'ajax'      => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'ezshop_nonce' ),
		'wa'        => '98919842069',
		'termId'    => $term_id,
		'isShop'    => ( function_exists( 'is_shop' ) && is_shop() && ! is_product_category() ),
		'cats'      => ezshop_get_cats_data( $term_id ),
		'brands'    => ezshop_get_brands_data(),
		'price'     => ezshop_get_price_range(),
		'i18n'      => array(
			'items'     => 'کالا',
			'loading'   => 'در حال بارگذاری...',
			'noRes'     => 'محصولی با این فیلترها پیدا نشد.',
			'catLabel'  => $cat_label,
		),
	);
	?>
	<script id="ezshop-data" type="application/json"><?php echo wp_json_encode( $data ); ?></script>

	<template id="ezshop-side-tpl">
		<div class="ezshop-side-head">
			<span class="ezshop-side-title">فیلترها</span>
			<button type="button" class="ezshop-clear" data-ezshop-clear>حذف همه</button>
			<button type="button" class="ezshop-side-close" data-ezshop-close aria-label="بستن">
				<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
		</div>

		<div class="ezshop-chips" data-ezshop-chips></div>

		<div class="ezshop-group is-open" data-group="cat">
			<button type="button" class="ezshop-group-head" data-ezshop-toggle>
				<span>دسته‌بندی</span>
				<svg viewBox="0 0 24 24" width="16" height="16"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
			<div class="ezshop-group-body" data-body="cat"></div>
		</div>

		<div class="ezshop-group is-open" data-group="price">
			<button type="button" class="ezshop-group-head" data-ezshop-toggle>
				<span>محدوده قیمت (تومان)</span>
				<svg viewBox="0 0 24 24" width="16" height="16"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
			<div class="ezshop-group-body">
				<div class="ezshop-price">
					<div class="ezshop-price-track" data-track>
						<div class="ezshop-price-fill" data-fill></div>
						<input type="range" data-range-min min="0" max="0" value="0" step="1">
						<input type="range" data-range-max min="0" max="0" value="0" step="1">
					</div>
					<div class="ezshop-price-inputs">
						<label><span>از</span><input type="text" inputmode="numeric" data-input-min></label>
						<label><span>تا</span><input type="text" inputmode="numeric" data-input-max></label>
					</div>
				</div>
			</div>
		</div>

		<div class="ezshop-group" data-group="brand">
			<button type="button" class="ezshop-group-head" data-ezshop-toggle>
				<span>برند</span>
				<svg viewBox="0 0 24 24" width="16" height="16"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
			<div class="ezshop-group-body" data-body="brand"></div>
		</div>

		<div class="ezshop-group is-open" data-group="stock">
			<button type="button" class="ezshop-group-head" data-ezshop-toggle>
				<span>وضعیت موجودی</span>
				<svg viewBox="0 0 24 24" width="16" height="16"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
			<div class="ezshop-group-body">
				<label class="ezshop-switch">
					<input type="checkbox" data-ezshop-filter="in-stock">
					<span class="ezshop-switch-track"></span>
					<span class="ezshop-switch-label">فقط کالاهای موجود</span>
				</label>
				<label class="ezshop-switch">
					<input type="checkbox" data-ezshop-filter="on-sale">
					<span class="ezshop-switch-track"></span>
					<span class="ezshop-switch-label">فقط تخفیف‌دار</span>
				</label>
			</div>
		</div>

		<div class="ezshop-apply-wrap">
			<button type="button" class="ezshop-apply" data-ezshop-apply>
				<span class="ezshop-apply-txt">اعمال فیلتر</span>
				<span class="ezshop-apply-count" data-ezshop-apply-count hidden>۰</span>
			</button>
		</div>
	</template>

	<template id="ezshop-tabs-tpl">
		<div class="ezshop-tabs" role="tablist">
			<button type="button" class="ezshop-tab is-active" data-sort="date-desc">جدیدترین</button>
			<button type="button" class="ezshop-tab" data-sort="date-asc">قدیمی‌ترین</button>
			<button type="button" class="ezshop-tab" data-sort="popularity">پرفروش‌ترین</button>
			<button type="button" class="ezshop-tab" data-sort="views">پربازدیدترین</button>
			<button type="button" class="ezshop-tab" data-sort="price-asc">ارزان‌ترین</button>
			<button type="button" class="ezshop-tab" data-sort="price-desc">گران‌ترین</button>
		</div>
	</template>

	<div class="ezshop-backdrop" data-ezshop-backdrop hidden></div>

	<a href="https://wa.me/98919842069?text=<?php echo rawurlencode( 'سلام، برای انتخاب محصول مشاوره می‌خواهم.' ); ?>"
	   class="ezshop-wa" target="_blank" rel="noopener nofollow" aria-label="مشاوره واتساپ">
		<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true">
			<path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 2c-5.523 0-10 4.477-10 10 0 1.775.464 3.44 1.275 4.885L2 22l5.246-1.373a9.951 9.951 0 004.804 1.223c5.523 0 10-4.477 10-10s-4.477-10-10-10z"/>
		</svg>
		<span class="ezshop-wa-pulse" aria-hidden="true"></span>
	</a>
	<?php
}

/* ============================================================
 * ۳. توابع داده
 * ============================================================ */
function ezshop_get_cats_data( $parent_id = 0 ) {
	$parent_id = absint( $parent_id );
	$args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => $parent_id,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);
	$cats = get_terms( $args );
	$out  = array();
	if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
		foreach ( $cats as $t ) {
			$out[] = array(
				'id'    => (int) $t->term_id,
				'name'  => $t->name,
				'count' => (int) $t->count,
			);
		}
		return $out;
	}
	// اگر زیر‌دسته نداشت و روی یک دسته هستیم: خالی برگردان (نه کل فروشگاه)
	return $out;
}

function ezshop_get_brands_data() {
	$tax = apply_filters( 'ezshop_brand_taxonomy', 'product_brand' );
	if ( ! taxonomy_exists( $tax ) ) return array();
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
	$out = array();
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$out[] = array( 'id' => (int) $t->term_id, 'name' => $t->name, 'count' => (int) $t->count );
		}
	}
	return $out;
}

function ezshop_get_price_range() {
	global $wpdb;
	$row = $wpdb->get_row(
		"SELECT MIN(CAST(meta_value AS UNSIGNED)) AS min_price,
		        MAX(CAST(meta_value AS UNSIGNED)) AS max_price
		 FROM {$wpdb->postmeta}
		 WHERE meta_key = '_price' AND meta_value != ''"
	);
	return array(
		'min' => isset( $row->min_price ) ? (int) $row->min_price : 0,
		'max' => isset( $row->max_price ) ? (int) $row->max_price : 0,
	);
}

/* ============================================================
 * ۴. AJAX
 * ============================================================ */
add_action( 'wp_ajax_ezshop_filter',        'ezshop_ajax_filter' );
add_action( 'wp_ajax_nopriv_ezshop_filter', 'ezshop_ajax_filter' );

function ezshop_ajax_filter() {
	check_ajax_referer( 'ezshop_nonce', 'nonce' );

	$paged    = max( 1, (int) ( $_POST['paged'] ?? 1 ) );
	$per_page = max( 4, min( 48, (int) ( $_POST['per_page'] ?? 12 ) ) );
	$sort     = sanitize_key( $_POST['sort'] ?? 'date-desc' );
	$term_id  = (int) ( $_POST['term_id'] ?? 0 );
	$cats     = array_filter( array_map( 'intval', (array) ( $_POST['cats'] ?? array() ) ) );
	$brands   = array_filter( array_map( 'intval', (array) ( $_POST['brands'] ?? array() ) ) );
	$min      = (int) ( $_POST['min'] ?? 0 );
	$max      = (int) ( $_POST['max'] ?? 0 );
	$in_stock = ! empty( $_POST['in_stock'] );
	$on_sale  = ! empty( $_POST['on_sale'] );

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'tax_query'      => array( 'relation' => 'AND' ),
		'meta_query'     => array( 'relation' => 'AND' ),
	);

	switch ( $sort ) {
		case 'date-asc':    $args['orderby']='date';          $args['order']='ASC'; break;
		case 'popularity':  $args['meta_key']='total_sales';  $args['orderby']='meta_value_num'; $args['order']='DESC'; break;
		case 'views':
			$args['meta_key'] = apply_filters( 'ezshop_views_meta_key', '_ezlens_views' );
			$args['orderby'] = 'meta_value_num'; $args['order']='DESC'; break;
		case 'price-asc':   $args['meta_key']='_price';       $args['orderby']='meta_value_num'; $args['order']='ASC'; break;
		case 'price-desc':  $args['meta_key']='_price';       $args['orderby']='meta_value_num'; $args['order']='DESC'; break;
		default:            $args['orderby']='date';          $args['order']='DESC';
	}

	/*
	 * منطق دسته:
	 * - اگر کاربر زیر‌دسته/دسته انتخاب کرده → فقط همان‌ها (با زیر‌مجموعه‌ها)
	 * - وگرنه اگر داخل آرشیو دسته هستیم → همان دسته فعلی (با زیر‌مجموعه‌ها)
	 * - هرگز term_id و cats را با AND همزمان اعمال نکن (محصول دو دسته نمی‌شود)
	 */
	if ( ! empty( $cats ) ) {
		$args['tax_query'][] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => $cats,
			'operator'         => 'IN',
			'include_children' => true,
		);
	} elseif ( $term_id ) {
		$args['tax_query'][] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => array( $term_id ),
			'operator'         => 'IN',
			'include_children' => true,
		);
	}
	if ( $brands ) {
		$bt = apply_filters( 'ezshop_brand_taxonomy', 'product_brand' );
		if ( taxonomy_exists( $bt ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $bt,
				'field'    => 'term_id',
				'terms'    => $brands,
				'operator' => 'IN',
			);
		}
	}
	if ( $min || $max ) {
		$pq = array( 'relation' => 'AND' );
		if ( $min ) $pq[] = array( 'key'=>'_price','value'=>$min,'compare'=>'>=','type'=>'NUMERIC' );
		if ( $max ) $pq[] = array( 'key'=>'_price','value'=>$max,'compare'=>'<=','type'=>'NUMERIC' );
		$args['meta_query'][] = $pq;
	}
	if ( $in_stock ) $args['meta_query'][] = array( 'key'=>'_stock_status','value'=>'instock','compare'=>'=' );
	if ( $on_sale )  $args['post__in'] = array_merge( array(0), wc_get_product_ids_on_sale() );

	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) {
		woocommerce_product_loop_start();
		while ( $query->have_posts() ) { $query->the_post(); wc_get_template_part( 'content', 'product' ); }
		woocommerce_product_loop_end();
	} else {
		do_action( 'woocommerce_no_products_found' );
	}
	wp_reset_postdata();
	$html = ob_get_clean();

	ob_start();
	woocommerce_pagination();
	$pagination = ob_get_clean();

	wp_send_json_success( array(
		'html'       => $html,
		'pagination' => $pagination,
		'count'      => (int) $query->found_posts,
		'page'       => $paged,
		'max'        => (int) $query->max_num_pages,
	) );
}


/* پاپ‌آپ سبد — سراسری ووکامرس */
add_action( 'wp_head', 'ezshop_cart_popup_css', 46 );
function ezshop_cart_popup_css() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}
	// تقریباً همه صفحات فرانت که ممکن است add-to-cart بزنند
	?>
<style id="ezshop-cart-popup">
.mfp-bg{
	background:rgba(15,23,42,.45) !important;
	backdrop-filter:blur(8px) !important;
	-webkit-backdrop-filter:blur(8px) !important;
}
.wd-popup.wd-popup-added-cart{
	background:#fff !important;
	border-radius:20px !important;
	border:1px solid #e8edf5 !important;
	box-shadow:0 24px 64px rgba(3,31,138,.14),0 8px 24px rgba(15,23,42,.08) !important;
	padding:0 !important;
	max-width:400px !important;
	width:calc(100% - 32px) !important;
	overflow:hidden !important;
	animation:ezshopPopIn .35s cubic-bezier(.34,1.4,.64,1) !important;
}
@keyframes ezshopPopIn{
	from{opacity:0;transform:scale(.92) translateY(16px)}
	to{opacity:1;transform:scale(1) translateY(0)}
}
.wd-popup.wd-popup-added-cart::before{
	content:"";display:block;height:4px;
	background:linear-gradient(90deg,#031f8a,#0a3d91,#10b981);
}
.wd-popup-wrap > .wd-popup-close{
	position:absolute !important;top:12px !important;left:12px !important;right:auto !important;
	width:36px !important;height:36px !important;border-radius:50% !important;
	background:#f1f5f9 !important;border:1px solid #e2e8f0 !important;z-index:5 !important;
	display:flex !important;align-items:center !important;justify-content:center !important;
}
.wd-popup-wrap > .wd-popup-close:hover{background:#fee2e2 !important;transform:rotate(90deg) !important}
.wd-popup-close .wd-action-text{display:none !important}
.wd-popup-close .wd-action-icon{
	width:14px !important;height:14px !important;font-size:0 !important;
	background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round'><path d='M6 6l12 12M18 6L6 18'/></svg>") center/contain no-repeat !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart{
	padding:36px 28px 28px !important;text-align:center !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart::before{
	content:"";display:block;width:64px;height:64px;margin:0 auto 16px;border-radius:50%;
	background:#ecfdf5 url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23059669' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M20 6L9 17l-5-5'/></svg>") center/28px no-repeat;
}
.wd-popup.wd-popup-added-cart .added-to-cart h3{
	font-size:0 !important;margin:0 0 20px !important;line-height:1.5 !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart h3::before{
	content:"به سبد اضافه شد";display:block;font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:6px;
}
.wd-popup.wd-popup-added-cart .added-to-cart h3::after{
	content:"می‌توانید خرید را ادامه دهید یا سبد را ببینید.";display:block;font-size:.84rem;font-weight:500;color:#64748b;line-height:1.7;
}
.wd-popup.wd-popup-added-cart .added-to-cart .btn{
	display:inline-flex !important;align-items:center !important;justify-content:center !important;
	min-height:46px !important;padding:12px 18px !important;margin:4px !important;
	border-radius:12px !important;font-size:.88rem !important;font-weight:700 !important;
	text-decoration:none !important;border:1.5px solid transparent !important;
	transition:transform .15s,box-shadow .15s !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart .btn-default,
.wd-popup.wd-popup-added-cart .added-to-cart .close-popup{
	background:#fff !important;color:#0f172a !important;border-color:#e2e8f0 !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart .btn-default:hover,
.wd-popup.wd-popup-added-cart .added-to-cart .close-popup:hover{
	background:#f8fafc !important;transform:translateY(-1px) !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart .btn-accent,
.wd-popup.wd-popup-added-cart .added-to-cart .view-cart{
	background:linear-gradient(135deg,#031f8a 0%,#0a3d91 50%,#011663 100%) !important;
	color:#fff !important;border-color:transparent !important;
	box-shadow:0 8px 20px rgba(3,31,138,.22) !important;
}
.wd-popup.wd-popup-added-cart .added-to-cart .btn-accent:hover,
.wd-popup.wd-popup-added-cart .added-to-cart .view-cart:hover{
	transform:translateY(-1px) !important;color:#fff !important;
	box-shadow:0 12px 28px rgba(3,31,138,.3) !important;
}
</style>
	<?php
}


/* ============================================================
 * ۵. CSS
 * ============================================================ */
add_action( 'wp_head', 'ezshop_enhance_css', 45 );
function ezshop_enhance_css() {
	if ( ! ez_is_shop_context() ) return;
	?>
	<style id="ezshop-enhance-css">
	:root{
		--ezs-blue:#031f8a; --ezs-blue-d:#011663;
		--ezs-cta:#ff6b6d; --ezs-bg:#f5f7fb;
		--ezs-card:#ffffff; --ezs-text:#0f172a;
		--ezs-muted:#64748b; --ezs-border:#e8edf5;
		--ezs-soft:#f8fafc;
		--ezs-radius:16px;
	}

	/* ==================================================
	   چیدمان کلی
	   ================================================== */
	body.woocommerce-shop .wd-content-area,
	body.tax-product_cat .wd-content-area,
	body.tax-product_tag .wd-content-area{
		display:flex !important;
		flex-wrap:wrap !important;
		gap:16px !important;
		align-items:flex-start !important;
		max-width:1500px !important;
		margin-left:auto !important;
		margin-right:auto !important;
	}

	body.woocommerce-shop .wd-content-area > .ezshop-crumb-row,
	body.tax-product_cat .wd-content-area > .ezshop-crumb-row,
	body.tax-product_tag .wd-content-area > .ezshop-crumb-row{
		flex:1 1 100% !important;
		width:100% !important;
		order:0 !important;
		display:flex;
		justify-content:space-between;
		align-items:center;
		gap:12px;
		flex-wrap:wrap;
		background:var(--ezs-card);
		border:1px solid var(--ezs-border);
		border-radius:12px;
		padding:10px 16px;
		box-shadow:0 2px 8px -6px rgba(3,31,138,.08);
	}

	body.woocommerce-shop .wd-content-area > .shop-loop-head,
	body.tax-product_cat .wd-content-area > .shop-loop-head,
	body.tax-product_tag .wd-content-area > .shop-loop-head{
		flex:1 1 100% !important;
		width:100% !important;
		order:1 !important;
	}

	body.woocommerce-shop .wd-content-area > .ezshop-sidebar,
	body.tax-product_cat .wd-content-area > .ezshop-sidebar,
	body.tax-product_tag .wd-content-area > .ezshop-sidebar{
		flex:0 0 280px !important;
		order:2 !important;
		position:sticky !important;
		top:20px !important;
		background:var(--ezs-card) !important;
		border:1px solid var(--ezs-border) !important;
		border-radius:var(--ezs-radius) !important;
		padding:16px !important;
		box-shadow:0 4px 16px -8px rgba(3,31,138,.10) !important;
		max-height:calc(100vh - 40px) !important;
		overflow-y:auto !important;
	}

	body.woocommerce-shop .wd-content-area > .wd-products-element,
	body.tax-product_cat .wd-content-area > .wd-products-element,
	body.tax-product_tag .wd-content-area > .wd-products-element{
		flex:1 1 0 !important;
		min-width:0 !important;
		order:3 !important;
	}

	body.woocommerce .filters-area{display:none !important}
	body.woocommerce .wd-show-sidebar-btn{display:none !important}
	body.woocommerce .wd-filter-buttons{display:none !important}

	/* ==================================================
	   Breadcrumb
	   ================================================== */
	.ezshop-crumb-row .wd-breadcrumbs{
		display:flex !important;
		align-items:center;
		gap:6px;
		font-size:.82rem !important;
		color:var(--ezs-muted) !important;
		margin:0 !important;
	}
	.ezshop-crumb-row .wd-breadcrumbs a{
		color:var(--ezs-blue) !important;
		text-decoration:none !important;
		font-weight:600;
	}
	.ezshop-crumb-row .wd-breadcrumbs a:hover{ color:var(--ezs-blue-d) !important; }

	.ezshop-crumb-row .ezshop-count-badge{
		font-size:.78rem;
		color:var(--ezs-blue);
		background:rgba(3,31,138,.07);
		padding:4px 10px;
		border-radius:999px;
		font-weight:600;
		margin:0;
		white-space:nowrap;
	}

	/* ==================================================
	   پنل (shop-loop-head)
	   ================================================== */
	body.woocommerce .shop-loop-head{
		background:var(--ezs-card) !important;
		border:1px solid var(--ezs-border) !important;
		border-radius:var(--ezs-radius) !important;
		padding:12px 16px !important;
		margin-bottom:0 !important;
		box-shadow:0 4px 16px -8px rgba(3,31,138,.08) !important;
		display:flex !important;
		flex-direction:column !important;
		gap:12px !important;
		overflow:hidden !important;
	}

	body.woocommerce .shop-loop-head .ezshop-toolbar{
		display:flex;
		align-items:center;
		justify-content:space-between;
		gap:12px;
		flex-wrap:wrap;
	}

	body.woocommerce .shop-loop-head .ezshop-tabs{
		display:flex !important;
		gap:8px !important;
		overflow-x:auto !important;
		overflow-y:hidden !important;
		scrollbar-width:none;
		-ms-overflow-style:none;
		padding-bottom:2px;
		max-width:100% !important;
		width:100% !important;
		box-sizing:border-box;
		-webkit-overflow-scrolling:touch;
	}
	body.woocommerce .shop-loop-head .ezshop-tabs::-webkit-scrollbar{display:none}
	.ezshop-tab{
		white-space:nowrap;
		flex:0 0 auto;
		padding:9px 16px;
		border-radius:10px;
		font-family:IRANYekan,Tahoma,sans-serif;
		font-size:.82rem;font-weight:600;
		color:var(--ezs-muted);
		background:var(--ezs-soft);
		border:1px solid transparent;
		cursor:pointer;
		transition:all .2s ease;
	}
	.ezshop-tab:hover{color:var(--ezs-blue);background:rgba(3,31,138,.06)}
	.ezshop-tab.is-active{
		color:#fff;
		background:var(--ezs-blue);
		box-shadow:0 6px 16px -8px rgba(3,31,138,.45);
	}

	/* ==================================================
	   سایدبار سفارشی
	   ================================================== */
	.ezshop-side-head{
		display:flex;align-items:center;justify-content:space-between;
		padding-bottom:12px;border-bottom:1px solid var(--ezs-border);
		margin-bottom:12px;font-weight:800;color:var(--ezs-blue);font-size:.92rem;
		font-family:IRANYekan,Tahoma,sans-serif;
	}
	.ezshop-clear{
		font-size:.74rem;color:var(--ezs-cta);font-weight:600;
		padding:5px 9px;border-radius:8px;
		background:rgba(255,107,109,.08);
		border:none;cursor:pointer;font-family:inherit;
	}
	.ezshop-clear:hover{ background:rgba(255,107,109,.15); }
	.ezshop-side-close{display:none;padding:4px;color:var(--ezs-muted);background:none;border:none;cursor:pointer}

	.ezshop-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px}
	.ezshop-chips:empty{display:none}
	.ezshop-chip{
		display:inline-flex;align-items:center;gap:6px;
		background:rgba(3,31,138,.07);color:var(--ezs-blue);
		padding:5px 9px;border-radius:8px;font-size:.74rem;font-weight:600;
		font-family:IRANYekan,Tahoma,sans-serif;
	}
	.ezshop-chip button{
		color:var(--ezs-blue);padding:0 0 0 4px;line-height:1;
		background:none;border:none;cursor:pointer;font-size:1rem;
	}

	.ezshop-group{border-bottom:1px solid var(--ezs-border);padding:6px 0}
	.ezshop-group:last-child{border-bottom:none}
	.ezshop-group-head{
		width:100%;display:flex;justify-content:space-between;align-items:center;
		padding:10px 4px;font-weight:700;font-size:.85rem;color:var(--ezs-text);
		background:none;border:none;cursor:pointer;
		font-family:IRANYekan,Tahoma,sans-serif;
	}
	.ezshop-group-head svg{transition:transform .2s;color:var(--ezs-muted)}
	.ezshop-group.is-open .ezshop-group-head svg{transform:rotate(180deg)}
	.ezshop-group-body{display:none;padding:4px 4px 12px}
	.ezshop-group.is-open .ezshop-group-body{display:block}

	.ezshop-list{display:flex;flex-direction:column;gap:4px;max-height:220px;overflow-y:auto}
	.ezshop-list label{
		display:flex;align-items:center;gap:10px;padding:6px 8px;
		border-radius:8px;font-size:.82rem;cursor:pointer;
		transition:background .15s;
		font-family:IRANYekan,Tahoma,sans-serif;
	}
	.ezshop-list label:hover{background:var(--ezs-soft)}
	.ezshop-list input{accent-color:var(--ezs-blue)}
	.ezshop-list .count{
		margin-inline-start:auto;font-size:.7rem;color:var(--ezs-muted);
		background:#f1f5f9;padding:1px 7px;border-radius:999px;
	}

	.ezshop-price{padding-top:6px}
	.ezshop-price-track{position:relative;height:5px;background:#e2e8f0;border-radius:4px;margin:16px 8px 22px}
	.ezshop-price-fill{position:absolute;top:0;bottom:0;background:var(--ezs-blue);border-radius:4px}
	.ezshop-price-track input[type=range]{
		position:absolute;top:-7px;left:0;width:100%;height:20px;
		-webkit-appearance:none;appearance:none;background:none;
		pointer-events:none;margin:0;padding:0;
	}
	.ezshop-price-track input[type=range]::-webkit-slider-thumb{
		-webkit-appearance:none;pointer-events:auto;
		width:18px;height:18px;border-radius:50%;background:#fff;
		border:2px solid var(--ezs-blue);
		box-shadow:0 2px 6px rgba(3,31,138,.2);cursor:pointer;
	}
	.ezshop-price-track input[type=range]::-moz-range-thumb{
		pointer-events:auto;width:18px;height:18px;border-radius:50%;
		background:#fff;border:2px solid var(--ezs-blue);cursor:pointer;
	}
	.ezshop-price-inputs{display:grid;grid-template-columns:1fr 1fr;gap:8px}
	.ezshop-price-inputs label{
		display:flex;flex-direction:column;gap:4px;
		font-size:.72rem;color:var(--ezs-muted);
		font-family:IRANYekan,Tahoma,sans-serif;
	}
	.ezshop-price-inputs input{
		width:100%;padding:8px 10px;border:1px solid var(--ezs-border);
		border-radius:8px;font-family:IRANYekan,Tahoma,sans-serif;
		font-size:.82rem;background:var(--ezs-soft);color:var(--ezs-text);
		direction:rtl;
		text-align:right;
	}
	.ezshop-price-inputs input:focus{outline:none;border-color:var(--ezs-blue);background:#fff}

	.ezshop-switch{
		display:flex;align-items:center;gap:10px;padding:8px 4px;
		cursor:pointer;font-size:.82rem;
		font-family:IRANYekan,Tahoma,sans-serif;
	}
	.ezshop-switch input{display:none}
	.ezshop-switch-track{
		width:36px;height:20px;background:#cbd5e1;border-radius:999px;
		position:relative;transition:background .2s;flex-shrink:0;
	}
	.ezshop-switch-track::after{
		content:"";position:absolute;top:2px;right:2px;
		width:16px;height:16px;border-radius:50%;background:#fff;
		transition:transform .2s;
	}
	.ezshop-switch input:checked + .ezshop-switch-track{background:var(--ezs-blue)}
	.ezshop-switch input:checked + .ezshop-switch-track::after{transform:translateX(-16px)}
	.ezshop-apply-wrap{
		margin-top:14px;padding-top:12px;border-top:1px solid var(--ezs-border);
		position:sticky;bottom:0;z-index:2;
		background:linear-gradient(180deg,rgba(255,255,255,0) 0%,#fff 28%);
		padding-bottom:2px;
	}
	.ezshop-apply{
		display:flex;align-items:center;justify-content:center;gap:10px;
		width:100%;min-height:50px;padding:12px 18px;border-radius:14px;cursor:pointer;
		font-family:inherit;font-size:.92rem;font-weight:800;
		color:var(--ezs-blue);
		border:1.5px solid rgba(3,31,138,.18);
		background:linear-gradient(135deg,#ffffff 0%,#f3f6ff 45%,#e8eefc 100%);
		box-shadow:0 6px 18px rgba(3,31,138,.10), inset 0 1px 0 rgba(255,255,255,.9);
		transition:transform .15s,box-shadow .15s,opacity .15s,border-color .15s,color .15s;
	}
	.ezshop-apply:hover{
		transform:translateY(-1px);
		color:#fff;
		border-color:transparent;
		background:linear-gradient(135deg,#031f8a 0%,#0a3d91 50%,#011663 100%);
		box-shadow:0 10px 28px rgba(3,31,138,.28);
	}
	.ezshop-apply:active{transform:translateY(0)}
	.ezshop-apply:disabled{opacity:.65;cursor:wait;transform:none}
	.ezshop-apply-count{
		display:inline-flex;align-items:center;justify-content:center;
		min-width:22px;height:22px;padding:0 7px;border-radius:999px;
		background:rgba(3,31,138,.10);color:var(--ezs-blue);font-size:12px;font-weight:800;
		transition:background .15s,color .15s;
	}
	.ezshop-apply:hover .ezshop-apply-count{
		background:rgba(255,255,255,.22);color:#fff;
	}

	.ezshop-filter-toggle{
		display:none;align-items:center;gap:8px;justify-content:center;
		padding:11px 16px;border-radius:12px;
		font-weight:700;font-size:.84rem;
		color:#fff;
		background:var(--ezs-blue);
		border:1px solid var(--ezs-blue);
		cursor:pointer;
		font-family:IRANYekan,Tahoma,sans-serif;
		box-shadow:0 6px 16px -10px rgba(3,31,138,.6);
		transition:all .2s;
	}
	.ezshop-filter-toggle:hover{ background:var(--ezs-blue-d); }
	.ezshop-filter-badge{
		background:var(--ezs-cta);color:#fff;border-radius:999px;
		min-width:18px;height:18px;
		display:inline-flex;align-items:center;justify-content:center;
		font-size:.68rem;padding:0 6px;
	}

	.ezshop-wa{
		position:fixed;inset-inline-end:20px;bottom:20px;z-index:9999;
		width:56px;height:56px;border-radius:50%;
		display:inline-flex;align-items:center;justify-content:center;
		background:#25d366;color:#fff;
		box-shadow:0 10px 25px -8px rgba(37,211,102,.6);
		transition:transform .2s;text-decoration:none;
	}
	.ezshop-wa:hover{transform:scale(1.06);color:#fff}
	.ezshop-wa-pulse{
		position:absolute;inset:0;border-radius:50%;
		background:#25d366;opacity:.35;
		animation:ezshop-pulse 2s infinite;z-index:-1;
	}
	@keyframes ezshop-pulse{
		0%{transform:scale(1);opacity:.35}
		70%{transform:scale(1.6);opacity:0}
		100%{transform:scale(1.6);opacity:0}
	}

	body.ezshop-loading .wd-products-element{opacity:.45;pointer-events:none;transition:opacity .2s}

	/* ==================================================
	   کارت محصول
	   ================================================== */
	body.woocommerce .wd-product .wd-product-card-bg,
	body.woocommerce .wd-product .wd-product-card-hover,
	body.woocommerce .wd-product .hover-content-wrap,
	body.woocommerce .wd-product .content-product-imagin {
		display:none !important;
	}

	body.woocommerce .wd-product .wd-product-cats,
	body.woocommerce .wd-product .wd-product-brands-links {
		display:none !important;
	}

	body.woocommerce .wd-product,
	body.woocommerce .product-grid-item,
	body.woocommerce ul.products li.product {
		background:#ffffff !important;
		border:1px solid var(--ezs-border) !important;
		border-radius:14px !important;
		overflow:hidden !important;
		padding:0 !important;
		box-shadow:0 1px 2px rgba(15,23,42,.03) !important;
		transition:border-color .25s ease, box-shadow .25s ease, transform .25s ease !important;
		display:flex !important;
		flex-direction:column !important;
		height:100% !important;
	}

	body.woocommerce .wd-product:hover,
	body.woocommerce .product-grid-item:hover,
	body.woocommerce ul.products li.product:hover {
		border-color:rgba(3,31,138,.22) !important;
		box-shadow:0 12px 32px -14px rgba(3,31,138,.18) !important;
		transform:translateY(-3px) !important;
	}

	body.woocommerce .wd-product .product-element-top,
	body.woocommerce .wd-product .wd-product-thumb,
	body.woocommerce .wd-product .product-image-link {
		background:var(--ezs-soft) !important;
		padding:20px !important;
		border-radius:0 !important;
		overflow:hidden !important;
		display:flex !important;
		align-items:center !important;
		justify-content:center !important;
	}

	body.woocommerce .wd-product .product-element-top img,
	body.woocommerce .wd-product .wd-product-thumb img {
		width:100% !important;
		height:100% !important;
		max-height:240px !important;
		object-fit:contain !important;
		border-radius:0 !important;
		transition:transform .35s ease !important;
	}

	body.woocommerce .wd-product:hover .product-element-top img {
		transform:scale(1.05) !important;
	}

	body.woocommerce .wd-product .wd-buttons {
		opacity:0 !important;
		transition:opacity .2s ease !important;
	}
	body.woocommerce .wd-product:hover .wd-buttons {
		opacity:1 !important;
	}

	body.woocommerce .wd-product .product-element-bottom {
		padding:14px 16px 16px !important;
		display:flex !important;
		flex-direction:column !important;
		gap:10px !important;
		flex:1 1 auto !important;
		background:#ffffff !important;
		border-top:1px solid #f1f5f9 !important;
	}

	body.woocommerce .wd-product .wd-entities-title {
		margin:0 !important;
		padding:0 !important;
		font-size:13px !important;
		font-weight:600 !important;
		line-height:1.7 !important;
		min-height:44px !important;
		letter-spacing:0 !important;
	}

	body.woocommerce .wd-product .wd-entities-title a {
		color:var(--ezs-text) !important;
		text-decoration:none !important;
		font-weight:600 !important;
		display:-webkit-box !important;
		-webkit-line-clamp:2 !important;
		-webkit-box-orient:vertical !important;
		overflow:hidden !important;
		transition:color .2s !important;
	}
	body.woocommerce .wd-product .wd-entities-title a:hover {
		color:var(--ezs-blue) !important;
	}

	body.woocommerce .wd-product .wrap-price {
		margin:0 !important;
		padding:0 !important;
		display:flex !important;
		align-items:center !important;
		gap:6px !important;
		flex-wrap:wrap !important;
	}

	body.woocommerce .wd-product .price {
		color:var(--ezs-blue) !important;
		font-size:15px !important;
		font-weight:700 !important;
		font-family:IRANYekan,Tahoma,sans-serif !important;
		line-height:1.4 !important;
		margin:0 !important;
		display:block !important;
	}
	body.woocommerce .wd-product .price del {
		color:#94a3b8 !important;
		font-size:12px !important;
		font-weight:500 !important;
		margin-inline-end:4px !important;
	}
	body.woocommerce .wd-product .price ins {
		text-decoration:none !important;
		color:var(--ezs-cta) !important;
		font-weight:700 !important;
	}
	body.woocommerce .wd-product .price .woocommerce-Price-currencySymbol {
		font-size:11px !important;
		color:var(--ezs-muted) !important;
		font-weight:500 !important;
		margin-inline-end:2px !important;
	}

	/* ==================================================
	   ★ پنل خرید: تعداد بالا + دکمه پایین ★
	   ================================================== */
	body.woocommerce .wd-product .wd-add-btn,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace {
		margin-top:auto !important;
		margin-bottom:0 !important;
		padding:0 !important;
		display:flex !important;
		flex-direction:column !important;
		align-items:stretch !important;
		justify-content:flex-start !important;
		gap:6px !important;
		flex-wrap:nowrap !important;
		width:100% !important;
		height:auto !important;
		min-height:84px !important;
		max-height:none !important;
		background:transparent !important;
		border:none !important;
		border-radius:0 !important;
		overflow:visible !important;
		box-shadow:none !important;
		position:relative !important;
		top:auto !important;
		left:auto !important;
		right:auto !important;
		bottom:auto !important;
		transform:none !important;
		z-index:1 !important;
	}

	body.woocommerce .wd-product .wd-add-btn > .quantity,
	body.woocommerce .wd-product .wd-add-btn > a.button,
	body.woocommerce .wd-product .wd-add-btn > a.button.add_to_cart_button,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > a.button,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > .quantity {
		position:static !important;
		top:auto !important;
		left:auto !important;
		right:auto !important;
		bottom:auto !important;
		transform:none !important;
		float:none !important;
		visibility:visible !important;
		opacity:1 !important;
		z-index:auto !important;
		display:flex !important;
		margin:0 !important;
	}

	body.woocommerce .wd-product .wd-add-btn > .quantity {
		align-items:center !important;
		justify-content:space-between !important;
		background:var(--ezs-soft) !important;
		border:1px solid var(--ezs-border) !important;
		border-radius:10px !important;
		height:38px !important;
		min-height:38px !important;
		max-height:38px !important;
		padding:0 4px !important;
		flex:0 0 38px !important;
		overflow:hidden !important;
		width:100% !important;
		box-sizing:border-box !important;
		transition:border-color .2s, background .2s !important;
	}
	body.woocommerce .wd-product .wd-add-btn > .quantity:hover {
		border-color:rgba(3,31,138,.20) !important;
		background:#ffffff !important;
	}

	body.woocommerce .wd-product .wd-add-btn > .quantity .minus,
	body.woocommerce .wd-product .wd-add-btn > .quantity .plus {
		background:transparent !important;
		color:var(--ezs-blue) !important;
		border:none !important;
		height:100% !important;
		width:36px !important;
		min-width:36px !important;
		max-width:36px !important;
		font-size:16px !important;
		font-weight:700 !important;
		line-height:1 !important;
		display:inline-flex !important;
		align-items:center !important;
		justify-content:center !important;
		padding:0 !important;
		margin:0 !important;
		cursor:pointer !important;
		transition:background .15s, color .15s !important;
		box-shadow:none !important;
		border-radius:8px !important;
		text-shadow:none !important;
		position:static !important;
	}
	body.woocommerce .wd-product .wd-add-btn > .quantity .minus:hover,
	body.woocommerce .wd-product .wd-add-btn > .quantity .plus:hover {
		background:rgba(3,31,138,.09) !important;
		color:var(--ezs-blue-d) !important;
	}
	body.woocommerce .wd-product .wd-add-btn > .quantity .minus:active,
	body.woocommerce .wd-product .wd-add-btn > .quantity .plus:active {
		background:rgba(3,31,138,.15) !important;
	}

	body.woocommerce .wd-product .wd-add-btn > .quantity input.qty {
		background:transparent !important;
		border:none !important;
		color:var(--ezs-text) !important;
		font-family:IRANYekan,Tahoma,sans-serif !important;
		font-size:13px !important;
		font-weight:700 !important;
		height:100% !important;
		width:auto !important;
		min-width:0 !important;
		max-width:none !important;
		flex:1 1 auto !important;
		padding:0 !important;
		text-align:center !important;
		box-shadow:none !important;
		outline:none !important;
		border-radius:0 !important;
		-moz-appearance:textfield !important;
	}
	body.woocommerce .wd-product .wd-add-btn > .quantity input.qty::-webkit-outer-spin-button,
	body.woocommerce .wd-product .wd-add-btn > .quantity input.qty::-webkit-inner-spin-button {
		-webkit-appearance:none !important;
		margin:0 !important;
	}
	body.woocommerce .wd-product .wd-add-btn > .quantity input.qty:hover,
	body.woocommerce .wd-product .wd-add-btn > .quantity input.qty:focus {
		background:transparent !important;
		color:var(--ezs-text) !important;
		box-shadow:none !important;
		border:none !important;
	}

	body.woocommerce .wd-product .wd-add-btn > a.button,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > a.button,
	body.woocommerce .wd-product .wd-add-btn > a.button.add_to_cart_button {
		background:var(--ezs-blue) !important;
		color:#ffffff !important;
		border:none !important;
		border-radius:10px !important;
		font-family:IRANYekan,Tahoma,sans-serif !important;
		font-size:12.5px !important;
		font-weight:600 !important;
		padding:0 14px !important;
		height:40px !important;
		min-height:40px !important;
		max-height:40px !important;
		width:100% !important;
		min-width:0 !important;
		flex:0 0 40px !important;
		align-items:center !important;
		justify-content:center !important;
		transition:background .2s, box-shadow .2s, transform .1s !important;
		box-shadow:0 2px 8px -4px rgba(3,31,138,.4) !important;
		letter-spacing:0 !important;
		line-height:1 !important;
		text-decoration:none !important;
		white-space:nowrap !important;
		overflow:hidden !important;
		text-overflow:ellipsis !important;
		box-sizing:border-box !important;
	}
	body.woocommerce .wd-product .wd-add-btn > a.button:hover,
	body.woocommerce .wd-product .wd-add-btn > a.button.add_to_cart_button:hover {
		background:var(--ezs-blue-d) !important;
		color:#ffffff !important;
		box-shadow:0 4px 12px -4px rgba(3,31,138,.55) !important;
	}
	body.woocommerce .wd-product .wd-add-btn > a.button:active {
		transform:translateY(1px) !important;
	}

	/* ==================================================
	   ★ خنثی‌سازی استایل وودمارت روی span داخل دکمه ★
	   ================================================== */

	/* قانون وودمارت: .wd-add-btn-replace>a span { display:flex; ... min-height:inherit; }
	   این را کامل خنثی می‌کنیم */
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > a.button > span,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > a.button > span.wd-action-text,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > a.button > span.wd-action-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button > span,
	body.woocommerce .wd-product .wd-add-btn > a.button > span.wd-action-text,
	body.woocommerce .wd-product .wd-add-btn > a.button > span.wd-action-icon {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		min-height: 0 !important;
		height: auto !important;
		transition: none !important;
		transform: none !important;
	}

	/* ==================================================
	   ★ اصلاح هاور دکمه — جلوگیری از رفتار وودمارت ★
	   ================================================== */

	body.woocommerce .wd-product .wd-add-btn > a.button .wd-action-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button .wd-check-icon,
	body.woocommerce .wd-product:hover .wd-add-btn > a.button .wd-action-icon,
	body.woocommerce .wd-product:hover .wd-add-btn > a.button .wd-check-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button:hover .wd-action-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button:hover .wd-check-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button.add_to_cart_button .wd-action-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button.adding .wd-action-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button.added .wd-action-icon,
	body.woocommerce .wd-product .wd-add-btn > a.button.loading .wd-action-icon {
		display: none !important;
		visibility: hidden !important;
		opacity: 0 !important;
		width: 0 !important;
		height: 0 !important;
		overflow: hidden !important;
		position: absolute !important;
		left: -9999px !important;
	}

	body.woocommerce .wd-product .wd-add-btn > a.button .wd-action-text,
	body.woocommerce .wd-product:hover .wd-add-btn > a.button .wd-action-text,
	body.woocommerce .wd-product .wd-add-btn > a.button:hover .wd-action-text,
	body.woocommerce .wd-product .wd-add-btn > a.button.adding .wd-action-text,
	body.woocommerce .wd-product .wd-add-btn > a.button.added .wd-action-text,
	body.woocommerce .wd-product .wd-add-btn > a.button.loading .wd-action-text {
		display: inline-block !important;
		visibility: visible !important;
		opacity: 1 !important;
		position: static !important;
		max-width: none !important;
		width: auto !important;
		height: auto !important;
		overflow: visible !important;
		color: #ffffff !important;
		font-size: 12.5px !important;
		font-weight: 600 !important;
		line-height: 1 !important;
		transform: none !important;
		transition: none !important;
	}

	body.woocommerce .wd-product .wd-add-btn > a.button,
	body.woocommerce .wd-product:hover .wd-add-btn > a.button,
	body.woocommerce .wd-product .wd-add-btn > a.button.add_to_cart_button,
	body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace > a.button {
		background: var(--ezs-blue) !important;
		color: #ffffff !important;
		transition: background .2s ease, box-shadow .2s ease !important;
		transform: none !important;
	}

	body.woocommerce .wd-product .wd-add-btn > a.button:hover,
	body.woocommerce .wd-product .wd-add-btn > a.button:focus,
	body.woocommerce .wd-product .wd-add-btn > a.button.add_to_cart_button:hover {
		background: var(--ezs-blue-d) !important;
		color: #ffffff !important;
		box-shadow: 0 4px 12px -4px rgba(3,31,138,.55) !important;
		transform: none !important;
	}

	body.woocommerce .wd-product .wd-add-btn > a.button.loading,
	body.woocommerce .wd-product .wd-add-btn > a.button.adding,
	body.woocommerce .wd-product .wd-add-btn > a.button.added {
		background: var(--ezs-blue) !important;
		color: #ffffff !important;
		opacity: 1 !important;
		padding: 0 14px !important;
	}

	body.woocommerce .wd-product:hover .wd-add-btn,
	body.woocommerce .wd-product:hover .wd-add-btn.wd-add-btn-replace,
	body.woocommerce .wd-product.wd-hover-fw-button:hover .wd-add-btn,
	body.woocommerce .wd-product.wd-hover-fw-button:hover .wd-add-btn.wd-add-btn-replace {
		display: flex !important;
		flex-direction: column !important;
		gap: 6px !important;
		min-height: 84px !important;
		width: 100% !important;
		position: relative !important;
		transform: none !important;
	}

	body.woocommerce .wd-product:hover .wd-add-btn > .quantity {
		display: flex !important;
		width: 100% !important;
		opacity: 1 !important;
		visibility: visible !important;
		transform: none !important;
	}

	body.woocommerce .wd-product .wd-add-btn > a.button::before,
	body.woocommerce .wd-product .wd-add-btn > a.button::after {
		display: none !important;
		content: none !important;
	}

	@media (hover: none) {
		body.woocommerce .wd-product .wd-add-btn > a.button .wd-action-text {
			display: inline-block !important;
			font-size: 11.5px !important;
		}
	}

	/* بج تخفیف */
	body.woocommerce .wd-product span.onsale,
	body.woocommerce .wd-product .onsale {
		background:var(--ezs-cta) !important;
		color:#ffffff !important;
		font-family:IRANYekan,Tahoma,sans-serif !important;
		font-size:10px !important;
		font-weight:700 !important;
		padding:4px 8px !important;
		border-radius:6px !important;
		top:12px !important;
		inset-inline-start:12px !important;
		min-height:auto !important;
		min-width:auto !important;
		line-height:1.3 !important;
		box-shadow:0 4px 10px -4px rgba(255,107,109,.5) !important;
		margin:0 !important;
	}

	body.woocommerce .wd-products { gap:16px !important; }

	/* ==================================================
	   ریسپانسیو
	   ================================================== */
	@media (max-width:1200px){
		body.woocommerce-shop .wd-content-area > .ezshop-sidebar,
		body.tax-product_cat .wd-content-area > .ezshop-sidebar,
		body.tax-product_tag .wd-content-area > .ezshop-sidebar{
			flex:0 0 250px !important;
		}
	}

	@media (max-width:992px){
		body.woocommerce-shop .wd-content-area,
		body.tax-product_cat .wd-content-area,
		body.tax-product_tag .wd-content-area{
			display:block !important;
		}
		body.woocommerce-shop .wd-content-area > .ezshop-crumb-row,
		body.tax-product_cat .wd-content-area > .ezshop-crumb-row,
		body.tax-product_tag .wd-content-area > .ezshop-crumb-row{
			margin-bottom:12px;
		}
		body.woocommerce-shop .wd-content-area > .ezshop-sidebar,
		body.tax-product_cat .wd-content-area > .ezshop-sidebar,
		body.tax-product_tag .wd-content-area > .ezshop-sidebar{
			position:fixed !important;
			top:0 !important;
			right:-320px !important;
			left:auto !important;
			width:300px !important;
			height:100vh !important;
			z-index:10000 !important;
			border-radius:0 !important;
			transition:right .3s ease !important;
			padding:18px !important;
			overflow-y:auto !important;
			max-height:none !important;
			box-shadow:0 0 40px rgba(15,23,42,.25) !important;
			flex:none !important;
		}
		body.ezshop-side-open .wd-content-area > .ezshop-sidebar{
			right:0 !important;
		}
		body.ezshop-side-open{overflow:hidden}
		.ezshop-side-close{display:inline-flex !important}
		.ezshop-filter-toggle{display:inline-flex !important}
		.ezshop-apply-wrap{margin-top:12px;padding-top:12px}
		.ezshop-apply{min-height:52px;border-radius:14px;font-size:.95rem}
		.ezshop-backdrop{
			position:fixed;inset:0;background:rgba(15,23,42,.5);
			z-index:9999;backdrop-filter:blur(2px);
		}
		.ezshop-backdrop[hidden]{display:none}
	}

	@media (max-width:768px){
		body.woocommerce .shop-loop-head{
			padding:12px !important;
			gap:10px !important;
			border-radius:14px !important;
		}
		body.woocommerce .shop-loop-head .ezshop-tabs{
			gap:6px !important;
			padding:2px 0 4px !important;
			margin:0 -4px !important;
			padding-inline:4px !important;
			overflow-x:auto !important;
			overflow-y:hidden !important;
			max-width:100% !important;
			width:auto !important;
			flex-wrap:nowrap !important;
			-webkit-overflow-scrolling:touch !important;
			scroll-snap-type:x proximity;
			scrollbar-width:none;
		}
		body.woocommerce .shop-loop-head .ezshop-tabs::-webkit-scrollbar{display:none}
		.ezshop-tab{
			padding:8px 14px !important;
			font-size:.76rem !important;
			flex:0 0 auto !important;
			scroll-snap-align:start;
		}
		body.woocommerce .shop-loop-head .ezshop-toolbar{
			display:block !important;
		}
		.ezshop-filter-toggle{
			width:100% !important;
			padding:12px 16px !important;
			font-size:.85rem !important;
			border-radius:12px !important;
		}
		.ezshop-crumb-row{
			padding:10px 12px !important;
			flex-direction:column;
			align-items:flex-start !important;
			gap:8px !important;
		}
		.ezshop-crumb-row .wd-breadcrumbs{ font-size:.78rem !important; }
		.ezshop-crumb-row .ezshop-count-badge{ font-size:.72rem !important; }

		body.woocommerce .wd-product .product-element-top,
		body.woocommerce .wd-product .wd-product-thumb {
			padding:14px !important;
		}
		body.woocommerce .wd-product .product-element-top img {
			max-height:170px !important;
		}
		body.woocommerce .wd-product .product-element-bottom {
			padding:12px !important;
			gap:8px !important;
		}
		body.woocommerce .wd-product .wd-entities-title {
			font-size:12px !important;
			min-height:40px !important;
		}
		body.woocommerce .wd-product .price {
			font-size:13px !important;
		}

		body.woocommerce .wd-product .wd-add-btn,
		body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace,
		body.woocommerce .wd-product:hover .wd-add-btn,
		body.woocommerce .wd-product:hover .wd-add-btn.wd-add-btn-replace {
			gap:5px !important;
			min-height:78px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity {
			height:36px !important;
			min-height:36px !important;
			max-height:36px !important;
			flex:0 0 36px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity .minus,
		body.woocommerce .wd-product .wd-add-btn > .quantity .plus {
			width:34px !important;
			min-width:34px !important;
			max-width:34px !important;
			font-size:15px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity input.qty {
			font-size:12.5px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > a.button {
			height:38px !important;
			min-height:38px !important;
			max-height:38px !important;
			flex:0 0 38px !important;
			font-size:12px !important;
			padding:0 12px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > a.button .wd-action-text,
		body.woocommerce .wd-product:hover .wd-add-btn > a.button .wd-action-text {
			font-size:12px !important;
		}
	}

	@media (max-width:600px){
		.ezshop-wa{width:50px;height:50px;inset-inline-end:14px;bottom:14px}

		body.woocommerce .wd-product .product-element-top,
		body.woocommerce .wd-product .wd-product-thumb {
			padding:10px !important;
		}
		body.woocommerce .wd-product .product-element-top img {
			max-height:140px !important;
		}
		body.woocommerce .wd-product .product-element-bottom {
			padding:10px !important;
			gap:6px !important;
		}
		body.woocommerce .wd-product .wd-entities-title {
			font-size:11.5px !important;
			min-height:36px !important;
			line-height:1.6 !important;
		}
		body.woocommerce .wd-product .price {
			font-size:12.5px !important;
		}

		body.woocommerce .wd-product .wd-add-btn,
		body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace,
		body.woocommerce .wd-product:hover .wd-add-btn,
		body.woocommerce .wd-product:hover .wd-add-btn.wd-add-btn-replace {
			gap:5px !important;
			min-height:74px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity {
			height:34px !important;
			min-height:34px !important;
			max-height:34px !important;
			flex:0 0 34px !important;
			border-radius:9px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity .minus,
		body.woocommerce .wd-product .wd-add-btn > .quantity .plus {
			width:32px !important;
			min-width:32px !important;
			max-width:32px !important;
			font-size:14px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity input.qty {
			font-size:12px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > a.button {
			height:36px !important;
			min-height:36px !important;
			max-height:36px !important;
			flex:0 0 36px !important;
			font-size:11.5px !important;
			padding:0 10px !important;
			border-radius:9px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > a.button .wd-action-text,
		body.woocommerce .wd-product:hover .wd-add-btn > a.button .wd-action-text {
			font-size:11.5px !important;
		}
	}

	@media (max-width:400px){
		body.woocommerce .wd-product .wd-add-btn,
		body.woocommerce .wd-product .wd-add-btn.wd-add-btn-replace,
		body.woocommerce .wd-product:hover .wd-add-btn,
		body.woocommerce .wd-product:hover .wd-add-btn.wd-add-btn-replace {
			min-height:70px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity {
			height:32px !important;
			min-height:32px !important;
			max-height:32px !important;
			flex:0 0 32px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity .minus,
		body.woocommerce .wd-product .wd-add-btn > .quantity .plus {
			width:30px !important;
			min-width:30px !important;
			max-width:30px !important;
			font-size:13px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > .quantity input.qty {
			font-size:11.5px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > a.button {
			height:34px !important;
			min-height:34px !important;
			max-height:34px !important;
			flex:0 0 34px !important;
			font-size:11px !important;
		}
		body.woocommerce .wd-product .wd-add-btn > a.button .wd-action-text,
		body.woocommerce .wd-product:hover .wd-add-btn > a.button .wd-action-text {
			font-size:11px !important;
		}
	}

	
	/* ==================================================
	   پاپ‌آپ افزودن به سبد — مینیمال مدرن
	   ================================================== */
	.mfp-bg{
		background:rgba(15,23,42,.45) !important;
		backdrop-filter:blur(8px) !important;
		-webkit-backdrop-filter:blur(8px) !important;
	}
	.wd-popup-wrap.wd-popup-added-cart,
	.mfp-content .wd-popup-wrap{
		display:flex !important;
		align-items:center !important;
		justify-content:center !important;
	}
	.wd-popup.wd-popup-added-cart{
		background:#fff !important;
		border-radius:20px !important;
		border:1px solid #e8edf5 !important;
		box-shadow:0 24px 64px rgba(3,31,138,.14),0 8px 24px rgba(15,23,42,.08) !important;
		padding:0 !important;
		max-width:400px !important;
		width:calc(100% - 32px) !important;
		overflow:hidden !important;
		position:relative !important;
		animation:ezshopPopIn .35s cubic-bezier(.34,1.4,.64,1) !important;
	}
	@keyframes ezshopPopIn{
		from{opacity:0;transform:scale(.92) translateY(16px)}
		to{opacity:1;transform:scale(1) translateY(0)}
	}
	.wd-popup.wd-popup-added-cart::before{
		content:"";
		display:block;
		height:4px;
		background:linear-gradient(90deg,#031f8a,#0a3d91,#10b981);
	}
	.wd-popup-wrap .wd-popup-close,
	.mfp-content .wd-popup-close,
	.wd-popup-added-cart + .wd-popup-close,
	.wd-popup-wrap > .wd-popup-close{
		position:absolute !important;
		top:12px !important;
		left:12px !important;
		right:auto !important;
		width:36px !important;
		height:36px !important;
		border-radius:50% !important;
		background:#f1f5f9 !important;
		border:1px solid #e2e8f0 !important;
		z-index:5 !important;
		display:flex !important;
		align-items:center !important;
		justify-content:center !important;
		transition:background .2s,transform .2s !important;
	}
	.wd-popup-wrap > .wd-popup-close:hover{
		background:#fee2e2 !important;
		border-color:#fecaca !important;
		transform:rotate(90deg) !important;
	}
	.wd-popup-close .wd-action-text{display:none !important}
	.wd-popup-close .wd-action-icon{
		width:14px !important;height:14px !important;
		background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round'><path d='M6 6l12 12M18 6L6 18'/></svg>") center/contain no-repeat !important;
		font-size:0 !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart{
		padding:36px 28px 28px !important;
		text-align:center !important;
		font-family:inherit !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart::before{
		content:"";
		display:block;
		width:64px;height:64px;
		margin:0 auto 16px;
		border-radius:50%;
		background:#ecfdf5 url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23059669' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M20 6L9 17l-5-5'/></svg>") center/28px no-repeat;
		box-shadow:0 0 0 0 rgba(5,150,105,.25);
		animation:ezshopOkPulse 1.4s ease-out;
	}
	@keyframes ezshopOkPulse{
		0%{transform:scale(.7);box-shadow:0 0 0 0 rgba(5,150,105,.4)}
		70%{transform:scale(1.05)}
		100%{transform:scale(1);box-shadow:0 0 0 18px rgba(5,150,105,0)}
	}
	.wd-popup.wd-popup-added-cart .added-to-cart h3{
		font-size:0 !important;
		margin:0 0 20px !important;
		line-height:1.5 !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart h3::before{
		content:"به سبد اضافه شد";
		display:block;
		font-size:1.1rem;
		font-weight:800;
		color:#0f172a;
		margin-bottom:6px;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart h3::after{
		content:"می‌توانید خرید را ادامه دهید یا سبد را ببینید.";
		display:block;
		font-size:.84rem;
		font-weight:500;
		color:#64748b;
		line-height:1.7;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart .btn{
		display:inline-flex !important;
		align-items:center !important;
		justify-content:center !important;
		min-height:46px !important;
		padding:12px 18px !important;
		margin:4px !important;
		border-radius:12px !important;
		font-size:.88rem !important;
		font-weight:700 !important;
		text-decoration:none !important;
		transition:transform .15s,box-shadow .15s,background .15s !important;
		border:1.5px solid transparent !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart .btn-default,
	.wd-popup.wd-popup-added-cart .added-to-cart .close-popup{
		background:#fff !important;
		color:#0f172a !important;
		border-color:#e2e8f0 !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart .btn-default:hover,
	.wd-popup.wd-popup-added-cart .added-to-cart .close-popup:hover{
		background:#f8fafc !important;
		transform:translateY(-1px) !important;
		box-shadow:0 6px 16px rgba(15,23,42,.06) !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart .btn-accent,
	.wd-popup.wd-popup-added-cart .added-to-cart .view-cart{
		background:linear-gradient(135deg,#031f8a 0%,#0a3d91 50%,#011663 100%) !important;
		color:#fff !important;
		border-color:transparent !important;
		box-shadow:0 8px 20px rgba(3,31,138,.22) !important;
	}
	.wd-popup.wd-popup-added-cart .added-to-cart .btn-accent:hover,
	.wd-popup.wd-popup-added-cart .added-to-cart .view-cart:hover{
		transform:translateY(-1px) !important;
		box-shadow:0 12px 28px rgba(3,31,138,.3) !important;
		color:#fff !important;
	}

	@media (prefers-reduced-motion:reduce){
		.ezshop-wa-pulse{animation:none}
	}
	</style>
	<?php
}

/* ============================================================
 * ۶. JavaScript
 * ============================================================ */
add_action( 'wp_footer', 'ezshop_enhance_js', 100 );
function ezshop_enhance_js() {
	if ( ! ez_is_shop_context() ) return;
	?>
	<script id="ezshop-enhance-js">
	(function(){
		"use strict";
		var dataEl = document.getElementById('ezshop-data');
		if(!dataEl) return;
		var cfg = JSON.parse(dataEl.textContent || '{}');
		if(!cfg.ajax) return;

		function faNum(n){
			if(n === null || n === undefined || n === '') return '';
			try { return Number(n).toLocaleString('fa-IR'); }
			catch(e){ return String(n); }
		}
		function enNum(str){
			var fa='۰۱۲۳۴۵۶۷۸۹', ar='٠١٢٣٤٥٦٧٨٩', en='0123456789';
			var out = String(str || '');
			for(var i=0;i<10;i++){
				out = out.split(fa[i]).join(en[i]);
				out = out.split(ar[i]).join(en[i]);
			}
			return out.replace(/[^\d]/g,'');
		}

		var state = {
			paged: 1, sort: 'date-desc', termId: cfg.termId || 0,
			cats: [], brands: [], min: 0, max: 0,
			inStock: false, onSale: false, perPage: 12
		};

		var tries = 0, MAX = 60;
		function waitForStructure(){
			var contentArea = document.querySelector('.wd-content-area');
			var productsEl  = document.querySelector('.wd-content-area .wd-products-element');
			var shopHead    = document.querySelector('.wd-content-area .shop-loop-head');

			if((!contentArea || !productsEl) && tries < MAX){
				tries++;
				return setTimeout(waitForStructure, 100);
			}
			if(contentArea && productsEl) bootstrap(contentArea, productsEl, shopHead);
		}

		function bootstrap(contentArea, productsEl, shopHead){
			var filtersArea = contentArea.querySelector('.filters-area');
			if(filtersArea) filtersArea.remove();

			if(shopHead){
				var breadcrumb = shopHead.querySelector('.wd-breadcrumbs');
				var countEl    = shopHead.querySelector('.woocommerce-result-count');

				var oldCrumb = contentArea.querySelector(':scope > .ezshop-crumb-row');
				if(oldCrumb) oldCrumb.remove();

				if(breadcrumb || countEl){
					var crumbRow = document.createElement('div');
					crumbRow.className = 'ezshop-crumb-row';
					if(breadcrumb) crumbRow.appendChild(breadcrumb);
					if(countEl){
						countEl.classList.add('ezshop-count-badge');
						crumbRow.appendChild(countEl);
					}
					contentArea.insertBefore(crumbRow, shopHead);
				}

				shopHead.innerHTML = '';

				var tabsTpl = document.getElementById('ezshop-tabs-tpl');
				if(tabsTpl) shopHead.appendChild(tabsTpl.content.cloneNode(true));

				var toolbar = document.createElement('div');
				toolbar.className = 'ezshop-toolbar';
				var filterToggle = document.createElement('button');
				filterToggle.type = 'button';
				filterToggle.className = 'ezshop-filter-toggle';
				filterToggle.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><span>فیلترها</span><span class="ezshop-filter-badge" data-ezshop-badge hidden>0</span>';
				filterToggle.addEventListener('click', function(){
					document.body.classList.add('ezshop-side-open');
					var bd = document.querySelector('[data-ezshop-backdrop]');
					if(bd) bd.hidden = false;
				});
				toolbar.appendChild(filterToggle);
				shopHead.appendChild(toolbar);
			}

			var sidebar = contentArea.querySelector('.ezshop-sidebar');
			if(!sidebar){
				sidebar = document.createElement('aside');
				sidebar.className = 'ezshop-sidebar';
				var sideTpl = document.getElementById('ezshop-side-tpl');
				if(sideTpl) sidebar.appendChild(sideTpl.content.cloneNode(true));
				productsEl.parentNode.insertBefore(sidebar, productsEl);
			}

			/* عنوان گروه دسته: در فروشگاه «دسته‌بندی»، در آرشیو «زیر‌دسته‌ها» */
			var catHead = sidebar.querySelector('[data-group="cat"] .ezshop-group-head span');
			if(catHead && cfg.i18n && cfg.i18n.catLabel) catHead.textContent = cfg.i18n.catLabel;
			renderTerms(sidebar, 'cat', cfg.cats || []);
			renderTerms(sidebar, 'brand', cfg.brands || []);
			/* اگر زیر‌دسته‌ای نبود گروه دسته را مخفی کن */
			if(!(cfg.cats && cfg.cats.length)){
				var catGroup = sidebar.querySelector('[data-group="cat"]');
				if(catGroup) catGroup.style.display = 'none';
			}
			initPrice(sidebar, cfg.price || {min:0,max:0});
			bindSwitches(sidebar);

			document.addEventListener('click', function(e){
				var tab = e.target.closest('.ezshop-tab');
				if(tab){
					document.querySelectorAll('.ezshop-tab').forEach(function(t){ t.classList.remove('is-active'); });
					tab.classList.add('is-active');
					state.sort = tab.getAttribute('data-sort');
					state.paged = 1;
					load(true);
					return;
				}
				if(e.target.closest('[data-ezshop-clear]')){ resetAll(sidebar); return; }
				if(e.target.closest('[data-ezshop-close]')){
					document.body.classList.remove('ezshop-side-open');
					var bd = document.querySelector('[data-ezshop-backdrop]');
					if(bd) bd.hidden = true;
					return;
				}
				if(e.target.closest('[data-ezshop-apply]')){
					var applyBtn = e.target.closest('[data-ezshop-apply]');
					var txt = applyBtn.querySelector('.ezshop-apply-txt');
					if(txt) txt.textContent = 'در حال اعمال...';
					applyBtn.disabled = true;
					state.paged = 1;
					try { syncStateFromDom(sidebar); } catch(err) {}
					load(true);
					document.body.classList.remove('ezshop-side-open');
					var bd2 = document.querySelector('[data-ezshop-backdrop]');
					if(bd2) bd2.hidden = true;
					setTimeout(function(){
						applyBtn.disabled = false;
						if(txt) txt.textContent = 'اعمال فیلتر';
					}, 800);
					return;
				}
				if(e.target.closest('[data-ezshop-toggle]')){
					var g = e.target.closest('.ezshop-group');
					if(g) g.classList.toggle('is-open');
					return;
				}
				var pgLink = e.target.closest('nav.woocommerce-pagination a, .wd-pagination a, a.page-numbers');
				if(pgLink){
					e.preventDefault();
					var href = pgLink.getAttribute('href') || '';
					var m = href.match(/(\d+)/);
					if(m){ state.paged = parseInt(m[1],10); load(true); }
				}
			});

			document.addEventListener('click', function(e){
				if(e.target.matches('[data-ezshop-backdrop]')){
					document.body.classList.remove('ezshop-side-open');
					e.target.hidden = true;
				}
			});
			document.addEventListener('keydown', function(e){
				if(e.key === 'Escape'){
					document.body.classList.remove('ezshop-side-open');
					var bd = document.querySelector('[data-ezshop-backdrop]');
					if(bd) bd.hidden = true;
				}
			});

			var chipsBox = sidebar.querySelector('[data-ezshop-chips]');
			if(chipsBox){
				chipsBox.addEventListener('click', function(e){
					var btn = e.target.closest('button[data-clear]');
					if(!btn) return;
					var key = btn.getAttribute('data-clear');
					if(key === 'in-stock'){
						var si = sidebar.querySelector('[data-ezshop-filter="in-stock"]');
						if(si){ si.checked = false; si.dispatchEvent(new Event('change')); }
					}
					if(key === 'on-sale'){
						var so = sidebar.querySelector('[data-ezshop-filter="on-sale"]');
						if(so){ so.checked = false; so.dispatchEvent(new Event('change')); }
					}
					if(key === 'price'){
						var rMin = sidebar.querySelector('[data-range-min]');
						var rMax = sidebar.querySelector('[data-range-max]');
						if(rMin && rMax){
							rMin.value = rMin.min; rMax.value = rMax.max;
							rMin.dispatchEvent(new Event('input'));
							state.min = 0; state.max = 0; state.paged = 1;
							updateChips(sidebar);
						}
					}
					if(key === 'cat' || key === 'brand'){
						/* remove first matching checked of type via chip text is hard; uncheck all of type if single chip clicked from that group */
						var chipSpan = btn.parentElement && btn.parentElement.querySelector('span');
						var label = chipSpan ? chipSpan.textContent : '';
						sidebar.querySelectorAll('[data-body="'+key+'"] label').forEach(function(lab){
							var sp = lab.querySelector('span');
							if(sp && sp.textContent === label){
								var cb = lab.querySelector('input');
								if(cb && cb.checked){ cb.checked = false; cb.dispatchEvent(new Event('change')); }
							}
						});
					}
				});
			}

			function renderTerms(sidebar, key, terms){
				var box = sidebar.querySelector('[data-body="'+key+'"]');
				if(!box) return;
				if(!terms.length){ var g = box.closest('.ezshop-group'); if(g) g.style.display='none'; return; }
				/* key=cat → state.cats | key=brand → state.brands */
				var stateKey = (key === 'cat') ? 'cats' : (key === 'brand') ? 'brands' : key;
				if(!Array.isArray(state[stateKey])) state[stateKey] = [];
				var html = '<div class="ezshop-list">';
				terms.forEach(function(t){
					html += '<label><input type="checkbox" value="'+t.id+'" data-ezshop-term="'+key+'"><span>'+t.name+'</span><span class="count">'+t.count+'</span></label>';
				});
				html += '</div>';
				box.innerHTML = html;
				box.querySelectorAll('input[type=checkbox]').forEach(function(cb){
					cb.addEventListener('change', function(){
						var arr = state[stateKey];
						if(!Array.isArray(arr)){ arr = []; state[stateKey] = arr; }
						var v = parseInt(cb.value,10);
						if(isNaN(v)) return;
						var i = arr.indexOf(v);
						if(cb.checked && i<0) arr.push(v);
						if(!cb.checked && i>=0) arr.splice(i,1);
						state.paged = 1;
						updateChips(sidebar);
					});
				});
			}

			function initPrice(sidebar, price){
				var min = parseInt(price.min||0,10), max = parseInt(price.max||0,10);
				if(!max) return;
				var rMin = sidebar.querySelector('[data-range-min]');
				var rMax = sidebar.querySelector('[data-range-max]');
				var iMin = sidebar.querySelector('[data-input-min]');
				var iMax = sidebar.querySelector('[data-input-max]');
				var fill = sidebar.querySelector('[data-fill]');
				if(!rMin || !rMax) return;
				rMin.min = rMax.min = min;
				rMin.max = rMax.max = max;
				rMin.value = min; rMax.value = max;
				iMin.value = faNum(min); iMax.value = faNum(max);
				iMin.placeholder = faNum(min); iMax.placeholder = faNum(max);

				function updateFill(){
					var lo = +rMin.value, hi = +rMax.value;
					var total = max-min || 1;
					var left = ((lo-min)/total)*100;
					var right = ((hi-min)/total)*100;
					fill.style.insetInlineStart = left + '%';
					fill.style.width = (right-left) + '%';
				}
				function sync(){
					var lo = +rMin.value, hi = +rMax.value;
					if(lo>hi){ var t = lo; lo = hi; hi = t; rMin.value = lo; rMax.value = hi; }
					iMin.value = faNum(lo);
					iMax.value = faNum(hi);
					state.min = lo; state.max = hi;
					updateFill();
				}
				rMin.addEventListener('input', sync);
				rMax.addEventListener('input', sync);
				[rMin,rMax].forEach(function(r){
					r.addEventListener('change', function(){ sync(); state.paged=1; updateChips(sidebar); });
				});
				iMin.addEventListener('change', function(){
					var raw = parseInt(enNum(iMin.value) || min, 10);
					rMin.value = Math.max(min, Math.min(raw, +rMax.value));
					sync(); state.paged=1; updateChips(sidebar);
				});
				iMax.addEventListener('change', function(){
					var raw = parseInt(enNum(iMax.value) || max, 10);
					rMax.value = Math.min(max, Math.max(raw, +rMin.value));
					sync(); state.paged=1; updateChips(sidebar);
				});
				updateFill();
			}

			function bindSwitches(sidebar){
				var si = sidebar.querySelector('[data-ezshop-filter="in-stock"]');
				var so = sidebar.querySelector('[data-ezshop-filter="on-sale"]');
				if(si) si.addEventListener('change', function(){ state.inStock = si.checked; state.paged=1; updateChips(sidebar); });
				if(so) so.addEventListener('change', function(){ state.onSale  = so.checked; state.paged=1; updateChips(sidebar); });
			}

			function updateChips(sidebar){
				var box = sidebar.querySelector('[data-ezshop-chips]');
				var badge = document.querySelector('[data-ezshop-badge]');
				if(!box) return;
				var chips = [];
				sidebar.querySelectorAll('[data-body="cat"] input:checked').forEach(function(cb){
					chips.push({t: cb.parentElement.querySelector('span').textContent, k:'cat', cb: cb});
				});
				sidebar.querySelectorAll('[data-body="brand"] input:checked').forEach(function(cb){
					chips.push({t: cb.parentElement.querySelector('span').textContent, k:'brand', cb: cb});
				});
				if(state.inStock) chips.push({t:'موجود', k:'in-stock'});
				if(state.onSale)  chips.push({t:'تخفیف‌دار', k:'on-sale'});
				if(state.min || state.max){
					chips.push({t:'قیمت: ' + faNum(state.min||0) + ' — ' + (state.max?faNum(state.max):'∞') + ' تومان', k:'price'});
				}
				var html = '';
				chips.forEach(function(c){
					html += '<span class="ezshop-chip"><span>'+c.t+'</span><button type="button" data-clear="'+c.k+'" aria-label="حذف">&times;</button></span>';
				});
				box.innerHTML = html;
				if(badge){ badge.textContent = faNum(chips.length); badge.hidden = chips.length === 0; }
				var ac = sidebar.querySelector('[data-ezshop-apply-count]');
				if(ac){
					ac.textContent = faNum(chips.length);
					ac.hidden = chips.length === 0;
				}
			}

			function resetAll(sidebar){
				state.cats = []; state.brands = [];
				state.inStock = false; state.onSale = false;
				state.min = 0; state.max = 0; state.paged = 1;
				sidebar.querySelectorAll('.ezshop-list input[type=checkbox]').forEach(function(cb){ cb.checked=false; });
				var si = sidebar.querySelector('[data-ezshop-filter="in-stock"]'); if(si) si.checked=false;
				var so = sidebar.querySelector('[data-ezshop-filter="on-sale"]'); if(so) so.checked=false;
				var rMin = sidebar.querySelector('[data-range-min]'), rMax = sidebar.querySelector('[data-range-max]');
				if(rMin && rMax){ rMin.value = rMin.min; rMax.value = rMax.max; var ev=new Event('input'); rMin.dispatchEvent(ev); rMax.dispatchEvent(ev); }
				updateChips(sidebar);
				load(true);
			}

			function syncStateFromDom(sidebar){
				state.cats = [];
				state.brands = [];
				sidebar.querySelectorAll('[data-body="cat"] input[type=checkbox]:checked').forEach(function(cb){
					var v = parseInt(cb.value,10);
					if(!isNaN(v)) state.cats.push(v);
				});
				sidebar.querySelectorAll('[data-body="brand"] input[type=checkbox]:checked').forEach(function(cb){
					var v = parseInt(cb.value,10);
					if(!isNaN(v)) state.brands.push(v);
				});
				var si = sidebar.querySelector('[data-ezshop-filter="in-stock"]');
				var so = sidebar.querySelector('[data-ezshop-filter="on-sale"]');
				state.inStock = !!(si && si.checked);
				state.onSale  = !!(so && so.checked);
				var rMin = sidebar.querySelector('[data-range-min]');
				var rMax = sidebar.querySelector('[data-range-max]');
				if(rMin && rMax){
					var lo = parseInt(rMin.value,10), hi = parseInt(rMax.value,10);
					var amin = parseInt(rMin.min,10), amax = parseInt(rMax.max,10);
					/* فقط اگر از بازه کامل فاصله گرفته */
					if(!isNaN(lo) && !isNaN(amin) && lo > amin) state.min = lo; else state.min = 0;
					if(!isNaN(hi) && !isNaN(amax) && hi < amax) state.max = hi; else state.max = 0;
				}
			}

			var controller = null;
			function load(scrollTop){
				syncStateFromDom(sidebar);
				document.body.classList.add('ezshop-loading');
				if(controller) try{ controller.abort(); }catch(e){}
				controller = new AbortController();

				var body = new URLSearchParams();
				body.append('action','ezshop_filter');
				body.append('nonce', cfg.nonce);
				body.append('paged', String(state.paged||1));
				body.append('per_page', String(state.perPage||12));
				body.append('sort', state.sort||'date-desc');
				if(state.termId) body.append('term_id', String(state.termId));
				(state.cats||[]).forEach(function(v){ body.append('cats[]', String(v)); });
				(state.brands||[]).forEach(function(v){ body.append('brands[]', String(v)); });
				if(state.min) body.append('min', String(state.min));
				if(state.max) body.append('max', String(state.max));
				if(state.inStock) body.append('in_stock','1');
				if(state.onSale)  body.append('on_sale','1');

				fetch(cfg.ajax, {
					method:'POST',
					credentials:'same-origin',
					headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
					body: body.toString(),
					signal: controller.signal
				})
					.then(function(r){
						if(!r.ok) throw new Error('HTTP '+r.status);
						return r.json();
					})
					.then(function(res){
						if(!res || !res.success){
							console.warn('ezshop filter failed', res);
							return;
						}
						var d = res.data || {};
						var wrap = document.querySelector('.wd-products-element') || document.querySelector('.products') || document.querySelector('#main .site-content');
						var target = document.querySelector('.wd-products-element .products, .wd-products-element ul.products, ul.products.columns-3, ul.products.columns-4, ul.products');

						if(d.html){
							if(target){
								target.outerHTML = d.html;
							} else if(wrap){
								/* پاک کردن حلقه قبلی */
								var old = wrap.querySelector('.products, ul.products, .woocommerce-info, .wd-empty-grid');
								if(old) old.outerHTML = d.html;
								else wrap.insertAdjacentHTML('beforeend', d.html);
							}
						}

						var pg = document.querySelector('nav.woocommerce-pagination, .wd-pagination, .woocommerce-pagination');
						if(d.pagination){
							if(pg) pg.outerHTML = d.pagination;
							else if(wrap) wrap.insertAdjacentHTML('afterend', d.pagination);
						} else if(pg){
							pg.remove();
						}

						var countBadge = document.querySelector('.ezshop-count-badge');
						if(countBadge) countBadge.textContent = 'نمایش ' + faNum(d.count||0) + ' ' + ((cfg.i18n&&cfg.i18n.items)||'کالا');

						if(typeof jQuery !== 'undefined'){
							try{ jQuery(document.body).trigger('woodmart_products_loaded'); }catch(e){}
						}

						if(scrollTop){
							var y = (document.querySelector('.ezshop-crumb-row')||document.body).getBoundingClientRect().top + window.scrollY - 20;
							window.scrollTo({top:y, behavior:'smooth'});
						}
					})
					.catch(function(e){ if(e.name !== 'AbortError') console.warn('ezshop', e); })
					.finally(function(){ document.body.classList.remove('ezshop-loading'); });
			}

			var initCount = document.querySelector('.ezshop-count-badge');
			if(initCount){
				var txt = initCount.textContent.replace('نمایش همه', 'نمایش');
				initCount.textContent = txt;
			}

			/* پایان اسکلتون — نمایش UI واقعی */
			document.body.classList.add('ezshop-ready');
			document.body.classList.remove('ezshop-booting');
			var sk = document.querySelector('.ezshop-skeleton');
			if(sk) sk.remove();
		}

		waitForStructure();
		/* اگر ساختار دیر لود شد، حداکثر بعد از ۳ ثانیه اسکلتون برداشته شود */
		setTimeout(function(){
			if(!document.body.classList.contains('ezshop-ready')){
				document.body.classList.add('ezshop-ready');
				document.body.classList.remove('ezshop-booting');
				var sk = document.querySelector('.ezshop-skeleton');
				if(sk) sk.remove();
			}
		}, 3000);
	})();
	</script>
	<?php
}