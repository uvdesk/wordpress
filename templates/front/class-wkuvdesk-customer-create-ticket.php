<?php
/**
 * WKUVDESK_Customer_Create_Ticket handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Helper;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Customer_Create_Ticket' ) ) {
	/**
	 * WKUVDESK_Customer_Create_Ticket class.
	 */
	class WKUVDESK_Customer_Create_Ticket {
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
			add_shortcode( 'uvdesk', array( $this, 'wkuvdesk_customer_create_ticket' ) );
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
		 * @return void
		 */
		public function wkuvdesk_customer_create_ticket() {
			$uv                  = new Helper\WKUVDESK_Protected();
			$client_key          = $uv->get_client_key();
			$secret_key          = $uv->get_secret_key();
			$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );
			$company_domain      = get_option( 'uvdesk_company_domain', '' );
			$error               = array();

			if ( ! empty( $uvdesk_access_token ) && ! empty( $company_domain ) ) {
				if ( filter_has_var( INPUT_POST, 'submit1' ) ) {
					$captcha = '';
					if ( filter_has_var( INPUT_POST, 'g-recaptcha-response' ) ) {
						$captcha = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
					}

					if ( ! $captcha ) {
						$error[] = esc_html__( 'Please check the the captcha form.', 'uvdesk' );
					}

					$args          = array(
						'body' => array(
							'secret'   => $secret_key,
							'response' => $captcha,
							'remoteip' => isset( $_SERVER['HTTP_X_REAL_IP'] ) ? filter_var( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ), FILTER_VALIDATE_IP ) : '',
						),
					);
					$response      = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $args );
					$response_data = json_decode( wp_remote_retrieve_body( $response ) );

					if ( $response_data->success && ! empty( $captcha ) ) {
						if ( filter_has_var( INPUT_POST, 'uvdesk_create_ticket_nonce' ) ) {
							if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'uvdesk_create_ticket_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ), 'uvdesk_create_ticket_nonce_action' ) ) {
								wp_die( esc_html__( 'Sorry, your nonce did not verify.', 'uvdesk' ) );
							} elseif ( filter_has_var( INPUT_POST, 'subject' ) && ! empty( filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && filter_has_var( INPUT_POST, 'reply' ) && ! empty( filter_input( INPUT_POST, 'reply', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && filter_has_var( INPUT_POST, 'type' ) && ! empty( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
								$user       = wp_get_current_user();
								$user_name  = $user->user_nicename;
								$user_email = $user->user_email;
								$post_data  = array(
									'name'    => $user_name,
									'from'    => $user_email,
									'subject' => sanitize_text_field( filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
									'reply'   => sanitize_text_field( filter_input( INPUT_POST, 'reply', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
									'type'    => '4',
								);

								if ( ! empty( $user ) ) {
									if ( isset( $_FILES['attachments']['size'][0] ) && 0 === $_FILES['attachments']['size'][0] ) {
										$ticket_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_create_new_ticket( $post_data );
										$ticket_status = json_decode( $ticket_status );
										if ( isset( $ticket_status->message ) ) {
											echo '<div class="wkuvdesk-alert wkuvdesk-alert-success wkuvdesk-alert-fixed">
										<span>
										<span class="wkuvdesk-remove-file wkuvdesk-alert-msg"></span>
										' . esc_html( $ticket_status->message ) . '
										</span>
										</div>';
										} elseif ( isset( $ticket_status->error ) ) {
											$error[] .= $ticket_status->error;
										}
									} elseif ( isset( $_FILES['attachments'] ) && $_FILES['attachments']['size'][0] > 0 ) {
										$valid_file_mimes = array(
											'application/pdf',
											'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
											'application/msword',
											'image/png',
											'image/jpeg',
											'image/jpeg',
											'image/jpeg',
											'image/gif',
											'application/zip',
											'application/x-rar-compressed',
										);
										$file_mime_type   = isset( $_FILES['attachments']['tmp_name'][0] ) ? mime_content_type( sanitize_text_field( $_FILES['attachments']['tmp_name'][0] ) ) : '';

										if ( in_array( $file_mime_type, $valid_file_mimes, true ) ) {
											$ticket_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_create_new_ticket_with_attachement( $post_data, array_map( 'sanitize_text_field', $_FILES['attachments'] ) );
											if ( $ticket_status ) {
												$ticket_status = is_array( $ticket_status ) ? wp_json_encode( $ticket_status ) : $ticket_status;
												$ticket_status = json_decode( $ticket_status );
											}

											if ( isset( $ticket_status->success ) && ( 200 === $ticket_status->success ) ) {
												echo '<div class="wkuvdesk-alert wkuvdesk-alert-success wkuvdesk-alert-fixed">
												<span>
												<span class="wkuvdesk-remove-file wkuvdesk-alert-msg"></span>
												' . esc_html( $ticket_status->message ) . '
												</span>
												</div>';
											} elseif ( isset( $ticket_status->error ) && ( 404 === $ticket_status->error || 500 === $ticket_status->error ) ) {
												$error[] = $ticket_status->message;
											}
										} else {
											echo '<div class="wkuvdesk-alert wkuvdesk-alert-success wkuvdesk-alert-fixed  ">
													<span>
															<span class="wkuvdesk-remove-file wkuvdesk-alert-msg"></span>
															' . esc_html__( 'Please upload a valid file (PDF, DOC, DOCX, PNG, JPG, JPEG, GIF, ZIP, RAR) with maximum size of 20MB', 'uvdesk' ) .
													'</span>
											</div>';
										}
									}
								} else {
									echo '<div class="text-center uv-notify"><span class="wkuvdesk-alert wkuvdesk-alert-danger">' . esc_html__( 'There is some issue with user permission try again.', 'uvdesk' ) . '</span><div>';
								}
							} else {
								$error[] = esc_html__( 'Some fields are empty.', 'uvdesk' );
							}
						}
					}
				}
			} else {
				echo '<h1 class="wkuvdesk-pass-error">' . esc_html__( 'Please Enter a valid Access Token', 'uvdesk' ) . '</h1>';
			}
			?>
			<div class="main-body">
				<div class="container">
					<div class="title-uvdesk">
							<h2><?php esc_html_e( 'Create a Ticket', 'uvdesk' ); ?></h2>
							<a class="wkuvdesk-to-home" href="<?php echo esc_url( site_url() . '/uvdesk/customer/' ); ?>"> <?php esc_html_e( 'Back', 'uvdesk' ); ?></a>
					</div>
					<form name="" method="post" action="" enctype="multipart/form-data" novalidate="false" id="wkuvdesk-create-ticket-form">
						<?php wp_nonce_field( 'uvdesk_create_ticket_nonce_action', 'uvdesk_create_ticket_nonce' ); ?>
						<div class="form-group ">
							<label for="type"><?php esc_html_e( 'Type', 'uvdesk' ); ?></label>
							<select id="type" name="type" required data-role="tagsinput" data-live-search="data-live-search" class="selectpicker" tabindex="-98">
								<option value="" selected="selected"><?php esc_html_e( 'Choose query type', 'uvdesk' ); ?></option>
								<option value="<?php echo esc_attr( '87' ); ?>"><?php esc_html_e( 'Support', 'uvdesk' ); ?></option>
							</select>
							<span class="error-message" id="type-error"></span>
						</div>
						<div class="form-group ">
							<label for="subject"><?php esc_html_e( 'Subject', 'uvdesk' ); ?></label>
							<input type="text" id="subject" name="subject" required placeholder="<?php esc_attr_e( 'Enter Subject', 'uvdesk' ); ?>" class="form-control"/>
							<span class="error-message" id="subject-error"></span>
						</div>
						<div class="form-group ">
							<label for="reply"><?php esc_html_e( 'Message', 'uvdesk' ); ?></label>
							<textarea class="wkuvdesk-reply" id="reply" name="reply" required placeholder="<?php esc_attr_e( 'Brief Description about your query', 'uvdesk' ); ?>" data-iconlibrary="<?php esc_attr( 'fa' ); ?>" data-height="<?php esc_attr( '250' ); ?>" class="form-control"></textarea>
							<span class="error-message" id="reply-error"></span>
						</div>
						<div class="form-group">
							<div class="form-group wkuvdesk-attachments">
									<div class="labelWidget">
										<input id="wkuvdesk-attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="<?php echo esc_attr( 'attach-file' ); ?>" decoratefile="<?php echo esc_attr( 'decorateFile' ); ?>" infolabeltext="<?php esc_attr_e( '+ Attach File', 'uvdesk' ); ?>" infolabel="right" name="attachments[]" onchange="document.getElementById('file-name').textContent = this.files[0].name">
										<label class="attach-file pointer"></label>
										<i class="wkuvdesk-remove-file" id="remove-att"></i>
									</div>
									<span id="wkuvdesk-addFile" class="label-right pointer"><?php esc_html_e( 'Attach File', 'uvdesk' ); ?></span>
									<span id="file-name"></span>
									<span class="error-message" id="file-error"></span>
							</div>
						</div>
						<input type="hidden" id="_token" name="_token" value="<?php echo esc_attr( 'eJPW5s_yBH1S6iTM1eLI18Kdb304tl-IwIqE0ktJTd8' ); ?>" />
							<?php
							$client_key = empty( $client_key ) ? esc_html__( 'Check for client Keys', 'uvdesk' ) : $client_key;
							?>
							<div class="g-recaptcha wkuvdesk-transform" id="recaptcha" data-sitekey="<?php echo esc_attr( $client_key ); ?>"></div>
							<div class="uvdesk-captcha-error"><?php esc_html_e( 'Please verify that you are not a robot.', 'uvdesk' ); ?></div>
							<?php
							if ( ! empty( $error ) ) {
								?>
									<div class="wkuvdesk-captcha-error">
												<?php
												foreach ( $error as $err_mes ) {
													echo wp_kses_post( $err_mes . '<br>' );
												}
												?>
									</div>
								<?php
							}
							?>
							<button type="submit" id="submit1" name="submit1" class="wkuvdesk-btn-create-tkt"><?php esc_html_e( 'Create Ticket', 'uvdesk' ); ?></button>
					</form>
				</div>
			</div>
			<?php
		}
	}
}
