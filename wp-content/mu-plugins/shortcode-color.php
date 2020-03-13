<?php

function cp_color_shortcode( $atts ) {
	if (
		empty( $atts['value'] ) ||
		! preg_match( '/^#[a-f0-9]{6}$/i', $atts['value'] )
	) {
		return '';
	}

	$value = strtolower( $atts['value'] );

	return '<span style="
		display: inline-block;
		width: 0.9em;
		height: 0.9em;
		border-radius: 0.45em;
		background: ' . $value . ';
		vertical-align: middle;
		margin-top: -1.5px;
		margin-left: 0.15em;
	"></span> <code>' . $value . '</code>';
}
add_shortcode( 'cp-color', 'cp_color_shortcode' );
