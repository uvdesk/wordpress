<?php
/**
 * WKUVDESK_Common_Hook handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Includes\Common;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Common_Hook' ) ) {
	/**
	 * WKUVDESK_Common_Hook class.
	 */
	class WKUVDESK_Common_Hook {
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
			$common_handler = WKUVDESK_Common_Function::get_instance();
			add_action( 'wp_enqueue_scripts', array( $common_handler, 'wkuvdesk_front_enqueue_script' ) );
			add_action( 'admin_enqueue_scripts', array( $common_handler, 'wkuvdesk_front_enqueue_script' ) );

			add_action( 'wp_ajax_nopriv_change_ticket_agent', array( $common_handler, 'wkuvdesk_change_ticket_agent' ) );
			add_action( 'wp_ajax_change_ticket_agent', array( $common_handler, 'wkuvdesk_change_ticket_agent' ) );
			add_action( 'wp_ajax_nopriv_change_ticket_priority', array( $common_handler, 'wkuvdesk_change_ticket_priority' ) );
			add_action( 'wp_ajax_change_ticket_priority', array( $common_handler, 'wkuvdesk_change_ticket_priority' ) );

			add_action( 'wp_ajax_nopriv_sort_customer_ticket_via_status', array( $common_handler, 'wkuvdesk_sort_customer_ticket_via_status' ) );
			add_action( 'wp_ajax_sort_customer_ticket_via_status', array( $common_handler, 'wkuvdesk_sort_customer_ticket_via_status' ) );
			add_action( 'wp_ajax_nopriv_sort_ticket_via_api', array( $common_handler, 'wkuvdesk_sort_ticket_via_api' ) );
			add_action( 'wp_ajax_sort_ticket_via_api', array( $common_handler, 'wkuvdesk_sort_ticket_via_api' ) );

			add_action( 'wp_ajax_nopriv_get_thread_data_customer', array( $common_handler, 'wkuvdesk_get_thread_data_customer' ) );
			add_action( 'wp_ajax_get_thread_data_customer', array( $common_handler, 'wkuvdesk_get_thread_data_customer' ) );

			add_action( 'wp_ajax_nopriv_delete_thread_via_api', array( $common_handler, 'wkuvdesk_delete_thread_via_api' ) );
			add_action( 'wp_ajax_delete_thread_via_api', array( $common_handler, 'wkuvdesk_delete_thread_via_api' ) );
			add_action( 'wp_ajax_nopriv_toggle_the_starred', array( $common_handler, 'wkuvdesk_toggle_the_starred' ) );
			add_action( 'wp_ajax_toggle_the_starred', array( $common_handler, 'wkuvdesk_toggle_the_starred' ) );
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
