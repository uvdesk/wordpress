<?php
/**
 * Front function handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Includes\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WK_UVDESK_Front_Hook' ) ) {
	/**
	 * WK_UVDESK_Front_Hook class.
	 */
	class WK_UVDESK_Front_Hook {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			$front_function = WK_UVDESK_Front_Function::get_instance();
			add_action( 'wp_logout', array( $front_function, 'wk_uvdesk_redirect_after_logout' ) );
			add_action( 'template_redirect', array( $front_function, 'wk_uvdesk_calling_pages' ) );
			add_action( 'template_redirect', array( $front_function, 'wk_uvdesk_template_redirect' ) );
			add_action( 'wp_login_failed', array( $front_function, 'wk_uvdesk_front_end_login_fail' ) );
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
