<?php
/*
Plugin Name: Which Template File
Description: Plugin for developers. Display in the admin bar, the name of the template (PHP file) for this page. Display with different colors if the template owns to the current theme, the theme parent, or a plugin. An option page allows to chose if every logged user car see the template file in the admin-bar, or just the admnistrators of the website. (with "administrator" role)
Version: 4.5.0
Author: Gilles Dumas
Author URI: http://gillesdumas.com
*/

define('_WTF_OPTION_1', 'wtf_tpl_name_visibility');

/**
 * Display the name of the template used in the admin bar
 * 
 * @author Gilles Dumas <circusmind@gmail.com>
 * @since  20160229
 * @param  id    Integer l'identifiant du machin
 * @param  label String Le label à écrire
 * @return array()
 */
add_action('admin_bar_menu', 'gwp_my_admin_bar_menu', 9999);
function gwp_my_admin_bar_menu($wp_admin_bar) {
    
    if (is_admin()) {
        return;
    }
    
	global $user_ID, $template;
    
    if ($user_ID == 0) {
        return $template;
    }
    
    global $current_user;
    if (is_null($current_user)) {
        return;
    }
    
    $wtf_option_1 = get_option(_WTF_OPTION_1);
    if ($wtf_option_1 == 'administrator' || $wtf_option_1 == false) {
        // Alors le user connecté doit être administrator pour que l'on affiche le nom du tpl dans l'admin bar
        if (!in_array('administrator', $current_user->roles)) {
            return;
        }
    }
    
    if (strpos($template, '/') !== false) {
        $gwp_my_template_file = ltrim(strrchr($template, '/'), '/');
    }
    else {
        $gwp_my_template_file = $template;
    }
    
    // Check if the template is from the current theme, or from something else
    // (a plugin, a parent theme)
    
    $color = '';
    $theme = wp_get_theme();
    
    if (strpos($template, get_stylesheet_directory()) !== false) {
        // The template comes from the current theme
        $color = 'hotpink';
    }
    elseif (strpos($template, WP_PLUGIN_DIR) !== false) {
        // The template comes from a plugin
        $color = '#80ff00'; // green color
        $gwp_my_template_file.= ' &larr; plugin';
    }
    elseif (
        get_stylesheet_directory_uri() != get_template_directory_uri()  &&
        strpos($template, get_stylesheet_directory()) === false
    ) {
        // The template comes from the parent theme
        $color = '#00bfff'; // blue color
        $gwp_my_template_file.= ' &larr; parent theme';
    }
    
    $args = array(
        'id'      => '_gwp_my_template_file',
        'title'   => '<span id="gwp-wtf" style="color:'.$color.' !important;">'.$gwp_my_template_file.'</span>',
        'meta'   => array(
            'title' => $template,
            'class' => 'class_gwp_my_template_file'
        )
    );
    $wp_admin_bar->add_menu($args);
}


/**
 * Add a link in the plugin option page
 * 
 * @author Gilles Dumas <circusmind@gmail.com>
 * @since  20160518
 */
function wtf_add_action_links ( $links ) {    
    $mylinks = array('<a href="' . admin_url( 'admin.php?page=wtf_admin_page' ) . '">Settings</a>');
    return array_merge($links, $mylinks);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wtf_add_action_links');


/**
 * Admin page
 */
if (isset($_GET['page']) && ($_GET['page'] == 'wtf_admin_page')) {
    require('admin/class_page_admin.php');
    require('admin/add_menu_page.php');
}


/**
 * An action to add CSS to the <head> section
 * @author Gilles Dumas <circusmind@gmail.com>
 * @since  20160229
 */
add_action('wp_head', 'which_template_file_style');
function which_template_file_style() {
	?>
	<style type="text/css">
		.class_gwp_my_template_file {
			cursor:help;
		}
	</style>
	<?php
}





 