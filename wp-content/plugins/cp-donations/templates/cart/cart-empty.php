<?php
/**
 * Empty cart page
 * @package ClassicCommerce/Templates
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

$donations_page = get_option( '_cpdo_donations_page' );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<p class="return-to-shop">
		<a class="button wc-backward wpdo_return" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', get_permalink( $donations_page ) ) ); ?>">
			<?php esc_html_e( 'Return to donations page', 'classic-commerce' ); ?>
		</a>
	</p>
<?php endif; ?>
