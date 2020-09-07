<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="shipping_product_data" class="panel woocommerce_options_panel hidden">
	<div class="options_group">
		<?php
		if ( wc_product_weight_enabled() ) {
			woocommerce_wp_text_input(
				array(
					'id'          => '_weight',
					'value'       => $product_object->get_weight( 'edit' ),
					'label'       => __( 'Weight', 'classic-commerce' ) . ' (' . get_option( 'woocommerce_weight_unit' ) . ')',
					'placeholder' => wc_format_localized_decimal( 0 ),
					'desc_tip'    => true,
					'description' => __( 'Weight in decimal form', 'classic-commerce' ),
					'type'        => 'text',
					'data_type'   => 'decimal',
				)
			);
		}

		if ( wc_product_dimensions_enabled() ) {
			?>
			<p class="form-field dimensions_field">
				<?php /* translators: Classic Commerce dimension unit*/ ?>
				<label for="product_length"><?php printf( __( 'Dimensions (%s)', 'classic-commerce' ), get_option( 'woocommerce_dimension_unit' ) ); ?></label>
				<span class="wrap">
					<input id="product_length" placeholder="<?php esc_attr_e( 'Length', 'classic-commerce' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_length" value="<?php echo esc_attr( wc_format_localized_decimal( $product_object->get_length( 'edit' ) ) ); ?>" />
					<input placeholder="<?php esc_attr_e( 'Width', 'classic-commerce' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_width" value="<?php echo esc_attr( wc_format_localized_decimal( $product_object->get_width( 'edit' ) ) ); ?>" />
					<input placeholder="<?php esc_attr_e( 'Height', 'classic-commerce' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="_height" value="<?php echo esc_attr( wc_format_localized_decimal( $product_object->get_height( 'edit' ) ) ); ?>" />
				</span>
				<?php echo wc_help_tip( __( 'LxWxH in decimal form', 'classic-commerce' ) ); ?>
			</p>
			<?php
		}

		do_action( 'woocommerce_product_options_dimensions' );
		?>
	</div>

	<div class="options_group">
		<?php
		$args = array(
			'taxonomy'         => 'product_shipping_class',
			'hide_empty'       => 0,
			'show_option_none' => __( 'No shipping class', 'classic-commerce' ),
			'name'             => 'product_shipping_class',
			'id'               => 'product_shipping_class',
			'selected'         => $product_object->get_shipping_class_id( 'edit' ),
			'class'            => 'select short',
		);
		?>
		<p class="form-field shipping_class_field">
			<label for="product_shipping_class"><?php esc_html_e( 'Shipping class', 'classic-commerce' ); ?></label>
			<?php wp_dropdown_categories( $args ); ?>
			<?php echo wc_help_tip( __( 'Shipping classes are used by certain shipping methods to group similar products.', 'classic-commerce' ) ); ?>
		</p>
		<?php

		do_action( 'woocommerce_product_options_shipping' );
		?>
	</div>
</div>
