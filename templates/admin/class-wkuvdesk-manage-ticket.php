<?php
/**
 * WKUVDESK_Manage_Ticket handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Helper;
use WKUVDESK\Includes;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Manage_Ticket' ) ) {
	/**
	 * WKUVDESK_Manage_Ticket class.
	 */
	class WKUVDESK_Manage_Ticket {
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
			$this->wkuvdesk_manage_ticket();
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
		 * @return mixed
		 */
		public static function wkuvdesk_manage_ticket() {
			if ( 'delete' === filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
				$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				if ( ! empty( $post_id ) ) {
					$ids     = $post_id;
					$success = Helper\WKUVDESK_Api_Handler::wkuvdesk_delete_tag_ticket( 'ticket/' . $ids . '/trash.json' );
					if ( $success ) {
						wp_safe_redirect( admin_url( 'admin.php?page=uvdesk_ticket_system&msg=deleted' ) );
						exit;
					}
				}
			}

			if ( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
				$ticket_id = wp_unslash( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			} else {
				$admin_url = admin_url( 'admin.php?page=uvdesk_ticket_system' );
				printf(
					'<h2>%s <a href="%s">%s</a>.</h2>',
					esc_html__( 'Select a ticket first from', 'uvdesk' ),
					esc_url( $admin_url ),
					esc_html__( 'here', 'uvdesk' )
				);
				return;
			}

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
			if ( filter_input( INPUT_POST, 'uvdesk_thread_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'uvdesk_thread_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ), 'uvdesk_thread_nonce_action' ) ) {
					esc_html_e( 'Sorry, your nonce did not verify.', 'uvdesk' );
					return;
				} elseif ( filter_input( INPUT_POST, 'agent_email', FILTER_SANITIZE_EMAIL ) && ! empty( filter_input( INPUT_POST, 'agent_email', FILTER_SANITIZE_EMAIL ) ) && filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) && filter_input( INPUT_POST, 'thread_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( filter_input( INPUT_POST, 'thread_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
					$sdt = explode( ',', sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) );
					if ( isset( $_FILES['attachments']['size'][0] ) && 0 === $_FILES['attachments']['size'][0] ) {
						$reply         = isset( $_POST['thread_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['thread_desc'] ) ) : '';
						$thread_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_post_thread_data_api(
							'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json',
							array(
								'threadType' => sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ),
								'reply'      => $reply,
								'status'     => sanitize_text_field( $sdt[0] ),
								'actAsType'  => 'agent',
								'actAsEmail' => sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'agent_email', FILTER_SANITIZE_EMAIL ) ) ),
							)
						);
						$thread_status = json_decode( $thread_status );
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
							$reply         = isset( $_POST['thread_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['thread_desc'] ) ) : '';
							$thread_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_post_thread_data_api_with_attachment(
								'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json',
								array(
									'threadType' => sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ),
									'reply'      => $reply,
									'status'     => sanitize_text_field( $sdt[0] ),
									'actAsType'  => 'agent',
									'actAsEmail' => sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'agent_email', FILTER_SANITIZE_EMAIL ) ) ),
								),
								array_map( 'sanitize_text_field', $_FILES['attachments'] ),
							);
							$thread_status = json_decode( $thread_status );
						} else {
							echo '<div class="alert alert-success alert-fixed  ">
								<span>
									<span class="uv-uvdesk-remove-file alert-msg"></span>
									' . esc_html__( 'Please upload a valid file (PDF, DOC, DOCX, PNG, JPG, JPEG, GIF, ZIP, RAR) with maximum size of 20MB', 'uvdesk' ) . '
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
					?>
				<div class="updated notice">
					<p><?php echo isset( $thread_status->message ) ? esc_html( $thread_status->message ) : esc_html__( 'Contact technical team ...', 'uvdesk' ); ?></p>
					</div>
						<?php
				}
			}
			$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );

			if ( ! empty( $uvdesk_access_token ) ) {
				$ticket_id      = ! empty( $ticket_id ) ? $ticket_id : wp_unslash( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
				$ticket_details = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'ticket/' . $ticket_id . '.json' );

				if ( isset( $ticket_details->error ) || empty( $ticket_details ) ) {
					echo wp_kses_post( '<h1>' . esc_html__( 'Invalid Details.', 'uvdesk' ) . '</h1><h3>' . esc_html__( 'Please contact Administrator.', 'uvdesk' ) . '</h3>' );
				} else {
						$ticket_thread    = ! empty( $ticket_details->ticket->id ) ? Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'ticket/' . $ticket_details->ticket->id . '/threads.json' ) : '';
						$data_api_members = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api(
							'members.json',
							array(
								'sort'     => 'name',
								'fullList' => 'true',
							)
						);

					if ( isset( $ticket_thread->error ) || empty( $ticket_thread ) || isset( $data_api_members->error ) || empty( $data_api_members ) ) {
						echo wp_kses_post( '<h1>' . esc_html__( 'Invalid Details.', 'uvdesk' ) . '</h1><h3>' . esc_html__( 'Please contact Administrator.', 'uvdesk' ) . '</h3>' );
					} else {
						$tkt_agent       = ! empty( $ticket_details->ticket->agent->detail->agent->name ) ? $ticket_details->ticket->agent->detail->agent->name : '';
						$tkt_agent_email = ! empty( $ticket_details->ticket->agent->email ) ? $ticket_details->ticket->agent->email : '';
						?>
					<div>
						<header style="display:inline-block;">
							<h1>
							<?php
							esc_html_e( 'Ticket #', 'uvdesk' );
							echo isset( $ticket_details->ticket->incrementId ) ? esc_attr( $ticket_details->ticket->incrementId ) : '';
							?>
							</h1>
							<input type='hidden' value="<?php echo isset( $ticket_details->ticket->id ) ? esc_attr( $ticket_details->ticket->id ) : ''; ?>" class="uv-ticket-id">
							<a href = "<?php echo esc_url( admin_url( 'admin.php?page=uvdesk_ticket_system' ) ); ?>" class="button button-primary back-to-list"><?php esc_html_e( 'All tickets', 'uvdesk' ); ?></a>
						</header>
						<div class="uvuvdesk-pre-loader">
							<img class="uv-uvdesk-ajax-loader-img" <?php echo wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( array() ) ); ?> alt="<?php esc_attr_e( 'Loading...', 'uvdesk' ); ?>" />
						</div>
					</div>
					<div class="wk-main-wrapper" >
						<div class="uv-tk-manage-wrapper">
							<div class="wk-cards tkt-intro">
								<h2>
									<?php
									$select = $ticket_details->ticket->isStarred ? esc_attr( 'stared' ) : '';
									$str_no = $ticket_details->ticket->isStarred ? 1 : 0;
									echo( '<div style="display: inline-block;vertical-align: text-bottom;"><input type="radio" style="opacity: 0;" ><span class="wk-starred-ico ' . ( isset( $select ) ? esc_attr( $select ) : '' ) . '" data-id="' . ( isset( $ticket_details->ticket->id ) ? esc_attr( $ticket_details->ticket->id ) : '' ) . '" data-star-val="' . ( isset( $str_no ) ? esc_attr( $str_no ) : '' ) . '"></span></div>' );
									?>
									<?php echo isset( $ticket_details->ticket->subject ) ? esc_attr( $ticket_details->ticket->subject ) : ''; ?>
								<h2>
								<p>
									<span class="wk-space"><span class="wk-highlight"><?php esc_html_e( 'Created on -', 'uvdesk' ); ?> </span><?php echo esc_html( $ticket_details->ticket->formatedCreatedAt ); ?></span>
									<span class="wk-space"><span class="wk-highlight"><?php esc_html_e( 'Agent - ', 'uvdesk' ); ?></span>
									<select class="wk-sel-agent" data-id="<?php echo isset( $ticket_details->ticket->id ) ? esc_attr( $ticket_details->ticket->id ) : ''; ?>">
										<option value=""><?php esc_html_e( 'Add agent', 'uvdesk' ); ?></option>
										<?php
										foreach ( $data_api_members as $key => $value ) {
											$select = ( $tkt_agent === $value->name ) ? esc_attr( 'selected' ) : '';
											echo( "<option value='" . esc_attr( $value->id ) . "' " . esc_attr( $select ) . '>' . esc_html( $value->name ) . '</option>' );
										}
										?>
									</select>
									</span>
									<span class="wk-space"><span class="wk-highlight"><?php esc_html_e( 'Priority -', 'uvdesk' ); ?> </span>
									<select id="wk-sel-priority">
										<option value=""><?php esc_html_e( 'Add priority', 'uvdesk' ); ?></option>
										<?php
										$priorities = array(
											1 => esc_html__( 'Low', 'uvdesk' ),
											2 => esc_html__( 'Medium', 'uvdesk' ),
											3 => esc_html__( 'High', 'uvdesk' ),
											4 => esc_html__( 'Urgent', 'uvdesk' ),
										);
										foreach ( $priorities as $id => $label ) {
											$selected = ( isset( $ticket_details->ticket->priority->id ) && $id === $ticket_details->ticket->priority->id ) ? esc_attr( 'selected' ) : '';
											printf( '<option value="%d" %s>%s</option>', absint( $id ), esc_attr( $selected ), esc_html( $label ) );
										}
										?>
									</select>
								</p>
							</div>
						<div class="wk-cards tkt-replay">
							<div class= "uv-uvdesk-replay-inline">
							<?php
							$thumbnail = isset( $ticket_details->ticket->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->smallThumbnail )
								? $ticket_details->ticket->customersmallThumbnail ?? $ticket_details->ticket->customer->profileImage
								: esc_url( WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png' );
							$alt_text  = esc_url( WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png' ) === $thumbnail ? esc_attr__( 'Default Thumbnail', 'uvdesk' ) : esc_attr__( 'Customer Thumbnail', 'uvdesk' );
							echo '<img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ) ) . ' alt="' . esc_attr( $alt_text ) . '" />';
							?>
							<span class="tkt-name">
							<?php
							echo wp_kses_post(
								isset( $ticket_details->ticket->customer->detail->customer->name )
								? $ticket_details->ticket->customer->detail->customer->name
								: ( $ticket_details->ticket->customer->detail->name ?? $ticket_details->ticket->customer->detail->firstName ?? 'Unknown' )
							);
							?>
							</span>
								<span class="tkt-timestamp">
								<?php
								echo isset( $ticket_details->ticket->formatedCreatedAt ) ? esc_html( $ticket_details->ticket->formatedCreatedAt ) : '';
								?>
								<span class="wk-accord"></span>
							</span>
						</div>
						<div class="tkt-message" >
							<?php
							echo ! empty( $ticket_details->createThread->reply ) ? wp_kses( $ticket_details->createThread->reply, $allowed_html ) : '';
							if ( ! empty( $ticket_details->createThread->attachments ) ) :
								?>
							<div class="thread-attachments">
								<div class="attachments">
									<h4><strong><?php esc_html_e( 'Uploaded files', 'uvdesk' ); ?></strong></h4>
								<?php
								foreach ( $ticket_details->createThread->attachments as $attchment_key => $attchment_value ) :
									$aid          = $attchment_value->id;
									$domain       = get_option( 'uvdesk_company_domain', '' );
									$access_token = get_option( 'uvdesk_access_token', '' );
									$anamea       = $attchment_value->name;
									$tmp          = ( explode( '.', $anamea ) );
									$aname        = end( $tmp );
									$img_ar       = array( 'png', 'jpg', 'jpeg' );

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
												<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
													<i class="wk-file-zip" title="" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>"  data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
												</a>
																	<?php
									} else {
										$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
										?>
												<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank" title="<?php echo esc_attr( $anamea ); ?>">
													<i class="wk-file" title="" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
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
						<?php
						$tot_post   = ! empty( $ticket_thread->pagination->totalItems ) ? $ticket_thread->pagination->totalCount : 0;
						$last_count = ! empty( $ticket_thread->pagination->lastItemNumber ) ? $ticket_thread->pagination->lastItemNumber : 0;

						if ( $tot_post - $last_count > 0 && $last_count > 0 ) {
							?>
						<div style="position:relative;" id="ajax-load-page">
							<span class="pagination-space"  data-page="<?php echo esc_attr( $ticket_details->ticket->id . '-' . $ticket_thread->pagination->current ); ?>"><?php echo esc_html( $tot_post - $last_count ); ?></span>
						</div>
						<div id="uv-desk-content-here-aj"></div><hr>
							<?php
						}
						if ( isset( $ticket_thread->threads ) ) :
							for ( $i = count( $ticket_thread->threads ) - 1; $i >= 0; $i-- ) {
								$thread_value = ! empty( $ticket_thread->threads[ $i ] ) ? $ticket_thread->threads[ $i ] : '';
								?>
							<div class="wk-cards tkt-replay" data-thread-id="<?php echo esc_attr( $thread_value->id ); ?>">
								<div class= "uv-uvdesk-replay-inline">
								<?php
									$thumbnail = isset( $thread_value->user->smallThumbnail ) && ! empty( $thread_value->user->smallThumbnail )
									? $thread_value->user->smallThumbnail
									: WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
									echo '<img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ) ) . ' alt="' . esc_attr__( 'Loading ...', 'uvdesk' ) . '" />';
								?>
								<span class="tkt-name">
									<?php
									$wk_cus_name = isset( $thread_value->user->detail->agent->name )
									? $thread_value->fullname
									: $thread_value->fullname;
									echo esc_html( $wk_cus_name );
									?>
								</span>
								<span class="tkt-timestamp"><?php echo esc_html( $thread_value->formatedCreatedAt ); ?>
									<span class="wk-accord"></span>
									<span class="wk-delete-tkt-reply"></span>
								</span>
								</div>
								<div class="tkt-message" >
									<?php
									echo ! empty( $thread_value->reply ) ? wp_kses( $thread_value->reply, $allowed_html ) : '';
									if ( ! empty( $thread_value->attachments ) ) :
										?>
										<div class="thread-attachments">
												<div class="attachments">
													<h4><strong> <?php esc_html_e( 'Uploaded files', 'uvdesk' ); ?> </strong></h4>
														<?php
														foreach ( $thread_value->attachments as $attchment_key => $attchment_value ) :
															$aid          = $attchment_value->id;
															$domain       = get_option( 'uvdesk_company_domain', '' );
															$access_token = get_option( 'uvdesk_access_token', '' );
															$anamea       = $attchment_value->name;
															$tmp          = ( explode( '.', $anamea ) );
															$aname        = end( $tmp );
															$img_ar       = array( 'png', 'jpg', 'jpeg' );
															if ( in_array( (string) $aname, $img_ar, true ) ) {
																$wk_image = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
																$wk_image = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
																?>
															<a href="<?php echo esc_url( $wk_image ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
																<img <?php echo wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $wk_image ), $allowed_html ); ?> class="fa fa-file zip"  data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"/>
															</a>
																<?php
															} elseif ( 'zip' === $aname ) {
																$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
																?>
															<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
																<i class="wk-file-zip" title="" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
																</i>
															</a>
																<?php
															} else {
																$attach_url = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
																?>
																<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
																	<i class="wk-file" title="" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
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
								<?php
							}
						endif;
						?>
				<div class="tab-block form-div">
					<div class= "uv-uvdesk-replay-inline">
						<?php
						$thumbnail = isset( $thread_value->user->smallThumbnail ) && ! empty( $thread_value->user->smallThumbnail )
							? $thread_value->user->smallThumbnail
							: WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
						echo '<img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ) ) . ' alt="' . esc_attr__( 'Loading ...', 'uvdesk' ) . '" />';
						?>
						<span class="tkt-name">
							<?php

							$agent_name = ! empty( $ticket_details->ticket->agent->detail->name ) ?
														esc_html( $ticket_details->ticket->agent->detail->name ) :
														esc_html__( 'Not Assigned', 'uvdesk' );

							if ( isset( $ticket_details->ticket->agent->currentUserAgentInstance->name ) ) {
								$agent_name = $ticket_details->ticket->agent->currentUserAgentInstance->name;
							} elseif ( isset( $ticket_details->ticket->agent->detail->name ) ) {
								$agent_name = $ticket_details->ticket->agent->detail->name;
							}
								echo wp_kses( $agent_name, $allowed_html );
							?>
						</span>
					</div>
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="reply">
							<form class="col-sm-12" enctype="multipart/form-data" method="post" id="admin-submit-ticket">
							<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?>
								<input type="hidden" name="agent_email" value="<?php echo $tkt_agent_email ? esc_html( $tkt_agent_email ) : ''; ?>"/>
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

									wp_editor( esc_textarea( '' ), esc_attr( 'product_desc' ), $settings );
									?>
									<div class="form-group wk-uvdesk-attachments">
										<div class="labelWidget">
											<input id="uv-uvdesk-attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="attach-file" decoratefile="decorateFile" infolabeltext="<?php esc_attr_e( '+ Attach File', 'uvdesk' ); ?>" infolabel="right" name="attachments[]">
											<label class="attach-file pointer"></label>
											<i class="uv-uvdesk-remove-file" id="remove-att"></i>
										</div>
										<span id="addFile" class="label-right pointer"><?php esc_html_e( 'Attach File', 'uvdesk' ); ?></span>
									</div>
									<div class="col-sm-12 dropup">
										<div class=" reply-status-dropup">
											<button class="button button-primary reply-submit" type="submit">
													<?php esc_html_e( 'Reply', 'uvdesk' ); ?>
											</button>
										</div>
									</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="uv-tk-manage-sidebar">
				<div class="wk-cards tkt-replay" style="margin-left: 0;">
					<h2><?php esc_html_e( 'Customer', 'uvdesk' ); ?></h2>
						<?php
						$thumbnail = isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail )
							? $ticket_details->ticket->customer->detail->customer->smallThumbnail
							: WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
						echo '<img ' . wp_kses_post( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ) ) . ' />';
						?>
					<p>
						<?php
						echo wp_kses(
							isset( $ticket_details->ticket->customer->detail->customer->name )
							? $ticket_details->ticket->customer->detail->customer->name
							: ( $ticket_details->ticket->customer->detail->name ?? $ticket_details->ticket->customer->detail->firstName ?? 'Unknown' ),
							$allowed_html
						);
						?>
					</p>
					<p>
						<?php
						echo wp_kses(
							isset( $ticket_details->ticket->customer->email )
							? $ticket_details->ticket->customer->email
							: ( $ticket_details->customer->email ?? 'Unknown' ),
							$allowed_html
						);
						?>
					</p>
				</div>
				<div class="wk-cards tkt-replay" style="margin-left: 0;">
						<?php
						if ( isset( $ticket_details->ticket->customer->id ) ) {
							$c_email         = $ticket_details->ticket->customer->email;
							$data_assign_api = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api(
								'tickets.json',
								array(
									'actAsType'  => esc_attr( 'customer' ),
									'actAsEmail' => $c_email,
								)
							);
						}
						?>
					<h2><?php esc_html_e( 'Total', 'uvdesk' ); ?> <?php echo isset( $data_assign_api->pagination->totalCount ) ? esc_attr( $data_assign_api->pagination->totalCount ) : '0'; ?>  <?php esc_html_e( 'Tickets', 'uvdesk' ); ?></h2>
					<div class="tkt-section" >
						<?php
							$count = 1;
						if ( isset( $data_assign_api->tickets ) && is_array( $data_assign_api->tickets ) ) {
							foreach ( $data_assign_api->tickets as $tkt_detail ) {
								?>
						<a style="text-decoration: none" href="<?php echo esc_url( '?page=uvdesk_ticket_system&action=view&post=' . esc_attr( $tkt_detail->incrementId ) ); ?>">
							<p><?php echo esc_html( $count ); ?>.
								<span style="color:#000"><?php echo esc_html( '#' . $tkt_detail->id ); ?></span>
								<span> <?php echo esc_html( $tkt_detail->subject ); ?></span>
							</p>
						</a>
								<?php
								if ( $count > 2 ) {
									$cust_url = esc_url( admin_url( 'admin.php?page=uvdesk_ticket_system&custmr-action=customer-tkt&cid=' ) . $ticket_details->ticket->customer->id );

									echo "<a href='" . esc_url( $cust_url ) . "' ><h4>" . esc_html__( 'View All', 'uvdesk' ) . '</h4></a>';
									break;
								}
									++$count;
							}
							?>
					</div>
				</div>
			</div>
		</div>
							<?php
						}
					}
				}
			} else {
				esc_html_e( 'Invalid setting details.', 'uvdesk' );
			}
		}
	}
}
