<?php
/**
 * WK_UVDESK handler final class.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**Check if class exists.*/
if ( ! class_exists( 'WK_UVDESK' ) ) {
	/**
	 * Final class WK_UVDESK.
	 */
	final class WK_UVDESK {
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
			$this->wk_uvdesk_define_constants();
			$this->wk_uvdesk_init_hooks();
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Defining plugin's constant.
		 *
		 * @return void
		 */
		public function wk_uvdesk_define_constants() {
			defined( 'WK_UVDESK_PLUGIN_URL' ) || define( 'WK_UVDESK_PLUGIN_URL', plugin_dir_url( __DIR__ ) );// Plugin url.
			defined( 'WK_UVDESK_VERSION' ) || define( 'WK_UVDESK_VERSION', '2.0.2' );// Plugin version.
			defined( 'WK_UVDESK_SCRIPT_VERSION' ) || define( 'WK_UVDESK_SCRIPT_VERSION', '2.0.2' );// Script version.
			defined( 'WK_UVDESK_DB_VERSION' ) || define( 'WK_UVDESK_DB_VERSION', '2.0.2' ); // Database version.
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @return void
		 */
		private function wk_uvdesk_init_hooks() {
			add_action( 'init', array( $this, 'wk_uvdesk_load_plugin_textdomain' ), 0 );
			add_action( 'plugins_loaded', array( $this, 'wk_uvdesk_load_plugin' ) );
		}

		/**
		 * Helper function to convert array to HTML attributes.
		 *
		 * @param array $attributes Array of attributes.
		 *
		 * @return mixed
		 */
		public static function wk_uvdesk_convert_attributes_to_html( $attributes ) {
			if ( is_array( $attributes ) ) {
				$spinner_url = admin_url( 'images/spinner-2x.gif' );
				$attributes  = array(
					'src'   => $spinner_url,
					'class' => 'uv-uvdesk-ajax-loader-img',
					'alt'   => __( 'Loading...', 'wk-uvdesk' ),
				);
			} else {
				$attributes = array(
					'src' => $attributes,
					'alt' => __( 'Loading...', 'wk-uvdesk' ),
				);
			}

			$html = '';
			foreach ( $attributes as $key => $value ) {
				$html .= sprintf( '%s="%s" ', esc_attr( $key ), esc_attr( $value ) );
			}
			return $html;
		}

		/**
		 * Load plugin text domain.
		 *
		 * @return void
		 */
		public function wk_uvdesk_load_plugin_textdomain() {
			load_plugin_textdomain( 'wk-uvdesk', false, plugin_basename( dirname( WK_UVDESK_PLUGIN_FILE ) ) . '/languages' ); // Plugin text domain.
		}

		/**
		 * Load native plugin files.
		 *
		 * @return void
		 */
		public function wk_uvdesk_load_plugin() {
			WK_UVDESK_File_Handler::get_instance(); // Load plugin.
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @return void
		 */
		public function __clone() {
			wp_die( __FUNCTION__ . esc_html__( 'Cloning is forbidden.', 'wk-uvdesk' ) );
		}

		/**
		 * Deserializing instances of this class is forbidden.
		 *
		 *  @return void
		 */
		public function __wakeup() {
			wp_die( __FUNCTION__ . esc_html__( 'Deserializing instances of this class is forbidden.', 'wk-uvdesk' ) );
		}
	}
}
