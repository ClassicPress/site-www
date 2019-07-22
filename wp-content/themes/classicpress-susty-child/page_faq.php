<?php
/*
Template Name: FAQ
*/

get_header();

?>
	<!--header id="page-title">
		<?php //the_title( '<h1>', '</h1>' ); ?>
	</header><!-- .entry-header -->
	<div id="primary">
		<main id="main" class="page-main">
		<?php susty_wp_post_thumbnail(); ?>
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

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
