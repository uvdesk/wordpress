<?php
/**
 * WKUVDESK_Register handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Helper;

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Register' ) ) {
	/**
	 * WKUVDESK_Register class.
	 */
	class WKUVDESK_Register {
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
			add_shortcode( 'uvdesk', array( $this, 'wkuvdesk_display_uvdesk_form' ) );
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
		public function wkuvdesk_display_uvdesk_form() {
			$uv         = new Helper\WKUVDESK_Protected();
			$client_key = $uv->get_client_key();
			?>
			<div class="wk-uvdesk-signup-form">
				<h1><?php esc_html_e( 'Sign Up', 'wk-uvdesk' ); ?></h1>
				<form method="POST" action="">
					<?php wp_nonce_field( 'uvdesk_nonce', 'uvdesk_nonce' ); ?>
					<div class="form-group">
						<p>
							<label for="cust_name"><?php esc_html_e( 'Name', 'wk-uvdesk' ); ?></label>
							<input type="text" id="cust_name" name="user_name"/>
						</p>
					</div>
					<div class="form-group">
						<p>
							<label for="cust_email"><?php esc_html_e( 'Email', 'wk-uvdesk' ); ?></label>
							<input type="email" id="cust_email" name="user_email">
						</p>
					</div>
					<div class="form-group">
						<p>
							<label for="cust_pass"><?php esc_html_e( 'Password', 'wk-uvdesk' ); ?></label>
							<input type="password" id="cust_pass" name="user_pass">
						</p>
					</div>
					<?php $this->wkuvdesk_process_registration(); ?><br><br>

					<?php
					if ( empty( $client_key ) ) {
						$client_key = esc_html__( 'Check for client Keys', 'wk-uvdesk' );
					}
					?>
					<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $client_key ); ?>"></div>
					<?php wp_nonce_field( 'sign-up', 'signup-nonce' ); ?>
					<input name="action" type="hidden" value="<?php echo esc_attr( 'adduser' ); ?>" />
					<input type="submit" name="signup_submit" value="<?php esc_attr_e( 'Submit', 'wk-uvdesk' ); ?>" class="signup_submit">
				</form>

				<div class="text-center">
					<h3><?php esc_html_e( 'OR', 'wk-uvdesk' ); ?></h3>
					<h4><?php esc_html_e( 'Already Have An Account?', 'wk-uvdesk' ); ?>
						<a href="<?php echo esc_url( site_url() . '/uvdesk' ); ?>"><?php esc_html_e( 'Login', 'wk-uvdesk' ); ?></a>
					</h4>
				</div>
			</div>
				<?php
		}

		/**
		 * Process the registration.
		 *
		 * @return void
		 */
		public function wkuvdesk_process_registration() {
			if ( filter_input( INPUT_POST, 'signup_submit', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'signup-nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) && wp_verify_nonce( sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'signup-nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ), 'sign-up' ) ) {
				$uv         = new Helper\WKUVDESK_Protected();
				$secret_key = $uv->get_secret_key();
				$captcha    = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ? sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) : '';

				if ( empty( $captcha ) ) {
					echo '<span class="wk-uvdesk-pass-error">' . esc_html__( 'Please check the captcha form.', 'wk-uvdesk' ) . '</span>';
					return;
				}

				$remote_addr = '';

				if ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) && filter_var( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ?? null ), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					$remote_addr = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
				}

				$response      = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha . '&remoteip=' . $remote_addr );
				$response_data = json_decode( wp_remote_retrieve_body( $response ) );               if ( ! $response_data->success ) {
					echo '<span class="wk-uvdesk-pass-error">' . esc_html__( 'Captcha error', 'wk-uvdesk' ) . '</span>';
					return;
				}

				// User credentials.
				$user_name  = filter_input( INPUT_POST, 'user_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ? sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'user_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) : '';
				$user_email = filter_input( INPUT_POST, 'user_email', FILTER_SANITIZE_EMAIL ) ? sanitize_email( wp_unslash( filter_input( INPUT_POST, 'user_email', FILTER_SANITIZE_EMAIL ) ) ) : '';
				$user_pass  = filter_input( INPUT_POST, 'user_pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ? sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'user_pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) : '';

				if ( empty( $user_name ) || empty( $user_email ) || ! is_email( $user_email ) || empty( $user_pass ) || strlen( $user_pass ) <= 8 ) {
					echo '<span class="wk-uvdesk-pass-error">' . esc_html__( 'All fields are required and password must be at least 8 characters.', 'wk-uvdesk' ) . '</span>';
					return;
				}

				if ( email_exists( $user_email ) ) {
					echo '<span class="wk-uvdesk-pass-error">' . esc_html__( 'Email already exists.', 'wk-uvdesk' ) . '</span>';
					return;
				}

				$user_cred = array(
					'user_login' => $user_email,
					'user_email' => $user_email,
					'user_pass'  => $user_pass,
					'role'       => get_option( 'default_role' ),
				);

				$new_user_id = wp_insert_user( $user_cred );

				if ( ! is_wp_error( $new_user_id ) ) {
					update_user_meta( $new_user_id, 'first_name', $user_name );
					echo '<span class="wk-uvdesk-pass-succ">' . esc_html__( 'Registration user account created', 'wk-uvdesk' ) . '</span>';

					$user = get_user_by( 'id', $new_user_id );
					wp_set_current_user( $new_user_id, $user->user_login );
					wp_set_auth_cookie( $new_user_id );
					do_action( 'wp_login', $user->user_login, $user );

					wp_safe_redirect( site_url() . '/uvdesk/customer' );
					exit;
				} else {
					echo '<span class="wk-uvdesk-pass-error">' . esc_html__( 'Registration failed. Please try again.', 'wk-uvdesk' ) . '</span>';
				}
			}
		}
	}
}

