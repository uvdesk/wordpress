<?php
/**
 * APi file token handler.
 *
 * @package UVdesk Free Helpdesk
 */
namespace WKUVDESK\Helper;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/** Check class exists or not */
if ( ! class_exists( 'WKUVDESK_Protected' ) ) {
	/**
	 * WKUVDESK_Protected class.
	 */
	class WKUVDESK_Protected {
		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		private $access_token;

		/**
		 * Client key variable.
		 *
		 * @var $client_key
		 */
		private $client_key;

		/**
		 * Secret key variable.
		 *
		 * @var $secret_key
		 */
		private $secret_key;

		/**
		 * Company domain variable.
		 *
		 * @var $company_domain
		 */
		private $company_domain;

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			// setup default access token secret key and client key.
			$this->set_access_token();
			$this->set_secret_key();
			$this->set_client_key();
			$this->set_company_domain();
		}

		/**
		 * Get access token.
		 *
		 * @return string
		 */
		protected function get_access_token() {
			return $this->access_token;
		}

		/**
		 * Get secret key.
		 *
		 * @return string
		 */
		public function get_secret_key() {
			return $this->secret_key;
		}

		/**
		 * Get client key.
		 *
		 * @return string
		 */
		public function get_client_key() {
			return $this->client_key;
		}

		/**
		 * Get company domain.
		 *
		 * @return string
		 */
		protected function get_company_domain() {
			return $this->company_domain;
		}

		/**
		 * Set access token.
		 *
		 * @return void
		 */
		private function set_access_token() {
			$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );
			if ( ! empty( $uvdesk_access_token ) ) {
				$this->access_token = $uvdesk_access_token;
			} else {
				$this->access_token = '';
			}
		}

		/**
		 * Set client key.
		 *
		 * @return void
		 */
		private function set_client_key() {
			$uvdesk_client_key = get_option( 'uvdesk_client_key' );
			if ( ! empty( $uvdesk_client_key ) ) {
				$this->client_key = $uvdesk_client_key;
			} else {
				$this->client_key = '';
			}
		}

		/**
		 * Set company domain.
		 *
		 * @return void
		 */
		private function set_company_domain() {
			$uvdesk_company_domain = get_option( 'uvdesk_company_domain' );
			if ( ! empty( $uvdesk_company_domain ) ) {
				$this->company_domain = $uvdesk_company_domain;
			} else {
				$this->company_domain = '';
			}
		}

		/**
		 * Set secret key.
		 *
		 * @return void
		 */
		private function set_secret_key() {
			$uvdesk_secret_key = get_option( 'uvdesk_secret_key' );
			if ( ! empty( $uvdesk_secret_key ) ) {
				$this->secret_key = $uvdesk_secret_key;
			} else {
				$this->secret_key = '';
			}
		}
	}
}
