<?php

/**
 * Stylesheet version (cache buster)
 */
function cp_susty_get_asset_version() {
	return '20190830.6';
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


/***load Font Awesome scripts***/
function enqueue_load_fa() {
wp_enqueue_script( 'load-fa', 'https://kit.fontawesome.com/6152c16332.js' );
//wp_enqueue_script( 'load-fa', 'https://use.fontawesome.com/releases/latest/js/all.js');
}
add_action( 'wp_enqueue_scripts', 'enqueue_load_fa' );

function enqueue_fa() {
    wp_enqueue_style('font-awesome-v5', '/wp-content/themes/classicpress-susty-child/font-awesome-v5/css/fontawesome-all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_fa');

/****Add widgets to blog sidebar***/
if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'id' => 'blog-sidebar',
    'name' => 'Blog Sidebar',
    'before_widget' => '<div class = "widget-container">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>',
  )
);
/** Modify Featured Image Text **/
function filter_featured_image_admin_text( $content, $post_id, $thumbnail_id ){
    $help_text = '<p>' . __( '<i>Ideal size is 800 x 471 pixels.</i>', 'ClassicPress' ) . '</p>';
    return $help_text . $content;
}
add_filter( 'admin_post_thumbnail_html', 'filter_featured_image_admin_text', 10, 3 );

/****simplify blog detection*********/
function is_blog () {
    return ( is_archive() || is_author() || is_category() || is_home() || is_single() || is_tag()) && 'post' == get_post_type();
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

/* Add Twitter card tags for social sharing. */
add_action( 'wp_head', 'cp_insert_twittercard_tags', 0 );
function cp_insert_twittercard_tags() {

	// Bring $post object into scope.
	global $post;

	// Set defaults for Twitter shares.
	$url   = get_bloginfo( 'url' );
	$title = get_bloginfo( 'name' );
	$desc  = get_bloginfo( 'description' );
	$image = 'https://docs.classicpress.net/wp-content/classicpress/logos/icon-gradient-600.png';

	// If on a post or page, reset defaults.
	if( is_single() || is_page() ) {

		// Update URL to current post/page.
		$url = get_permalink();

		// Update title only if $post has non-empty title.
		$title = ( get_the_title() ) ? get_the_title() : $title;

		// Update description only if $post has non-empty excerpt.
		if ( ! empty( $post->post_excerpt ) ) {
			$desc = $post->post_excerpt;
		}

		// Update image if post/page has a thumbnail.
		if ( has_post_thumbnail() ) {
			$image_properties = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) , 'medium_large' );
			$image = $image_properties[0];
		}

	}

	// Assemble the meta tag markup.
	$markup  = '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	$markup .= '<meta name="twitter:url" content="' . esc_attr( $url ) . '" />' . "\n";
	$markup .= '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	$markup .= '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	$markup .= '<meta name="twitter:image" content="' . esc_attr( $image ) . '" />' . "\n";
	$markup .= '<meta name="twitter:image:alt" content="' . esc_attr( $title ) . '" />' . "\n";
	$markup .= '<meta name="twitter:site" content="@getclassicpress" />' . "\n";
	// Add creator tag if author profile has a Twitter username.
	if ( get_the_author_meta( 'twitter' ) ) {
		$markup .= (
			'<meta name="twitter:creator" content="@'
			. esc_attr( str_replace( '@', '', get_the_author_meta( 'twitter' ) ) )
			. '" />'
			. "\n"
		);
	}
	// Print the tags.
	echo $markup;
}

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
