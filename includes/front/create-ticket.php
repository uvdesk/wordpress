<?php

if ( ! defined( 'ABSPATH' ) ) {

		exit; // Exit if accessed directly

}

function wk_customer_create_ticket() {

	$uv = new UvdeskProtected();

	$client_key = $uv->get_client_key();

	$secret_key = $uv->get_secret_key();

	$uvdesk_access_token = get_option( 'uvdesk_access_token' );

	$company_domain = get_option('uvdesk_company_domain');

	$error = array();

	if ( ! empty( $uvdesk_access_token ) && !empty( $company_domain ) ) {

		if ( isset( $_POST['submit1'] ) ) {

			$captcha = "";

			if ( isset( $_POST['g-recaptcha-response'] ) ) {

					$captcha = $_POST['g-recaptcha-response'];

			}

			if ( ! $captcha ) {
					$error[] = 'Please check the the captcha form.';
					// echo '<div class="alert alert-success alert-fixed alert-load"><span><span class="remove-file alert-msg"></span>Please check the the captcha form.</span></div>';

			}


			$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);

			$responseData = json_decode($response);

			if ($responseData->success==false) {

				echo "<script>";
				?>

				jQuery(".captcha-error").css('display','block');

				<?php echo "</script>";

			}

			if ( isset( $_POST['uvdesk_create_ticket_nonce'] ) ) {
				if (! wp_verify_nonce( $_POST['uvdesk_create_ticket_nonce'], 'uvdesk_create_ticket_nonce_action')) {

					 print 'Sorry, your nonce did not verify.';

					 exit;

				} else {

					 if(isset($_POST['subject']) && !empty($_POST['subject']) && isset($_POST['reply']) && !empty($_POST['reply']) && isset($_POST['type']) && !empty($_POST['type'])){

							$user=wp_get_current_user();
							$user_name = $user->user_nicename;
							$user_email = $user->user_email;
							$_POST_data = array(
								'name'=>$user_name,
								'from'=>$user_email,
								'subject'=>sanitize_text_field( $_POST['subject'] ),
								'reply'=>sanitize_text_field($_POST['reply']),'type'=>'4'
							);

							if (!empty($user)) {

								if( $_FILES['attachments']['size'][0] == 0 ){
									// $obj=new UVDESK_API();
									$ticket_status = UVDESK_API::create_new_ticket($_POST_data);
									$ticket_status=json_decode($ticket_status);
									 echo '<div class="alert alert-success alert-fixed alert-load">
													<span>
															<span class="remove-file alert-msg"></span>
															'.$ticket_status->message.'
													</span>
											</div>'; ?>
									<script>
									 setTimeout(function() {
											jQuery(".alert-fixed").fadeOut()
									}, 4000);
									</script>
									<?php

								}
								else{

								 if(isset($_FILES['attachments']) && $_FILES['attachments']['size'][0]>0){

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
												'application/x-rar-compressed'

										);
										$file_mime_type = mime_content_type($_FILES['attachments']['tmp_name'][0]);

									 if ( in_array($file_mime_type, $valid_file_mimes) ) {

											$ticket_status=UVDESK_API::create_new_ticket_with_attachement($_POST_data,$_FILES['attachments']);
											$ticket_status=json_decode($ticket_status);

											echo '<div class="alert alert-success alert-fixed alert-load">
															<span>
																	<span class="remove-file alert-msg"></span>
																	'.$ticket_status->message.'
															</span>
													</div>'; ?>
													<script>
													setTimeout(function() {
															jQuery(".alert-fixed").fadeOut()
													}, 4000);
													</script>

											<?php
										}
										else{

										echo '<div class="alert alert-success alert-fixed alert-load">
													<span>
															<span class="remove-file alert-msg"></span>
															'.$ticket_status->message.'
													</span>
											</div>'; ?>
											<script>
											setTimeout(function() {
													jQuery(".alert-fixed").fadeOut()
											}, 4000);
											</script>

										<?php

										}
								}
							}

						 }
						 else{

								echo '<div class="text-center uv-notify"><span class="alert alert-danger">There is some issue with user permission try again.</span><div>';

						 }
			 		 }
					 else{
						 		$error[] = 'Some fields are empty.';
								// echo '<div class="alert alert-success alert-fixed alert-load"><span><span class="remove-file alert-msg"></span>Some fields are empty.</span></div>';

					 }
				}
			}
		}
	}
	else{

		echo "<h1>Please Enter a valid Access Token<h1>";

	}

	if ( ! empty( $error ) ) {
		?>

		<div class="alert alert-success alert-fixed alr-err alert-load">
			<span>
				<span class="remove-file alert-msg"></span>
					<?php

					foreach ( $error as $sno => $err_mes ) {
						echo ( $err_mes .'<br>');
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

								<h2>Create a Ticket</h2>

								<a class="to-home" href="<?php echo site_url().'/uvdesk/customer/'; ?>">Back</a>

						</div>

								<form name="" method="post" action="" enctype="multipart/form-data" novalidate="false" id="createTicketForm">

										<?php wp_nonce_field( 'uvdesk_create_ticket_nonce_action', 'uvdesk_create_ticket_nonce' ); ?>

														<div class="form-group ">

																<label for="type" >Type</label>

																<select id="type" name="type" required="required" data-role="tagsinput" data-live-search="data-live-search" class="selectpicker" tabindex="-98">

																		<option value="" selected="selected">Choose query type</option>

																		<option value="87">Support</option>

																</select>

														</div>

														<div class="form-group ">

																<label for="subject">Subject</label>

																<input type="text" id="subject" name="subject" required="required" placeholder="Enter Subject" class="form-control">

														</div>

														<div class="form-group ">

																<label for="reply" >Message</label>

																<textarea id="reply" name="reply" required="required" placeholder="Brief Description about your query" data-iconlibrary="fa" data-height="250" class="form-control"></textarea>

														</div>

														<div class="form-group ">

																<div class="form-group attachments">
																		<div class="labelWidget">
																				<input id="attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="attach-file" decoratefile="decorateFile" infolabeltext="+ Attach File" infolabel="right" name="attachments[]">
																				<label class="attach-file pointer"></label>
																				<i class="remove-file" id="remove-att"></i>
																		</div>
																		<span id="addFile" class="label-right pointer">Attach File</span>
																</div>

														</div>

												<input type="hidden" id="_token" name="_token" value="eJPW5s_yBH1S6iTM1eLI18Kdb304tl-IwIqE0ktJTd8">



										<?php

												if(empty($client_key)){
														$client_key='Check for client Keys';
												}

										?>
										<div class="g-recaptcha" id="recaptcha" data-sitekey="<?php echo $client_key; ?>" style="transform:scale(0.77);transform-origin:0;-webkit-transform:scale(0.77);transform:scale(0.77);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>


												<div class="captcha-error">Please verify that you are not a robot.</div>

												<button type="submit" id="submit1" name="submit1" class="btn-create-tkt">Create Ticket</button>

								</form>

		</div>

</div>

<?php } ?>
