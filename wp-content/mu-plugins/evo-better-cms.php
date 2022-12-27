<?php

/*
Plugin Name: Evo Better CMS
Description: Several enhancements and simplifications that make WordPress a better CMS
Version: 1.0
License: GPL
Author: Ray Gulick, Evo Web Dev
Author URI: http://www.evowebdev.com
*/

// remove empty paragraph tags
add_filter('the_content', 'remove_empty_p', 20, 1);
function remove_empty_p($content){
	$content = force_balance_tags($content);
	return preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
}

//remove autop filter
remove_filter('the_content', 'wpautop');
remove_filter('the_excerpt', 'wpautop');

//add excerpts to pages
add_post_type_support( 'page', 'excerpt' );

//add ID to post and page lists (including CPTs)
add_filter('manage_posts_columns', 'posts_columns_id', 5);
add_action('manage_posts_custom_column', 'posts_custom_id_columns', 5, 2);
add_filter('manage_pages_columns', 'posts_columns_id', 5);
add_action('manage_pages_custom_column', 'posts_custom_id_columns', 5, 2);
function posts_columns_id($defaults){
    $defaults['wps_post_id'] = __('ID');
    return $defaults;
}
function posts_custom_id_columns($column_name, $id){
	if($column_name === 'wps_post_id'){
		echo $id;
    }
}

//add admin stylesheet
add_action( 'admin_enqueue_scripts', 'load_admin_style' );
function load_admin_style() {
	wp_enqueue_style( 'admin_css', get_stylesheet_directory_uri() . '/admin-style.css', false, '1.0.0' );
}

// Add custom stylesheet to TinyMCE editor
if ( ! function_exists('tdav_css') ) {
function tdav_css($wp) {
	$wp .= ',' . get_bloginfo('stylesheet_directory') . '/editor-style.css';
	return $wp;
	}
}
add_filter( 'mce_css', 'tdav_css' );

//add text styles to TinyMCE format dropdown
add_filter( 'mrw_mce_text_style', 'mrw_add_text_styles' );
function mrw_add_text_styles( $styles ) {
	$new_styles = array(
	array(
		'title' => "Small",
		'inline' => 'span',
		'selector' => 'p,li,h2,h3,h4',
		'classes' => 'small'
	),
	array(
		'title' => "Code Block",
		'selector' => 'p,li',
		'classes' => 'code'
	),
	array(
		'title' => "Subhead Top",
		'selector' => 'h2,h3,h4',
		'classes' => 'subhdtop'
	),
	array(
		'title' => "Purple Button",
		'selector' => 'p',
		'classes' => 'button purple'
	),
	array(
		'title' => "Blue Button",
		'selector' => 'p',
		'classes' => 'button blue'
	)
	);
	return array_merge( $styles, $new_styles );
}

//add 'very simple' ACF wysiwyg toolbar set (requires ACF Pro?)
add_filter( 'acf/fields/wysiwyg/toolbars' , 'my_toolbars'  );
function my_toolbars( $toolbars ) {

	$toolbars['Very Simple' ] = array();
	$toolbars['Very Simple' ][1] = array('formatselect', 'pastetext', 'removeformat', 'bold' , 'italic' , 'link', 'unlink', 'bullist', 'numlist', 'code', );

}

/*****Manage formats used in TinyMCE
 * customize the WordPress tinymce editor
 * Show kitchen sink by default. Remove h1 and address from block styles
 * deeply indebted to http://wordpress.stackexchange.com/a/128950/9844
 * @param $args array exising mceargs
 * @return modified $args array
 */
add_filter( 'tiny_mce_before_init', 'mrw_mce_init' );
function mrw_mce_init( $args ) {
	
	$style_formats = array(
		array(
			'title' => 'Paragraph',
			'format' => 'p'
			),
		array(
			'title' => 'Header 2',
			'format' => 'h2'
		),
		array(
			'title' => 'Header 3',
			'format' => 'h3'
		),
		array(
			'title' => 'Header 4',
			'format' => 'h4'
		),
		array(
			'title' => 'Blockquote',
			'format' => 'blockquote',
			'icon' => 'blockquote'
		),
		array(
			'title' => 'Div',
			'format' => 'div',
			'icon' => 'div'
		),
		array(
			'title' => 'Other Formats',
			'items' => array(
				array(
					'title' => 'Superscript',
					'format' => 'superscript',
					'icon' => 'superscript'
				),
				array(
					'title' => 'Subscript',
					'format' => 'subscript',
					'icon' => 'subscript'
				),
				array(
					'title' => 'pre',
					'format' => 'pre'
				)
			)
		)
	);

	// Custom filter to add text styles from evo-better-cms
	$text_styles = array();
	$text_styles = apply_filters( 'mrw_mce_text_style', $text_styles );
	if( !empty( $text_styles) ) {
		$text_styles = array(
			'title' => 'Text Styles',
			'items' => $text_styles
		);
		// put style formats second-to-last
		$other_formats = array_pop( $style_formats );
		$style_formats = array_merge( $style_formats, array( $text_styles ), array( $other_formats ) );
	}

	// Last minute filter for anything more complicated before json_encoded
	$style_formats = apply_filters( 'mrw_mce_style_formats', $style_formats );

	$args['style_formats'] = json_encode( $style_formats );
	
	return $args;
}