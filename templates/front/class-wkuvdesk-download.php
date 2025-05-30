<?php
/**
 * WKUVDESK_Download handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Download' ) ) {
	/**
	 * WKUVDESK_Download class.
	 */
	class WKUVDESK_Download {
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
			$this->wkuvdesk_customer_download_ticket_file();
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

		/**
		 * Shortcode function.
		 *
		 * @return string
		 */
		public function wkuvdesk_customer_download_ticket_file() {
			// Retrieve configuration options.
			$access_token   = get_option( 'uvdesk_access_token', '' );
			$company_domain = get_option( 'uvdesk_company_domain', '' );

			// Get attachment ID from query variable.
			$aid = get_query_var( 'aid' );

			// Validate attachment ID.
			if ( ! empty( intval( $aid ) ) && isset( $aid ) ) {
				// Construct API URL for attachment.
				$url = sprintf(
					'http://%s.webkul.com/en/api/ticket/attachment/%d.json?access_token=%s',
					$company_domain,
					$aid,
					$access_token
				);

				// Fetch attachment via WordPress HTTP API.
				$response = wp_remote_get( $url );

				// Handle API request errors.
				if ( is_wp_error( $response ) ) {
					return null;
				}

				// Retrieve response body and content type.
				$result       = wp_remote_retrieve_body( $response );
				$content_type = wp_remote_retrieve_header( $response, 'content-type' );

				// Validate response.
				if ( empty( $result ) ) {
					return null;
				}

				// Parse file type and generate filename.
				$type_parts = explode( '/', $content_type );
				$filename   = sprintf( '%d.%s', $aid, $type_parts[1] );

				// Get upload directory.
				$upload_dir = wp_upload_dir();
				$file_path  = path_join( $upload_dir['path'], $filename );

				// Ensure WP_Filesystem is available.
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				WP_Filesystem();
				global $wp_filesystem;

				// Save file and trigger download.
				if ( $wp_filesystem->put_contents( $file_path, $result ) ) {
					// Set headers for file download.
					header( 'Content-Type: ' . $content_type );
					header( 'Content-Disposition: attachment; filename=' . $filename );

					// Output file contents using WP_Filesystem method.
					$file_contents = $wp_filesystem->get_contents( $file_path );
					echo wp_kses_post( $file_contents );

					// Delete file using wp_delete_file().
					wp_delete_file( $file_path );
					exit; // Prevent any further output.
				}
			}
		}
	}
}
