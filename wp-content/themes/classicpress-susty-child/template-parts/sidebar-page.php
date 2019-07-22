<?php
$sidebarsearch = get_field('search_sidebar');
echo '<div id="sidebar" class="sidebar-page">';
	if ($sidebarsearch !== 'No') {
	echo '<div class = "widget-container"><form role="search" method="get" class="search-form" action="/">';
		echo '<label>';
			echo '<span class="screen-reader-text">Search for:</span>';
			echo '<input type="search" class="search-field" placeholder="Search &hellip;" value="" name="s" />';
		echo ' </label>';
		echo '<input type="submit" class="search-submit" value="Search" />';
	echo '</form></div>';
	}
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
			echo '<p>'.$sbnotetext.'</p>';
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
echo '</div>';/*sidebar-page*/
?>
