<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user   = wp_get_current_user();
$name   = $user->first_name ? $user->first_name : ( $user->display_name ? $user->display_name : $user->user_login );
$cards  = EzLens_CD_Overview::cards();
$freq   = EzLens_CD_Overview::frequent_products();
$cart_n = EzLens_CD_Overview::cart_count();
$shop   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$cart   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$orders_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '#';
$addr_url   = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-address' ) : '#';
$supp_url   = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'support' ) : '#';
?>
<div class="ezcd-overview">
	<div class="ezcd-hello">
		<div class="ezcd-hello-icon" aria-hidden="true"><?php echo ezcd_icon( 'user' ); ?></div>
		<div class="ezcd-hello-text">
			<p class="ezcd-hello-title">سلام <?php echo esc_html( $name ); ?>، خوش آمدید</p>
			<p class="ezcd-hello-sub">همه‌چیز برای پیگیری سفارش و مدیریت حسابتان اینجاست — آرام و شفاف.</p>
		</div>
	</div>

	<div class="ezcd-stat-grid">
		<?php foreach ( $cards as $card ) : ?>
			<a class="ezcd-stat-card ezcd-stat-<?php echo esc_attr( $card['key'] ); ?>" href="<?php echo esc_url( $card['href'] ); ?>">
				<span class="ezcd-stat-ico"><?php echo ezcd_icon( $card['icon'] ); ?></span>
				<span class="ezcd-stat-num"><?php echo esc_html( ezcd_fa( (string) $card['count'] ) ); ?></span>
				<span class="ezcd-stat-lbl"><?php echo esc_html( $card['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<?php if ( $cart_n > 0 ) : ?>
		<div class="ezcd-alert-cart">
			<span class="ezcd-alert-ico"><?php echo ezcd_icon( 'shopping-cart' ); ?></span>
			<div>
				<strong>سبد خریدتان منتظر شماست</strong>
				<p><?php echo esc_html( ezcd_fa( (string) $cart_n ) ); ?> کالا هنوز نهایی نشده — با یک کلیک می‌توانید خرید را تمام کنید.</p>
			</div>
			<a class="ezcd-btn ezcd-btn-grad" href="<?php echo esc_url( $cart ); ?>">ادامه خرید</a>
		</div>
	<?php endif; ?>

	<div class="ezcd-quick">
		<a class="ezcd-quick-item" href="<?php echo esc_url( $shop ); ?>">
			<span class="ezcd-ico"><?php echo ezcd_icon( 'shop' ); ?></span>
			<span>فروشگاه</span>
		</a>
		<a class="ezcd-quick-item" href="<?php echo esc_url( $orders_url ); ?>">
			<span class="ezcd-ico"><?php echo ezcd_icon( 'package' ); ?></span>
			<span>سفارش‌ها</span>
		</a>
		<a class="ezcd-quick-item" href="<?php echo esc_url( $addr_url ); ?>">
			<span class="ezcd-ico"><?php echo ezcd_icon( 'map-pin' ); ?></span>
			<span>آدرس‌ها</span>
		</a>
		<a class="ezcd-quick-item" href="<?php echo esc_url( $supp_url ); ?>">
			<span class="ezcd-ico"><?php echo ezcd_icon( 'headset' ); ?></span>
			<span>پشتیبانی</span>
		</a>
	</div>

	<?php if ( $freq ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">محصولاتی که دوباره می‌خرید</h2>
			<p class="ezcd-section-sub">بر اساس سفارش‌های قبلی‌تان — برای صرفه‌جویی در زمان.</p>
			<div class="ezcd-prod-scroll">
				<?php foreach ( $freq as $product ) :
					$img = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'ezcd-prod-img' ) );
					?>
					<div class="ezcd-prod-card">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="ezcd-prod-thumb"><?php echo $img; ?></a>
						<div class="ezcd-prod-name"><?php echo esc_html( $product->get_name() ); ?></div>
						<div class="ezcd-prod-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
						<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
							<button type="button" class="ezcd-btn ezcd-btn-grad ezcd-add-wish-cart" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">افزودن به سبد</button>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php if ( class_exists( 'EzLens_CD_Menu' ) ) : ?>
<section class="ezcd-card" style="margin-top:18px">
	<h2 class="ezcd-h2" style="margin:0 0 12px;font-size:1rem">دسترسی سریع به همه بخش‌ها</h2>
	<div class="ezcd-all-links">
		<?php foreach ( EzLens_CD_Menu::items() as $item ) :
			$url = EzLens_CD_Menu::url_for( $item['endpoint'] );
			?>
			<a class="ezcd-all-link" href="<?php echo esc_url( $url ); ?>" data-ezcd-nav="<?php echo esc_attr( $item['id'] ); ?>">
				<span class="ezcd-ico"><?php echo ezcd_icon( $item['icon'] ); ?></span>
				<span><?php echo esc_html( $item['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
