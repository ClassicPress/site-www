<?php

function cp_logo_link_html( $dir, $entry, $format_key, $link_text ) {
	$dir_url = site_url( "/$dir" );
	return (
		'<a href="' . esc_attr( $dir_url . $entry['formats'][ $format_key ]) . '">'
		. esc_html( $link_text )
		. '</a>'
	);
}

function cp_logo_list_shortcode( $atts ) {
	$dir = trim( $atts['dir'] ?? '', '/' );
	if ( empty( $dir ) || ! is_dir( ABSPATH . $dir ) ) {
		return '';
	}
	$dir = trailingslashit( $dir );
	$dir_full = ABSPATH . $dir;
	$dir_url = site_url( "/$dir" );

	if ( ! file_exists( "$dir_full/index.json" ) ) {
		return '';
	}

	$index = json_decode( file_get_contents( "$dir_full/index.json" ), true );

	$html = "<table class=\"cp-logo-list\">\n";
	foreach ( $index as $svg => $entry ) {
		$html .= "<tr><td class=\"cp-logo-preview\">\n";
		$url = esc_url( $dir_url . $svg );
		$html .= "<a href=\"$url\"><div class=\"cp-logo-wrapper\">";
		$html .= "<div class=\"cp-logo-thumbnail\" style=\"
			width: 120px;
			height: 120px;
			background-image: url( '$url' );
			background-repeat: no-repeat;
			background-size: contain;
			background-position: center;
		\"></div></div></a>\n";
		$html .= "</td><td class=\"cp-logo-info\">\n";
		$html .= "<div class=\"cp-logo-description\">";
		$html .= esc_html( $entry['description'] );
		$html .= "</div>\n";
		$html .= "Formats: ";
		$html .= cp_logo_link_html( $dir, $entry, 'svg', 'SVG' );
		$html .= " | PNG: ";
		$html .= cp_logo_link_html( $dir, $entry, 'png:1200', '1200px' );
		$html .= ", ";
		$html .= cp_logo_link_html( $dir, $entry, 'png:600', '600px' );
		$html .= " | ";
		$html .= cp_logo_link_html( $dir, $entry, 'pdf', 'PDF' );
	}
	$html .= "</table>\n";

	return $html;
}
add_shortcode( 'cp-logo-list', 'cp_logo_list_shortcode' );
