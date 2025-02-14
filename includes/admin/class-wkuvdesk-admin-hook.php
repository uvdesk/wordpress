<?php
/**
 * Admin End Hooks.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Templates\Admin as Template_Admin;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Admin_Hook' ) ) {
	/**
	 * Admin Hook class.
	 */
	class WKUVDESK_Admin_Hook {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Admin Functions Construct.
		 *
		 * @return void
		 */
		public function __construct() {
			$admin_handler = WKUVDESK_Admin_Function::get_instance();

			add_action( 'admin_menu', array( $admin_handler, 'wkuvdesk_admin_menu' ) );
			add_action( 'admin_init', array( $admin_handler, 'wkuvdesk_default_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $admin_handler, 'wkuvdesk_back_enqueue_script' ) );

			// Add settings for admin plugin settings.
			add_filter( 'plugin_action_links_' . WKUVDESK_PLUGIN_BASENAME, array( $admin_handler, 'wkuvdesk_add_plugin_setting_links' ) );
			add_filter( 'plugin_row_meta', array( $admin_handler, 'wkuvdesk_plugin_row_meta' ), 10, 2 );
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}
	}
}
