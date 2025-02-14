<?php
/**
 * WKUVDESK handler final class.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**Check if class exists.*/
if ( ! class_exists( 'WKUVDESK' ) ) {
	/**
	 * Final class WKUVDESK.
	 */
	final class WKUVDESK {
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
			$this->wkuvdesk_define_constants();
			$this->wkuvdesk_init_hooks();
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
		public function wkuvdesk_define_constants() {
			defined( 'WKUVDESK_PLUGIN_URL' ) || define( 'WKUVDESK_PLUGIN_URL', plugin_dir_url( __DIR__ ) );// Plugin url.
			defined( 'WKUVDESK_VERSION' ) || define( 'WKUVDESK_VERSION', '2.0.2' );// Plugin version.
			defined( 'WKUVDESK_SCRIPT_VERSION' ) || define( 'WKUVDESK_SCRIPT_VERSION', '2.0.2' );// Script version.
			defined( 'WKUVDESK_DB_VERSION' ) || define( 'WKUVDESK_DB_VERSION', '2.0.2' ); // Database version.
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @return void
		 */
		private function wkuvdesk_init_hooks() {
			add_action( 'init', array( $this, 'wkuvdesk_load_plugin_textdomain' ), 0 );
			add_action( 'plugins_loaded', array( $this, 'wkuvdesk_load_plugin' ) );
		}

		/**
		 * Helper function to convert array to HTML attributes.
		 *
		 * @param array $attributes Array of attributes.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_convert_attributes_to_html( $attributes ) {
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
		public function wkuvdesk_load_plugin_textdomain() {
			load_plugin_textdomain( 'wk-uvdesk', false, plugin_basename( dirname( WKUVDESK_PLUGIN_FILE ) ) . '/languages' ); // Plugin text domain.
		}

		/**
		 * Load native plugin files.
		 *
		 * @return void
		 */
		public function wkuvdesk_load_plugin() {
			WKUVDESK_File_Handler::get_instance(); // Load plugin.
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
