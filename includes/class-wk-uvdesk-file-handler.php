<?php
/**
 * WK_UVDESK_File_Handler handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WK_UVDESK\Includes\Admin;
use WK_UVDESK\Includes\Front;
use WK_UVDESK\Includes\Common;

/**Check if class exists.*/
if ( ! class_exists( 'WK_UVDESK_File_Handler' ) ) {
	/**
	 * Class WK_UVDESK_File_Handler.
	 */
	class WK_UVDESK_File_Handler {
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
			$this->wk_uvdesk_native_files();
		}

		/**
		 * Native files.
		 *
		 * @return void
		 */
		public function wk_uvdesk_native_files() {
			if ( is_admin() ) {
				Admin\WK_UVDESK_Admin_Hook::get_instance();
			} else {
				Front\WK_UVDESK_Front_Hook::get_instance();
			}

			Common\WK_UVDESK_Common_Hook::get_instance();
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
