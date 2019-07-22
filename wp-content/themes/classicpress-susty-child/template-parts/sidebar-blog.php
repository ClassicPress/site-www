<?php
echo '<div id="sidebar" class="sidebar-blog">';
if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Blog Sidebar') ) : 
endif;
echo '</div>';
?>
