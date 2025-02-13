<?php
/**
 * WK_UVDESK_Common_Function.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Includes\Common;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WK_UVDESK\Helper;
use WK_UVDESK\Includes;

/** Check class exists or not */
if ( ! class_exists( 'WK_UVDESK_Common_Function' ) ) {
	/**
	 * WK_UVDESK_Common_Function class.
	 */
	class WK_UVDESK_Common_Function {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Change ticket status.
		 *
		 * @return void
		 */
		public function wk_uvdesk_change_ticket_agent() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$ticket_ids = filter_input( INPUT_POST, 'ticket_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$ticket_ids = explode( ',', $ticket_ids );
				$agent      = filter_input( INPUT_POST, 'agent', FILTER_SANITIZE_NUMBER_INT );
				$agent      = empty( $agent ) ? 0 : $agent;
				$json_data  = array(
					'ids'     => $ticket_ids,
					'agentId' => $agent,
				);

				if ( $json_data ) {
					$function_handler = new Helper\WK_UVDESK_Api_Handler();
					$data_customerapi = $function_handler->wk_uvdesk_update_ticket( 'tickets/agent.json', $json_data );

					if ( ! empty( $data_customerapi ) ) {
						wp_send_json_success( $data_customerapi );
					} else {
						wp_send_json_error( $data_customerapi );
					}
				} else {
					wp_send_json_error( esc_html__( 'Please enter a valid filter', 'wk-uvdesk' ) );
				}
				die;
			}
		}

		/**
		 * Change ticket priority.
		 *
		 * @return void
		 */
		public function wk_uvdesk_change_ticket_priority() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$ticket_ids = filter_input( INPUT_POST, 'ticket_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$ticket_ids = explode( ',', $ticket_ids );
				$priority   = filter_input( INPUT_POST, 'priority', FILTER_SANITIZE_NUMBER_INT );
				$priority   = empty( $priority ) ? 0 : $priority;
				$json_data  = array(
					'ids'        => $ticket_ids,
					'priorityId' => $priority,
				);
				if ( $json_data ) {
					$function_handler = new Helper\WK_UVDESK_Api_Handler();
					$data_customerapi = $function_handler->wk_uvdesk_update_ticket( 'tickets/priority.json', $json_data );

					if ( ! empty( $data_customerapi ) ) {
						wp_send_json_success( $data_customerapi );
					} else {
						wp_send_json_error( $data_customerapi );
					}
				} else {
					wp_send_json_error( esc_html__( 'Please enter a valid filter', 'wk-uvdesk' ) );
				}
				die;
			}
		}

		/**
		 * Get data according to status.
		 *
		 * @return void
		 */
		public function wk_uvdesk_sort_customer_ticket_via_status() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$field            = filter_input( INPUT_POST, 'field', FILTER_SANITIZE_NUMBER_INT );
				$field            = empty( $field ) ? 0 : wp_unslash( $field );
				$current_user     = wp_get_current_user();
				$m_id             = filter_input( INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT );
				$m_id             = empty( $m_id ) ? $current_user->ID : wp_unslash( $m_id );
				$function_handler = new Helper\WK_UVDESK_Api_Handler();
				$data_assign_api  = $function_handler->wk_uvdesk_get_customer_data_api( 'tickets.json', array( 'agent' => $m_id ) );
				$c_email          = $current_user->user_email;

				if ( $field ) {
					$arr_sum          = array(
						'status'     => $field,
						'direction'  => 'desc',
						'actAsEmail' => $c_email,
						'actAsType'  => 'customer',
					);
					$data_customerapi = $function_handler->wk_uvdesk_get_customer_data_api( 'tickets.json', $arr_sum );
					if ( ! empty( $data_customerapi ) ) {
						$content = $this->wk_uvdesk_final_json_data_customer( $data_customerapi );
						wp_send_json_success( $content );
					} else {
						wp_send_json_error( $data_assign_api );
					}
				} else {
					wp_send_json_error( esc_html__( 'Please enter a valid filter', 'wk-uvdesk' ) );
				}
				die;
			}
		}

		/**
		 * Get data base on priority.
		 *
		 * @return void
		 */
		public function wk_uvdesk_sort_ticket_via_api() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$field        = filter_input( INPUT_POST, 'field', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$field        = empty( $field ) ? '' : wp_unslash( $field );
				$link_page    = filter_input( INPUT_POST, 'page_link', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$link_page    = empty( $link_page ) ? 0 : wp_unslash( $link_page );
				$is_admin     = filter_input( INPUT_POST, 'is_admin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$is_admin     = empty( $is_admin ) ? current_user_can( 'manage_options' ) : wp_unslash( $is_admin );
				$order_input  = filter_input( INPUT_POST, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$order        = empty( $order_input ) ? 'asc' : wp_unslash( $order_input );
				$current_user = wp_get_current_user();
				$c_email      = $current_user->user_email;

				if ( $field ) {
					$function_handler = new Helper\WK_UVDESK_Api_Handler();
					if ( ! $is_admin ) {
						$m_id             = filter_input( INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT ) ? filter_input( INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT ) : $current_user->ID;
						$data_assign_api  = $function_handler->wk_uvdesk_get_customer_data_api( 'tickets.json', array( 'agent' => $m_id ) );
						$data_customerapi = $function_handler->wk_uvdesk_get_customer_data_api(
							'tickets.json',
							array(
								'sort'      => $field,
								'direction' => $order,
							)
						);
					} else {
						$data_customerapi = $function_handler->wk_uvdesk_get_customer_data_api(
							'tickets.json',
							array(
								'sort'      => $field,
								'direction' => $order,
								'search'    => $c_email,
							)
						);
					}
					if ( ! empty( $data_customerapi ) ) {
						$content = $this->wk_uvdesk_final_json_data_customer( $data_customerapi, $link_page );
						wp_send_json_success( $content );
					} else {
						wp_send_json_error( $data_assign_api );
					}
				} else {
					wp_send_json_error( esc_html__( 'Please enter a valid filter', 'wk-uvdesk' ) );
				}
				die;
			}
		}

		/**
		 * Get customer information.
		 *
		 * @return void
		 */
		public function wk_uvdesk_get_thread_data_customer() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$page    = filter_input( INPUT_POST, 'page_no', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$page    = explode( '-', $page );
				$tid     = $page[0];
				$page_no = $page[1] + 1;
				if ( 0 !== $tid ) {
					$data_assign_api = Helper\WK_UVDESK_Api_Handler::wk_uvdesk_get_customer_data_api( 'ticket/' . $tid . '/threads.json', array( 'page' => $page_no ) );

					if ( $data_assign_api ) {
						$content = $this->wk_uvdesk_final_thread_json_data( $data_assign_api, $tid );
						wp_send_json_success( $content );
					} else {
						wp_send_json_error( $data_assign_api );
					}
				} else {
					wp_send_json_error( esc_html__( 'Please enter a valid ticket Id', 'wk-uvdesk' ) );
				}
				die;
			}
		}

		/**
		 * Delete ticket.
		 *
		 * @return void
		 */
		public function wk_uvdesk_delete_thread_via_api() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$thread_id = filter_input( INPUT_POST, 'thread-id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$tid       = filter_input( INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT );

				if ( empty( $thread_id ) || empty( $tid ) ) {
					wp_send_json_error( esc_html__( 'Required parameters are missing', 'wk-uvdesk' ) );
					die;
				}
				if ( intval( $thread_id ) ) {
					$data_thread = Helper\WK_UVDESK_Api_Handler::wk_uvdesk_threds_delete_tag_ticket(
						'ticket/' . $tid . '/thread/' . $thread_id . '.json',
						array(
							'id'     => $thread_id,
							'ticket' => $tid,
						)
					);

					if ( isset( $data_thread->message ) ) {
						wp_send_json_success( wp_json_encode( $data_thread ) );
					} else {
						wp_send_json_error( esc_html__( 'There is an error in deleting thread1', 'wk-uvdesk' ) );
					}
					die;
				} else {
					wp_send_json_error( esc_html__( 'There is an error in deleting thread2', 'wk-uvdesk' ) );
					die;
				}
			}
		}

		/**
		 * Get ticket data toggle.
		 *
		 * @return void
		 */
		public function wk_uvdesk_toggle_the_starred() {
			if ( check_ajax_referer( 'wk-uvdesk-api-ajaxnonce', 'nonce', false ) ) {
				$t_id          = filter_input( INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT );
				$ticket_stared = filter_input( INPUT_POST, 'stared_no', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

				if ( empty( $t_id ) || empty( $ticket_stared ) ) {
					wp_send_json_error( esc_html__( 'Required parameters are missing', 'wk-uvdesk' ) );
					die;
				}

				$function_handler    = new Helper\WK_UVDESK_Api_Handler();
				$json_data           = array(
					'editType' => 'star',
					'value'    => $ticket_stared,
				);
				$data_starred_ticket = $function_handler->wk_uvdesk_get_patch_data_api( 'ticket/' . $t_id . '.json', $json_data );

				if ( $data_starred_ticket ) {
					wp_send_json_success( $data_starred_ticket );
				} else {
					wp_send_json_error( $data_starred_ticket );
				}

				die;
			}
		}

		/**
		 * Show ticket data.
		 *
		 * @param object $data_api Ticket data.
		 * @param string $tid ticket id.
		 *
		 * @return mixed
		 */
		public function wk_uvdesk_final_thread_json_data( $data_api = array(), $tid = '' ) {
			ob_start();
			if ( empty( $data_api ) && empty( $tid ) ) {
				echo '';
			} else {
				$tot_post     = isset( $data_api->pagination->totalCount ) ? $data_api->pagination->totalCount : 0;
				$last_count   = isset( $data_api->pagination->lastItemNumber ) ? $data_api->pagination->lastItemNumber : 0;
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
					<span class="pagination-space" data-page="<?php echo esc_attr( $tid . '-' . $data_api->pagination->current ); ?>"><?php echo wp_kses( $tot_post - $last_count, $allowed_html ); ?></span>
				</div>
				<hr>
					<?php
				}

				for ( $i = ! empty( $data_api->threads ) ? count( $data_api->threads ) - 1 : 0; $i >= 0; $i-- ) :
					if ( 1 !== get_current_user_id() ) {
						$thread_value = $data_api->threads[ $i ];
						$img_url      = ! empty( $thread_value->user->smallThumbnail ) ? esc_url( $thread_value->user->smallThumbnail ) : esc_url( 'https://cdn.uvdesk.com/uvdesk/images/e09dabf.png' );
						?>
						<div class="thread-created-info">
							<div class="msg-header">
								<span class="img-icon">
									<?php echo wp_kses_post( '<img ' . wp_kses( Includes\WK_UVDESK::wk_uvdesk_convert_attributes_to_html( $img_url ), $allowed_html ) . '>' ); ?>
								</span>
								<span class="info">
									<span class="rpy-name">
										<?php
										echo isset( $thread_value->user->detail->agent ) && ! empty( $thread_value->user->detail->agent ) ? esc_attr( $thread_value->user->detail->agent ) : ( isset( $thread_value->user->detail->customer->name ) ? esc_attr( $thread_value->user->detail->customer->name ) : '' );
										?>
									</span>
									<?php if ( ! empty( $thread_value->formatedCreatedAt ) ) { ?>
										<br><span class="create-date">
											<?php echo esc_html( $thread_value->formatedCreatedAt ); ?>
										</span>
									<?php } ?>
								</span>
							</div>
							<div class="frnt-msg">
								<?php
								if ( ! empty( $thread_value->reply ) ) {
									echo wp_kses( $thread_value->reply, $allowed_html );
								}
								if ( ! empty( $thread_value->attachments ) ) {
									?>
										<div class="thread-attachments">
											<div class="attachments">
												<p><strong><?php esc_html_e( 'Uploaded files', 'wk-uvdesk' ); ?></strong></p>
												<?php
												foreach ( $thread_value->attachments as $attchment_value ) {
													$aid    = $attchment_value->id;
													$anamea = $attchment_value->name;
													$tmp    = ( explode( '.', $anamea ) );
													$aname  = end( $tmp );
													$img_ar = array( 'png', 'jpg', 'jpeg' );
													if ( in_array( (string) $aname, $img_ar, true ) ) {
														$wk_image = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
														$wk_image = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
														?>
															<a href="<?php echo esc_url( $wk_image ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
																<img <?php echo wp_kses( Includes\WK_UVDESK::wk_uvdesk_convert_attributes_to_html( $wk_image ), $allowed_html ); ?> class="fa fa-file zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
															</a>
														<?php
													} elseif ( 'zip' === $aname ) {
														$uv           = new Helper\WK_UVDESK_Protected();
														$domain       = $uv->get_company_domain();
														$access_token = get_option( 'uvdesk_access_token', '' );
														$attach_url   = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
														?>
														<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
															<i class="wk-file-zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
															</i>
														</a>
														<?php
													} else {
														$uv           = new Helper\WK_UVDESK_Protected();
														$domain       = $uv->get_company_domain();
														$access_token = get_option( 'uvdesk_access_token', '' );
														$attach_url   = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
														?>
														<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
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
						<?php
						if ( 0 !== $i ) {
							echo '<hr/>';
						}
					} else {
						$thread_value = ! empty( $data_api->threads[ $i ] ) ? $data_api->threads[ $i ] : '';
						$img_url      = ! empty( $thread_value->user->smallThumbnail ) ? esc_url( $thread_value->user->smallThumbnail ) : esc_url( 'https://cdn.uvdesk.com/uvdesk/images/e09dabf.png' );
						echo '<div class="wk-cards tkt-replay " data-thread-id="' . esc_attr( $thread_value->id ) . '">';
						echo '<div class= "uv-uvdesk-replay-inline" >';
						echo '<img ' . wp_kses( Includes\WK_UVDESK::wk_uvdesk_convert_attributes_to_html( $img_url ), $allowed_html ) . ' alt="' . esc_attr( $img_url ) . '" />';
						?>
						<span class="tkt-name">
							<?php echo esc_attr( ! empty( $thread_value->user->detail->agent ) ? $thread_value->user->detail->agent : $thread_value->user->detail->customer->name ); ?>
						</span>
						<span class="tkt-timestamp"><?php echo esc_html( $thread_value->formatedCreatedAt ); ?>
							<span class="wk-accord"></span>
							<span class="wk-delete-tkt-reply"></span>
						</span>
						</div>
						<div class="tkt-message" >
							<?php
							echo wp_kses( $thread_value->reply, $allowed_html );

							if ( ! empty( $thread_value->attachments ) ) :
								?>
								<div class="thread-attachments">
									<div class="attachments">
										<h4><strong><?php esc_html_e( 'Uploaded files', 'wk-uvdesk' ); ?></strong></h4>
										<?php
										foreach ( $thread_value->attachments as $attchment_key => $attchment_value ) :
											$aid    = $attchment_value->id;
											$anamea = $attchment_value->name;
											$tmp    = ( explode( '.', $anamea ) );
											$aname  = end( $tmp );
											$img_ar = array( 'png', 'jpg', 'jpeg' );
											if ( in_array( (string) $aname, $img_ar, true ) ) {
												$wk_image = ! empty( $attchment_value->attachmentThumb ) ? $attchment_value->attachmentThumb : $attchment_value->path;
												$wk_image = str_replace( '/company/', '/thread_image_orignal/', $wk_image );
												?>
												<a href="<?php echo esc_url( $wk_image ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
													<img  <?php echo wp_kses( Includes\WK_UVDESK::wk_uvdesk_convert_attributes_to_html( $wk_image ), $allowed_html ); ?> class="fa fa-file zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"/>
												</a>
												<?php
											} elseif ( 'zip' === $aname ) {
												$uv           = new Helper\WK_UVDESK_Protected();
												$domain       = $uv->get_company_domain();
												$access_token = get_option( 'uvdesk_access_token', '' );
												$attach_url   = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
												?>
												<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
													<i class="wk-file-zip" title="<?php echo esc_attr( $anamea ); ?>" data-toggle="<?php echo esc_attr( 'tooltip' ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>"></i>
												</a>
												<?php
											} else {
												$uv           = new Helper\WK_UVDESK_Protected();
												$domain       = $uv->get_company_domain();
												$access_token = get_option( 'uvdesk_access_token', '' );
												$attach_url   = 'https://' . esc_attr( $domain ) . '.uvdesk.com/en/api/ticket/attachment/' . esc_attr( $aid ) . '.json?access_token=' . esc_attr( $access_token );
												?>
												<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo esc_attr( $anamea ); ?>" target="_blank">
													<i class="wk-file"  data-toggle="tooltip" title="<?php echo esc_attr( $anamea ); ?>" data-original-title="<?php echo esc_attr( $attchment_value->name ); ?>">
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
						<?php
					}
				endfor;

				return ob_get_clean();
			}
		}

		/**
		 * Final json data customer.
		 *
		 * @param array  $data_api data.
		 * @param string $link_page link page.
		 *
		 * @return mixed
		 */
		public function wk_uvdesk_final_json_data_customer( $data_api = '', $link_page = '' ) {
			ob_start();
			if ( empty( $data_api->tickets ) ) {
				$con = "<div class='table-container' id='ticket-table'>
							<div class='tabs-table'>
								<table class='table'>
									<tr >
										<td class='record-no'><span>" . esc_html__( 'No Record Found', 'wk-uvdesk' ) . '</span></td>
									</tr>
								</table>
							</div>
						</div>';
				return $con;
			}
			?>
		<div class="table-container" id="ticket-table">
		<div class="tabs-table">
			<table class="table">
				<tr>
					<td class="check-col"></td>
					<td class="id-col"><?php esc_html_e( 'Id', 'wk-uvdesk' ); ?></td>
					<td class="reply-col"><?php esc_html_e( 'Reply', 'wk-uvdesk' ); ?></td>
					<td class="date-col"><?php esc_html_e( 'Date', 'wk-uvdesk' ); ?></td>
					<td class="subject-col"><?php esc_html_e( 'Subject', 'wk-uvdesk' ); ?></td>
					<td class="agent-name-col"><?php esc_html_e( 'Agent Name', 'wk-uvdesk' ); ?> </td>
				</tr>
					<?php
					$count = 1;
					if ( ! empty( $data_api->tickets ) && isset( $data_api->tickets ) ) {
						foreach ( $data_api->tickets as $ticket_key => $ticket_value ) {
							?>
							<tr data-toggle="tooltip" data-placement="left" title="" class="Open 1 unread" data-original-title="Open" >
								<td class="check-col">
									<span class="uv-uvdesk-priority-check" style="background-color:<?php echo wp_kses_post( $ticket_value->priority->color ); ?>"></span>
								</td>
								<td class="id-col" >
									<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
									<?php echo wp_kses_post( '#' . $ticket_value->id ); ?>
									</a>
								</td>
								<td class="reply-col">
									<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
										<span class="badge badge-lg"><?php echo wp_kses_post( $ticket_value->totalThreads ); ?></span>
									</a>
								</td>
								<td class="date-col">
									<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
										<span class="date"><?php echo wp_kses_post( $ticket_value->formatedCreatedAt ); ?></span>
									</a>
								</td>
								<td class="subject-col">
									<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>" class="subject">
									<?php echo wp_kses_post( $ticket_value->subject ); ?>
									</a>
									<span class="fade-subject"></span>
								</td>
								<td class="agent-name-col">
									<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
									<?php
									if ( ! empty( $ticket_value->agent->name ) ) {
										echo esc_html( $ticket_value->agent->name );
									} else {
										esc_html_e( 'Not Assigned', 'wk-uvdesk' );
									}
									?>
									</a>
								</td>
							</tr>
								<?php
								++$count;
						}
					}
					?>

			</table>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="navigation">
			<?php
			$tot_post  = $data_api->pagination->totalCount;
			$per_page  = $data_api->pagination->numItemsPerPage;
			$last_page = $data_api->pagination->pageCount;

			/**
			 * Pagination.
			 *
			 * @param int    $tot_post total post.
			 * @param int    $per_page per page.
			 * @param int    $last_page last page.
			 * @param int    $paged paged.
			 * @param string $link_page link page.
			 *
			 * @return mixed
			 */
			function uv_pagination( $tot_post, $per_page, $last_page, $paged, $link_page ) {
				$prev_arrow = esc_html__( 'Next »', 'wk-uvdesk' );
				$next_arrow = esc_html__( '« Previous', 'wk-uvdesk' );

				if ( $tot_post > 0 ) {
					$total = $tot_post / $per_page;
				} else {
					$total = $last_page;
				}
				$big = 99999; // need an unlikely integer.

				if ( $total > 1 ) {
					if ( get_option( 'permalink_structure' ) ) {
						$format = 'page/%#%/';
					} else {
						$format = '&paged=%#%';
					}
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => str_replace( $big, '%#%', $link_page ),
								'format'    => $format,
								'current'   => max( 1, $paged ),
								'total'     => ceil( $total ),
								'mid_size'  => 3,
								'type'      => 'list',
								'prev_text' => $next_arrow,
								'next_text' => $prev_arrow,
							)
						)
					);
				}
			}
			echo "<nav class='uv-pagination'>";
			$paged = get_query_var( 'paged' );

			uv_pagination( $tot_post, $per_page, $last_page, $paged, $link_page );

			echo '</div>';
			?>

		</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @return void
		 */
		public function wk_uvdesk_front_enqueue_script() {
			$current_user = get_current_user_id();

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_script( 'wk-uvdesk-front-script', WK_UVDESK_PLUGIN_URL . 'assets/dist/js/wk-uvdesk-frontend-script.min.js', array( 'jquery' ), WK_UVDESK_SCRIPT_VERSION, true );
			wp_enqueue_style( 'wk-uvdesk-front-style', WK_UVDESK_PLUGIN_URL . 'assets/dist/css/wk-uvdesk-front-style.min.css', array(), WK_UVDESK_SCRIPT_VERSION );
			wp_enqueue_script( 'wk-uvdesk-recaptcha-script', 'https://www.google.com/recaptcha/api.js', array(), WK_UVDESK_SCRIPT_VERSION, true );
			wp_localize_script(
				'wk-uvdesk-front-script',
				'apiScript',
				array(
					'api_admin_ajax'    => admin_url( 'admin-ajax.php' ),
					'api_nonce'         => wp_create_nonce( 'wk-uvdesk-api-ajaxnonce' ),
					'uvdesk_member_url' => site_url() . '/uvdesk/customer',
					'is_admin'          => $current_user,
				)
			);
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

