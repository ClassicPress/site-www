<?php
/**
 * Template Name: Post with Custom Sidebar
 * Template Post Type: post
 *
 * A template for displaying single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Susty
 */

get_header();
?>

	<div id="primary">
		<main id="main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation( array(
				'next_text' => __( 'Next post: %title <span class="screen-reader-text">Continue Reading</span>' ),
				'prev_text' => __( 'Previous post: %title <span class="screen-reader-text">Continue Reading</span>' ),
			) );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

		</main><!-- #main -->
		<?php get_template_part( 'template-parts/sidebar', 'page' ); ?>
	</div><!-- #primary -->
	

<?php
get_footer();
