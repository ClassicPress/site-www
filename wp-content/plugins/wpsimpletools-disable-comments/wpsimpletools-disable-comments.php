<?php

/*
 * Plugin Name: WpSimpleTools Disable Comments
 * Description: Completely disables comments functionality from backend and frontend.
 * Author: WpSimpleTools
 * Author URI: https://profiles.wordpress.org/wpsimpletools/#content-plugins
 * Version: 1.0.4
 * Plugin Slug: wpsimpletools-disable-comments
 */
if (! defined('ABSPATH')) {
    die("Don't call this file directly.");
}

function wpst_dc_post_types_support() {

    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}

function wpst_dc_status() {

    return false;
}

function wpst_dc_hide_existing_comments($comments) {

    $comments = array();
    return $comments;
}

function wpst_dc_admin_menu() {

    remove_menu_page('edit-comments.php');
}

function wpst_dc_admin_menu_redirect() {

    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit();
    }
}

function wpst_dc_dashboard() {

    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}

function wpst_dc_admin_bar_render() {

    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}

function wpst_dc_no_wp_comments() {

    wp_die('No comments');
}

add_filter('comments_array', 'wpst_dc_hide_existing_comments', 10, 2); // Hide existing comments
add_filter('comments_open', 'wpst_dc_status', 20, 2); // Close comments on the front-end
add_filter('pings_open', 'wpst_dc_status', 20, 2);

add_action('admin_init', 'wpst_dc_admin_menu_redirect'); // Redirect any user trying to access comments page
add_action('admin_init', 'wpst_dc_dashboard'); // Remove comments metabox from dashboard
add_action('admin_menu', 'wpst_dc_admin_menu'); // Remove comments page in menu

add_action('admin_init', 'wpst_dc_post_types_support'); // Disable support for comments and trackbacks in post types
add_action('pre_comment_on_post', 'wpst_dc_no_wp_comments'); // Disables comments API
add_action('wp_before_admin_bar_render', 'wpst_dc_admin_bar_render');  // Remove comments links from admin bar
