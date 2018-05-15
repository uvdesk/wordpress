<?php


function wk_customer_login() {

	if ( ! is_user_logged_in() ) {
		// Display WordPress login form:.
		$args = array(
			'redirect'       => home_url( '/uvdesk/customer' ),
			'form_id'        => 'loginform-custom',
			'label_username' => __( 'Email or Username' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in'   => __( 'Login' ),
			'remember'       => true,
		);

		echo "<div class='login-form'>";
		if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) {
			echo '<div class="alert alert-success alert-fixed  alr-err alert-load"><span><span class="remove-file alert-msg"></span>Invalid details- please enter valid details.</span></div>';?>
			<script>
			setTimeout(function() {
					jQuery(".alert-fixed").fadeOut()
			}, 4000);
			</script>
			<?php
		}
		echo '<h1>Member Login</h1>
					<div class="head">
						<img src="' . UVDESK_API . 'assets/images/user.png" alt=""/>
					</div>';

		wp_login_form( $args );

		echo "<div class='text-center'><h3>OR</h3>";
		echo '<h4><a href="' . site_url() . '/uvdesk/register">Register Here</a></h4></div>';

	} else {

		$user_id = get_current_user_id();

		if ( 1 === $user_id ) {

			wp_safe_redirect( admin_url() . 'admin.php?page=uvdesk_ticket_system' );

			exit;

		} else {

			wp_safe_redirect( site_url() . '/uvdesk/customer' );

			exit;

		}
	}
	echo '</div>';
}

function display_uvdesk_form( $atts ) {

	$uv = new UvdeskProtected();

	$client_key = $uv->get_client_key();

	$secret_key = $uv->get_secret_key();

?>

			<div class="wk-signup-form">

				<h1>Sign Up</h1>

				<form method="POST" action="">

						<div class="form-group">

							<p>

								<label for="cust_name">Name</label>

								<input type="text" id="cust_name" name="user_name">

							</p>

						</div>

						<div class="form-group">

							<p>

								<label for="cust_email">Email</label>

								<input type="email" id="cust_email" name="user_email">

							</p>

						</div>


						<div class="form-group">

							<p>

								<label for="cust_pass">Password</label>

								<input type="password" id="cust_pass" name="user_pass">

							</p>

						</div>

						<span class="pass-error">Your Password Must Contain At Least 8 Characters!</span> <br><br>

						<?php

						if ( empty( $client_key ) ) {
								$client_key = 'Check for client Keys';
						}

						?>

								<div class="g-recaptcha" id="recaptcha" data-sitekey="<?php echo $client_key; ?>" style="transform:scale(0.77);transform-origin:0;-webkit-transform:scale(0.77);transform:scale(0.77);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>

						<?php wp_nonce_field( 'sign-up', 'signup-nonce' ) ?>

						<input name="action" type="hidden" id="action" value="adduser" />

						<input type="submit" name="signup_submit" value="Submit" class="signup_submit">

				</form>
				<div class='text-center'>
					<h3>OR</h3>
				<h4>Already Have An Account? <a href="<?php echo site_url() . '/uvdesk/login'; ?>">Login</a></h4>
			</div>
		</div>

<?php

function process_registration() {

	$uv = new UvdeskProtected();

	$secret_key = $uv->get_secret_key();

	if ( isset( $_POST['signup-nonce'] ) && wp_verify_nonce( $_POST['signup-nonce'], 'sign-up' ) ) {

			$error = new WP_Error();

			$captcha = '';

		if ( isset( $_POST['g-recaptcha-response'] ) ) {

			$captcha = $_POST['g-recaptcha-response'];

		}

		if ( ! $captcha ) {

				echo '<span class="captcha-empty">Please check the the captcha form.</span>';

				return false;

		}


		$response = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'] );

		$responseData = json_decode( $response );

		if ( $responseData->success === false ) {

			echo '<script>';
			?>

			jQuery(".captcha-error").css('display','block');

			<?php

			echo '</script>';

			return false;

		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['action'] ) && $_POST['action'] === 'adduser' ) {

				$user_pass = sanitize_text_field( $_POST['user_pass'] );

				$user_login = explode( '@', sanitize_text_field( $_POST['user_email'] ) );

				$user_name = sanitize_text_field( $_POST['user_name'] );

				$error_code = $error->get_error_codes();

				$user_cred = array(

					'user_pass'  => sanitize_text_field( $_POST['user_pass'] ),
					'user_login' => $user_login[0],
					'user_email' => sanitize_text_field( $_POST['user_email'] ),
					'role'       => get_option( 'default_role' ),

				);

			if ( ! $_POST['user_name'] ) {

					$error->add( 'empty', 'Name is required for registration.' );

			} elseif ( ! $user_cred['user_email'] ) {

					$error->add( ' empty', 'Email is required for registration.' );

			} elseif ( ! is_email( $user_cred['user_email'] ) ) {

					$error->add( 'invalid', 'You must enter a valid email address.' );

			} elseif ( ! $user_cred['user_pass'] ) {

					$error->add( 'empty', 'Password is required for registration.' );

			} elseif ( strlen( $user_cred['user_pass'] ) < '8' ) {

					$error->add( 'invalid', 'Your Password Must Contain At Least 8 Characters!' );

			} elseif ( email_exists( $user_cred['user_email'] ) ) {

					$error->add( 'invalid', 'Sorry, that email address is already used!' );

			}

			if ( isset( $error_code ) && ! empty( $error_code ) ) {

				return $error;

			} else {

				$new_user = wp_insert_user( $user_cred );

				update_user_meta( $new_user, 'first_name', $user_name );

				if ( $new_user ) {

					echo '<script>';

				?>

						alert('registered successfully');

				<?php

					echo '</script>';

					wp_safe_redirect( site_url() . '/uvdesk/customer' );

					return $new_user;

				}
			}
		}
	}
}

if ( isset( $_POST['signup_submit'] ) ) {

		$result = process_registration();

	if ( $result ) {

		if ( is_wp_error( $result ) ) {

			echo '<span class="captcha-empty">' . implode( '</li><li>', $result->get_error_messages() ) . '</span>';

		} else {

				wp_set_current_user( $result ); // set the current wp user.

				wp_set_auth_cookie( $result );

		}
	}
}
}

?>
