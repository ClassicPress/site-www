<?php
/**
 * Single Product Custom Amount Input
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="cpd" <?php echo CPD_Custom_Amount_Helpers::get_data_attributes( $cpd_product, $suffix ); ?> >

	<?php do_action( 'cpd_before_price_input', $cpd_product, $suffix ); ?>

		<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo wp_kses_post( $input_label ); ?></label>
		<input
			type="text"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', (array) $classes ) ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr( strip_tags( $input_label ) ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"

			<?php
			if ( ! empty( $custom_attributes ) && is_array( $custom_attributes ) ) {
				foreach ( $custom_attributes as $key => $value ) {
					printf( '%s="%s" ', esc_attr( $key ), esc_attr( $value ) );
				}
			}
			?>
		/>

		<input type="hidden" name="update-price" value="<?php echo esc_attr( $updating_cart_key ); ?>" />
		<input type="hidden" name="_cpdnonce" value="<?php echo esc_attr( $_cpdnonce ); ?>" />

	<?php do_action( 'cpd_after_price_input', $cpd_product, $suffix ); ?>

</div>
