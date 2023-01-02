<?php

/**
 * Stylesheet version (cache buster)
 */
function cp_susty_get_asset_version() {
	return '20210908';
}


/**
 * Enqueue scripts and styles
 */
function cp_susty_enqueue_assets() {
	wp_enqueue_style(
		'cp-susty-parent-style',
		get_template_directory_uri() . '/style.css',
		[],
		cp_susty_get_asset_version()
	);

	wp_enqueue_script(
		'cp-susty-script',
		get_stylesheet_directory_uri() . '/script.js',
		array( 'jquery' ),
		cp_susty_get_asset_version()
	);
}
add_action( 'wp_enqueue_scripts', 'cp_susty_enqueue_assets' );


/*Register new menu to replace 'Primary'*/
unregister_nav_menu( 'Primary', 'susty' ); /*should unregister problem menu, but does not*/
register_nav_menus( array(
	'main-menu' => __( 'MainMenu', 'susty' ),
	'footer-menu' => __( 'FooterMenu', 'susty' ),
) );



/****Add widgets to blog sidebar***/
if ( function_exists('register_sidebar') ){
  register_sidebar(array(
    'id' => 'blog-sidebar',
    'name' => 'Blog Sidebar',
    'before_widget' => '<div class = "widget-container">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>',
  ));
  register_sidebar(array(
    'id' => 'main-sidebar',
    'name' => 'Main Sidebar',
    'before_widget' => '<div class = "widget-container">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>',
  ));
};



/** Modify Featured Image Text **/
function filter_featured_image_admin_text( $content, $post_id, $thumbnail_id ){
    $help_text = '<p>' . __( '<i>Ideal size is 800 x 471 pixels.</i>', 'ClassicPress' ) . '</p>';
    return $help_text . $content;
}
add_filter( 'admin_post_thumbnail_html', 'filter_featured_image_admin_text', 10, 3 );


/****simplify blog detection*********/
function is_blog () {
    return ( is_archive() || is_author() || is_category() || is_home() || is_tag()) && 'post' == get_post_type();
}


/**
 * Set our own version string for the theme's stylesheet
 */
function cp_susty_override_style_css_version( $version, $type, $handle ) {
	if ( $type !== 'style' || $handle !== 'susty-style' ) {
		return $version;
	}
	return cp_susty_get_asset_version();
}
add_filter( 'classicpress_asset_version', 'cp_susty_override_style_css_version', 10, 3 );


/*
 * Add the page slug as a class to the <body>
 * Gives greater flexibility for styling
 */
function cp_add_page_slug_body_class( $classes ) {
    global $post;
    if ( isset( $post ) ) {
        $classes[] = 'page-' . $post->post_name;
    }
    return $classes;
}
add_filter( 'body_class', 'cp_add_page_slug_body_class' );
