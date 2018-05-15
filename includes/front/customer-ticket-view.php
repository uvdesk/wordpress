<?php

if ( ! defined( 'ABSPATH' ) ) {

		exit; // Exit if accessed directly.

}

function wk_customer_ticket_view() {

	$ticket_id = intval( get_query_var( 'tid' ) );

	$paged = intval( get_query_var( 'paged' ) );

	if ( 0 === $paged ) {
		$paged = 1;
	}

	$current_user = wp_get_current_user();

	$c_email = $current_user->user_email;

	$uvdesk_access_token = get_option( 'uvdesk_access_token' );

	if ( ! empty( $uvdesk_access_token ) ) {

		if ( isset( $_POST['submit-thread'] ) ) {
			if ( isset( $_POST['uvdesk_thread_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_POST['uvdesk_thread_nonce'], 'uvdesk_thread_nonce_action' ) ) {

					print 'Sorry, your nonce did not verify.';

					exit;

				} else {

					if ( isset( $_POST['customer_email'] ) && ! empty( $_POST['customer_email'] ) && isset( $_POST['threadType'] ) && ! empty( $_POST['threadType'] ) && isset( $_POST['status'] ) && ! empty( $_POST['status'] ) && isset( $_POST['thread_desc'] ) && ! empty( $_POST['thread_desc'] ) ) {
								$sdt   = explode( ',', $_POST['status'] );
								$reply = sanitize_text_field( $_POST['thread_desc'] );

						if ( 0 === count( $_FILES['attachments']['type'] ) ) {

								$thread_status = UVDESK_API::post_thread_data_api( 'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json' , array(

									'threadType' => sanitize_text_field( $_POST['threadType'] ),

									'reply' => $reply,

									'status' => sanitize_text_field( $sdt[0] ),

									'actAsType'  => 'customer',

									'actAsEmail' => sanitize_text_field( $_POST['customer_email'] ),

								) );

						} else {

								$thread_status = UVDESK_API::post_thread_data_api_with_attachment( 'ticket/' . sanitize_text_field( $sdt[1] ) . '/threads.json', array(

									'threadType' => sanitize_text_field( $_POST['threadType'] ),

									'reply' => $reply,

									'status' => sanitize_text_field( $sdt[0] ),

									'actAsType' => 'customer',

									'actAsEmail' => sanitize_text_field( $_POST['customer_email'] ),

								),

								$_FILES['attachments']
							);

						}

						$thread_status = json_decode( $thread_status );
						echo '<div class="alert alert-success alert-fixed alert-load">
									 <span>
											 <span class="remove-file alert-msg"></span>
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
		}

		$arr_sum = array(

			'actAsType' => 'customer',
			'actAsEmail' => $c_email,
		);
		$ticket_details = UVDESK_API::get_customer_data_api( 'ticket/' . $ticket_id . '.json', $arr_sum );

		if ( isset( $ticket_details->error ) || empty( $ticket_details ) ) {

				echo '<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>';

		} else {

			if ( ! empty( $ticket_details->ticket->status->name ) && isset( $ticket_details->ticket->status->name ) ) {

					$ticket_status_name = $ticket_details->ticket->status->name;

			}
			if ( ! empty( $ticket_details->ticket->priority->name ) && isset( $ticket_details->ticket->priority->name ) ) {

					$ticket_priority = $ticket_details->ticket->priority->name;

			}

			if ( ! empty( $ticket_details->ticket->group->name ) && isset( $ticket_details->ticket->group->name ) ) {

					$ticket_group_name = $ticket_details->ticket->group->name;

			}
			if ( ! empty( $ticket_details->ticket->type->name ) && isset( $ticket_details->ticket->type->name ) ) {

					$ticket_type_name = $ticket_details->ticket->type->name;

			}

			if ( ! empty( $ticket_details->ticket->agent->detail->agent->name ) && isset( $ticket_details->ticket->agent->detail->agent->name ) ) {

					$ticket_agentname = $ticket_details->ticket->agent->detail->agent->name;

			}

			if ( ! empty( $ticket_details->ticket->formatedCreatedAt ) && isset( $ticket_details->ticket->formatedCreatedAt ) ) {

					$ticket_created = $ticket_details->ticket->formatedCreatedAt;

			}
			if ( ! empty( $ticket_details->ticket->customer->detail->customer->name ) && isset( $ticket_details->ticket->customer->detail->customer->name ) ) {

					$customer_name = $ticket_details->ticket->customer->detail->customer->name;

			}
			if ( ! empty( $ticket_details->ticket->customer->email ) && isset( $ticket_details->ticket->customer->email ) ) {

					$customer_email = $ticket_details->ticket->customer->email;

			}
			if ( ! empty( $ticket_details->createThread->reply ) && isset( $ticket_details->createThread->reply ) ) {

					$created_thread = $ticket_details->createThread->reply;

			}

			$ticket_thread = UVDESK_API::get_customer_data_api( 'ticket/' . $ticket_details->ticket->id . '/threads.json' );

			?>

			<div class="body content-wrap">

				<div class="pre-loader">

					<img class="ajax-loader-img" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="">

				</div>

				<div class="tkt-front-header">

					<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/' ); ?>" class='to-main-list'>  All tickets</a>


				</div>

				<div class="side-section-front">

					<p class='side-sec-head'>TICKET INFROMATION</p>

					<span>
						<span class="side-title">ID</span>
						<span class="side-info"># <?php echo esc_html( $ticket_details->ticket->id ); ?></span>
					</span>
					<span>
						<span class="side-title">Timestamp</span>
						<span class="side-info"><?php echo esc_html( $ticket_details->ticket->formatedCreatedAt ); ?></span>
					</span>
					<span>
						<span class="side-title">Total Replies</span>
						<span class="side-info"><?php echo esc_html( $ticket_details->ticketTotalThreads ); ?></span>
					</span>
					<hr>
					<span>
						<span class="side-title">Agent</span>
						<span class="side-info">
							<?php
							if ( ! empty( $ticket_details->ticket->agent->detail->agent->name ) ) {

								echo esc_html( $ticket_details->ticket->agent->detail->agent->name );
							} else {
									echo 'Not Assigned';
							}
							?>
						</span>
					</span>
						<?php

						if ( ! empty( $ticket_details->ticket->priority->name ) ) :

						?>

					<span>
						<span class="side-title">Priority</span>
						<span class="side-info"><b class="priority-check" style="background-color:<?php echo $ticket_details->ticket->priority->color; ?>"></b><?php echo esc_html( $ticket_details->ticket->priority->name ); ?></span>
					</span>

					<?php endif; ?>
					<span>
						<span class="side-title">Status</span>
						<span class="side-info"> <?php echo esc_html( $ticket_status_name ); ?></span>
					</span>

				</div>

				<div class="whole-wrapper">

						<div class="tkt-front-intro">

							<div style="display:inline-block;margin:10px 20px;">
								<span style="display:inline-block;font-size:20px" class="wk-highlight" >Subject :- </span>
								<h4 style="display:inline-block;" class="tkt-subject">

									<?php

									echo esc_html( $ticket_details->ticket->subject );

									?>

								</h4>

								<p>

									<span class="wk-space">

										<span class="wk-highlight">Created on - </span><?php echo esc_html( $ticket_details->ticket->formatedCreatedAt ); ?>

									</span>

									<span class="wk-space">

										<span class="wk-highlight">Agent - </span>

										<?php
										if ( ! empty( $ticket_details->ticket->agent->detail->agent->name ) ) {

												echo esc_html( $ticket_details->ticket->agent->detail->agent->name );
										} else {
												echo 'Not Assigned';
										}
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

											if ( isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) ) {

														echo ( '<img src="' . esc_html( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) . '">' );

											} else {

														echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

											}
												?>
										</span>

										<span class="info">

													<span class="rpy-name"><?php echo ( esc_attr( $customer_name ) . '  ' ); ?></span>&emsp;Created ticket

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
											<?php if ( ! empty( $created_thread ) ) : ?>

													<?php print_r( $created_thread ); ?>

											<?php endif; ?>
											<?php
												if ( ! empty( $ticket_details->createThread->attachments ) ) :
											?>

											<div class="thread-attachments">

													<div class="attachments">

														<p><strong>Uploaded files</strong></p>

															<?php
															foreach ( $ticket_details->createThread->attachments as $attchment_key => $attchment_value ) :

																$domain = get_option('uvdesk_company_domain');
																
																$access_token=get_option('uvdesk_access_token');

																$aid = $attchment_value->id;

																$anamea = $attchment_value->name;

																$tmp = ( explode( '.', $anamea ) );

																$aname = end( $tmp );

																$img_ar = array( 'png', 'jpg', 'jpeg' );

																if ( in_array( $aname, $img_ar, true ) ) {
																?>
																<a href="<?php echo esc_html( $attchment_value->attachmentOrginal ); ?>" target="_blank">

																		<img src="<?php echo esc_html( $attchment_value->attachmentThumb ); ?>" class="fa fa-file zip" title="<?php echo esc_html( $anamea ); ?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>">

																</a>

																<?php
																} elseif ( $aname === 'zip' ) {

																	$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
																	?>
																	<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">

																		<i class="wk-file-zip" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>"></i>

																	</a>
																	<?php
																} else {
																	$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
																?>

																<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																		<i class="wk-file" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>">

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
						$tot_post=$ticket_thread->pagination->totalCount;
						$per_page=$ticket_thread->pagination->numItemsPerPage;
						$last_page=$ticket_thread->pagination->pageCount;
						$last_count=$ticket_thread->pagination->lastItemNumber;

						if ( $tot_post - $last_count > 0 && $last_count > 0 ) {
							?>
						<div style="position:relative;" id="ajax-load-page">
							<span class="pagination-space"  data-page="<?php echo ( $ticket_details->ticket->id.'-'.$ticket_thread->pagination->current ); ?>"><?php echo ( $tot_post - $last_count ); ?></span>
						</div>
							<div id="content-here-aj">

							</div>
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

															if ( isset( $thread_value->user->smallThumbnail ) && ! empty( $thread_value->user->smallThumbnail ) ) {

																		echo ( '<img src="' . esc_html( $thread_value->user->smallThumbnail ) . '">' );

															} else {

																		echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

															}
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
														<?php if ( ! empty( $thread_value->reply ) ) { ?>

																<?php echo $thread_value->reply; ?>

														<?php } ?>
														<?php
														if ( ! empty( $thread_value->attachments ) ) {
														?>

														<div class="thread-attachments">

															<div class="attachments">

																<p><strong>Uploaded files</strong></p>

																			<?php
																			foreach ( $thread_value->attachments as $attchment_key => $attchment_value ) {

																				$domain = get_option('uvdesk_company_domain');

																				$access_token = get_option( 'uvdesk_access_token' );

																				$aid = $attchment_value->id;

																				$anamea = $attchment_value->name;

																				$tmp = ( explode( '.', $anamea ) );

																				$aname = end( $tmp );

																				$img_ar = array( 'png', 'jpg', 'jpeg' );

																				if ( in_array( $aname, $img_ar, true ) ) {
																		?>
																		<a href="<?php echo esc_url( $attchment_value->attachmentOrginal ); ?>" target="_blank">

																				<img src="<?php echo esc_html( $attchment_value->attachmentThumb );?>" class="fa fa-file zip" title="<?php echo esc_html( $anamea );?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>">

																		</a>

																		<?php
																				} elseif ( $aname === 'zip' ) {

																					$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
																		?>
																		<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																				<i class="wk-file-zip" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>">

																				</i>
																		</a>
																		<?php
																				} else {

																					$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
																	?>

																<a href="<?php echo esc_url( $attach_url ); ?>" target="_blank">
																	<i class="wk-file" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>"></i>
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

									if ( isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) && ! empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) ) {

												echo ( '<img src="' . esc_html( $ticket_details->ticket->customer->detail->customer->smallThumbnail ) . '">' );

									} else {

												echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

									}
										?>
								</span>

								<span class="info">

									<span class="rpy-name">You can write your reply</span>

								</span>

							</div>

							<form enctype="multipart/form-data" method="post" action="">

										<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?>

										<input type="hidden" name="customer_email" value="<?php echo $ticket_details->ticket->customer->email; ?>">
										<input type="hidden" name="threadType" value="reply">
										<input type="hidden" name="status" class="reply-status" value="1,<?php echo $ticket_details->ticket->id; ?>">

										<?php

											$settings = array(

												'media_buttons' => true, // show insert/upload button(s)

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

										 echo wp_editor( '', 'product_desc', $settings );

										?>
										<div class="form-group attachments">
												<div class="labelWidget">
														<input id="attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="attach-file" decoratefile="decorateFile" infolabeltext="+ Attach File" infolabel="right" name="attachments[]">
														<label class="attach-file pointer"></label>
														<i class="remove-file" id="remove-att"></i>
												</div>
												<span id="addFile" class="label-right pointer">Attach File</span>
										</div>

										<div class="reply-submit">

												<button class="submit-rply" type="submit" name="submit-thread">
														Reply
												</button>

										</div>

							</form>

						</div>

				</div>

			</div>

		<?php
		}
	} else {

				echo '<h1>Please Enter a valid Access Token</h1>';

	}
}
