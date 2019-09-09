<?php

/*
 * Removes Contact Form 7 scripts from all pages except where needed (currently 'contact').
 * If forms are added to other pages, need to add page slug to is_page() . For example:
 * is_page( array('contact', 'new-page') )
 * 
 * Sept 9 2019
 */

add_action( 'wp_print_scripts', 'cp_deregister_cf7_javascript', 100 );
function cp_deregister_cf7_javascript() {
    if ( ! is_page('contact') ) {
        wp_deregister_script( 'contact-form-7' );
    }
}

add_action( 'wp_print_styles', 'cp_deregister_cf7_styles', 100 );
function cp_deregister_cf7_styles() {
    if ( ! is_page('contact') ) {
        wp_deregister_style( 'contact-form-7' );
    }
}
