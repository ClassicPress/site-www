<?php
echo '<div id="sidebar" class="sidebar-page">';
	echo '<div class="sidebar-internal">';
		$sbquote = get_field('sb_quote');
		$sbquoteauth = get_field('sbquote_author');
		$sbqlocation = get_field('sbquote_location');
		$sbnotehd = get_field('sbnote_heading');
		$sbnotetext = get_field('sbnote_text');
		$sbnotelink = get_field('sbnote_link');
		$sbnotextlink = get_field('sbnote_extlink');
		$sbbutton = get_field('sbnote_button');
		$sbnotehdnobox = get_field('sbnote_headingnobox');
		$sbnotetextnobox = get_field('sbnote_textnobox');
		$sbnotelinknobox = get_field('sbnote_linknobox');
		$sbbuttonnobox = get_field('sbnote_buttonnobox');
		if (($sbquote) && ($sbquoteauth)) {
			echo '<div class="sidebarquote">';
			echo '<p>'.$sbquoteauth;
				if ($sbqlocation) {
					echo '<span class="qlocation">'.$sbqlocation.'</span>';
				}
			echo '</p>';
			echo '</div>';
		}
		if (($sbnotehd) || ($sbnotetext)) {
		echo '<div class="sidebar-note">';
			if ($sbnotehd) {
			echo '<h3>'.$sbnotehd.'</h3>';
			}
			if ($sbnotetext) {
			echo $sbnotetext;
			}
			if ($sbnotextlink) {
			echo '<p class="readmore"><a href="'.$sbnotextlink.'" target="_blank" rel="noreferrer noopener">'.$sbbutton.'</a></p>';
			} elseif ($sbnotelink) {
			echo '<p class="readmore"><a href="'.$sbnotelink.'">'.$sbbutton.'</a></p>';
			}
		echo '</div>';
		}
		if (($sbnotehdnobox) || ($sbnotetextnobox)) {
		echo '<div class="sidebar-nobox">';
			if ($sbnotehdnobox) {
			echo '<h3>'.$sbnotehdnobox.'</h3>';
			}
			if ($sbnotetextnobox) {
			echo '<p>'.$sbnotetextnobox.'</p>';
			}
			if ($sbnotelinknobox) {
			echo '<p class="readmore"><a href="'.$sbnotelinknobox.'">'.$sbbuttonnobox.'</a></p>';
			}
		echo '</div>';
		}
	echo '</div>';/*sidebar-internal*/

	$is_hidden = get_field('hide_global_sidebar');
	if ( empty($is_hidden) ){
		if ( is_active_sidebar( 'main-sidebar' ) ) : 
				dynamic_sidebar( 'main-sidebar' );
		endif;
	}
echo '</div>';/*sidebar-page*/
