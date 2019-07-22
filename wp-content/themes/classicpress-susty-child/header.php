<?php
/**
 * The header for our theme
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Susty
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<!--link rel="preload" href="<?php //echo home_url( '/wp-content/themes/classicpress-susty-child/fonts/DejaVuSans-webfont.woff2' ); ?>" as="font" type="font/woff2" crossorigin-->
	<link rel="preload" href="<?php echo home_url( '/wp-content/themes/classicpress-susty-child/fonts/source-sans-pro-v12-latin-600.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo home_url( '/wp-content/themes/classicpress-susty-child/fonts/source-sans-pro-v12-latin-regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo home_url( '/wp-content/themes/classicpress-susty-child/fonts/source-sans-pro-v12-latin-italic.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo home_url( '/wp-content/themes/classicpress-susty-child/images/logo-white.svg' ); ?>" as="image" type="image/svg+xml">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'susty' ); ?></a>

	<nav id="secondary-nav">
	<div class="soc2nav">
		<div class="hdrsocial">
		<?php 
		if(get_field('social_icons', 'options')):
			while(has_sub_fields('social_icons', 'options')):
				$socicon = get_sub_field('social_icon', 'options'); 
				$socname = get_sub_field('social_name', 'options');
				$soclink = get_sub_field('social_link', 'options'); 
					if (($socicon) && ($socname) && ($soclink)) {
					echo '<a href="'.$soclink.'" target="_blank" title="'.$socname.'" rel="noreferrer noopener">';
					echo $socicon;
					echo '</a>';
					}
			endwhile;
		endif;
		?>
		</div>
		<div class="smenu">
		<ul>
			<?php
			$donate = get_field('donate_link', 'option');
			$switch = get_field('migration_plugin', 'option');
			$getcp = get_field('get_classicpress', 'option');
			echo '<li><a href="'.$donate.'" target="_blank" rel="noreferrer noopener">Donate</a></li>';
			echo '<li class="switchbutton migrate">'.$switch.'</li>';
			echo '<li class="switchbutton download">'.$getcp.'</li>';
			?>
		</ul>
		</div>
	</div>
	</nav>
	<?php if ( is_front_page() ) { 
	echo '<section class="home-hero-container">';
	} ?>
	<header id="masthead">
		<div id="inner-header">
			<span class="logo" role="banner">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo home_url( '/wp-content/themes/classicpress-susty-child/images/logo-white.svg' ); ?>" width="250" alt="ClassicPress logo"> <span class="screen-reader-text"><?php esc_html_e( 'Home', 'susty' ); ?></span></a>
			</span>

			<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Main menu">
				<?php
				/*wp_nav_menu( array(
					'theme_location' => 'Primary',
					'menu_id'        => 'primary-menu',
				) ); broke this when renamed menu-1 in menu admin; renaming it back did not fix*/
				?>	
				<?php 
				$navcheck = '' ;
				$navcheck = wp_nav_menu( array( 
					'theme_location' => 'main-menu', 
					'depth' => 2, 
					'menu_id' => 'primary-menu', /*keeping original id so nav css and js still works*/
					'fallback_cb' => '', 
					'echo' => false ) ); 
				if ($navcheck == '') { 
					echo '<ul>';
					wp_list_pages('title_li=&sort_column=menu_order');
					echo '</ul>';
				} else {
					echo($navcheck); 
				}
				?>
			</nav><!-- #site-navigation -->
			
			<!--a href="<?php //echo home_url( '/download/' ); ?>" class="button get-started"><?php //esc_html_e( 'Get Started', 'susty' ); ?></a-->
			
		</div>
	</header>
	<? if(!is_front_page()) { 
			echo '<header id="page-title">';
			if (is_blog()) {
			echo '<h1>';
			esc_html_e( 'ClassicPress Blog', 'susty' );
			echo '</h1>';
			} elseif (is_search()) {
			echo '<h1>';
			esc_html_e( 'Search Results', 'susty' );
			echo '</h1>';
			} else {
			the_title( '<h1>', '</h1>' );
			}
			echo '</header><!-- .entry-header -->';
		} 
	?>
	<div id="content" role="main">
