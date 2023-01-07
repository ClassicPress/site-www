<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Susty
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!--header>
		<?php //the_title( '<h1>', '</h1>' ); ?>
	</header--><!-- .entry-header -->

	<div id="page-content">
		<?php
		the_content();
		if (is_page_template('page_faq.php')) {
			if(get_field('q_a')) {
				while(has_sub_fields('q_a')) {
				$question = get_sub_field('question');
				$answer = get_sub_field('answer');
					if (($question) && ($answer)) {
					echo '<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"><p class="question"><span itemprop="name">'.$question.'</span></p>';
					echo '<div class="toggle" style="display: none;" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><span itemprop="text">'.$answer.'</span></div></div>';
					}	
				}
			}
		} //if is faq template

		if (is_page_template('page_democracy.php')) {
			if(get_field('dem_cat')) {
				while(has_sub_fields('dem_cat')) {
				$demcategory = get_sub_field('dem_category');
				$demdescript = get_sub_field('dem_desc');
				$demrights = get_sub_field('dem_rights');
				$demrespons = get_sub_field('dem_resp');
					if ($demcategory) {
					echo '<h3 class="demcat">'.$demcategory.'</h3>';
					echo '<div class="toggle dem">';
						echo '<div class="demblock">';
						echo '<h4>Description</h4>';
						if ($demdescript) {
							echo $demdescript;
						}
						echo '</div>';
						echo '<div class="demblock">';
						echo '<h4>ClassicPress Rights</h4>';
						if ($demrights) {
							echo $demrights;
						}
						echo '</div>';
						echo '<div class="demblock">';
						echo '<h4>ClassicPress Responsibilities</h4>';
						if ($demrespons) {
							echo $demrespons;
						}
						echo '</div>';
					echo '</div>';
					}	
				}
			}
			$textbelow = get_field('text_below');
			if ($textbelow) {
				echo $textbelow;
			}
		} //if is democracy template

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'susty' ),
			'after'  => '</div>',
		) );
		?>
	</div>

	<?php if ( get_edit_post_link() ) : ?>
		<p class="edit-link">
			<?php
			edit_post_link(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Edit <span class="screen-reader-text">%s</span>', 'susty' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)//,
				//'<span class="edit-link">',
				//'</span>'
			);
			?>
		</p>
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->
