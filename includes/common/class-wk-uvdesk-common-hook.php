<?php
/**
 * WK_UVDESK_Common_Hook handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Includes\Common;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WK_UVDESK_Common_Hook' ) ) {
	/**
	 * WK_UVDESK_Common_Hook class.
	 */
	class WK_UVDESK_Common_Hook {
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
			$common_handler = WK_UVDESK_Common_Function::get_instance();
			add_action( 'wp_enqueue_scripts', array( $common_handler, 'wk_uvdesk_front_enqueue_script' ) );
			add_action( 'admin_enqueue_scripts', array( $common_handler, 'wk_uvdesk_front_enqueue_script' ) );

			add_action( 'wp_ajax_nopriv_change_ticket_agent', array( $common_handler, 'wk_uvdesk_change_ticket_agent' ) );
			add_action( 'wp_ajax_change_ticket_agent', array( $common_handler, 'wk_uvdesk_change_ticket_agent' ) );
			add_action( 'wp_ajax_nopriv_change_ticket_priority', array( $common_handler, 'wk_uvdesk_change_ticket_priority' ) );
			add_action( 'wp_ajax_change_ticket_priority', array( $common_handler, 'wk_uvdesk_change_ticket_priority' ) );

			add_action( 'wp_ajax_nopriv_sort_customer_ticket_via_status', array( $common_handler, 'wk_uvdesk_sort_customer_ticket_via_status' ) );
			add_action( 'wp_ajax_sort_customer_ticket_via_status', array( $common_handler, 'wk_uvdesk_sort_customer_ticket_via_status' ) );
			add_action( 'wp_ajax_nopriv_sort_ticket_via_api', array( $common_handler, 'wk_uvdesk_sort_ticket_via_api' ) );
			add_action( 'wp_ajax_sort_ticket_via_api', array( $common_handler, 'wk_uvdesk_sort_ticket_via_api' ) );

			add_action( 'wp_ajax_nopriv_get_thread_data_customer', array( $common_handler, 'wk_uvdesk_get_thread_data_customer' ) );
			add_action( 'wp_ajax_get_thread_data_customer', array( $common_handler, 'wk_uvdesk_get_thread_data_customer' ) );

			add_action( 'wp_ajax_nopriv_delete_thread_via_api', array( $common_handler, 'wk_uvdesk_delete_thread_via_api' ) );
			add_action( 'wp_ajax_delete_thread_via_api', array( $common_handler, 'wk_uvdesk_delete_thread_via_api' ) );
			add_action( 'wp_ajax_nopriv_toggle_the_starred', array( $common_handler, 'wk_uvdesk_toggle_the_starred' ) );
			add_action( 'wp_ajax_toggle_the_starred', array( $common_handler, 'wk_uvdesk_toggle_the_starred' ) );
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
