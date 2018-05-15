<?php
/**
 * Installation related functions and actions.
 *
 * @author 		Webkul
 * @category 	Admin
 * @package 	webkul/Classes
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'UVDESK_Install' ) ) :
/**
 * UVDESK_Install Class
 */
class UVDESK_Install {
	/**
	* Hook in tabs.
	*/

	public function __construct() {



		register_activation_hook( UVDESK_PLUGIN_FILE, array( $this, 'install' ) );
	}


	/**
	 * Install MP
	*/
	public function install(){

		add_option('uvdesk_access_token', '826B0639C1AE373DD2B515F12F24131341149826B0639C1AE373DD2B515F12F241313', '', 'yes');

		$this->create_pages();

	}



		/**
	 * Create pages that the plugin relies on, storing page id's in variables.
		 *
		 * @access public
		 * @return void
		 */
	public function create_pages() {

		$postarr = array(
			'post_content' => '[uvdesk]',
			'post_title' => 'uvdesk',
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'post_name' => 'uvdesk'
		);
		$pageid = wp_insert_post($postarr);
	}


}
endif;
return new UVDESK_Install();
