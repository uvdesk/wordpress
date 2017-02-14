<?php 

/**
 * WooCommerce Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author 		webkul
 * @category 	Core
 * @package 	uvdesk-app/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function uvdesk_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );
 
	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';
 
	// Allow template parts to be filtered
	$templates = apply_filters( 'uvdesk_get_template_part', $templates, $slug, $name );
 
	// Return the part that is found
	return uvdesk_locate_template( $templates, $load, false );
}


/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function uvdesk_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;
 
	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) { 
		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;
 
		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );
 
		// Check child theme first
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'templates/' . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . 'templates/' . $template_name;
			break;
 
		// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'templates/' . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . 'templates/' . $template_name;
			break;
 
		// Check theme compatibility last
		} elseif ( file_exists( realpath(dirname(__FILE__) . '/..') . '/templates/'. $template_name ) ) {
			$located = realpath(dirname(__FILE__) . '/..') . '/templates/'. $template_name;
			break;
		}
	} ; 
	if ( ( true == $load ) && ! empty( $located ) ){
		if ( $require_once ) {
			require_once( $located );
		} else {
			require( $located );
		}
	}
 
	return $located;
}
?>