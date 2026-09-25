<?php
/**
 * Open shell chrome around native WooCommerce endpoint content (Phase 1)
 *
 * @var string $current
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = wp_get_current_user();
$name = $user->display_name ? $user->display_name : $user->user_login;
$phone = get_user_meta( $user->ID, 'billing_phone', true );
$menu    = EzLens_CD_Menu::items();
$bottom  = EzLens_CD_Menu::bottom_items();
$current = isset( $current ) ? $current : ezcd_current_endpoint();

$labels = array();
foreach ( $menu as $it ) {
	$labels[ $it['id'] ] = $it['label'];
}
$page_title = isset( $labels[ $current ] ) ? $labels[ $current ] : 'حساب کاربری';
?>
<div class="ezcd ezcd-wrap-wc" id="ezcd" dir="rtl">
	<aside class="ezcd-sidebar" aria-label="منوی حساب">
		<div class="ezcd-profile">
			<div class="ezcd-avatar" aria-hidden="true"><?php echo ezcd_icon( 'user' ); ?></div>
			<div class="ezcd-profile-meta">
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
					$is  = ( $current === $item['id'] ) || ( $current === $item['endpoint'] );
					?>
					<li class="<?php echo $is ? 'is-active' : ''; ?>">
						<a href="<?php echo esc_url( $url ); ?>">
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
			<h1 class="ezcd-title"><?php echo esc_html( $page_title ); ?></h1>
		</header>
		<div class="ezcd-body ezcd-body-wc">
