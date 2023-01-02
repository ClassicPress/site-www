<?php
/*
Template Name: Home (Front) Page
*/

get_header();

?>


	<?php //HOME HERO
	$herotitle = get_field('hero_maintitle');
	$herosubtitle = get_field('hero_subtitle');
	$herotext = get_field('hero_text');
	$imgvid = get_field('image_or_video');
	$video = get_field('video_embed');
	$heroimage = get_field('hero_image');
	$heroalt = get_field('hero_imagealt');
	if ($herotitle) {
		echo '<div class="home-hero-container">';
		echo '<div class="home-hero">';

		echo '<div class="home-hero-text">';
			echo '<h1>'.$herotitle.'</h1>';
			if ($herosubtitle) {
			echo '<h2>'.$herosubtitle.'</h2>';
			}
			if ($herotext) {
			echo '<h3>'.$herotext.'</h3>';
			}
		
		?>
		<div class="cta-btns">
		<?php
				$featurelink = get_field('feature_link');
				$featurelinktext = get_field('feature_link_text');
				if (($featurelink) && ($featurelinktext)) {
				echo '<p class="button purple migrate"><a href="'.$featurelink.'">'.$featurelinktext.'</a></p>';
				}
			?>
			<p><a href="/get-classicpress/#switch-to-classicpress">Switch From WordPress</a></p>
		</div>
		<?php echo '</div>';
		
		echo '<div class="home-hero-image">';
		if (($imgvid == 'Video') && ($video)) {
			echo '<div class="embed-container">'.$video.'</div>';
		} elseif (($imgvid == 'Image') && ($heroimage)) {
			echo '<img src="'.$heroimage.'" alt="'.$heroalt.'">';
		}
		echo '</div>';
		;echo '</div><!-- .home-hero -->';
		echo '</div><!-- .home-hero-container -->';
	}
	// (conditional opening <section> tag in header.php template)
	?>

<section class="homepanel2">
	<article class="features-home">
		<div class="feature">
		<?php
			$featicon1 = get_field('feature1_icon');
			$feathead1 = get_field('feature1_head');
			$feattext1 = get_field('feature1_text');
			if ($feathead1) {
				if ($featicon1) {
					echo '<div class="ficon">';
					echo $featicon1;
					echo '</div>';
				}

				echo '<h2>'.$feathead1.'</h2>';
			}
			if ($feattext1) {
				echo '<p>'.$feattext1.'</p>';
			}
		?>
		</div>
		<div class="feature">
		<?php
			$featicon2 = get_field('feature2_icon');
			$feathead2 = get_field('feature2_head');
			$feattext2 = get_field('feature2_text');
			if ($feathead2) {
				if ($featicon2) {
					echo '<div class="ficon">';
					echo $featicon2;
					echo '</div>';
				}
				echo '<h2>'.$feathead2.'</h2>';
			}
			if ($feattext2) {
				echo '<p>'.$feattext2.'</p>';
			}
		?>
		</div>
		<div class="feature">
		<?php
			$featicon3 = get_field('feature3_icon');
			$feathead3 = get_field('feature3_head');
			$feattext3 = get_field('feature3_text');
			if ($feathead3) {
				if ($featicon3) {
					echo '<div class="ficon">';
					echo $featicon3;
					echo '</div>';
				}
				echo '<h2>'.$feathead3.'</h2>';
			}
			if ($feattext3) {
				echo '<p>'.$feattext3.'</p>';
			}
		?>
		</div>
	</article>
</section>

<section class="homepanel1">
	<article class="community-home">
	<?php echo get_post_field('post_content'); ?>
	</article>
</section>

<section class="homepanel4">
	<article class="involve-home">
		<?php
		$invhead = get_field('involved_head');
		$invtext = get_field('involved_text');
		if ($invhead) {
		echo '<h2>'.$invhead.'</h2>';
		}
		if ($invtext) {
		echo $invtext;
		}
		?>
	</article>
	<div class="getinvolved">
		<?php
		if(get_field('involved_buttons')) {
			while(has_sub_fields('involved_buttons')) {
			$intext = get_sub_field('internal_or_external');
			$extinv = get_sub_field('ext_involvlink');
			$intinv = get_sub_field('int_involvlink');
			$invbut = get_sub_field('involvbutton_text');
				if (($intext == 'Internal') && ($intinv) && ($invbut)) {
					echo '<p class="button purple"><a href="'.$intinv.'">'.$invbut.'</a></p>';
				} elseif (($intext == 'External') && ($extinv) && ($invbut)) {
					echo '<p class="button purple"><a href="'.$extinv.'" target="_blank" rel="noreferrer noopener">'.$invbut.'</a></p>';
				}
			}
		}
		?>
	</div>
</section>

<section class="sponsors involve-home">
	<h2>Sponsors</h2>
		<div class="sponsors-inner">
			<div class="sponsor">
				<a href="https://www.brinkhost.nl/" target="_blank" rel="external nofollow sponsored" title="ClassicPress Sponsor"><img src="/wp-content/uploads/2022/07/brinkman-it-sponsor.png" alt="Brinkhost logo"></a>
			</div>
			<div class="sponsor">
				<a href="https://www.tukutoi.com/" target="_blank" rel="external nofollow sponsored" title="ClassicPress Sponsor"><img src="/wp-content/uploads/2022/07/tukutoi-sponsor.png" alt="TukuToi logo"></a>
			</div>
		</div>
		<a href="https://opencollective.com/classicpress/contribute" target="_blank" title="Become a ClassicPress sponsor">Become a Sponsor</a>
</section>

<?php

get_footer();

?>
