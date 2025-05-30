<?php
/**
 * Admin End WKUVDESK_Admin_Function handler.
 *
 * @package UVdesk Free Helpdesk
 */
namespace WKUVDESK\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Templates\Admin;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Admin_Function' ) ) {
	/**
	 * WKUVDESK_Admin_Function class.
	 */
	class WKUVDESK_Admin_Function {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Add admin dashboard menu.
		 *
		 * @return void
		 */
		public function wkuvdesk_admin_menu() {
			add_menu_page( esc_html__( 'UVdesk Ticket System', 'uvdesk' ), esc_html__( 'UVdesk Ticket System', 'uvdesk' ), 'manage_options', 'wkuvdesk_ticket_system', array( $this, 'wkuvdesk_ticket_list' ), 'dashicons-admin-page', 3 );
			add_submenu_page( 'wkuvdesk_ticket_system', esc_html__( 'UVDesk Setting', 'uvdesk' ), esc_html__( 'UVDesk Setting', 'uvdesk' ), 'manage_options', 'wkuvdesk_setting', array( $this, 'wkuvdesk_configuration_menu' ) );
			add_submenu_page( 'wkuvdesk_ticket_system', esc_html__( 'Extensions', 'uvdesk' ), esc_html__( 'Extensions', 'uvdesk' ), 'manage_options', 'wkuvdesk_extensions', array( $this, 'wkuvdesk_extensions_menu' ) );
			add_submenu_page( 'wkuvdesk_ticket_system', esc_html__( 'Services', 'uvdesk' ), esc_html__( 'Services', 'uvdesk' ), 'manage_options', 'wkuvdesk_services', array( $this, 'wkuvdesk_services_menu' ) );
		}

		/**
		 * Show setting links.
		 *
		 * @param array $links Setting links.
		 *
		 * @return array
		 */
		public function wkuvdesk_add_plugin_setting_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=wkuvdesk_ticket_system' ) ) . '" aria-label="' . esc_attr__( 'Settings', 'uvdesk' ) . '">' . esc_html__( 'Settings', 'uvdesk' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Plugin row data.
		 *
		 * @param string $links Links.
		 * @param string $file Filepath.
		 *
		 * @hooked 'plugin_row_meta' filter hook.
		 *
		 * @return array $links links.
		 */
		public function wkuvdesk_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( WKUVDESK_PLUGIN_BASENAME ) === $file ) {
				$row_meta = array(
					'docs'    => '<a target="_blank" href="' . esc_url( 'https://webkul.com/blog/wordpress-helpdesk-plugin/' ) . '" aria-label="' . esc_attr__( 'View documentation', 'uvdesk' ) . '">' . esc_html__( 'Docs', 'uvdesk' ) . '</a>',
					'support' => '<a target="_blank" href="' . esc_url( 'https://webkul.uvdesk.com/' ) . '" aria-label="' . esc_attr__( 'Visit customer support', 'uvdesk' ) . '">' . esc_html__( 'Support', 'uvdesk' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Render settings for services.
		 *
		 * @return void
		 */
		public function wkuvdesk_services_menu() {
			?>
			<wk-area></wk-area>
			<?php
		}

		/**
		 * Support and services menu.
		 *
		 * @return void
		 */
		public function wkuvdesk_extensions_menu() {
			?>
			<div class="wkbc-wrap extensions">
				<webkul-extensions></webkul-extensions>
			</div>
			<?php
		}

		/***
		 * Create ticket list.
		 *
		 * @return void
		 */
		public function wkuvdesk_ticket_list() {
			if ( ! empty( filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && ! empty( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) ) && filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
				Admin\WKUVDESK_Manage_Ticket::get_instance();
			} else {
				new Admin\WKUVDESK_admin_ticket();
			}
		}

		/**
		 * Add admin site configuration option.
		 *
		 * @return void
		 */
		public function wkuvdesk_default_settings() {
			register_setting(
				'wkuvdesk-settings-group',
				'uvdesk_access_token',
				apply_filters(
					'wkuvdesk_access_token_args',
					array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					)
				)
			);
			register_setting(
				'wkuvdesk-settings-group',
				'uvdesk_company_domain',
				apply_filters(
					'wkuvdesk_company_domain_args',
					array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					)
				)
			);
			register_setting(
				'wkuvdesk-settings-group',
				'uvdesk_client_key',
				apply_filters(
					'wkuvdesk_client_key_args',
					array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					)
				)
			);
			register_setting(
				'wkuvdesk-settings-group',
				'uvdesk_secret_key',
				apply_filters(
					'wkuvdesk_secret_key_args',
					array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					)
				)
			);
		}

		/**
		 * Add admin side script.
		 *
		 * @return void
		 */
		public function wkuvdesk_back_enqueue_script() {
			$current_user = get_current_user_id();
			wp_enqueue_style( 'wkuvdesk-style', WKUVDESK_PLUGIN_URL . 'assets/dist/css/wkuvdesk-backend-style.min.css', array(), WKUVDESK_VERSION );
			wp_enqueue_script( 'wkuvdesk-back-script', WKUVDESK_PLUGIN_URL . 'assets/dist/js/wkuvdesk-beckend-script.min.js', array( 'jquery' ), WKUVDESK_VERSION, true );
			$screen = get_current_screen();

			if ( ! empty( $screen ) && 'uvdesk-ticket-system_page_wkuvdesk_services' === $screen->id ) {
				wp_enqueue_script(
					'wkwp-addons-support-services',
					'https://webkul.com/common/modules/wksas.bundle.js',
					array(),
					WKUVDESK_VERSION,
					true
				);
			}
			if ( ! empty( $screen ) && 'uvdesk-ticket-system_page_wkuvdesk_extensions' === $screen->id ) {
				wp_enqueue_script(
					'wkwp-addons-extensions',
					'https://wpdemo.webkul.com/wk-extensions/client/wk.ext.js',
					array(),
					WKUVDESK_VERSION,
					true
				);
			}

			wp_localize_script(
				'wkuvdesk-back-script',
				'wkuvdesk_api_script',
				array(
					'apiAdminAjax'    => admin_url( 'admin-ajax.php' ),
					'apiNonce'        => wp_create_nonce( 'wkuvdesk-api-ajaxnonce' ),
					'uvdeskMemberUrl' => esc_url( site_url() . '/uvdesk/customer' ),
					'isAdmin'         => $current_user,
				)
			);
		}

		/**
		 * Add admin side configuration menu.
		 *
		 * @return void
		 */
		public function wkuvdesk_configuration_menu() {
			Admin\WKUVDESK_Backend_Setting::get_instance();
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
