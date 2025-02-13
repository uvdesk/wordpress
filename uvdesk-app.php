<?php
/**
 * Plugin Name: UVdesk Free Helpdesk
 * Plugin URI: https://wordpress.org/plugins/uvdesk/
 * Description: WordPress Uvdesk ticket system will integrate symphony based ticket in WordPress framework using symfony api.
 * Version: 2.0.2
 * Author: Webkul
 * Author URI: http://webkul.com
 * Text Domain: wk-uvdesk
 * Domain Path: /languages
 *
 * Requires at least: 6.5
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * Tested up to PHP: 8.3
 *
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Store URI: https://wordpress.org/plugins/uvdesk/
 * Blog URI: https://webkul.com/blog/wordpress-helpdesk-plugin/
 *
 * @package UVdesk Free Helpdesk
 **/

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WK_UVDESK\Includes;

// Define Constants.
defined( 'WK_UVDESK_PLUGIN_FILE' ) || define( 'WK_UVDESK_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
defined( 'WK_UVDESK_PLUGIN_BASENAME' ) || define( 'WK_UVDESK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load core auto-loader.
require __DIR__ . '/autoloader/class-wk-uvdesk-autoload.php';

// Include the main class.
if ( ! class_exists( 'WK_UVDESK', false ) ) {
	include_once WK_UVDESK_PLUGIN_FILE . '/includes/class-wk-uvdesk.php';
	Includes\WK_UVDESK::get_instance();
}

// Include the main installation class.
if ( ! class_exists( 'WK_UVDESK_Install', false ) ) {
	Includes\WK_UVDESK_Install::get_instance();
}

/**
 * Custom page rules for custom post type.
 *
 * @param array $rules Rules.
 *
 * @return array
 */
function wk_uvdesk_insert_custom_rules( $rules ) {
	$newrules = array(
		'(.+)/([a-z]+)/ticket/([a-z]+)/([0-9]+)/page/([0-9]+)/?$' => 'index.php?pagename=$matches[1]&main_page=$matches[2]&type=ticket&action=$matches[3]&tid=$matches[4]&pagination=page&paged=$matches[5]',
		'(.+)/([a-z]+)/ticket/([a-z]+)/([0-9]+)/?$' => 'index.php?pagename=$matches[1]&main_page=$matches[2]&type=ticket&action=$matches[3]&tid=$matches[4]',
		'(.+)/([a-z]+)/page/([0-9]+)/?$'            => 'index.php?pagename=$matches[1]&main_page=$matches[2]&pagination=page&paged=$matches[3]',
		'(.+)/([a-z]+)/create-ticket/?$'            => 'index.php?pagename=$matches[1]&main_page=$matches[2]&create=create-ticket',
		'(.+)/([a-z]+)/([0-9]+)/?$'                 => 'index.php?pagename=$matches[1]&main_page=$matches[2]&aid=$matches[3]',
		'my-account/(.+)/?$'                        => 'index.php?pagename=my-account&$matches[1]=$matches[1]',
		'(.+)/([a-z]+)/?$'                          => 'index.php?pagename=$matches[1]&main_page=$matches[2]',
		'(.+)/?$'                                   => 'index.php?pagename=$matches[1]',
	);

	return $newrules + $rules; // Your rules at the top.
}
add_filter( 'rewrite_rules_array', 'wk_uvdesk_insert_custom_rules', 10, 1 );

/**
 * Intercept query vars.
 *
 * @param array $vars Query vars.
 *
 * @return array
 */
function wk_uvdesk_insert_custom_var( $vars ) {
	$vars[] = 'pagename';
	$vars[] = 'main_page';
	$vars[] = 'type';
	$vars[] = 'action';
	$vars[] = 'tid';
	$vars[] = 'pagination';
	$vars[] = 'paged';
	$vars[] = 'create';
	$vars[] = 'aid';

	return $vars;
}
add_filter( 'query_vars', 'wk_uvdesk_insert_custom_var', 10, 1 );
