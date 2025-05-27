<?php
/**
 * WKUVDESK_Customer_Ticket_View handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Helper;
use WKUVDESK\Includes;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Customer_Ticket_View' ) ) {
	/**
	 * WKUVDESK_Customer_Ticket_View class.
	 */
	class WKUVDESK_Customer_Ticket_View {
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
			add_shortcode( 'uvdesk', array( $this, 'wkuvdesk_customer_ticket_view' ) );
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
		public function wkuvdesk_customer_ticket_view() {
			$ticket_id           = intval( get_query_var( 'tid' ) );
			$paged               = intval( get_query_var( 'paged' ) );
			$paged               = max( 1, $paged );
			$current_user        = wp_get_current_user();
			$c_email             = $current_user->user_email;
			$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );

			if ( ! empty( $uvdesk_access_token ) ) {
				if ( filter_input( INPUT_POST, 'submit-thread', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
					if ( filter_input( INPUT_POST, 'uvdesk_thread_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
						if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'uvdesk_thread_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ), 'uvdesk_thread_nonce_action' ) ) {
								echo '<div class="wkuvdesk-alert wkuvdesk-alert-danger wkuvdesk-alert-fixed">
									<span>
											<span class="wkuvdesk-remove-file wkuvdesk-alert-msg"></span>
											' . esc_html__( 'Security verification failed. Please try again.', 'uvdesk' ) . '
									</span>
							</div>';
							return;
						} elseif ( filter_input( INPUT_POST, 'customer_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && ! empty( $_POST['thread_desc'] ) ) {
							$sdt   = explode( ',', filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
							$reply = isset( $_POST['thread_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['thread_desc'] ) ) : '';

							if ( isset( $_FILES['attachments']['type'] ) && 0 === count( $_FILES['attachments']['type'] ) ) {
									$thread_status = Helper\WKUVDESK_Api_Handler::wkuvdesk_post_thread_data_api(
										'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json',
										array(
											'threadType' => sanitize_text_field( filter_input( INPUT_POST, 'threadType', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ),
											'reply'      => $reply,
											'status'     => isset( $sdt[0] ) ? sanitize_text_field( $sdt[0] ) : '',
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
										'status'     => isset( $sdt[0] ) ? sanitize_text_field( $sdt[0] ) : '',
										'actAsType'  => 'customer',
										'actAsEmail' => sanitize_text_field( filter_input( INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL ) ),
									),
									filter_var( $_FILES['attachments'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY )
								);
							}

							$thread_status = json_decode( $thread_status );

							echo '<div class="wkuvdesk-alert wkuvdesk-alert-success wkuvdesk-alert-fixed ">
									<span>
											<span class="wkuvdesk-remove-file"></span>
											' . esc_html( $thread_status->message ) . '
									</span>
							</div>';
						}
					}
				}

				$arr_sum        = array(
					'actAsType'  => esc_attr( 'customer' ),
					'actAsEmail' => $c_email,
				);
				$ticket_details = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'ticket/' . $ticket_id . '.json', $arr_sum );

				if ( isset( $ticket_details->error ) || empty( $ticket_details ) ) {
					echo '<h4>' . esc_html( $ticket_details->error_description ) . '</h4><h4>' . esc_html__( 'Please contact Administrator.', 'uvdesk' ) . '</h4>';
				} else {
					if ( ! empty( $ticket_details->ticket->status->name ) && isset( $ticket_details->ticket->status->name ) ) {
						$ticket_status_name = $ticket_details->ticket->status->name;
					}

					if ( ! empty( $ticket_details->ticket->formatedCreatedAt ) && isset( $ticket_details->ticket->formatedCreatedAt ) ) {
						$ticket_created = $ticket_details->ticket->formatedCreatedAt;
					}

					if ( ! empty( $ticket_details->ticket->customer->detail->name ) && isset( $ticket_details->ticket->customer->detail->name ) ) {
						$customer_name = $ticket_details->ticket->customer->detail->name;
					}

					if ( ! empty( $ticket_details->createThread->reply ) && isset( $ticket_details->createThread->reply ) ) {
						$created_thread = $ticket_details->createThread->reply;
					}

					$ticket        = ! empty( $ticket_details->ticket->id ) ? $ticket_details->ticket->id : 0;
					$ticket_thread = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'ticket/' . $ticket . '/threads.json' );

					$ticket_thread->threads = ! empty( $ticket_thread->threads ) ? $ticket_thread->threads : array();

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

					$spinner_url  = admin_url( 'images/spinner-2x.gif' );
					$spinner_args = array(
						'src'   => $spinner_url,
						'class' => 'wkuvdesk-ajax-loader-img',
						'alt'   => esc_html__( 'Loading...', 'uvdesk' ),
					);
					?>
					<div class="wkuvdesk-block-container wkuvdesk-content-wrap">
						<div class="wkuvdesk-pre-loader">
							<?php
							echo '<img ' . wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $spinner_args ), $allowed_html ) . ' />';
							?>
						</div>
						<div class="tkt-front-header">
							<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/' ); ?>" class='to-main-list'><?php esc_html_e( 'All tickets', 'uvdesk' ); ?></a>
						</div>
						<div class="side-section-front">
							<p class='side-sec-head'><?php esc_html_e( 'TICKET INFROMATION', 'uvdesk' ); ?></p>
							<span>
								<span class="side-title"><?php esc_html_e( 'ID', 'uvdesk' ); ?></span>
								<span class="side-info"><?php esc_html_e( '#', 'uvdesk' ); ?><?php echo ! empty( $ticket_details->ticket->id ) ? esc_html( $ticket_details->ticket->id ) : ''; ?></span>
							</span>
							<span>
								<span class="side-title"><?php esc_html_e( 'Timestamp', 'uvdesk' ); ?></span>
								<span class="side-info"><?php echo isset( $ticket_details->ticket->formatedCreatedAt ) ? esc_html( $ticket_details->ticket->formatedCreatedAt ) : ''; ?></span>
							</span>
							<span>
								<span class="side-title"><?php esc_html_e( 'Total Replies', 'uvdesk' ); ?></span>
								<span class="side-info"><?php echo isset( $ticket_details->ticketTotalThreads ) ? esc_html( $ticket_details->ticketTotalThreads ) : ''; ?></span>
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
								<?php
								if ( ! empty( $ticket_details->ticket->priority->name ) ) {
									?>
									<span>
										<span class="side-title"><?php esc_html_e( 'Priority', 'uvdesk' ); ?></span>
										<span class="side-info"><b class="wkuvdesk-priority-check" style="<?php echo esc_attr( 'background-color:' . $ticket_details->ticket->priority->color ); ?>"></b><?php echo esc_html( $ticket_details->ticket->priority->name ); ?></span>
									</span>
									<?php
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
								?>
							<span>
								<span class="side-title"><?php esc_html_e( 'Status', 'uvdesk' ); ?></span>
								<span class="side-info"> <?php echo esc_html( $ticket_status_name ); ?></span>
							</span>
						</div>
						<div class="whole-wrapper">
								<div class="tkt-front-intro">
									<div style="<?php echo esc_attr( 'display:inline-block;margin:10px 20px;' ); ?>">
										<span style="<?php echo esc_attr( 'display:inline-block;font-size:20px' ); ?>" class="wkuvdesk-highlight" ><?php esc_html_e( 'Subject :-', 'uvdesk' ); ?></span>
										<h4 style="display:inline-block;" class="tkt-subject">
											<?php
											echo esc_html( $ticket_details->ticket->subject );
											?>
										</h4>
										<p>
											<span class="wkuvdesk-space">
											<span class="wkuvdesk-highlight"><?php esc_html_e( 'Created on - ', 'uvdesk' ); ?></span><?php echo esc_html( $ticket_details->ticket->formatedCreatedAt ); ?>
											</span>
											<span class="wkuvdesk-space">
												<span class="wkuvdesk-highlight"><?php esc_html_e( ' Agent -', 'uvdesk' ); ?> </span>
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
													$thumbnail = $ticket_details->ticket->customer->detail->customer->smallThumbnail ?? esc_url( WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png' );
												echo '<img ' . wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ), $allowed_html ) . '/>';
												?>
												</span>
												<span class="info">
													<span class="rpy-name"><?php echo ( esc_attr( $customer_name ) . '  ' ); ?></span>&emsp;
																						<?php
																						esc_html_e( 'Created ticket', 'uvdesk' );
																						?>
														<?php if ( ! empty( $ticket_created ) ) : ?>
														<br>
														<span class="create-date">
															<?php echo esc_html( $ticket_created ); ?>
														</span>
														<?php endif; ?>
												</span>
											</div>
											<?php endif; ?>
											<div class="frnt-msg">
												<?php
												if ( ! empty( $created_thread ) ) {
													echo wp_kses_post( $created_thread );
												}
												?>
												<?php
												if ( ! empty( $ticket_details->createThread->attachments ) ) :
													?>
													<div class="thread-attachments">
															<div class="attachments">
																<p><strong> <?php esc_html_e( 'Uploaded files', 'uvdesk' ); ?> </strong></p>
																<?php
																foreach ( $ticket_details->createThread->attachments as $attchment_key => $attchment_value ) :
																	$domain       = get_option( 'uvdesk_company_domain', '' );
																	$access_token = get_option( 'uvdesk_access_token', '' );
																	$aid          = $attchment_value->id;
																	$anamea       = $attchment_value->name;
																	$tmp          = ( explode( '.', $anamea ) );
																	$aname        = ! empty( end( $tmp ) ) ? end( $tmp ) : '';
																	$img_ar       = array( 'png', 'jpg', 'jpeg' );

																	if ( in_array( $aname, $img_ar, true ) ) {
																		$wk_image     = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
																		$wk_image     = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
																		$wk_image     = str_replace( '/thread_image_thumb/', '/thread_image_orignal/', $wk_image );
																		$allowed_html = array(
																			'a'    => array(
																				'href' => true,
																				'title' => true,
																				'class' => true,
																				'target' => true,
																			),
																			'strong' => array(),
																			'em'   => array(),
																			'p'    => array(
																				'class' => true,
																			),
																			'div'  => array(
																				'class' => true,
																				'id' => true,
																			),
																			'span' => array(
																				'class' => true,
																			),
																			'br'   => array(),
																			'img'  => array(
																				'src' => true,
																				'alt' => true,
																				'class' => true,
																				'width' => true,
																				'height' => true,
																				'id'  => true,
																				'style' => true,
																			),
																			'form' => array(
																				'method' => true,
																				'action' => true,
																				'class' => true,
																				'id' => true,
																			),
																			'input' => array(
																				'type' => true,
																				'name' => true,
																				'id'   => true,
																				'class' => true,
																				'value' => true,
																				'placeholder' => true,
																				'required' => true,
																				'checked' => true,
																			),
																			'label' => array(
																				'for' => true,
																				'class' => true,
																			),
																			'h1'   => array(
																				'class' => true,
																			),
																			'h2'   => array(
																				'class' => true,
																			),
																			'h3'   => array(
																				'class' => true,
																			),
																			'h4'   => array(
																				'class' => true,
																			),
																			'button' => array(
																				'type' => true,
																				'class' => true,
																				'id'   => true,
																			),
																		);
																		?>
																		<a href="<?php echo esc_url( $wk_image ); ?>" target="_blank">
																				<img <?php echo wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $wk_image ), $allowed_html ); ?> class="fa fa-file zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="tooltip" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">

																		</a>
																		<?php
																	} elseif ( 'zip' === $aname ) {
																		$attach_url = esc_url(
																			'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token )
																		);
																		?>
																			<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																				<i class="wkuvdesk-file-zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="tooltip" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
																			</a>
																			<?php
																	} else {
																		$attach_url = esc_url( 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token ) );
																		?>
																		<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																			<i class="wkuvdesk-file" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
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
								$tot_post     = isset( $ticket_thread->pagination->totalCount ) ? $ticket_thread->pagination->totalCount : 0;
								$last_count   = isset( $ticket_thread->pagination->lastItemNumber ) ? $ticket_thread->pagination->lastItemNumber : 0;
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

								if ( $tot_post - $last_count > 0 && $last_count > 0 ) {
									?>
									<div style="position:relative;" id="ajax-load-page">
										<span class="pagination-space" data-page="<?php echo esc_attr( $ticket_details->ticket->id . '-' . $ticket_thread->pagination->current ); ?>"><?php echo wp_kses( $tot_post - $last_count, $allowed_html ); ?></span>
									</div>
									<div id="wkuvdesk-content-here-aj">
									</div>
									<hr>
									<?php
								}
								?>
								<div class="ticket-view-page" id="ticket-view-page">
									<div class="ticket-thread">
										<?php
										$wk_count = ! empty( $ticket_thread->threads ) ? count( $ticket_thread->threads ) : 0;
										for ( $i = $wk_count - 1; $i >= 0; $i-- ) :
											$thread_value = $ticket_thread->threads[ $i ];
											?>
												<div class="thread-created-info">
													<div class="msg-header">
														<span class="img-icon">
														<?php
														$allowed_html = array(
															'a'    => array(
																'href' => true,
																'title' => true,
																'class' => true,
																'target' => true,
															),
															'strong' => array(),
															'em'   => array(),
															'p'    => array(
																'class' => true,
															),
															'div'  => array(
																'class' => true,
																'id' => true,
															),
															'span' => array(
																'class' => true,
															),
															'br'   => array(),
															'img'  => array(
																'src' => true,
																'alt' => true,
																'class' => true,
																'width' => true,
																'height' => true,
																'id'  => true,
																'style' => true,
															),
															'form' => array(
																'method' => true,
																'action' => true,
																'class' => true,
																'id' => true,
															),
															'input' => array(
																'type' => true,
																'name' => true,
																'id'   => true,
																'class' => true,
																'value' => true,
																'placeholder' => true,
																'required' => true,
																'checked' => true,
															),
															'label' => array(
																'for' => true,
																'class' => true,
															),
															'h1'   => array(
																'class' => true,
															),
															'h2'   => array(
																'class' => true,
															),
															'h3'   => array(
																'class' => true,
															),
															'h4'   => array(
																'class' => true,
															),
															'button' => array(
																'type' => true,
																'class' => true,
																'id'   => true,
															),
														);
														$thumbnail    = isset( $thread_value->user->smallThumbnail ) && ! empty( $thread_value->user->smallThumbnail ) ?
															$thread_value->user->smallThumbnail :
															WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
														echo '<img ' . wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ), $allowed_html ) . ' alt="' . esc_attr__( 'user-img', 'uvdesk' ) . '" class="img-circle" />';
														?>
														</span>
														<span class="info">
															<span class="rpy-name">
																<?php
																$user_name = isset( $thread_value->user->name ) ?
																	$thread_value->user->name :
																	( ! empty( $thread_value->user->email ) ? $thread_value->user->email : '' );
																echo esc_html( $user_name );
																?>
															</span>
															<?php if ( ! empty( $thread_value->formatedCreatedAt ) ) { ?>
																<br>
																<span class="create-date">
																	<?php echo esc_html( $thread_value->formatedCreatedAt ); ?>
																</span>
															<?php } ?>
														</span>
													</div>
													<div class="frnt-msg">
															<?php
															if ( ! empty( $thread_value->reply ) ) {
																echo wp_kses_post( $thread_value->reply );
															}
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
																	$img_ar       = array( 'png', 'jpg', 'jpeg' );

																	if ( in_array( $aname, $img_ar, true ) ) {
																		$wk_image     = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
																		$wk_img_main  = ! empty( $attchment_value->path ) ? $attchment_value->path : $attchment_value->attachmentThumb;
																		$wk_image     = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
																		$wk_image     = str_replace( '/thread_image_thumb/', '/thread_image_orignal/', $wk_image );
																		$allowed_html = array(
																			'a'    => array(
																				'href' => true,
																				'title' => true,
																				'class' => true,
																				'target' => true,
																			),
																			'strong' => array(),
																			'em'   => array(),
																			'p'    => array(
																				'class' => true,
																			),
																			'div'  => array(
																				'class' => true,
																				'id' => true,
																			),
																			'span' => array(
																				'class' => true,
																			),
																			'br'   => array(),
																			'img'  => array(
																				'src' => true,
																				'alt' => true,
																				'class' => true,
																				'width' => true,
																				'height' => true,
																				'id'  => true,
																				'style' => true,
																			),
																			'form' => array(
																				'method' => true,
																				'action' => true,
																				'class' => true,
																				'id' => true,
																			),
																			'input' => array(
																				'type' => true,
																				'name' => true,
																				'id'   => true,
																				'class' => true,
																				'value' => true,
																				'placeholder' => true,
																				'required' => true,
																				'checked' => true,
																			),
																			'label' => array(
																				'for' => true,
																				'class' => true,
																			),
																			'h1'   => array(
																				'class' => true,
																			),
																			'h2'   => array(
																				'class' => true,
																			),
																			'h3'   => array(
																				'class' => true,
																			),
																			'h4'   => array(
																				'class' => true,
																			),
																			'button' => array(
																				'type' => true,
																				'class' => true,
																				'id'   => true,
																			),
																		);
																		?>
																		<a href="<?php echo esc_url( $wk_img_main ); ?>" target="_blank">
																				<img <?php echo wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $wk_image ), $allowed_html ); ?> class="fa fa-file zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
																		</a>
																		<?php
																	} elseif ( 'zip' === $aname ) {
																		$attach_url = esc_url( 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token ) );
																		?>
																		<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																			<i class="wkuvdesk-file-zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
																			</i>
																		</a>
																		<?php
																	} else {
																		$attach_url = esc_url( 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token ) );
																		?>
																		<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																			<i class="wkuvdesk-file" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
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
											$allowed_html = array(
												'a'      => array(
													'href' => true,
													'title' => true,
													'class' => true,
													'target' => true,
												),
												'strong' => array(),
												'em'     => array(),
												'p'      => array(
													'class' => true,
												),
												'div'    => array(
													'class' => true,
													'id' => true,
												),
												'span'   => array(
													'class' => true,
												),
												'br'     => array(),
												'img'    => array(
													'src' => true,
													'alt' => true,
													'class' => true,
													'width' => true,
													'height' => true,
													'id'  => true,
													'style' => true,
												),
												'form'   => array(
													'method' => true,
													'action' => true,
													'class' => true,
													'id' => true,
												),
												'input'  => array(
													'type' => true,
													'name' => true,
													'id'   => true,
													'class' => true,
													'value' => true,
													'placeholder' => true,
													'required' => true,
													'checked' => true,
												),
												'label'  => array(
													'for' => true,
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
													'type' => true,
													'class' => true,
													'id'   => true,
												),
											);
											$thumbnail    = isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail )
												? $ticket_details->ticket->customer->detail->customer->smallThumbnail
												: WKUVDESK_PLUGIN_URL . 'assets/images/e09dabf.png';
											echo wp_kses_post( '<img ' . wp_kses( Includes\WKUVDESK::wkuvdesk_convert_attributes_to_html( $thumbnail ), $allowed_html ) . '/>' );
											?>
										</span>
										<span class="info">
											<span class="rpy-name"><?php esc_html_e( 'You can write your reply', 'uvdesk' ); ?></span>
										</span>
									</div>
									<form enctype="multipart/form-data" method="post" action="">
										<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?>
										<input type="hidden" name="customer_email" value="<?php echo esc_attr( $ticket_details->ticket->customer->email ); ?>">
										<input type="hidden" name="threadType" value="reply">
										<input type="hidden" name="status" class="reply-status" value="<?php echo esc_attr( '1,' . $ticket_details->ticket->id ); ?>">
										<?php
											$settings = array(
												'media_buttons' => true, // show insert/upload button(s).
												'textarea_name' => 'thread_desc',
												'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
												'tabindex' => '',
												'teeny'    => false,
												'dfw'      => false,
												'tinymce'  => true, /* load TinyMCE, can be used to pass settings directly to TinyMCE using an array()*/
												'quicktags' => false, /* load Quicktags, can be used to pass settings directly to Quicktags using an array()*/
												'force_br_newlines' => true,
												'force_p_newlines' => false,
											);

											wp_editor( esc_attr( '' ), esc_attr( 'product_desc' ), $settings );
											?>
										<div class="form-group wkuvdesk-attachments">
											<div class="labelWidget">
													<input id="wkuvdesk-attachments" class="fileHide" type="file" enableremoveoption="<?php echo esc_attr( 'enableRemoveOption' ); ?>" decoratecss="<?php echo esc_attr( 'attach-file' ); ?>" decoratefile="<?php echo esc_attr( 'decorateFile' ); ?>" infolabeltext="<?php esc_attr_e( '+ Attach File', 'uvdesk' ); ?>" infolabel="<?php echo esc_attr( 'right' ); ?>" name="attachments[]">
													<label class="attach-file pointer"></label>
													<i class="wkuvdesk-remove-file" id="remove-att"></i>
											</div>
											<span id="wkuvdesk-addFile" class="label-right pointer"><?php esc_html_e( 'Attach File', 'uvdesk' ); ?></span>
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
				esc_html_e( 'Please Enter a valid Access Token', 'uvdesk' );
			}
		}
	}
}

