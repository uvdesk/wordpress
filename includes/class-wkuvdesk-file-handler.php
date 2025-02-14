<?php
/**
 * WKUVDESK_File_Handler handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Includes\Admin;
use WKUVDESK\Includes\Front;
use WKUVDESK\Includes\Common;

/**Check if class exists.*/
if ( ! class_exists( 'WKUVDESK_File_Handler' ) ) {
	/**
	 * Class WKUVDESK_File_Handler.
	 */
	class WKUVDESK_File_Handler {
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
				Admin\WKUVDESK_Admin_Hook::get_instance();
			} else {
				Front\WKUVDESK_Front_Hook::get_instance();
			}

			Common\WKUVDESK_Common_Hook::get_instance();
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
