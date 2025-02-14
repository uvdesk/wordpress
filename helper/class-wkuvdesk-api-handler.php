<?php
/**
 * WKUVDESK_Api_Handler Token file handler
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Helper;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Api_Handler' ) ) {
	/**
	 * WKUVDESK_Api_Handler class.
	 */
	class WKUVDESK_Api_Handler extends WKUVDESK_Protected {
		/**
		 * Timeout for HTTP requests.
		 *
		 * @var int
		 */
		public static $timeout = 45;

		/**
		 * Create new ticket with attachment.
		 *
		 * @param array $post_attachment post attachment.
		 * @param array $post_image post image.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_create_new_ticket_with_attachement( $post_attachment, $post_image = array() ) {
			$uv     = new WKUVDESK_Protected();
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/tickets.json';

			// Generate a boundary for multipart/form-data.
			$boundary = wp_generate_password( 24, false );
			// Prepare the fields.
			$multipart_data = '';
			// Add text fields.
			$fields = array(
				'type'    => '4',
				'name'    => $post_attachment['name'],
				'from'    => $post_attachment['from'],
				'subject' => $post_attachment['subject'],
				'reply'   => $post_attachment['reply'],
			);

			foreach ( $fields as $key => $value ) {
				$multipart_data .= "--{$boundary}\r\n";
				$multipart_data .= "Content-Disposition: form-data; name=\"{$key}\"\r\n\r\n";
				$multipart_data .= "{$value}\r\n";
			}

			// Add file attachments.
			if ( ! empty( $post_image['name'] ) && is_array( $post_image['name'] ) ) {
				foreach ( $post_image['name'] as $key => $filename ) {
					if ( ! empty( $post_image['tmp_name'][ $key ] ) ) {
						$file_path = $post_image['tmp_name'][ $key ];

						if ( ! file_exists( $file_path ) ) {
							return array( 'error' => esc_html__( 'File not found', 'wk-uvdesk' ) );
						}

						$file_contents = wp_remote_get( $file_path )['body'];
						if ( false === $file_contents ) {
							return array( 'error' => esc_html__( 'Unable to read file', 'wk-uvdesk' ) );
						}

						$file_type = wp_check_filetype( $filename );
						if ( empty( $file_type['type'] ) ) {
							return array( 'error' => esc_html__( 'Invalid file type', 'wk-uvdesk' ) );
						}

						$multipart_data .= "--{$boundary}\r\n";
						$multipart_data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . sanitize_file_name( $filename ) . "\"\r\n";
						$multipart_data .= "Content-Type: {$file_type['type']}\r\n\r\n";
						$multipart_data .= "{$file_contents}\r\n";
					}
				}
			}

			// End boundary.
			$multipart_data .= "--{$boundary}--";
			// Prepare headers.
			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
				'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
			);
			// Perform the request.
			$response = wp_remote_post(
				$url,
				array(
					'method'  => 'POST',
					'headers' => $headers,
					'body'    => $multipart_data,
					'timeout' => self::$timeout,
				)
			);
			// Check for errors.
			if ( is_wp_error( $response ) ) {
				return array( 'error' => $response->get_error_message() );
			}

			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			if ( 201 === $status_code || 200 === $status_code ) {
				return wp_json_encode(
					array(
						'success' => 200,
						'message' => esc_html__( 'Ticket created successfully.', 'wk-uvdesk' ),
					)
				);
			} elseif ( 404 === $status_code ) {
				return wp_json_encode(
					array(
						'error'   => 404,
						'message' => esc_html__( 'Error, Please check the endpoint.', 'wk-uvdesk' ),
					)
				);
			} else {
				return wp_json_encode(
					array(
						'error'   => 500,
						'message' => esc_html__( 'Unknown error occurred!', 'wk-uvdesk' ),
					)
				);
			}
		}

		/**
		 * Create new ticket.
		 *
		 * @param array $post_array post array.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_create_new_ticket( $post_array = array() ) {
			$uv     = new WKUVDESK_Protected();
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/tickets.json';
			// Convert data to JSON.
			$data = wp_json_encode( $post_array );
			// Set headers.
			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
				'Content-Type'  => 'application/json',
			);

			// Perform the request.
			$response = wp_remote_post(
				$url,
				array(
					'method'  => 'POST',
					'headers' => $headers,
					'body'    => $data,
					'timeout' => self::$timeout,
				)
			);

			// Check for errors.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}
			// Retrieve the response code and body.
			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			// Handle different status codes.
			if ( in_array( $status_code, array( 200, 201 ), true ) ) {
				return $response_body;
			} elseif ( 400 === $status_code ) {
				return array( 'error' => esc_html__( 'Error, request data not valid. (http-code: 400)', 'wk-uvdesk' ) );
			} elseif ( 404 === $status_code ) {
				return array( 'error' => esc_html__( 'Error, resource not found (http-code: 404)', 'wk-uvdesk' ) );
			} else {
				return $response_body; // Return raw response for other HTTP codes.
			}
		}

		/**
		 * Send data to UVDesk API via POST request.
		 *
		 * @param string $thread_url_param The specific API endpoint.
		 * @param array  $thread_param     The data to be sent to the API.
		 *
		 * @return string|false API response or error message.
		 */
		public static function wkuvdesk_post_thread_data_api( $thread_url_param = '', $thread_param = array() ) {
			// Initialize UVDesk helper.
			$uv = new WKUVDESK_Protected();

			// Validate input parameters.
			if ( empty( $thread_url_param ) ) {
				return esc_html__( 'Error: API endpoint is required.', 'wk-uvdesk' );
			}

			// Retrieve company domain and access token.
			$domain       = $uv->get_company_domain();
			$access_token = $uv->get_access_token();

			// Additional validation for domain and access token.
			if ( empty( $domain ) || empty( $access_token ) ) {
				return esc_html__( 'Error: Missing UVDesk configuration.', 'wk-uvdesk' );
			}

			// Construct API URL.
			$url  = sprintf(
				'https://%s.uvdesk.com/en/api/%s',
				esc_url_raw( $domain ),
				esc_url_raw( $thread_url_param )
			);
			$data = wp_json_encode( $thread_param );
			$url  = 'https://' . $domain . '.uvdesk.com/en/api/' . $thread_url_param;

			// Initialize cURL request.
			$response = wp_remote_post(
				$url,
				array(
					'method'  => 'POST',
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Content-Type'  => 'application/json',
					),
					'body'    => $data,
					'timeout' => self::$timeout,
				)
			);

			// Handle response.
			if ( is_wp_error( $response ) ) {
				return sprintf(
				/* translators: %s: Error message */
					esc_html__( 'WordPress HTTP API Error: %s', 'wk-uvdesk' ),
					$response->get_error_message()
				);
			}

			// Get response code and body.
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Process response based on HTTP status code.
			switch ( $response_code ) {
				case 200:
				case 201:
					return $response_body;
				case 400:
					return esc_html__( 'Error: Bad Request. (HTTP Code: 400)', 'wk-uvdesk' );
				case 404:
					return esc_html__( 'Error: Resource not found. (HTTP Code: 404)', 'wk-uvdesk' );
				default:
					return sprintf(
					/* translators: %1$d: HTTP status code, %2$s: Response message */
						esc_html__( 'Error: HTTP Status Code: %1$d. Response: %2$s', 'wk-uvdesk' ),
						$response_code,
						$response_body
					);
			}
		}

		/**
		 * Post thread data api with attachment for UVDesk in WordPress.
		 *
		 * @param string $thread_url_param Thread URL parameter.
		 * @param array  $thread_param Thread parameters.
		 * @param array  $post_image Post image details.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_post_thread_data_api_with_attachment( $thread_url_param = '', $thread_param = array(), $post_image = array() ) {
			// Ensure we have required WordPress functions.
			if ( ! function_exists( 'wp_parse_args' ) ) {
				require_once ABSPATH . 'wp-includes/functions.php';
			}

			// Default thread parameters.
			$thread_param = wp_parse_args(
				$thread_param,
				array(
					'threadType' => 'reply',
					'reply'      => '',
					'status'     => 'pending',
					'actAsType'  => 'customer',
					'actAsEmail' => get_option( 'admin_email' ),
				)
			);

			// Get UVDesk helper instance.
			$uv = new WKUVDESK_Protected();

			// Construct the URL.
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/' . $thread_url_param;

			// Prepare multipart form data.
			$line_ends     = "\r\n";
			$mime_boundary = md5( time() );

			// Initialize data.
			$data = '';

			// Add standard fields.
			$fields = array(
				'threadType' => $thread_param['threadType'],
				'reply'      => $thread_param['reply'],
				'status'     => $thread_param['status'],
				'actAsType'  => $thread_param['actAsType'],
				'actAsEmail' => $thread_param['actAsEmail'],
			);

			foreach ( $fields as $key => $value ) {
				$data .= '--' . $mime_boundary . $line_ends;
				$data .= 'Content-Disposition: form-data; name="' . $key . '"' . $line_ends . $line_ends;
				$data .= $value . $line_ends;
			}

			// Process attachments.
			$attachments = array();
			$count       = isset( $post_image['type'] ) && is_array( $post_image['type'] ) ? count( $post_image['type'] ) : 0;
			if ( ! empty( $post_image['name'] ) ) {
				for ( $i = 0; $i < $count; $i++ ) {
					if ( $post_image['size'][ $i ] > 0 ) {
						$attachments[] = array(
							'name'     => $post_image['name'][ $i ],
							'type'     => $post_image['type'][ $i ],
							'tmp_name' => $post_image['tmp_name'][ $i ],
						);
					}
				}
			}

			// Add file attachments using WordPress wp_filesystem.
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			foreach ( $attachments as $attachment ) {
				$data .= '--' . $mime_boundary . $line_ends;
				$data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . $attachment['name'] . '"' . $line_ends;
				$data .= 'Content-Type: ' . $attachment['type'] . $line_ends . $line_ends;

				// Use WP_Filesystem to read file contents.
				$file_contents = $wp_filesystem->get_contents( $attachment['tmp_name'] );
				$data         .= $file_contents . $line_ends;
			}

			$data .= '--' . $mime_boundary . '--' . $line_ends . $line_ends;

			// Prepare headers.
			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
				'Content-Type'  => 'multipart/form-data; boundary=' . $mime_boundary,
			);

			// Use WordPress HTTP API with more cURL-like configuration.
			$args = array(
				'method'    => 'POST',
				'headers'   => $headers,
				'body'      => $data,
				'timeout'   => self::$timeout,
				'sslverify' => false,
			);

			$response = wp_remote_post( $url, $args );

			// Handle response similar to cURL.
			if ( is_wp_error( $response ) ) {
				return 'Error: ' . $response->get_error_message();
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Directly return response body for successful requests.
			if ( 201 === $response_code ) {
				return $response_body;
			}

			// Return error or response for other scenarios.
			return $response_body;
		}

		/**
		 * Post tag ticket.
		 *
		 * @param string $url_param url param.
		 * @param array  $param param.
		 *
		 * @return mixed
		 */
		public function wkuvdesk_post_tag_ticket( $url_param = '', $param = array() ) {
			$uv     = new WKUVDESK_Protected();
			$domain = $uv->get_company_domain();
			// Construct the URL.
			$url = 'https://' . $domain . '.uvdesk.com/en/api/' . $url_param;
			// Convert parameters to JSON format.
			$data = wp_json_encode( $param );
			// Set up headers.
			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
				'Content-Type'  => 'application/json',
			);
			// Perform the request.
			$response = wp_remote_post(
				$url,
				array(
					'method'  => 'POST',
					'headers' => $headers,
					'body'    => $data,
					'timeout' => self::$timeout,
				)
			);
			// Check for errors.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}
			// Retrieve response details.
			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			// Handle response based on HTTP status code.
			if ( 200 === $status_code || 201 === $status_code ) {
				return $response_body;
			} elseif ( 400 === $status_code ) {
				return esc_html__( 'Error: Request data not valid. (HTTP Code: 400)', 'wk-uvdesk' );
			} elseif ( 404 === $status_code ) {
				return esc_html__( 'Error: Resource not found. (HTTP Code: 404)', 'wk-uvdesk' );
			} else {
				/* translators: %1$d: HTTP status code, %2$s: Response body */
				return sprintf(
					'Error: HTTP Status Code: %1$d. Response: %2$s',
					$status_code,
					$response_body
				);
			}
		}

		/**
		 * Retrieve customer data via UVdesk API.
		 *
		 * @param string $url_param   The API endpoint path.
		 * @param array  $query_params Associative array of query parameters to include in the request.
		 *
		 * @return mixed|WP_Error Response data or WP_Error on failure.
		 */
		public static function wkuvdesk_get_customer_data_api( $url_param = '', $query_params = array() ) {
			$uv = new WKUVDESK_Protected();

			// Build the query string from query parameters.
			$query_string = ! empty( $query_params ) ? '?' . http_build_query( $query_params ) : '';

			// Construct the full API URL.
			$domain = $uv->get_company_domain();
			$url    = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/' . esc_attr( $url_param ) . $query_string;

			// Set the request headers.
			$headers = array(
				'Authorization' => 'Bearer ' . sanitize_text_field( $uv->get_access_token() ),
			);

			// Make the HTTP GET request.
			$response = wp_remote_get( $url, array( 'headers' => $headers ) );

			// Check if the request returned an error.
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );
			$data        = json_decode( $body );

			// Handle response status codes.
			if ( 200 === $status_code ) {
				return $data;
			} elseif ( 404 === $status_code ) {
				return esc_html__( 'Error: Resource not found. (HTTP Code: 404)', 'wk-uvdesk' );
			} else {
				/* translators: %1$d: HTTP status code, %2$s: Response message */
				return sprintf(
					'Error: HTTP Status Code: %1$d. Response: %2$s',
					$status_code,
					$body
				);
			}
		}

		/**
		 * Delete tag ticket.
		 *
		 * @param string $tag_url_param url param.
		 * @param array  $tag_params params.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_delete_tag_ticket( $tag_url_param = '', $tag_params = array() ) {
			$uv     = new WKUVDESK_Protected();
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/' . $tag_url_param;
			// Set headers.
			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
			);
			// Prepare arguments for the DELETE request.
			$args = array(
				'method'      => 'PUT', // Change from DELETE to PUT.
				'headers'     => $headers,
				'data_format' => 'body',
				'timeout'     => self::$timeout,
			);
			// Make the request.
			$response = wp_remote_request( $url, $args );
			// Check for errors.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}
			// Retrieve response details.
			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			// Decode JSON response.
			$response_data = json_decode( $response_body, true );
			// Handle response based on HTTP status code.
			if ( 200 === $status_code ) {
				return $response_data;
			} elseif ( 404 === $status_code ) {
				return esc_html__( 'Error: Resource not found. (HTTP Code: 404)', 'wk-uvdesk' );
			} else {
				// * translators: % 1$d: HTTP status code, % 2$s: Response message * /
				return sprintf(
					'Error: HTTP Status Code: %1$d. Response: %2$s',
					$status_code,
					$response_body
				);
			}
		}

		/**
		 * Delete tag ticket threds.
		 *
		 * @param string $tag_url_param url param.
		 * @param array  $tag_prm params.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_threds_delete_tag_ticket( $tag_url_param = '', $tag_prm = array() ) {
			// Initialize UVDesk helper.
			$uv     = new WKUVDESK_Protected();
			$domain = $uv->get_company_domain();

			// Construct the full API URL.
			$url = 'https://' . $domain . '.uvdesk.com/en/api/' . $tag_url_param;

			// Prepare headers for authentication.
			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
				'Content-Type'  => 'application/json',
			);

			// Pass parameters directly in the URL if needed .
			if ( ! empty( $tag_prm ) ) {
				$url .= '?' . http_build_query( $tag_prm );
			}

			// Prepare arguments for the request.
			$args = array(
				'method'    => 'DELETE',
				'headers'   => $headers,
				'timeout'   => self::$timeout,
				'sslverify' => false,
			);

			// Make the request.
			$response = wp_remote_request( $url, $args );

			// Check for errors.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}

			// Retrieve response details.
			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Decode JSON response.
			$decoded_response = json_decode( $response_body );

			// Handle response based on HTTP status code.
			if ( 200 === $status_code ) {
				return $decoded_response;
			} elseif ( 404 === $status_code ) {
				return 'Error, resource not found (http-code: 404)';
			} else {
				/* translators: %1$d: HTTP status code, %2$s: Response message */
				return sprintf(
					'Error: HTTP Status Code: %1$d. Response: %2$s',
					$status_code,
					wp_kses_post( $response_body )
				);
			}
		}

		/**
		 * Update ticket.
		 *
		 * @param string $ticket_url_param url param.
		 * @param array  $ticket_params params.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_update_ticket( $ticket_url_param = '', $ticket_params = array() ) {
			$uv     = new WKUVDESK_Protected();
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/' . $ticket_url_param;

			$headers = array(
				'Authorization' => 'Bearer ' . $uv->get_access_token(),
			);

			$args = array(
				'method'      => 'PUT',
				'headers'     => $headers,
				'body'        => $ticket_params,
				'data_format' => 'body',
				'timeout'     => self::$timeout,
			);

			$response = wp_remote_request( $url, $args );

			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}

			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			$response_data = json_decode( $response_body );

			if ( 200 === $status_code ) {
				return $response_data;
			} elseif ( 404 === $status_code ) {
				return esc_html__( 'Error, resource not found (http-code: 404)', 'wk-uvdesk' );
			}
		}

		/**
		 * Get Attachment data from UVDesk API.
		 *
		 * @param string $attachment_url_param URL parameter for attachment retrieval.
		 *
		 * @return mixed
		 */
		public static function wkuvdesk_get_attachment_data_api( $attachment_url_param = '' ) {
			// Validate input.
			if ( empty( $attachment_url_param ) ) {
				return new \WP_Error(
					'uvdesk_invalid_param',
					__( 'Invalid attachment URL parameter', 'wk-uvdesk' )
				);
			}

			// Get UVDesk helper instance.
			$uv = new WKUVDESK_Protected();

			// Construct the URL.
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/' . $attachment_url_param;

			// Prepare request arguments.
			$args = array(
				'method'  => 'GET',
				'headers' => array(
					'Authorization' => 'Bearer ' . $uv->get_access_token(),
				),
			);

			// Make the request using WordPress HTTP API.
			$response = wp_remote_get( $url, $args );

			// Check for WordPress HTTP API errors.
			if ( is_wp_error( $response ) ) {
				return new \WP_Error(
					'uvdesk_api_error',
					sprintf(
					/* translators: %s: Error message */
						__( 'API Communication Error: %s', 'wk-uvdesk' ),
						$response->get_error_message()
					)
				);
			}

			// Get response code and body.
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Handle different response scenarios.
			switch ( $response_code ) {
				case 200:
					return $response_body;

				case 404:
					return new \WP_Error(
						'uvdesk_resource_not_found',
						__( 'Error: Resource not found (HTTP Code: 404)', 'wk-uvdesk' )
					);

				default:
					// Log unexpected response for debugging.
						sprintf(
							'UVDesk API Unexpected Response - Code: %d, Body: %s',
							$response_code,
							$response_body
						);

					return new \WP_Error(
						'uvdesk_unexpected_response',
						sprintf(
						/* translators: %1$d: HTTP status code, %2$s: Response message */
							__( 'Error: Unexpected HTTP Status Code %1$d. Response: %2$s', 'wk-uvdesk' ),
							$response_code,
							$response_body
						)
					);
			}
		}

		/**
		 * Get or Update ticket data via PATCH request.
		 *
		 * @param string $patch_url_param URL parameter for the API endpoint.
		 * @param array  $ticket_prm Parameters for the PATCH request.
		 *
		 * @return mixed
		 */
		public function wkuvdesk_get_patch_data_api( $patch_url_param = '', $ticket_prm = array() ) {
			// Validate input.
			if ( empty( $patch_url_param ) ) {
				return new \WP_Error(
					'uvdesk_invalid_param',
					__( 'Invalid URL parameter for ticket data', 'wk-uvdesk' )
				);
			}

			// Get UVDesk helper instance.
			$uv = new WKUVDESK_Protected();

			// Construct the URL.
			$domain = $uv->get_company_domain();
			$url    = 'https://' . $domain . '.uvdesk.com/en/api/' . $patch_url_param;

			// Prepare request arguments.
			$args = array(
				'method'  => 'PATCH',
				'headers' => array(
					'Authorization' => 'Bearer ' . $uv->get_access_token(),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => $ticket_prm,
				'timeout' => self::$timeout,
			);

			// Make the request using WordPress HTTP API.
			$response = wp_remote_request( $url, $args );

			// Check for WordPress HTTP API errors.
			if ( is_wp_error( $response ) ) {
				return new \WP_Error(
					'uvdesk_api_error',
					sprintf(
					/* translators: %s: Error message */
						__( 'API Communication Error: %s', 'wk-uvdesk' ),
						$response->get_error_message()
					)
				);
			}

			// Get response code and body.
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Attempt to decode JSON response.
			$decoded_response = json_decode( $response_body );

			// Handle different response scenarios.
			switch ( $response_code ) {
				case 200:
					return $decoded_response ? $decoded_response : $response_body;

				case 404:
					return new \WP_Error(
						'uvdesk_resource_not_found',
						__( 'Error: Resource not found (HTTP Code: 404)', 'wk-uvdesk' )
					);

				default:
						sprintf(
							'UVDesk API Unexpected Response - Code: %d, Body: %s',
							$response_code,
							$response_body
						);

					return new \WP_Error(
						'uvdesk_unexpected_response',
						sprintf(
						/* translators: %1$d: HTTP status code, %2$s: Response message */
							__( 'Error: Unexpected HTTP Status Code %1$d. Response: %2$s', 'wk-uvdesk' ),
							$response_code,
							$response_body
						)
					);
			}
		}
	}
}
