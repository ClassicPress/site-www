<?php
/**
 * Checkout Form
 * @package ClassicCommerce/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'classic-commerce' ) ) );
	return;
}

$donations_page = get_option( '_cpdo_donations_page' );
?>

<div class="col2-set">
	<p class="return-to-shop">
		<a class="button wc-backward wpdo_return" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', get_permalink( $donations_page ) ) ); ?>">
			<?php esc_html_e( 'Return to donations page', 'classic-commerce' ); ?>
		</a>
	</p>
</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'classic-commerce' ); ?></h3>

	<?php echo '<a href="' . esc_url( add_query_arg( 'empty_cart', 'yes' ) ) . '" class="button wpdo_return" title="' . esc_attr( 'Clear Cart', 'classic-commerce' ) . '">' . esc_html( 'Clear Cart', 'classic-commerce' ) . '</a>'; ?>

	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>
	<br />

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
