<?php

/*
Plugin Name: Evo Better CMS
Description: Several enhancements and simplifications that make WordPress a better CMS
Version: 1.0
License: GPL
Author: Ray Gulick, Evo Web Dev
Author URI: http://www.evowebdev.com
*/


/*** Add message to post thumbnail meta box 
function swd_admin_post_thumbnail_add_label($content, $post_id, $thumbnail_id)
{
    $post = get_post($post_id);
    if ($post->post_type == 'post') {
        $content .= '<p><i>Ideal size: 800 x 471 pixels!!!</i></p>';
        return $content;
    }

    return $content;
}
add_filter('admin_post_thumbnail_html', 'swd_admin_post_thumbnail_add_label', 10, 3);***/

/***Remove junk from head
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); */

/***Disable Default Dashboard Widgets
@ http://digwp.com/2014/02/disable-default-dashboard-widgets/ 
function disable_default_dashboard_widgets() {
	global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
		// yoast seo
		//unset($wp_meta_boxes['dashboard']['normal']['core']['yoast_db_widget']);
		// gravity forms
		//unset($wp_meta_boxes['dashboard']['normal']['core']['rg_forms_dashboard']);
}
add_action('wp_dashboard_setup', 'disable_default_dashboard_widgets', 999); */

/*** REMOVE META BOXES FROM DEFAULT POSTS SCREEN
function remove_default_post_screen_metaboxes() {
	remove_meta_box( 'postcustom','post','normal' ); //Custom Fields Metabox
	remove_meta_box( 'commentstatusdiv','post','normal' ); // Comments Metabox
	remove_meta_box( 'trackbacksdiv','post','normal' ); // Talkback Metabox
	//remove_meta_box( 'authordiv','post','normal' ); // Author Metabox
	//remove_meta_box( 'slugdiv','post','normal' ); // Slug Metabox
	remove_meta_box( 'revisionsdiv','post','normal' ); // Revisions Metabox
}
add_action('admin_menu','remove_default_post_screen_metaboxes'); */

/*** REMOVE META BOXES FROM DEFAULT PAGES SCREEN
function remove_default_page_screen_metaboxes() {
	global $post_type;
	remove_meta_box( 'postcustom','page','normal' ); // Custom Fields Metabox
	remove_meta_box( 'commentstatusdiv','page','normal' ); // Comments Metabox
	remove_meta_box('commentsdiv','page','normal'); // Comments
	remove_meta_box( 'trackbacksdiv','page','normal' ); // Talkback Metabox
	//remove_meta_box( 'slugdiv','page','normal' ); // Slug Metabox
	remove_meta_box( 'authordiv','page','normal' ); // Author Metabox
	remove_meta_box( 'revisionsdiv','page','normal' ); // Revisions Metabox
}
add_action('admin_menu','remove_default_page_screen_metaboxes'); */

/*****CHANGE EXCERPT METABOX TITLE
add_filter( 'gettext', 'wpse22764_gettext', 10, 2 );
function wpse22764_gettext( $translation, $original ) {
    if ( 'Excerpt' == $original ) {
        return 'Listing Excerpt';
    } else {
        $pos = strpos($original, 'Excerpts are optional hand-crafted summaries of your');
        if ($pos !== false) {
            return  'Excerpts appear in listings of pages and posts. Without an excerpt here, WordPress will insert the first 30 words of the main text (which might not be ideal for the listing).';
        }
    }
    return $translation;
} */

/****Remove menu items from admin bar
add_action( 'admin_bar_menu', 'wpss_admin_bar_menu', 100 );
function wpss_admin_bar_menu() {
	global $wp_admin_bar;
	//$wp_admin_bar->remove_menu( 'dashboard' );
	//$wp_admin_bar->remove_menu( 'themes' );
	//$wp_admin_bar->remove_menu( 'widgets' );
	//$wp_admin_bar->remove_menu( 'menus' );
	$wp_admin_bar->remove_menu( 'comments' );
	$wp_admin_bar->remove_menu( 'updates' );
	$wp_admin_bar->remove_menu( 'new-post' );
	$wp_admin_bar->remove_menu( 'new-media' );
	$wp_admin_bar->remove_menu( 'new-link' );
	$wp_admin_bar->remove_menu( 'new-user' );
	$wp_admin_bar->remove_menu( 'new-theme' );
	$wp_admin_bar->remove_menu( 'new-plugin' );
	$wp_admin_bar->remove_menu( 'customize' );
	$wp_admin_bar->remove_menu( 'menu-toggle' ); //for use with OZH; prevents empty toggle
} */

/***Gravity Forms - Remove add 'New Form' from +New submenu (technique above doesn't work?) 
add_action( 'wp_before_admin_bar_render', 'remove_wp_logo', 999 );
function remove_wp_logo() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_node( 'gravityforms-new-form' );
} */

// remove empty paragraph tags
add_filter('the_content', 'remove_empty_p', 20, 1);
function remove_empty_p($content){
	$content = force_balance_tags($content);
	return preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
}

//remove autop filter
remove_filter('the_content', 'wpautop');
remove_filter('the_excerpt', 'wpautop');

/**disable default image link
function wpb_imagelink_setup() {
	$image_set = get_option( 'image_default_link_type' );
	if ($image_set !== 'none') {
		update_option('image_default_link_type', 'none');
	}
} */

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

/* remove the 'Basic' toolbar completely
	unset( $toolbars['Basic' ] );
	return $toolbars; */
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

?>