<?php
/**
 * داشبورد مشتری — یک منوی دسکتاپ + نوار پایین موبایل با «بیشتر»
 *
 * @var string $current
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user  = wp_get_current_user();
$name  = $user->display_name ? $user->display_name : $user->user_login;
$phone = get_user_meta( $user->ID, 'billing_phone', true );
if ( ! $phone ) {
	$phone = get_user_meta( $user->ID, 'digits_phone', true );
}
$menu    = EzLens_CD_Menu::items();
$bottom  = EzLens_CD_Menu::bottom_items();
$current = isset( $current ) ? $current : 'overview';

if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'view-order' ) ) {
	$current = 'view-order';
}
$page_title  = class_exists( 'EzLens_CD_Sections' ) ? EzLens_CD_Sections::title( $current ) : 'حساب کاربری';
$account_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-account' ) : '#';

$is_active = function ( $item, $current ) {
	return ( $current === $item['id'] ) || ( $current === $item['endpoint'] )
		|| ( 'view-order' === $current && 'orders' === $item['id'] )
		|| ( in_array( $current, array( 'edit-address', 'addresses' ), true ) && 'addresses' === $item['id'] )
		|| ( in_array( $current, array( 'edit-account', 'account' ), true ) && 'account' === $item['id'] );
};
?>
<div class="ezcd" id="ezcd" dir="rtl">
	<!-- تنها منوی دسکتاپ -->
	<aside class="ezcd-sidebar" aria-label="منوی حساب">
		<a class="ezcd-profile" href="<?php echo esc_url( $account_url ); ?>" data-ezcd-nav="account" title="مشاهده مشخصات">
			<div class="ezcd-avatar" aria-hidden="true"><?php echo ezcd_icon( 'user' ); ?></div>
			<div class="ezcd-profile-meta">
				<div class="ezcd-profile-name"><?php echo esc_html( $name ); ?></div>
				<?php if ( $phone ) : ?>
					<div class="ezcd-profile-phone"><?php echo esc_html( ezcd_fa( $phone ) ); ?></div>
				<?php endif; ?>
				<span class="ezcd-profile-hint">مشاهده مشخصات</span>
			</div>
		</a>
		<nav class="ezcd-nav">
			<ul>
				<?php foreach ( $menu as $item ) :
					$url = EzLens_CD_Menu::url_for( $item['endpoint'] );
					$on  = $is_active( $item, $current );
					?>
					<li class="<?php echo $on ? 'is-active' : ''; ?>" data-section="<?php echo esc_attr( $item['id'] ); ?>">
						<a href="<?php echo esc_url( $url ); ?>" data-ezcd-nav="<?php echo esc_attr( $item['id'] ); ?>">
							<span class="ezcd-ico"><?php echo ezcd_icon( $item['icon'] ); ?></span>
							<span class="ezcd-lbl"><?php echo esc_html( $item['label'] ); ?></span>
							<?php if ( ! empty( $item['badge'] ) ) : ?>
								<span class="ezcd-badge"><?php echo esc_html( $item['badge'] ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</aside>

	<main class="ezcd-main">
		<header class="ezcd-main-head">
			<h1 class="ezcd-title" id="ezcd-title"><?php echo esc_html( $page_title ); ?></h1>
		</header>
		<div class="ezcd-body" id="ezcd-body" data-section="<?php echo esc_attr( $current ); ?>">
			<?php
			if ( class_exists( 'EzLens_CD_Sections' ) ) {
				echo EzLens_CD_Sections::render( $current );
			} else {
				echo '<p>بخش‌ها در دسترس نیستند.</p>';
			}
			?>
		</div>
	</main>

	<!-- نوار پایین موبایل: ۴ بخش مهم + بیشتر -->
	<nav class="ezcd-bottom" aria-label="منوی موبایل">
		<?php foreach ( $bottom as $item ) :
			$url = EzLens_CD_Menu::url_for( $item['endpoint'] );
			$on  = $is_active( $item, $current );
			?>
			<a class="ezcd-bottom-item<?php echo $on ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>" data-ezcd-nav="<?php echo esc_attr( $item['id'] ); ?>">
				<span class="ezcd-ico"><?php echo ezcd_icon( $item['icon'] ); ?></span>
				<span class="ezcd-lbl"><?php echo esc_html( $item['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
		<button type="button" class="ezcd-bottom-item ezcd-more-btn" id="ezcd-more-btn" aria-label="منوی بیشتر">
			<span class="ezcd-ico"><?php echo ezcd_icon( 'grid' ); ?></span>
			<span class="ezcd-lbl">بیشتر</span>
		</button>
	</nav>

	<!-- کشو فقط موبایل — از سمت راست (طبیعی RTL) -->
	<div class="ezcd-drawer-backdrop" id="ezcd-drawer-backdrop" hidden></div>
	<aside class="ezcd-drawer" id="ezcd-drawer" aria-hidden="true" aria-label="همه بخش‌ها">
		<div class="ezcd-drawer-head">
			<strong>همه بخش‌ها</strong>
			<button type="button" class="ezcd-drawer-close" id="ezcd-drawer-close" aria-label="بستن">
				<?php echo ezcd_icon( 'x' ); ?>
			</button>
		</div>
		<div class="ezcd-drawer-profile">
			<div class="ezcd-avatar" aria-hidden="true"><?php echo ezcd_icon( 'user' ); ?></div>
			<div>
				<div class="ezcd-profile-name"><?php echo esc_html( $name ); ?></div>
				<?php if ( $phone ) : ?>
					<div class="ezcd-profile-phone"><?php echo esc_html( ezcd_fa( $phone ) ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<nav class="ezcd-nav">
			<ul>
				<?php foreach ( $menu as $item ) :
					$url = EzLens_CD_Menu::url_for( $item['endpoint'] );
					$on  = $is_active( $item, $current );
					?>
					<li class="<?php echo $on ? 'is-active' : ''; ?>">
						<a href="<?php echo esc_url( $url ); ?>" data-ezcd-nav="<?php echo esc_attr( $item['id'] ); ?>">
							<span class="ezcd-ico"><?php echo ezcd_icon( $item['icon'] ); ?></span>
							<span class="ezcd-lbl"><?php echo esc_html( $item['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</aside>
</div>
<script>
(function(){
	var more=document.getElementById('ezcd-more-btn');
	var drawer=document.getElementById('ezcd-drawer');
	var back=document.getElementById('ezcd-drawer-backdrop');
	var closeBtn=document.getElementById('ezcd-drawer-close');
	function openD(){
		if(!drawer||!back) return;
		back.hidden=false;
		requestAnimationFrame(function(){
			drawer.classList.add('is-open');
			back.classList.add('is-open');
			drawer.setAttribute('aria-hidden','false');
		});
		document.body.style.overflow='hidden';
	}
	function closeD(){
		if(!drawer||!back) return;
		drawer.classList.remove('is-open');
		back.classList.remove('is-open');
		drawer.setAttribute('aria-hidden','true');
		document.body.style.overflow='';
		setTimeout(function(){ back.hidden=true; },280);
	}
	if(more) more.addEventListener('click', openD);
	if(closeBtn) closeBtn.addEventListener('click', closeD);
	if(back) back.addEventListener('click', closeD);
	if(drawer) drawer.querySelectorAll('a').forEach(function(a){ a.addEventListener('click', closeD); });
	document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeD(); });
})();
</script>
