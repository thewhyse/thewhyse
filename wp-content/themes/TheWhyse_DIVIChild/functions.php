<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */

add_action( 'wp_enqueue_scripts', 'Divi_child_style' );
function Divi_child_style() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css',array('parent-style'));
}

// smacss
function thewhyse_assets() {
	wp_register_style( 'thewhyse-stylesheet', get_theme_file_uri() . '/dist/css/bundle.css', array(), '1.0.0', 'all' );
	wp_enqueue_style('thewhyse-stylesheet');
	wp_enqueue_script('thewhyse_js', get_theme_file_uri() . '/dist/js/bundle.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'thewhyse_assets');
