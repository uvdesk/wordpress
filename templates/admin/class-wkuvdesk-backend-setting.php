<?php
/**
 * WKUVDESK_Backend_Setting handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Backend_Setting' ) ) {
	/**
	 * WKUVDESK_Backend_Setting class.
	 */
	class WKUVDESK_Backend_Setting {
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
			$this->wkuvdesk_backend_settings();
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
		public function wkuvdesk_backend_settings() {
			?>
			<div class="wrap wkuvdesk_wrap">
				<div class="wkuvdesk-header">
					<h1><?php esc_html_e( 'UVDesk Settings', 'uvdesk' ); ?></h1>
				</div>
				<?php
				if ( filter_input( INPUT_GET, 'settings-updated', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'uvdesk' ) . '</p></div>';
				}
				?>
				<form method="post" action="options.php">
					<?php settings_fields( 'wkuvdesk-settings-group' ); ?>
					<div class="inner-wrap">
						<div class="wkuvdesk-settings-wrap">
							<div class="wkuvdesk-settings-left">
								<div class="wkuvdesk-settings-left-inner">
									<h3><strong><?php esc_html_e( 'Access Token', 'uvdesk' ); ?></strong></h3>
									<input id="uvdesk_access_token" type="text" name="uvdesk_access_token"  value="<?php echo esc_attr( get_option( 'uvdesk_access_token', '' ) ); ?>"/>
									<p class="description">
										<?php esc_html_e( 'You need to create access token from  ', 'uvdesk' ); ?><a href="<?php echo esc_url( 'https://www.uvdesk.com' ); ?>"><?php esc_html_e( 'Uvdesk Site', 'uvdesk' ); ?></a>
									</p>
								</div>

						<div class="wkuvdesk-settings-left-inner">
							<h3><strong><?php esc_html_e( 'Company Domain', 'uvdesk' ); ?></strong></h3>
							<input id="uvdesk_company_domain" type="text" name="uvdesk_company_domain"  value="<?php echo esc_attr( get_option( 'uvdesk_company_domain', '' ) ); ?>"/>
							<p class="description"><?php esc_html_e( 'This field is the domain of your organization.', 'uvdesk' ); ?></p>
						</div>
						<div class="wkuvdesk-settings-left-inner">
							<h3><strong><?php esc_html_e( 'Setup Recaptcha', 'uvdesk' ); ?></strong></h3>
							<div class="form-group">
								<label for="uvdesk_client_key" ><?php esc_html_e( 'Client Key OR Site Key', 'uvdesk' ); ?></label>
								<input type="text" id="uvdesk_client_key" name="uvdesk_client_key" value="<?php echo esc_attr( get_option( 'uvdesk_client_key', '' ) ); ?>" placeholder="<?php esc_attr_e( 'eg :-12***********12', 'uvdesk' ); ?>">
								<p class="description">
									<?php esc_html_e( 'You can create recaptcha keys from', 'uvdesk' ); ?> <a href="<?php echo esc_url( 'https://www.google.com/recaptcha/intro/index.html' ); ?>" target="_blank"><?php esc_html_e( 'Google RECAPTCHA', 'uvdesk' ); ?></a> <?php esc_html_e( 'Site', 'uvdesk' ); ?>
								</p>
								<label for="uvdesk_secret_key" ><?php esc_html_e( 'Secret Key', 'uvdesk' ); ?></label>
								<input type="text" id="uvdesk_secret_key" name="uvdesk_secret_key" value="<?php echo esc_attr( get_option( 'uvdesk_secret_key', '' ) ); ?>" placeholder="<?php esc_attr_e( 'eg :-12***********34', 'uvdesk' ); ?>">
							</div>
						</div>
					</div>
					<?php submit_button(); ?>
					</div>
				</form>
			</div>
		</div>
			<?php
		}
	}
}
