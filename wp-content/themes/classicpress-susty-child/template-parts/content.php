<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Susty
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php if ( ! is_singular() ) : ?>
	<header class="blog">
		<?php the_title(
			'<h2><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">',
			'</a></h2>'
		); ?>
	</header>
<?php endif; ?>
	
	<?php susty_wp_post_thumbnail(); ?>

	<div>
		<?php
		if ( 'post' === get_post_type() ) :
		?>
			<p class="entry-meta">
				<!--span class="author-avatar">
					<?php //echo get_avatar( get_the_author_meta( 'ID' ), '50' ); ?>
				</span-->
				<span class="post-meta">
					<?php susty_wp_posted_on(); ?>
					<?php susty_wp_posted_by(); ?>
					<?php esc_html_e( ' | Category: ', 'susty' ); ?>
					<?php the_category(', '); ?>
				</span>
			</p><!-- .entry-meta -->
		<?php endif; ?>
		<?php
		the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'susty' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		) );

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'susty' ),
			'after'  => '</div>',
		) );
		?>
	</div>

	<footer>
	</footer>
</article><!-- #post-<?php the_ID(); ?> -->
