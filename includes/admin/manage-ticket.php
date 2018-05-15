<?php

if ( isset( $_GET['post'] ) ) {

$ticket_id = $_GET['post'];

}else{

	echo( '<h2>Select a ticket first from <a href="'.admin_url( 'admin.php?page=uvdesk_ticket_system' ).'">here</a>.</h2>' );

	exit;

}

if ( isset( $_POST['uvdesk_thread_nonce'] ) ) {

		if (! wp_verify_nonce( $_POST['uvdesk_thread_nonce'], 'uvdesk_thread_nonce_action')) {

			 print 'Sorry, your nonce did not verify.';

			 exit;

		} else {
			 if( isset($_POST['agent_email']) && !empty($_POST['agent_email']) && isset($_POST['threadType']) && !empty($_POST['threadType']) && isset($_POST['status']) && !empty($_POST['status']) && isset($_POST['thread_desc']) && !empty( $_POST['thread_desc'] ) ){
				$sdt=explode(',',$_POST['status']);

				if(empty($_FILES['attachments']['type'][0])){
						$thread_status=UVDESK_API::post_thread_data_api('ticket/'.sanitize_text_field($sdt[1]).'/threads.json',array('threadType'=>sanitize_text_field($_POST['threadType']),'reply'=>stripslashes(wpautop($_POST['thread_desc'],true)),'status'=>sanitize_text_field($sdt[0]),'actAsType'=>'agent','actAsEmail'=>sanitize_text_field($_POST['agent_email'])));

						$thread_status=json_decode($thread_status);
				}
				else{
						$thread_status=UVDESK_API::post_thread_data_api_with_attachment('ticket/'.sanitize_text_field($sdt[1]).'/threads.json',array('threadType'=>sanitize_text_field($_POST['threadType']),'reply'=>stripslashes(wpautop($_POST['thread_desc'])),'status'=>sanitize_text_field($sdt[0]),'actAsType'=>'agent','actAsEmail'=>sanitize_text_field($_POST['agent_email'])),$_FILES['attachments']);

						$thread_status=json_decode($thread_status);
				}
				?>
				<div class="updated notice">
				<p><?php echo $thread_status->message;?></p>
				</div>
				<?php

			 }
		}
}


$uvdesk_access_token = get_option( 'uvdesk_access_token' );

