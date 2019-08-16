<?php
/**
 * The template for displaying the footer
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Susty
 */
?>

	</div>

	<footer id="colophon">
		<div class="classic">
			<div class="footerleft">
				<a href="/"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/icon-white.svg" alt="ClassicPress" /></a>
				<p class="registration">ClassicPress is a company limited by guarantee with registration number 11549088.</p>
				<ul class="nav">
					<li><a href="/contact/">Contact Us</a></li>
				</ul>
			</div>
			<div class="footerright">
				<?php
				$footmenu = wp_nav_menu( array(
					'theme_location' => 'footer-menu',
					'depth' => 1,
					'menu_id' => 'footmenu',
					'menu_class' => 'nav'
				) );
				if ($footmenu) {
					echo($footmenu);
				}
				?>
			</div>
		</div>
	</footer>
	<footer id="legal">
		<div class="cplegal">
			<div class="cpcopyright">
				<p>© 2018-<?php echo date("Y"); ?> ClassicPress. All Rights Reserved.</p>
			</div>
			<div class="cppolicy">
				<p><a href="https://www.iubenda.com/privacy-policy/41030260" target="_blank" rel="noopener noreferrer">Privacy Policy</a> | <a href="https://www.iubenda.com/privacy-policy/41030260/cookie-policy" target="_blank" rel="noopener noreferrer">Cookie Policy</a></p>
			</div>
		</div>
	</footer>

</div>

<?php wp_footer(); ?>

</body>
</html>
