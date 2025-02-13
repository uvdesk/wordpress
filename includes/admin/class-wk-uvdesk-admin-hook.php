<?php
/**
 * Admin End Hooks.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WK_UVDESK\Templates\Admin as Template_Admin;

/** Check class exists or not */
if ( ! class_exists( 'WK_UVDESK_Admin_Hook' ) ) {
	/**
	 * Admin Hook class.
	 */
	class WK_UVDESK_Admin_Hook {
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
			$admin_handler = WK_UVDESK_Admin_Function::get_instance();

			add_action( 'admin_menu', array( $admin_handler, 'wk_uvdesk_admin_menu' ) );
			add_action( 'admin_init', array( $admin_handler, 'wk_uvdesk_default_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $admin_handler, 'wk_uvdesk_back_enqueue_script' ) );

			// Add settings for admin plugin settings.
			add_filter( 'plugin_action_links_' . WK_UVDESK_PLUGIN_BASENAME, array( $admin_handler, 'wk_uvdesk_add_plugin_setting_links' ) );
			add_filter( 'plugin_row_meta', array( $admin_handler, 'wk_uvdesk_plugin_row_meta' ), 10, 2 );
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