if ( ! empty( $uvdesk_access_token ) ) {

$ticket_details = UVDESK_API::get_customer_data_api( 'ticket/' . $ticket_id . '.json' );

	if ( isset( $ticket_details->error ) || empty( $ticket_details ) ) {

	echo ( '<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>' );

	} else {

			$ticket_thread = UVDESK_API::get_customer_data_api( 'ticket/'.$ticket_details->ticket->id.'/threads.json' );

			$data_api_members=UVDESK_API::get_customer_data_api('members.json',array('sort'=>'name','fullList'=>'true'));

			if ( isset( $ticket_thread->error ) || empty( $ticket_thread ) || isset( $data_api_members->error ) || empty( $data_api_members ) ) {

				echo ( '<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>' );

			}else {

			if( !empty( $ticket_details->ticket->agent->detail->agent->name ) )

			$tkt_agent =  $ticket_details->ticket->agent->detail->agent->name ;

			else

			$tkt_agent = '';

			if( !empty( $ticket_details->ticket->agent->email ) )

			$tkt_agent_email =  $ticket_details->ticket->agent->email ;

			else

			$tkt_agent_email = '';

			$data_api_groups=UVDESK_API::get_customer_data_api('groups.json');

			if( !empty( $ticket_details->ticket->group ) )

				$tkt_group =  $ticket_details->ticket->group ;

			else

				$tkt_group = '';

		 ?>
<div>

 <header style="display:inline-block;">

	 <h1>Ticket #<?php echo $ticket_details->ticket->incrementId ;?></h1>

	 <input type='hidden' value="<?php echo $ticket_details->ticket->id;?>" class="uv-ticket-id">

	 <a href = "<?php echo( admin_url( 'admin.php?page=uvdesk_ticket_system' ) ); ?>" class="button button-primary back-to-list">All tickets </a>

 </header>

 <div class="pre-loader">

	 <img class="ajax-loader-img" src="<?php echo(admin_url('images/spinner-2x.gif'));?>" alt="">

 </div>

</div>
	<div class="wk-main-wrapper" >

		<div class="uv-tk-manage-wrapper">


		<div class="wk-cards tkt-intro">

			<h2>
				<?php
				if($ticket_details->ticket->isStarred){
					$select = 'stared';
					$str_no = 1;
				}
				else{
					$select = '';
					$str_no = 0;
				}
				 echo('<div style="display: inline-block;vertical-align: text-bottom;"><input type="radio" style="opacity: 0;" ><span class="wk-starred-ico '.$select.'" data-id="'.$ticket_details->ticket->id.'" data-star-val="'.$str_no.'"></span></div>');?>
				<?php echo( $ticket_details->ticket->subject );  ?>

			<h2>

			<p>

			<span class="wk-space"><span class="wk-highlight">Created on - </span><?php echo(  $ticket_details->ticket->formatedCreatedAt ); ?></span>

			<span class="wk-space"><span class="wk-highlight">Agent - </span>

			<select class="wk-sel-agent" data-id="<?php echo $ticket_details->ticket->id; ?>">

				<option value="">Add agent</option>

					<?php foreach ( $data_api_members as $key => $value ):

						if( $tkt_agent == $value->name ){
							$select = 'selected';
						}else{
							$select = '';
						}

						echo( "<option value='".$value->id."' ".$select.">".$value->name."</option>" );

					endforeach;
					 ?>

			</select>

			</span>
			<span class="wk-space"><span class="wk-highlight">Priority - </span>

			<select id="wk-sel-priority">

				<option value="">Add priority</option>
				<option value='1' <?php if($ticket_details->ticket->priority->id == 1 ) echo 'selected'; ?> >Low</option>
				<option value='2' <?php if($ticket_details->ticket->priority->id == 2 ) echo 'selected'; ?> >Medium</option>
				<option value='3' <?php if($ticket_details->ticket->priority->id == 3 ) echo 'selected'; ?> >High</option>
				<option value='4' <?php if($ticket_details->ticket->priority->id == 4 ) echo 'selected'; ?> >Urgent</option>

			</select>
			</span>

			</p>
		</div>

		<div class="wk-cards tkt-replay">
			<div class= "replay-inline">
				<?php

				if(isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail) && !empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail)){

							echo '<img src="'.$ticket_details->ticket->customer->detail->customer->smallThumbnail.'">';

					}
					else{

							echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

					}
					?>
			 <span class="tkt-name"><?php echo ( $ticket_details->ticket->customer->detail->customer->name );?></span>
			 <span class="tkt-timestamp"><?php echo $ticket_details->ticket->formatedCreatedAt;?><span class="wk-accord"></span></span>

		 </div>
		 <div class="tkt-message" >
			 <?php echo( $ticket_details->createThread->reply ); ?>
			 <?php

					 if(!empty($ticket_details->createThread->attachments)) :  ?>

					 <div class="thread-attachments">

							<div class="attachments">

								<h4><strong>Uploaded files</strong></h4>

											<?php foreach ($ticket_details->createThread->attachments as $attchment_key => $attchment_value) :

												$aid = $attchment_value->id;

												$domain = get_option('uvdesk_company_domain');

												$access_token=get_option('uvdesk_access_token');

												$anamea = $attchment_value->name;

												$tmp = ( explode( '.', $anamea ) );
												$aname = end( $tmp );
												$img_ar = array( 'png', 'jpg', 'jpeg' );

									if ( in_array( $aname, $img_ar ) ) {
										?>
										<a href="<?php echo $attchment_value->attachmentOrginal; ?>" target="_blank">
												<img src="<?php echo $attchment_value->attachmentThumb;?>" class="fa fa-file zip" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

										</a>

										<?php
									} elseif ( $aname == 'zip' ) {

										$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
										?>
										<a href="<?php echo $attach_url; ?>" title="<?php echo $anamea;?>" target="_blank">
												<i class="wk-file-zip" title="" data-toggle="tooltip"  data-original-title="<?php echo $attchment_value->name; ?>">

												</i>
										</a>
										<?php
									}
									else{

										$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;

								 ?>

								 <a href="<?php echo $attach_url; ?>" target="_blank" title="<?php echo $anamea;?>">
										 <i class="wk-file" title="" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

										 </i>
								 </a>

				 <?php }

											 endforeach; ?>

									</div>

							 </div>

					<?php     endif; ?>
		 </div>
	 </div>

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
					 for ($i=count($ticket_thread->threads)-1;$i>=0;$i--){

								 $thread_value=$ticket_thread->threads[$i];

										if(isset( $thread_value->user->detail->agent)){

												 $msg_from = $thread_value->user->detail->agent->name;

										 }
										 else{

												 $msg_from = $thread_value->user->detail->customer->name;

												 }

								?>

								<div class="wk-cards tkt-replay " data-thread-id="<?php echo $thread_value->id;?>">
								<div class= "replay-inline" >
										<?php

											if(isset( $thread_value->user->smallThumbnail) && !empty( $thread_value->user->smallThumbnail)){

														echo '<img src="'.$thread_value->user->smallThumbnail.'">';

												}
												else{

														echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

												}

												?>
									 <span class="tkt-name">

										 <?php if(isset( $thread_value->user->detail->agent)){

										 echo $thread_value->user->detail->agent->name;

											 }
											 else{

										 echo $thread_value->user->detail->customer->name;}

										 ?>

									 </span>
									 <span class="tkt-timestamp"><?php echo $thread_value->formatedCreatedAt;?>
										 <span class="wk-accord"></span>
										 <span class="wk-delete-tkt-reply"></span>
									 </span>
								</div>
								<div class="tkt-message" >
									 <?php

									 echo( $thread_value->reply ); ?>
									 <?php
											if(!empty($thread_value->attachments)) :
													?>

										 <div class="thread-attachments">

												<div class="attachments">

													<h4><strong>Uploaded files</strong></h4>

												 <?php foreach ($thread_value->attachments as $attchment_key => $attchment_value) :

																	$aid = $attchment_value->id;

																	$domain = get_option('uvdesk_company_domain');

																	$access_token=get_option('uvdesk_access_token');

																	$anamea = $attchment_value->name;

																	$tmp = ( explode( '.', $anamea ) );
																	$aname = end( $tmp );
																	$img_ar = array( 'png', 'jpg', 'jpeg' );
														if ( in_array( $aname, $img_ar ) ) {
															?>
															<a href="<?php echo $attchment_value->attachmentOrginal; ?>" title="<?php echo $anamea;?>" target="_blank">
																	<img src="<?php echo $attchment_value->attachmentThumb;?>" class="fa fa-file zip"  data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

															</a>

															<?php
														}elseif($aname == 'zip'){
															$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
															?>
															<a href="<?php echo $attach_url; ?>" title="<?php echo $anamea;?>" target="_blank">
																	<i class="wk-file-zip" title="" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

																	</i>
															</a>
															<?php
														}
														else{
															$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
													 ?>

													 <a href="<?php echo $attach_url; ?>" title="<?php echo $anamea;?>" target="_blank">
															 <i class="wk-file" title="" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

															 </i>
													 </a>

									 <?php }
												endforeach; ?>

												</div>

												</div>

												<?php endif; ?>
								 </div>
							 </div>

								<?php

					 }


				?>

				<div class="tab-block form-div">
					<div class= "replay-inline">
						<?php

						if(isset( $thread_value->user->smallThumbnail) && !empty( $thread_value->user->smallThumbnail)){

										echo '<img src="'.$thread_value->user->smallThumbnail.'">';

								}
								else{

										echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

								}
							?>
					 <span class="tkt-name"><?php if(isset($ticket_details->ticket->agent->detail->agent->name)) echo $ticket_details->ticket->agent->detail->agent->name; else 'No Agent Assigned';?></span>
				 </div>

						<div class="tab-content">
								<div role="tabpanel" class="tab-pane active" id="reply">

										<form class="col-sm-12" enctype="multipart/form-data" method="post" id="admin-submit-ticket">

											<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?>

											<input type="hidden" name="agent_email" value="<?php

											if( !empty( $tkt_agent_email ) )
												{
													echo $tkt_agent_email;
												}
												?>"
												>
												<input type="hidden" name="threadType" value="reply">

												<input type="hidden" name="status" class="reply-status" value="1,<?php echo $ticket_details->ticket->id; ?>">

												 <?php

												 $settings = array(

													'media_buttons' => true, // show insert/upload button(s)

													'textarea_name' => 'thread_desc',

													'textarea_rows' => get_option('default_post_edit_rows', 10),

													'tabindex' => '',

													'teeny' => false,

													'dfw' => false,

													'tinymce' => true, /* load TinyMCE, can be used to pass settings directly to TinyMCE using an array()*/

													'quicktags' => false, /* load Quicktags, can be used to pass settings directly to Quicktags using an array()*/

													'force_br_newlines' => true,

													'force_p_newlines' => false

													);

												 echo wp_editor('','product_desc',$settings);

												 ?>

												<div class="form-group attachments">
														<div class="labelWidget">
																<input id="attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="attach-file" decoratefile="decorateFile" infolabeltext="+ Attach File" infolabel="right" name="attachments[]">
																<label class="attach-file pointer"></label>
																<i class="remove-file" id="remove-att"></i>
														</div>
														<span id="addFile" class="label-right pointer">Attach File</span>
												</div>
												<div class="col-sm-12 dropup">
														<div class=" reply-status-dropup">
																<button class="button button-primary reply-submit" type="submit">
																		Reply
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
			<h2>Customer</h2>
			<?php

			if(isset( $ticket_details->ticket->customer->detail->customer->smallThumbnail) && !empty( $ticket_details->ticket->customer->detail->customer->smallThumbnail)){

						echo '<img src="'.$ticket_details->ticket->customer->detail->customer->smallThumbnail.'">';

				}
				else{

						echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';

				}
				?>
			<p><?php echo($ticket_details->ticket->customer->detail->customer->name); ?></p>
			<p><?php echo($ticket_details->ticket->customer->email); ?></p>
		</div>

		<div class="wk-cards tkt-replay" style="margin-left: 0;">
			<?php
			if(isset($ticket_details->ticket->customer->detail->customer->id)){
				$c_email = $ticket_details->ticket->customer->email;
				$data_assign_api=UVDESK_API::get_customer_data_api('tickets.json',array('actAsType'=>'customer','actAsEmail'=>$c_email));
			}
			?>
			<h2>Total <?php echo $data_assign_api->pagination->totalCount;?>  Tickets</h2>
			<div class="tkt-section" >
				<?php
				$count = 1;
				foreach( $data_assign_api->tickets as $tkt_detail ) {

					?>

						<a style="text-decoration: none" href="?page=uvdesk_ticket_system&action=view&post=<?php echo $tkt_detail->incrementId?>">
							<p><?php echo $count ?>.
								<span style="color:#000"><?php echo('#'.$tkt_detail->id)?></span>

								<span> <?php echo $tkt_detail->subject ?></span>
							 </p>
						</a>

					<?php
					if( $count > 2 ){
						$cust_url = admin_url( "admin.php?page=uvdesk_ticket_system&custmr-action=customer-tkt&cid=" ).$ticket_details->ticket->customer->id;

						echo "<a href='".$cust_url."' ><h4>View All</h4></a>";
						break;
					}

					$count++;
				}
				?>
			</div>
		</div>

		<?php

		if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ( 1 == 3 ) ) {

				$user = get_user_by( 'email', $ticket_details->ticket->customer->email );

				if($user){

					?>

					<div class="wk-cards tkt-replay" style="margin-left: 0;">

						<?php

							$userId = $user->ID;

							$orderid = wc_get_customer_last_order( $userId );

							var_dump($orderid);

						?>

					</div>

				<?php

				}

		}

		 ?>

	</div>
</div>
		<?php

		 }
  }
}
else{
	echo( '<h1>Invalid setting details. </h1>' );
}
?>
