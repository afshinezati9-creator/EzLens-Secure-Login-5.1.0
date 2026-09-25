<?php
/**
 * Close shell after WooCommerce endpoint content
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$bottom = class_exists( 'EzLens_CD_Menu' ) ? EzLens_CD_Menu::bottom_items() : array();
$current = function_exists( 'ezcd_current_endpoint' ) ? ezcd_current_endpoint() : '';
?>
		</div><!-- .ezcd-body -->
	</main>
	<nav class="ezcd-bottom" aria-label="منوی موبایل">
		<?php foreach ( $bottom as $item ) :
			$url = EzLens_CD_Menu::url_for( $item['endpoint'] );
			$is  = ( $current === $item['id'] );
			?>
			<a class="ezcd-bottom-item<?php echo $is ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
				<span class="ezcd-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( $item['icon'] ) : ''; ?></span>
				<span class="ezcd-lbl"><?php echo esc_html( $item['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
</div><!-- #ezcd -->
