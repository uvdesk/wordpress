<?php
/**
 * Installation related functions and actions.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Includes;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**Check if class exists.*/
if ( ! class_exists( 'WKUVDESK_Install' ) ) {
	/**
	 * WKUVDESK_Install Class.
	 */
	class WKUVDESK_Install {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Hook in tabs.
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'wkuvdesk_install' ) );
		}

		/**
		 * Install main method.
		 *
		 * @return void
		 */
		public function wkuvdesk_install() {
			add_option( 'uvdesk_access_token', '826B0639C1AE373DD2B515F12F24131341149826B0639C1AE373DD2B515F12F241313', '', 'yes' );
			$this->wkuvdesk_create_pages();
		}

		/**
		 * Create pages that the plugin relies on, storing page id's in variables.
		 *
		 * @return void
		 */
		public function wkuvdesk_create_pages() {
			// Use WP_Query to check for existing page.
			$page_query = new \WP_Query(
				array(
					'title'     => 'uvdesk',
					'post_type' => 'page',
					'number'    => 1,
				)
			);

			// Check if page already exists.
			if ( ! $page_query->have_posts() ) {
				$uvdesk_postarr = array(
					'post_content'   => '[uvdesk]',
					'post_title'     => 'uvdesk',
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_name'      => 'uvdesk',
				);

				$page_id = wp_insert_post( $uvdesk_postarr );

				// Only update meta and flush if page was successfully created.
				if ( $page_id ) {
					update_post_meta( $page_id, '_wp_page_template', 'default' );
					flush_rewrite_rules();
				}
			} else {
				$page_query->the_post();
				$page_id = get_the_ID();
				update_post_meta( $page_id, '_wp_page_template', 'default' );
				wp_reset_postdata();
			}
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
