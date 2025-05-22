<?php
/**
 * WKUVDESK Front Function.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Includes\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Templates\Admin as Template_Admin;
use WKUVDESK\Templates\Front as Template_Front;
use WKUVDESK\Helper;
use WKUVDESK\Includes;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Front_Function' ) ) {
	/**
	 * WKUVDESK_Front_Function class.
	 */
	class WKUVDESK_Front_Function {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Show ticket dashboard according to customer position on server.
		 *
		 * @return void
		 */
		public function wkuvdesk_calling_pages() {
			global $current_user, $wp_query;

			$pagename      = ! empty( $wp_query->get( 'pagename' ) ) ? sanitize_text_field( $wp_query->get( 'pagename' ) ) : get_post_field( 'post_name', get_queried_object_id() );
			$main_page     = ! empty( $wp_query->get( 'main_page' ) ) ? $wp_query->get( 'main_page' ) : $wp_query->get( 'name' );
			$action        = $wp_query->get( 'action' );
			$tid           = $wp_query->get( 'tid' );
			$ticket_type   = $wp_query->get( 'type' );
			$pagination    = $wp_query->get( 'pagination' );
			$paged         = $wp_query->get( 'paged' );
			$create_ticket = $wp_query->get( 'create' );
			$aid           = $wp_query->get( 'aid' );

			if ( empty( $pagename ) ) {
				if ( 'register' === $main_page ) {
					Template_Front\WKUVDESK_Register::get_instance();
				} else {
					$this->wkuvdesk_active_shortcode();
				}
				return;
			}

			$is_customer = 'customer' === $main_page && $current_user->ID;
			$is_admin    = 'admin' === $main_page && $current_user->ID;

			if ( $is_customer ) {
				if ( 'view' === $action && 'ticket' === $ticket_type && ! empty( $tid ) ) {
					Template_Front\WKUVDESK_Customer_Ticket_View::get_instance();
				} elseif ( 'page' === $pagination && ! empty( $paged ) ) {
					Template_Front\WKUVDESK_Customer::get_instance();
				} elseif ( 'create-ticket' === $create_ticket ) {
					Template_Front\WKUVDESK_Customer_Create_Ticket::get_instance();
				} else {
					Template_Front\WKUVDESK_Customer::get_instance();
				}
			} elseif ( $is_admin ) {
				if ( 'view' === $action && 'ticket' === $ticket_type && ! empty( $tid ) ) {
					new Template_Admin\WKUVDESK_Admin_Ticket();
				} else {
					new Template_Admin\WKUVDESK_Admin_Ticket();
				}
			} elseif ( 'download' === $main_page && ! empty( $aid ) ) {
				Template_Front\WKUVDESK_Download::get_instance();
			} elseif ( 'login' === $main_page ) {
				$this->wkuvdesk_active_shortcode();
			} elseif ( 'register' === $main_page ) {
				Template_Front\WKUVDESK_Register::get_instance();
			} else {
				$this->wkuvdesk_active_shortcode();
			}
		}

		/**
		 * Active main shortcode.
		 *
		 * @return void
		 */
		public function wkuvdesk_active_shortcode() {
			add_shortcode( 'uvdesk', array( $this, 'wkuvdesk_customer_login' ) );
		}

		/**
		 * Customer Login form.
		 *
		 * @return void
		 */
		public function wkuvdesk_customer_login() {
			if ( ! is_user_logged_in() ) {
				$args = array(
					'redirect'       => home_url( '/uvdesk/customer' ),
					'form_id'        => esc_attr( 'loginform-custom' ),
					'label_username' => esc_html__( 'Email or Username', 'uvdesk' ),
					'label_password' => esc_html__( 'Password', 'uvdesk' ),
					'label_remember' => esc_html__( 'Remember Me', 'uvdesk' ),
					'label_log_in'   => esc_html__( 'Login', 'uvdesk' ),
					'remember'       => true,
				);

				$output = '<div class="wk-uvdesk-login-form">';

				$output .= '<h1>' . esc_html__( 'Member Login', 'uvdesk' ) . '</h1>';
				if ( 'failed' === filter_input( INPUT_GET, 'login', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
					$output .= '<div class="text-center wk-uvdesk-pass-error"><span>' . esc_html__( 'Invalid details- please enter valid details.', 'uvdesk' ) . '</span></div>';
				}
				$output .= '<div class="head"><img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( WKUVDESK_PLUGIN_URL . 'assets/images/user.png' ) ) . '" alt="' . esc_attr__( 'User', 'uvdesk' ) . '"/></div>';
				ob_start();
				wp_login_form( $args );
				$output      .= ob_get_clean();
				$output      .= '<div class="text-center"><h3>' . esc_html__( 'OR', 'uvdesk' ) . '</h3>';
				$output      .= '<h4><a href="' . esc_url( home_url( '/uvdesk/register' ) ) . '">' . esc_html__( 'Register Here', 'uvdesk' ) . '</a></h4></div>';
				$output      .= '</div>';
				$allowed_html = array(
					'a'      => array(
						'href'   => true,
						'title'  => true,
						'class'  => true,
						'target' => true,
					),
					'strong' => array(),
					'em'     => array(),
					'p'      => array(
						'class' => true,
					),
					'div'    => array(
						'class' => true,
						'id'    => true,
					),
					'span'   => array(
						'class' => true,
					),
					'br'     => array(),
					'img'    => array(
						'src'    => true,
						'alt'    => true,
						'class'  => true,
						'width'  => true,
						'height' => true,
						'id'     => true,
						'style'  => true,
					),
					'form'   => array(
						'method' => true,
						'action' => true,
						'class'  => true,
						'id'     => true,
					),
					'input'  => array(
						'type'        => true,
						'name'        => true,
						'id'          => true,
						'class'       => true,
						'value'       => true,
						'placeholder' => true,
						'required'    => true,
						'checked'     => true,
					),
					'label'  => array(
						'for'   => true,
						'class' => true,
					),
					'h1'     => array(
						'class' => true,
					),
					'h2'     => array(
						'class' => true,
					),
					'h3'     => array(
						'class' => true,
					),
					'h4'     => array(
						'class' => true,
					),
					'button' => array(
						'type'  => true,
						'class' => true,
						'id'    => true,
					),
				);

				echo wp_kses( $output, $allowed_html );
			} else {
				$user_id = get_current_user_id();
				$url     = ( 1 === $user_id ) ? admin_url( 'admin.php?page=uvdesk_ticket_system' ) : home_url( '/uvdesk/customer' );

				wp_safe_redirect( esc_url( $url ) );
				exit;
			}
		}

		/**
		 * Redirect customer after logout.
		 *
		 * @return void
		 */
		public function wkuvdesk_redirect_after_logout() {
			$url          = ! current_user_can( 'manage_options' ) ? 'uvdesk/?loggedout=true' : 'wp-login.php?loggedout=true';
			$redirect_url = home_url( $url );

			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * After login redirect user.
		 *
		 * @return void
		 */
		public function wkuvdesk_template_redirect() {
			global $wp_query;

			$main_page = ! empty( $wp_query->get( 'main_page' ) ) ? $wp_query->get( 'main_page' ) : $wp_query->get( 'name' );

			if ( is_page( 'uvdesk' ) && 'login' === $main_page && is_user_logged_in() ) {
				$url = ( 1 === get_current_user_id() ) ? admin_url( 'admin.php?page=uvdesk_ticket_system' ) : home_url( '/uvdesk/customer' );
				wp_safe_redirect( esc_url( $url ) );
				exit();
			}

			if ( is_page( 'uvdesk' ) && 'customer' === $main_page && is_user_logged_in() ) {
				if ( 1 === get_current_user_id() ) {
					wp_safe_redirect( esc_url( admin_url( 'admin.php?page=uvdesk_ticket_system' ) ) );
					exit();
				}
			} elseif ( is_page( 'uvdesk' ) && 'admin' === $main_page && is_user_logged_in() ) {
				if ( 1 !== get_current_user_id() ) {
					wp_safe_redirect( esc_url( home_url( '/uvdesk/customer' ) ) );
					exit();
				}
			}

			if ( is_page( 'uvdesk' ) && 'customer' === $main_page && ! is_user_logged_in() ) {
				wp_safe_redirect( esc_url( home_url( '/uvdesk/login' ) ) );
				exit();
			}
		}

		/**
		 * Redirect user after login failed.
		 *
		 * @return void
		 */
		public function wkuvdesk_front_end_login_fail() {
			$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
			$referrer = explode( '?', $referrer );
			if ( ! empty( $referrer ) && ! strstr( $referrer[0], 'wp-login' ) && ! strstr( $referrer[0], 'wp-admin' ) ) {
				wp_safe_redirect( $referrer[0] . '?login=failed' );
				exit;
			}
		}

		/**
		 * Customer create ticket.
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
				if ( null !== filter_input( INPUT_POST, 'submit1', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'submit1', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
					$captcha = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
					if ( null === $captcha || false === $captcha ) {
						$error[] = esc_html__( 'Please check the the captcha form.', 'uvdesk' );
					}
					$response      = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha . '&remoteip=' . esc_attr( isset( $_SERVER['HTTP_X_REAL_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) ) : '' ) );
					$response_body = wp_remote_retrieve_body( $response );
					$response_data = json_decode( $response_body );
					if ( false === $response_data->success ) {
						?>
						<script>
						jQuery(".uv-uvdesk-captcha-error").css('display','block');
						</script>
						<?php
					}

					if ( null !== filter_input( INPUT_POST, 'uvdesk_create_ticket_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
						if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'uvdesk_create_ticket_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ), 'uvdesk_create_ticket_nonce_action' ) ) {
							esc_html_e( 'Sorry, your nonce did not verify.', 'uvdesk' );
							exit;
						} elseif ( ! empty( filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && ! empty( filter_input( INPUT_POST, 'reply', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && ! empty( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
								$user       = wp_get_current_user();
								$user_name  = $user->user_nicename;
								$user_email = $user->user_email;
								$_post_data = array(
									'name'    => $user_name,
									'from'    => $user_email,
									'subject' => sanitize_text_field( filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
									'reply'   => sanitize_text_field( filter_input( INPUT_POST, 'reply', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
									'type'    => '4',
								);

								if ( ! empty( $user ) ) {
									if ( isset( $_FILES['attachments']['size'][0] ) && 0 === $_FILES['attachments']['size'][0] ) {
										$ticket_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_create_new_ticket( $_post_data );
										$ticket_status = json_decode( $ticket_status );
										if ( isset( $ticket_status->message ) ) {
											echo '<div class="alert alert-success alert-fixed alert-load">
										<span>	<span class="uv-uvdesk-remove-file alert-msg"></span>' . esc_attr( $ticket_status->message ) . '</span></div>';
											?>
										<script>
										setTimeout(function() {
											jQuery(".alert-fixed").fadeOut()
										}, 4000);
										</script>
											<?php
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
											$file_mime_type   = isset( $_FILES['attachments']['tmp_name'][0] ) ? sanitize_text_field( mime_content_type( sanitize_text_field( $_FILES['attachments']['tmp_name'][0] ) ) ) : '';                                            if ( in_array( (string) $file_mime_type, $valid_file_mimes, true ) ) {
												$ticket_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_create_new_ticket_with_attachement( $_post_data, array_map( 'sanitize_text_field', $_FILES['attachments'] ) );

												$ticket_status = json_decode( $ticket_status );
												if ( isset( $ticket_status->success ) && ( 200 === $ticket_status->success ) ) {
													echo '<div class="alert alert-success alert-fixed alert-load">
												<span>
												<span class="uv-uvdesk-remove-file alert-msg"></span>
												' . esc_html( $ticket_status->message ) . '
												</span>
												</div>';
													?>
												<script>
												setTimeout(function() {
													jQuery(".alert-fixed").fadeOut()
												}, 4000);
												</script>
													<?php
												} elseif ( isset( $ticket_status->error ) && ( 404 === $ticket_status->error ) ) {
														$error[] = $ticket_status->message;
												} else {
													$error[] = $ticket_status->message;
												}
											} else {
												echo '<div class="alert alert-success alert-fixed alert-load">
														<span>
															<span class="uv-uvdesk-remove-file alert-msg"></span>
															' . esc_html__( 'Please insert a valid image.', 'uvdesk' ) . '
														</span>
													  </div>';
												?>
											<script>
											setTimeout(function() {
													jQuery(".alert-fixed").fadeOut()
											}, 4000);
											</script>

												<?php
											}
									}
								} else {
									echo '<div class="text-center uv-notify"><span class="alert alert-danger">' . esc_html__( 'There is some issue with user permission try again.', 'uvdesk' ) . '</span><div>';
								}
						} else {
							$error[] = esc_html__( 'Some fields are empty.', 'uvdesk' );
						}
					}
				}
			} else {
				echo '<h1>' . esc_html__( 'Please Enter a valid Access Token', 'uvdesk' ) . ' <h1> ';
			}

			if ( ! empty( $error ) ) {
				?>
			<div class="alert alert-success alert-fixed alr-err alert-load"><?php esc_html_e( 'uvdesk', 'uvdesk' ); ?>
				<span>
					<span class="uv-uvdesk-remove-file alert-msg"></span>
						<?php
						foreach ( $error as $sno => $err_mes ) {
							echo esc_html( $err_mes ) . '<br>';
						}
						?>
						<script>
						setTimeout(function() {
								jQuery(".alert-fixed").fadeOut()
						}, 4000);
						</script>
				</span>
			</div>
				<?php
			}
			?>
			<div class="main-body">
				<div class="container">
					<div class="title-uvdesk">
						<h2><?php esc_html_e( 'Create a Ticket', 'uvdesk' ); ?></h2>
						<a class="wk-uvdesk-to-home" href="<?php echo esc_url( site_url() . '/uvdesk/customer/' ); ?>"><?php esc_html_e( 'Back', 'uvdesk' ); ?></a>
					</div>
					<form name="" method="post" action="" enctype="multipart/form-data" novalidate="false" id="wk-uvdesk-create-ticket-form">
					<?php wp_nonce_field( 'uvdesk_create_ticket_nonce_action', 'uvdesk_create_ticket_nonce' ); ?>
						<div class="form-group ">
							<label for="type" ><?php esc_html_e( 'Type', 'uvdesk' ); ?></label>
							<select id="type" name="type" required data-role="<?php echo esc_attr( 'tagsinput' ); ?>" data-live-search="<?php echo esc_attr( 'data-live-search' ); ?>" class="selectpicker" tabindex="-98">
								<option value="" selected="selected"><?php esc_html_e( 'Choose query type', 'uvdesk' ); ?></option>
								<option value="<?php echo esc_attr( '87' ); ?>"><?php esc_html_e( 'Support', 'uvdesk' ); ?></option>
							</select>
						</div>
						<div class="form-group ">
							<label for="subject"><?php esc_html_e( 'Choose query type', 'uvdesk' ); ?><?php esc_html_e( 'Subject', 'uvdesk' ); ?></label>
							<input type="text" id="subject" name="subject" required placeholder="<?php esc_attr_e( 'Enter Subject', 'uvdesk' ); ?>" class="form-control">
						</div>
						<div class="form-group ">
							<label for="reply" ><?php esc_html_e( 'Choose query type', 'uvdesk' ); ?><?php esc_html_e( 'Message', 'uvdesk' ); ?></label>
							<textarea id="reply" name="reply" required placeholder="<?php esc_attr_e( 'Brief Description about your query', 'uvdesk' ); ?>" data-iconlibrary="<?php echo esc_attr( 'fa' ); ?>" data-height="<?php echo esc_attr( '250' ); ?>" class="form-control"></textarea>
						</div>
						<div class="form-group ">
							<div class="form-group wk-uvdesk-attachments">
								<div class="labelWidget">
									<input id="uv-uvdesk-attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="attach-file" decoratefile="decorateFile" infolabeltext="<?php esc_attr_e( '+ Attach File', 'uvdesk' ); ?>" infolabel="<?php echo esc_attr( 'right' ); ?>" name="attachments[]">
									<label class="attach-file pointer"></label>
									<i class="uv-uvdesk-remove-file" id="remove-att"></i>
								</div>
								<span id="addFile" class="label-right pointer"><?php esc_html_e( 'Attach File', 'uvdesk' ); ?></span>
							</div>
						</div>
						<input type="hidden" id="_token" name="_token" value="<?php echo esc_attr( 'eJPW5s_yBH1S6iTM1eLI18Kdb304tl-IwIqE0ktJTd8' ); ?>">
						<?php
						$client_key = empty( $client_key ) ? esc_html__( 'Check for client Keys', 'uvdesk' ) : $client_key;
						?>
						<div class="g-recaptcha" id="recaptcha" data-sitekey="<?php echo esc_attr( $client_key ); ?>" style="transform:scale(0.77);transform-origin:0;-webkit-transform:scale(0.77);transform:scale(0.77);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>
						<div class="uv-uvdesk-captcha-error"><?php esc_html_e( 'Please verify that you are not a robot.', 'uvdesk' ); ?></div>
						<button type="submit" id="submit1" name="submit1" class="wk-uvdesk-btn-create-tkt"><?php esc_html_e( 'Create Ticket', 'uvdesk' ); ?></button>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Show create ticket in customer side.
		 *
		 * @return void
		 */
		public function wkuvdesk_customer_ticket_view() {
			$ticket_id           = intval( get_query_var( 'tid', '' ) );
			$paged               = intval( get_query_var( 'paged', '' ) );
			$paged               = max( 1, $paged );
			$current_user        = wp_get_current_user();
			$c_email             = $current_user->user_email;
			$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );
			$allowed_html        = array(
				'a'      => array(
					'href'   => true,
					'title'  => true,
					'class'  => true,
					'target' => true,
				),
				'strong' => array(),
				'em'     => array(),
				'p'      => array(
					'class' => true,
				),
				'div'    => array(
					'class' => true,
					'id'    => true,
				),
				'span'   => array(
					'class' => true,
				),
				'br'     => array(),
				'img'    => array(
					'src'    => true,
					'alt'    => true,
					'class'  => true,
					'width'  => true,
					'height' => true,
					'id'     => true,
					'style'  => true,
				),
				'form'   => array(
					'method' => true,
					'action' => true,
					'class'  => true,
					'id'     => true,
				),
				'input'  => array(
					'type'        => true,
					'name'        => true,
					'id'          => true,
					'class'       => true,
					'value'       => true,
					'placeholder' => true,
					'required'    => true,
					'checked'     => true,
				),
				'label'  => array(
					'for'   => true,
					'class' => true,
				),
				'h1'     => array(
					'class' => true,
				),
				'h2'     => array(
					'class' => true,
				),
				'h3'     => array(
					'class' => true,
				),
				'h4'     => array(
					'class' => true,
				),
				'button' => array(
					'type'  => true,
					'class' => true,
					'id'    => true,
				),
			);

			if ( ! empty( $uvdesk_access_token ) ) {
				if ( null !== filter_input( INPUT_POST, 'submit-thread', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
					if ( null !== filter_input( INPUT_POST, 'uvdesk_thread_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
						$nonce = isset( $_POST['uvdesk_thread_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['uvdesk_thread_nonce'] ) ) : '';
						if ( ! wp_verify_nonce( $nonce, 'uvdesk_thread_nonce_action' ) ) {
							esc_html_e( 'Sorry, your nonce did not verify.', 'uvdesk' );
							exit;
						} elseif ( null !== filter_input( INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL ) && ! empty( filter_input( INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL ) ) && null !== filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && null !== filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && null !== filter_input( INPUT_POST, 'thread_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( filter_input( INPUT_POST, 'thread_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
							$sdt   = explode( ',', filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
							$reply = isset( $_POST['thread_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['thread_desc'] ) ) : '';

							if ( isset( $_FILES['attachments']['type'] ) && 0 === count( $_FILES['attachments']['type'] ) ) {
									$thread_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_post_thread_data_api(
										'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json',
										array(
											'threadType' => sanitize_text_field( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
											'reply'      => $reply,
											'status'     => sanitize_text_field( $sdt[0] ),
											'actAsType'  => 'customer',
											'actAsEmail' => sanitize_text_field( filter_input( INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL ) ),
										)
									);
							} else {
									$thread_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_post_thread_data_api_with_attachment(
										'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json',
										array(
											'threadType' => sanitize_text_field( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
											'reply'      => $reply,
											'status'     => sanitize_text_field( $sdt[0] ),
											'actAsType'  => 'customer',
											'actAsEmail' => sanitize_text_field( filter_input( INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL ) ),
										),
										filter_var( $_FILES['attachments'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY )
									);
							}

								$thread_status = json_decode( $thread_status );
								echo '<div class="alert alert-success alert-fixed alert-load">
									 <span>
											 <span class="uv-uvdesk-remove-file alert-msg"></span>
											 ' . esc_html( $thread_status->message ) . '
									 </span>
							 </div>';
							?>
							<script>
							setTimeout(
							function() {
									jQuery(".alert-fixed").fadeOut()
							}
							, 4000);
							</script>
								<?php
						}
					}
				}

				$arr_sum        = array(
					'actAsType'  => 'customer',
					'actAsEmail' => $c_email,
				);
				$ticket_details = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'ticket/' . $ticket_id . '.json', $arr_sum );

				if ( isset( $ticket_details->error ) || empty( $ticket_details ) ) {
					echo '<h4>' . esc_html( $ticket_details->error_description ) . '</h1><h4>' . esc_html__( 'Please contact Administrator.', 'uvdesk' ) . '</h3>';
				} else {
					if ( ! empty( $ticket_details->ticket->status->name ) && isset( $ticket_details->ticket->status->name ) ) {
						$ticket_status_name = $ticket_details->ticket->status->name;
					}

					if ( ! empty( $ticket_details->ticket->formatedCreatedAt ) && isset( $ticket_details->ticket->formatedCreatedAt ) ) {
						$ticket_created = $ticket_details->ticket->formatedCreatedAt;
					}

					if ( ! empty( $ticket_details->ticket->customer->detail->customer->name ) && isset( $ticket_details->ticket->customer->detail->customer->name ) ) {
						$customer_name = $ticket_details->ticket->customer->detail->customer->name;
					}

					if ( ! empty( $ticket_details->create_thread->reply ) && isset( $ticket_details->create_thread->reply ) ) {
						$created_thread = $ticket_details->create_thread->reply;
					}

					$ticket_thread = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'ticket/' . $ticket_details->ticket->id . '/trash.json' );
					?>
			<div class="uv-uvdesk-block-container wk-uvdesk-content-wrap">
				<div class="uvuvdesk-pre-loader">
					<?php
					echo '<img ' . wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( array() ), $allowed_html ) . ' alt="' . esc_attr_e( 'Loading...', 'uvdesk' ) . '" />';
					?>
				</div>
				<div class="tkt-front-header">
					<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/' ); ?>" class='to-main-list'>  <?php esc_html_e( 'All tickets', 'uvdesk' ); ?></a>
				</div>
				<div class="side-section-front">
					<p class='side-sec-head'><?php esc_html_e( 'TICKET INFROMATION', 'uvdesk' ); ?></p>
					<span>
						<span class="side-title"><?php esc_html_e( 'ID', 'uvdesk' ); ?></span>
						<span class="side-info"><?php esc_html_e( '#', 'uvdesk' ); ?> <?php echo esc_html( $ticket_details->ticket->id ); ?></span>
					</span>
					<span>
						<span class="side-title"><?php esc_html_e( 'Timestamp', 'uvdesk' ); ?></span>
						<span class="side-info"><?php echo esc_html( $ticket_details->ticket->formatedCreatedAt ); ?></span>
					</span>
					<span>
						<span class="side-title"><?php esc_html_e( 'Total Replies', 'uvdesk' ); ?></span>
						<span class="side-info"><?php echo esc_html( $ticket_details->ticketTotalThreads ); ?></span>
					</span>
					<hr>
					<span>
						<span class="side-title"><?php esc_html_e( 'Agent', 'uvdesk' ); ?></span>
						<span class="side-info">
							<?php
							echo empty( $ticket_details->ticket->agent->detail->name ) ? esc_html__( 'Not Assigned', 'uvdesk' ) : esc_html( $ticket_details->ticket->agent->detail->name );
							?>
							</span>
					</span>
						<?php if ( ! empty( $ticket_details->ticket->priority->name ) ) : ?>
					<span>
						<span class="side-title"><?php esc_html_e( 'Priority', 'uvdesk' ); ?> </span>
						<span class="side-info"><b class="uv-uvdesk-priority-check" style="<?php echo esc_attr( 'background-color:' . $ticket_details->ticket->priority->color ); ?>"></b><?php echo esc_html( $ticket_details->ticket->priority->name ); ?></span>
					</span>
						<?php endif; ?>
					<span>
						<span class="side-title"><?php esc_html_e( 'Status', 'uvdesk' ); ?></span>
						<span class="side-info"> <?php echo esc_html( $ticket_status_name ); ?></span>
					</span>
				</div>
				<div class="whole-wrapper">
						<div class="tkt-front-intro">
							<div style="display:inline-block;margin:10px 20px;">
								<span class="wk-highlight" ><?php esc_html_e( 'Subject :- ', 'uvdesk' ); ?></span>
								<h4 class="tkt-subject">
									<?php echo esc_html( $ticket_details->ticket->subject ); ?>
								</h4>
								<p>
									<span class="wk-space">
										<span class="wk-highlight"><?php esc_html_e( 'Created on - ', 'uvdesk' ); ?></span><?php echo esc_html( $ticket_details->ticket->formatedCreatedAt ); ?>
									</span>
									<span class="wk-space">
										<span class="wk-highlight"><?php esc_html_e( 'Agent - ', 'uvdesk' ); ?></span>
										<?php
										$agent_name = ! empty( $ticket_details->ticket->agent->detail->name ) ?
														esc_html( $ticket_details->ticket->agent->detail->name ) :
														esc_html__( 'Not Assigned', 'uvdesk' );
										echo esc_html( $agent_name );
										?>
									</span>
								</p>
							</div>
						</div>
						<hr>
						<div class="ticket-create">
								<div class="thread-created-info">
									<?php if ( ! empty( $customer_name ) ) : ?>
									<div class="msg-header">
										<span class="img-icon">
											<?php
											$thumbnail = isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) ?
												$ticket_details->ticket->customer->detail->customer->smallThumbnail :
												WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
											echo '<img ' . wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ), $allowed_html ) . ' alt="' . esc_attr__( 'Loading ...', 'uvdesk' ) . '" />';
											?>
										</span>
										<span class="info">
											<span class="rpy-name"><?php echo esc_html( $customer_name ); ?></span>&emsp; <?php esc_html_e( 'created ticket', 'uvdesk' ); ?>
											<?php if ( ! empty( $ticket_created ) ) : ?>
											<br>
											<span class=" create-date">
												<?php echo esc_html( $ticket_created ); ?>
											</span>
											<?php endif; ?>
										</span>
									</div>
									<?php endif; ?>
									<div class="frnt-msg">
										<?php
										if ( ! empty( $created_thread ) ) :
											echo wp_kses_post( $created_thread );
										endif;

										if ( ! empty( $ticket_details->createThread->attachments ) ) :
											?>
											<div class="thread-attachments">
												<div class="attachments">
													<p><strong><?php esc_html_e( 'Uploaded files', 'uvdesk' ); ?></strong></p>
											<?php
											foreach ( $ticket_details->createThread->attachments as $attchment_key => $attchment_value ) :
												$domain       = get_option( 'uvdesk_company_domain', '' );
												$access_token = get_option( 'uvdesk_access_token', '' );
												$aid          = $attchment_value->id;
												$anamea       = $attchment_value->name;
												$tmp          = ( explode( '.', $anamea ) );
												$aname        = end( $tmp );
												$img_ar       = array( 'png', 'jpg', 'jpeg' );

												if ( in_array( (string) $aname, $img_ar, true ) ) {
													$wk_image = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
													$wk_image = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
													?>
														<a href="<?php echo esc_url( $wk_image ); ?>" target="_blank">
															<img <?php echo wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $wk_image ), $allowed_html ); ?> class="fa fa-file zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="tooltip" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"/>
														</a>
														<?php
												} elseif ( 'zip' === $aname ) {
													$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
													?>
													<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
														<i class="wk-file-zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
													</a>
														<?php
												} else {
													$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
													?>
													<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
														<i class="wk-file" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
														</i>
													</a>
													<?php
												}
											endforeach;
											?>
											</div>
										</div>
										<?php endif; ?>
								</div>
							</div>
						</div>
						<hr>
						<?php
						$tot_post   = $ticket_thread->pagination->totalCount;
						$last_count = $ticket_thread->pagination->lastItemNumber;

						if ( $tot_post - $last_count > 0 && $last_count > 0 ) {
							?>
							<div style="position:relative;" id="ajax-load-page">
								<span class="pagination-space"  data-page="<?php echo esc_attr( $ticket_details->ticket->id . '-' . $ticket_thread->pagination->current ); ?>"><?php echo esc_html( $tot_post - $last_count ); ?></span>
							</div>
							<div id="uv-desk-content-here-aj"></div>
							<hr>
							<?php
						}
						?>
						<div class="ticket-view-page" id="ticket-view-page">
							<div class="ticket-thread">
								<?php
								for ( $i = count( $ticket_thread->threads ) - 1; $i >= 0; $i-- ) :
									$thread_value = $ticket_thread->threads[ $i ];
									?>
									<div class="thread-created-info">
										<div class="msg-header">
											<span class="img-icon">
												<?php
												$thumbnail = ! empty( $thread_value->user->smallThumbnail ) ? $thread_value->user->smallThumbnail : WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
												echo '<img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ) ) . ' alt="' . esc_attr__( 'Loading ...', 'uvdesk' ) . '" />';
												?>
												</span>
												<span class="info">
													<span class="rpy-name">
														<?php
														if ( isset( $thread_value->user->detail->agent ) ) {
															echo esc_html( $thread_value->user->detail->agent->name );
														} else {
															echo esc_html( $thread_value->user->detail->customer->name );
														}
														?>
													</span>
													<?php if ( ! empty( $thread_value->formatedCreatedAt ) ) : ?>
													<br><span class=" create-date">
															<?php echo esc_html( $thread_value->formatedCreatedAt ); ?>
													</span>
													<?php endif; ?>
												</span>
											</div>
											<div class="frnt-msg">
												<?php
												if ( ! empty( $thread_value->reply ) ) {
													echo wp_kses_post( $thread_value->reply );
												}
												?>
												<?php
												if ( ! empty( $thread_value->attachments ) ) {
													?>
												<div class="thread-attachments">
													<div class="attachments">
														<p><strong><?php esc_html_e( 'Uploaded files', 'uvdesk' ); ?></strong></p>
															<?php
															foreach ( $thread_value->attachments as $attchment_key => $attchment_value ) {
																$domain       = get_option( 'uvdesk_company_domain', '' );
																$access_token = get_option( 'uvdesk_access_token', '' );
																$aid          = $attchment_value->id;
																$anamea       = $attchment_value->name;
																$tmp          = ( explode( '.', $anamea ) );
																$aname        = end( $tmp );
																$img_ar       = array( 'png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp', 'tiff', 'svg' );

																if ( in_array( (string) $aname, $img_ar, true ) ) {
																	$wk_image = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
																	$wk_image = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
																	?>
																<a href="<?php echo esc_url( $wk_image ); ?>" target="_blank">
																	<img <?php echo wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $wk_image ) ); ?> class="fa fa-file zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
																</a>
																		<?php
																} elseif ( 'zip' === $aname ) {
																	$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
																	?>
																	<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																		<i class="wk-file-zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
																		</i>
																	</a>
																		<?php
																} else {
																	$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
																	?>
																		<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																			<i class="wk-file" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
																		</a>
																	<?php
																}
															}
															?>
													</div>
												</div>
												<?php } ?>
											</div>
										</div>
										<hr>
								<?php endfor; ?>
							</div>
						</div>
						<div role="tabpanel" class="tab-pane active" id="reply">
							<div class="msg-header">
								<span class="img-icon">
										<?php
										$thumbnail_url = isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail )
											? esc_url( $ticket_details->ticket->customer->detail->customer->smallThumbnail )
											: WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
										echo '<img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ) ) . ' alt="' . esc_attr__( 'Loading ...', 'uvdesk' ) . '" />';
										?>
								</span>
								<span class="info">
									<span class="rpy-name"><?php esc_html_e( 'You can write your reply', 'uvdesk' ); ?></span>
								</span>
							</div>
							<form enctype="multipart/form-data" method="post" action="">
								<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?>
								<input type="hidden" name="customer_email" value="<?php echo esc_attr( $ticket_details->ticket->customer->email ); ?>">
								<input type="hidden" name="threadType" value="<?php echo esc_attr( 'reply' ); ?>">
								<input type="hidden" name="status" class="reply-status" value="<?php echo esc_attr( '1,' . $ticket_details->ticket->id ); ?>">
								<?php
									$settings = array(
										'media_buttons'    => true, // show insert/upload button(s).
										'textarea_name'    => 'thread_desc',
										'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
										'tabindex'         => '',
										'teeny'            => false,
										'dfw'              => false,
										'tinymce'          => true, /* load TinyMCE, can be used to pass settings directly to TinyMCE using an array()*/
										'quicktags'        => false, /* load Quicktags, can be used to pass settings directly to Quicktags using an array()*/
										'force_br_newlines' => true,
										'force_p_newlines' => false,
									);

									echo wp_kses_post( wp_editor( '', 'product_desc', $settings ) );

									?>
									<div class="form-group wk-uvdesk-attachments">
											<div class="labelWidget">
													<input id="uv-uvdesk-attachments" class="fileHide" type="file" enableremoveoption="<?php echo esc_attr( 'enableRemoveOption' ); ?>" decoratecss="<?php echo esc_attr( 'attach-file' ); ?>" decoratefile="<?php echo esc_attr( 'decorateFile' ); ?>" infolabeltext="<?php esc_attr_e( '+ Attach File', 'uvdesk' ); ?>" infolabel="right" name="attachments[]">
													<label class="attach-file pointer"></label>
													<i class="uv-uvdesk-remove-file" id="remove-att"></i>
											</div>
											<span id="addFile" class="label-right pointer"><?php esc_html_e( 'Attach File', 'uvdesk' ); ?></span>
									</div>
									<div class="reply-submit">
											<button class="submit-rply" type="submit" name="submit-thread" value="submit-thread">
													<?php esc_html_e( 'Reply', 'uvdesk' ); ?>
											</button>
									</div>
							</form>
						</div>
					</div>
				</div>
					<?php
				}
			} else {
				echo '<h1>' . esc_html__( 'Please Enter a valid Access Token', 'uvdesk' ) . '</h1>';
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
