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
				<p class="registration">The ClassicPress project is under the direction of The ClassicPress Initiative, a nonprofit organization registered under section 501(c)(3) of the United States IRS code.</p>
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
			<div class="footersponsor">
				<h3>Infrastructure Sponsor</h3>
				<div class="inf_sponsor">
					<a href="https://www.litespeedtech.com/" target="_blank" rel="external nofollow sponsored" title="ClassicPress Infrastructure Sponsor"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/litespeed-webserver-logo.svg" alt="Litespeed Web Server logo"></a>
				</div>
			</div>
		</div>
	</footer>
	<footer id="legal">
		<div class="cplegal">
			<div class="cpcopyright">
				<p>© 2018-<?php echo date("Y"); ?> ClassicPress. All Rights Reserved.</p>
			</div>
			<div class="cppolicy">
				<p><a href="https://www.classicpress.net/privacy-policy/">Privacy Policy</a></p>
			</div>
		</div>
	</footer>

</div>

<?php wp_footer(); ?>

</body>
</html>
