<?php

/*
Plugin Name: Add Author Info to Posts
Description: Like the name says; from a WP Beginner post with a few modifications.
Version: 1.0
License: GPL
Author: WP Beginner
Author URI: https://www.wpbeginner.com/wp-tutorials/how-to-add-an-author-info-box-in-wordpress-posts/
*/

function wpb_author_info_box( $content ) {
 
global $post;
 
// Detect if it is a single post with a post author
if ( is_single() && isset( $post->post_author ) ) {
 
// Get author's display name 
$display_name = get_the_author_meta( 'display_name', $post->post_author );
 
// If display name is not available then use nickname as display name
if ( empty( $display_name ) )
$display_name = get_the_author_meta( 'nickname', $post->post_author );
 
// Get author's biographical information or description
$user_description = get_the_author_meta( 'user_description', $post->post_author );
 
// Get author's website URL 
$user_website = get_the_author_meta('url', $post->post_author);
 
// Get link to the author archive page
$user_posts = get_author_posts_url( get_the_author_meta( 'ID' , $post->post_author));

$author_details = '';
  
/*if ( ! empty( $display_name ) )
 
$author_details .= '<h5 class="author_name">About ' . $display_name . '</h5>';*/
 
if ( ! empty( $user_description ) && ( ! empty( $display_name ) ) )
// Author avatar and bio
 
$author_details .= '<div class="author_details">' . get_avatar( get_the_author_meta('user_email') , 90 ) . nl2br( '<div class="authtext"><h5 class="author_name">About ' . $display_name . '</h5><p>'.$user_description.'</p>' );
 
$author_details .= '<p class="author_links"><a href="'. $user_posts .'">View all posts by ' . $display_name . '</a>';  
 
// Check if author has a website in their profile
if ( ! empty( $user_website ) ) {
 
// Display author website link
$author_details .= ' | <a href="' . $user_website .'" target="_blank" rel="noreferrer noopener">Website</a></p></div></div>';
 
} else { 
// if there is no author website then just close the paragraph
$author_details .= '</p></div></div>';
}
 
// Pass all this info to post content  
$content = $content . '<div class="author_bio_section" >' . $author_details . '</div>';
}
return $content;
}
 
// Add our function to the post content filter 
add_action( 'the_content', 'wpb_author_info_box' );
 
// Allow HTML in author bio section 
remove_filter('pre_user_description', 'wp_filter_kses');
