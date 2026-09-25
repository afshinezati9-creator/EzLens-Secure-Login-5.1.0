<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$products = EzLens_CD_Wishlist::products();
?>
<div class="ezcd-wishlist">
	<?php if ( empty( $products ) ) : ?>
		<div class="ezcd-empty">
			<div class="ezcd-empty-ico"><?php echo ezcd_icon( 'heart' ); ?></div>
			<p>هنوز چیزی ذخیره نکرده‌اید. محصولات مورد علاقه را ذخیره کنید تا بعداً راحت پیدا شوند.</p>
			<a class="ezcd-btn ezcd-btn-primary" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>">مشاهده فروشگاه</a>
		</div>
	<?php else : ?>
		<div class="ezcd-wish-grid">
			<?php foreach ( $products as $product ) : ?>
				<div class="ezcd-prod-card" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="ezcd-prod-thumb"><?php echo $product->get_image( 'woocommerce_thumbnail' ); ?></a>
					<div class="ezcd-prod-name"><?php echo esc_html( $product->get_name() ); ?></div>
					<div class="ezcd-prod-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
					<div class="ezcd-wish-actions">
						<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
							<button type="button" class="ezcd-btn ezcd-btn-primary ezcd-add-wish-cart" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">افزودن به سبد</button>
						<?php endif; ?>
						<button type="button" class="ezcd-btn ezcd-btn-soft ezcd-wish-remove" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">حذف</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
