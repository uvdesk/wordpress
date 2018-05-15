<?php
/**
 * Plugin Name: Uvdesk Ticket system in wordpress
 * Plugin URI: https://store.webkul.com/Wordpress-Woocommerce-Marketplace.html
 * Description: WordPress Uvdesk ticket system will integrate symphony based ticket in wordpress framework using symfony api.
 * Version: 2.0.0
 * Author: Webkul
 * Author URI: http://webkul.com
 * Domain Path: plugins/uvdesk-app
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
**/
//BACKEND
/*---------------------------------------------------------------------------------------------*/
if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}


require_once('includes/class-uvdesk-protected.php');

if ( ! class_exists( 'UVDESK_API' ) ) :

final class UVDESK_API extends UvdeskProtected{

		protected static $_instance = null;

		private static $access_token;

		public static function instance() {

		if ( is_null( self::$_instance ) ) {

		self::$_instance = new self();

		}
		return self::$_instance;

		}

		private function includes() {
		// Include required files

		require_once( 'includes/class-uvdesk-install.php' );

		require_once('includes/admin/index.php');

		require_once( 'includes/class-uvdesk-form-handler.php' );

		require_once('includes/class-template-loader.php');

		require_once('includes/front/index.php');
		}


		public function __construct() {
		// Auto-load classes on demand
		ob_start();
		if ( function_exists( "__autoload" ) ) {

		spl_autoload_register( "__autoload" );

		}

		$this->wk_define_constants();

		// Include required files
		add_filter('rewrite_rules_array',array($this,'wp_insertcustom_rules'));

		add_filter('query_vars',array($this,'wp_insertcustom_vars'));

		$this->includes();

		// add_action( 'widgets_init', array( $this, 'include_widgets' ) );

		add_action('admin_init',array($this,'default_settings'));

		add_action('wp_enqueue_scripts',array($this,'front_enqueue_script'));

		add_action( 'admin_enqueue_scripts',array($this,'back_enqueue_script') );

		// Loaded action

		add_action('admin_menu', array($this, 'webkul_uvdesk_admin_menu'));

		}

		function wk_define_constants(){

		define( 'UVDESK_PLUGIN_FILE', __FILE__ );

		define( 'UVDESK_VERSION', '1.0' );

		define( 'UVDESK_API', plugin_dir_url(__FILE__));
		}

		function default_settings(){

		register_setting('webkul-uvdesk-settings-group','uvdesk_access_token');
		register_setting('webkul-uvdesk-settings-group','uvdesk_company_domain');
		register_setting('webkul-uvdesk-settings-group','uvdesk_client_key');
		register_setting('webkul-uvdesk-settings-group','uvdesk_secret_key');

		}



		// Admin settings page

		function webkul_uvdesk_admin_menu() {

		add_menu_page('UVdesk Ticket System','UVdesk Ticket System','manage_options','uvdesk_ticket_system',array($this,'list_ticket_uvdesk'),'dashicons-admin-page',3);

		add_submenu_page('uvdesk_ticket_system','UVDesk Setting','Settings','administrator','uvdesk_setting', array($this, 'uvdesk_settings_page'));

		}

		function uvdesk_settings_page() {

		include( 'includes/admin/uvdesk-backend-setting.php' );

		}

		function list_ticket_uvdesk() {

			if ( isset( $_GET['action'] ) && isset( $_GET['post'] ) && ! empty( $_GET['action'] ) && ! empty( $_GET['post'] ) && $_GET['action'] === 'view' ) {
				include( 'includes/admin/manage-ticket.php' );
			} else {
				include( 'includes/admin/class-uvdesk-admin-ticket.php' );
			}
		}

		function back_enqueue_script(){

		$current_user=get_current_user_id();

		wp_enqueue_script( 'uvdesk-back-script', UVDESK_API. 'assets/js/back-end-uvdesk.js', array( 'jquery' ), '4.9.15' );

		wp_localize_script( 'uvdesk-back-script', 'api_script', array( 'api_admin_ajax' => admin_url( 'admin-ajax.php' ), 'api_nonce' => wp_create_nonce('api-ajaxnonce'),'uvdesk_member_url'=>site_url().'/uvdesk/customer','is_admin'=>$current_user));

		wp_enqueue_style('uvdesk-style', UVDESK_API. 'assets/css/backend-style.css',array(),'1.2.5');
		}

		// Adding the id var so that WP recognizes it
		function wp_insertcustom_vars($vars){
		$vars[] = 'page_name';
		$vars[] = 'main_page';
		$vars[] = 'type';
		$vars[] = 'action';
		$vars[] = 'tid';
		$vars[] = 'pagination';
		$vars[] = 'paged';
		$vars[] = 'create';
		$vars[] = 'aid';

		return $vars;
		}

		function wp_insertcustom_rules($rules) {

		$newrules = array();
		$newrules=array(

			'(.+)/([a-z]+)/ticket/([a-z]+)/([0-9]+)/page/([0-9]+)/?'    => 'index.php?pagename=$matches[1]&main_page=$matches[2]&type=ticket&action=$matches[3]&tid=$matches[4]&pagination=page&paged=$matches[5]',
			'(.+)/([a-z]+)/ticket/([a-z]+)/([0-9]+)/?'    => 'index.php?pagename=$matches[1]&main_page=$matches[2]&type=ticket&action=$matches[3]&tid=$matches[4]',
			'(.+)/([a-z]+)/page/([0-9]+)/?'   			  => 'index.php?pagename=$matches[1]&main_page=$matches[2]&pagination=page&paged=$matches[3]',
			'(.+)/([a-z]+)/create-ticket/?'               => 'index.php?pagename=$matches[1]&main_page=$matches[2]&create=create-ticket',
			'(.+)/([a-z]+)/([0-9]+)/?'                    => 'index.php?pagename=$matches[1]&main_page=$matches[2]&aid=$matches[3]',
			'(.+)/([a-z]+)/?'                    		  => 'index.php?pagename=$matches[1]&main_page=$matches[2]',
			'(.+)/?'                    				  => 'index.php?pagename=$matches[1]'
		);

		return $newrules + $rules;
		}

		function front_enqueue_script(){

		$current_user=get_current_user_id();

		wp_enqueue_script( 'uvdesk-script', UVDESK_API. 'assets/js/plugin-uvdesk.js', array( 'jquery' ), '4.9.15' );

		wp_enqueue_script( 'uvdesk-recaptcha-script','https://www.google.com/recaptcha/api.js');

		wp_enqueue_style( 'fontawesome-css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style('uvdesk-style', UVDESK_API. 'assets/css/style.css',array(),'1.2.7');

		wp_localize_script( 'uvdesk-script', 'api_script', array( 'api_admin_ajax' => admin_url( 'admin-ajax.php' ), 'api_nonce' => wp_create_nonce('api-ajaxnonce'),'uvdesk_member_url'=>site_url().'/uvdesk/customer','is_admin'=>$current_user));

		}


		public static function create_new_ticket_with_attachement($post_attahment=array(),$post_image){

		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$url = 'https://'.$domain.'.uvdesk.com/en/api/tickets.json';

		$lineEnd = "\r\n";
		$mime_boundary = md5(time());
		$data = '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="type"' . $lineEnd . $lineEnd;
		$data .= "4" . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="name"' . $lineEnd . $lineEnd;
		$data .= $post_attahment['name'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="from"' . $lineEnd . $lineEnd;
		$data .= $post_attahment['from'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="subject"' . $lineEnd . $lineEnd;
		$data .= $post_attahment['subject'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="reply"' . $lineEnd . $lineEnd;
		$data .= $post_attahment['reply'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$attachments = array();
		for ( $i = 0; $i < count( $post_image['name'] ); $i++ ) {
		$attachments[ $i ]['name']     = $post_image['name'][ $i ];
		$attachments[ $i ]['type']     = $post_image['type'][ $i ];
		$attachments[ $i ]['tmp_name'] = $post_image['tmp_name'][ $i ];
		}

		foreach ( $attachments as $key => $attachement ) {
		$fileType = $attachement['type'];
		$fileName = $attachement['name'];
		$fileTmpName = $attachement['tmp_name'];
		// attachement 1
		$data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . $fileName . '"' . $lineEnd;
		$data .= "Content-Type: $fileType" . $lineEnd . $lineEnd;
		$data .= file_get_contents($fileTmpName) . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		}

		$headers = array(
		"Authorization: Bearer ".$uv->get_access_token(),
		"Content-type: multipart/form-data; boundary=" . $mime_boundary,
		);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($server_output, 0, $header_size);
		$response = substr($server_output, $header_size);
		if($info['http_code'] == 201) {

		return $response;

		} else {

		if($info['http_code'] == 404) {

			echo "Error, Please check the end point \n";

		}
		else {


			return $response;

			}
		}

		curl_close($ch);

		}

		public static function create_new_ticket($post_array=array()){

		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$url = 'https://'.$domain.'.uvdesk.com/en/api/tickets.json';

		$data = json_encode($post_array);

		$ch = curl_init($url);
		$headers = array(
		'Authorization: Bearer '.$uv->get_access_token(),
		'Content-type: application/json'
		);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($server_output, 0, $header_size);
		$response = substr($server_output, $header_size);
		if($info['http_code'] == 200 || $info['http_code'] == 201) {
		return $response;
		} elseif($info['http_code'] == 400) {
		echo "Error, request data not valid. (http-code: 400) \n";
		echo "Response :".$response." \n";
		} elseif($info['http_code'] == 404) {
		echo "Error, resource not found (http-code: 404) \n";
		} else {
		echo "Error, HTTP Status Code : " . $info['http_code'] . "\n";
		echo "Headers are ".$headers;
		echo "Response are ".$response;
		}
		curl_close($ch);
		}
		public static function post_thread_data_api($thread_url_param='',$thread_param=array()){
		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();


		$data = json_encode($thread_param);

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$thread_url_param;

		$ch = curl_init($url);
		$headers = array(
			'Authorization: Bearer '.$uv->get_access_token(),
			'Content-type: application/json'
		);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
		$headers = substr($server_output,0,$header_size);
		$response = substr($server_output,$header_size);

		if($info['http_code'] == 200 || $info['http_code'] == 201) {
			return $response;

		} elseif($info['http_code'] == 400) {
			return "Error,
		request data not valid. (http-code: 400) \n";
			echo "Response :".$response." \n";

		} elseif($info['http_code'] == 404) {
			echo "Error,
		resource not found (http-code: 404) \n";

		} else {
			echo "Error,
		HTTP Status Code : " . $info['http_code'] . "\n";
			echo "Headers are ".$headers;
			echo "Response are ".$response;

		}
		curl_close($ch);
		}

		public static function post_thread_data_api_with_attachment($thread_url_param='',$thread_param=array(),$post_image=''){

		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$thread_url_param;

		$lineEnd = "\r\n";
		$mime_boundary = md5(time());
		$data = '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="threadType"' . $lineEnd . $lineEnd;
		$data .= $thread_param['threadType'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="reply"' . $lineEnd . $lineEnd;
		$data .= $thread_param['reply'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="status"' . $lineEnd . $lineEnd;
		$data .= $thread_param['status'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="actAsType"' . $lineEnd . $lineEnd;
		$data .= $thread_param['actAsType'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		$data .= 'Content-Disposition: form-data; name="actAsEmail"' . $lineEnd . $lineEnd;
		$data .= $thread_param['actAsEmail'] . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;

		$attachments = array();
		for ( $i = 0; $i < count( $post_image['type'] ); $i++ ) {
		if ( $post_image['size'][ $i ] > 0 ) {
			$attachments[ $i ]['name']     = $post_image['name'][ $i ];
			$attachments[ $i ]['type']     = $post_image['type'][ $i ];
			$attachments[ $i ]['tmp_name'] = $post_image['tmp_name'][ $i ];
		}
		}
		foreach ( $attachments as $key => $attachement ) {
		$fileType = $attachement['type'];
		$fileName = $attachement['name'];
		$fileTmpName = $attachement['tmp_name'];
		// attachement 1
		$data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . $fileName . '"' . $lineEnd;
		$data .= "Content-Type: $fileType" . $lineEnd . $lineEnd;
		$data .= file_get_contents($fileTmpName) . $lineEnd;
		$data .= '--' . $mime_boundary . $lineEnd;
		}

		$data .= "--" . $mime_boundary . "--" . $lineEnd . $lineEnd;
		$headers = array(
		"Authorization: Bearer ".$uv->get_access_token(),
		"Content-type: multipart/form-data; boundary=" . $mime_boundary,
		);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($server_output, 0, $header_size);
		$response = substr($server_output, $header_size);
		if($info['http_code'] == 201) {
		return $response;
		} else {
		if($info['http_code'] == 404) {
			echo "Error, Please check the end point \n";
		} else {
			 return $response;
			}
		}
		curl_close($ch);
		}

		public function post_tag_ticket($url_param='',$param=array()){

		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$data = json_encode($param);

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$url_param;

		$ch = curl_init($url);

		$headers = array(
			'Authorization: Bearer '.$uv->get_access_token(),
			'Content-type: application/json'
		);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
		$headers = substr($server_output,0,$header_size);
		$response = substr($server_output,$header_size);
		if($info['http_code'] == 200 || $info['http_code'] == 201) {
			return $response;

		} elseif($info['http_code'] == 400) {
			echo "Error,
		request data not valid. (http-code: 400) \n";
			echo "Response :".$response." \n";

		} elseif($info['http_code'] == 404) {
			echo "Error,
		resource not found (http-code: 404) \n";

		} else {
			echo "Error,
		HTTP Status Code : " . $info['http_code'] . "\n";
			echo "Headers are ".$headers;
			echo "Response are ".$response;

		}
		curl_close($ch);
		}
		public static function get_customer_data_api($url_param='',$cheff=array()){
		$uv=new UvdeskProtected();
		$str='';
		// Return  tickets
		if (!empty($cheff)) {
		$str='?';
		$i=0;
		$append='';
		foreach ($cheff as $cheff_key => $cheff_value) {
			if($i>0){
				$append='&';
			}
			$str .=$append.$cheff_key.'='.$cheff_value;
			$i++;
		}
		}
		$domain = $uv->get_company_domain();

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$url_param.$str;

		$ch = curl_init($url);

		$headers = array(
			'Authorization: Bearer '.$uv->get_access_token(),
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($output, 0, $header_size);
		$response = substr($output, $header_size);

		$response=json_decode($response);

		if($info['http_code'] == 200) {
			return $response;
		} else if($info['http_code'] == 404) {
		 return "Error, resource not found (http-code: 404) \n";
		} else {
			// echo "Headers are ".$headers;
			 return $response;
		}
		curl_close($ch);
		}

		public static function delete_tag_ticket($tag_url_param='',$tag_prm=array()){

		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$str='';

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$tag_url_param;
		$ch = curl_init($url);
		$headers = array(
			'Authorization: Bearer '.$uv->get_access_token(),
		);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // note the Delete here
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tag_prm));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($output, 0, $header_size);
		$response = substr($output, $header_size);
		$response=json_decode($response);
		if($info['http_code'] == 200) {
			return $response;
		} else if($info['http_code'] == 404) {
		 return "Error, resource not found (http-code: 404) \n";
		} else {
			// echo "Headers are ".$headers;
			echo "Response are ".$response;
		}
		curl_close($ch);
		}

		public static function update_ticket($ticket_url_param='',$ticket_prm){
		$uv=new UvdeskProtected();
		$domain = $uv->get_company_domain();
		$str='';

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$ticket_url_param;
		$ch = curl_init($url);
		$headers = array(
			'Authorization: Bearer '.$uv->get_access_token(),
		);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // note Assignement here
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ticket_prm));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($output, 0, $header_size);
		$response = substr($output, $header_size);
		$response=json_decode($response);
		if($info['http_code'] == 200) {
			return $response;
		} else if($info['http_code'] == 404) {
		 return "Error, resource not found (http-code: 404) \n";
		} else {
			// echo "Headers are ".$headers;
			// echo "Response are ".$response;
		}
		curl_close($ch);
		}

		public static function get_attachment_data_api($attachment_url_param=''){

		$uv=new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$attachment_url_param;
		$ch = curl_init($url);
		$headers = array(
			'Authorization: Bearer '.$uv->get_access_token(),
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($output, 0, $header_size);
		$response = substr($output, $header_size);

		if($info['http_code'] == 200) {
			return $response;
		} else if($info['http_code'] == 404) {
		 return "Error, resource not found (http-code: 404) \n";
		} else {
			// echo "Headers are ".$headers;
			 return $response;
		}
		curl_close($ch);
		}

		function update_tag_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$tag=sanitize_text_field($_POST['tag']);
		$tid=intval($_POST['ticket_id']);
		if($tag){
		$data_tag=UVDESK_API::post_tag_ticket('ticket/'.$tid.'/tags.json',array('name'=>$tag));
		wp_send_json_success($data_tag);

		}
		else{
		wp_send_json_error("There is an error in adding tag");
		}
		die;
		}
		}

		function delete_tag_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$tag_id=sanitize_text_field($_POST['tag-id']);
		$tid=intval($_POST['ticket_id']);
		if(intval($tag_id)){
		$data_tag=UVDESK_API::delete_tag_ticket('ticket/'.$tid.'/tags.json',array('id'=>$tag_id));
		if($data_tag->message){

				echo json_encode($data_tag);
			}
			else{

				wp_send_json_error("There is an error in adding tag");
			}
		die;

		}
		else{
		wp_send_json_error("There is an error in adding tag");
		die;
		}
		}
		}

		function get_data_hash_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$hash=sanitize_text_field($_POST['hash']);
		if($hash){
			$data_hashapi=UVDESK_API::get_customer_data_api('tickets.json',array($hash=>1));
			if($data_hashapi){
				$content=$this->final_json_data($data_hashapi);
				wp_send_json_success($content);
			}
			else{

				wp_send_json_error($data_hashapi);
			}
		}
		else{
		echo "Please enter a valid label";
		}

		die;
		}
		}

		function list_members_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$data_member_api=UVDESK_API::get_customer_data_api('members.json',array('sort'=>'name','fullList'=>'true'));

		if($data_member_api){
			wp_send_json_success($data_member_api);
		}
		else{

			wp_send_json_error($data_member_api);
		}
		}
		else{
		echo "You are cheating.!";
		}

		die;
		}

		function get_default_data() {

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

				$data_customerapi=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','direction'=>'desc'));

				if(!empty($data_customerapi)){
						$content=$this->final_json_data($data_customerapi);

						wp_send_json_success($content);
				}
				else{

						wp_send_json_error($data_assign_api);
				}
		}


		die;
		}

		function get_tag_data() {
		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

				$data_customerapi=UVDESK_API::get_customer_data_api('tags.json',array('sort'=>'name','direction'=>'desc'));

				if(!empty($data_customerapi)){

						wp_send_json_success($data_customerapi);
				}
				else{

						wp_send_json_error($data_assign_api);
				}
		}


		die;
		}

		function get_data_via_team() {

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$t_id=intval($_POST['team_filter']);
		if($t_id){
		$data_team_api=UVDESK_API::get_customer_data_api('tickets.json',array('team'=>$t_id));
		if($data_team_api){
			$content=$this->final_json_data($data_team_api);
			wp_send_json_success($content);
		}
		else{

			wp_send_json_error($data_assign_api);
		}
		}
		else{
		echo "Please enter a valid Tab Id";
		}

		die;
		}
		}

		function get_data_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$tab_id=sanitize_text_field($_POST['tab_id']);
		if($tab_id){
			$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('status'=>intval($tab_id)));
			if($data_api){
				$content=$this->final_json_data($data_api,$tab_id);
				wp_send_json_success($content);
			}
			else{

				wp_send_json_error($data_api);
			}
		}
		else{
		echo "Please enter a valid Tab Id";
		}

		die;
		}
		}

		function get_customer_data(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$data_assign_api=UVDESK_API::get_customer_data_api('customers.json');

		if($data_assign_api){
			wp_send_json_success($data_assign_api);
		}
		else{

			wp_send_json_error($data_assign_api);
		}

		die;
		}
		}

		function get_data_via_customer(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$c_id=intval($_POST['customer_filter']);
		if($c_id){
		$data_assign_api=UVDESK_API::get_customer_data_api('tickets.json',array('customer'=>$c_id));
		if($data_assign_api){
			$content=$this->final_json_data($data_assign_api);
			wp_send_json_success($content);
		}
		else{

			wp_send_json_error($data_assign_api);
		}
		}
		else{
		echo "Please enter a valid Tab Id";
		}

		die;
		}
		}

		function get_group_data(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$data_group_api=UVDESK_API::get_customer_data_api('groups.json');

		if($data_group_api){
			wp_send_json_success($data_group_api);
		}
		else{

			wp_send_json_error($data_group_api);
		}

		die;
		}
		}

		function get_data_via_group(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$g_id=intval($_POST['group_filter']);
		if($g_id){
		$data_group_api=UVDESK_API::get_customer_data_api('groups.json',array('group'=>$g_id));
		if($data_group_api){
			$content=$this->final_json_data($data_group_api);
			wp_send_json_success($content);
		}
		else{

			wp_send_json_error($data_group_api);
		}
		}
		else{
		echo "Please enter a valid Tab Id";
		}

		die;
		}
		}

		function filter_members_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$m_id=intval($_POST['member_id']);

		if($m_id){

		$data_assign_api=UVDESK_API::get_customer_data_api('tickets.json',array('agent'=>$m_id));

		if($data_assign_api){

			$content=$this->final_json_data($data_assign_api);

			wp_send_json_success($content);
		}
		else{

			wp_send_json_error($data_assign_api);

		}

		}
		else{

		echo "Please enter a valid Tab Id";

		}

		die;
		}
		}

		function get_data_via_priority(){

		$priority_filter = sanitize_text_field($_POST['priority_filter']);
		if($priority_filter){
		$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('priority'=>intval($priority_filter)));

		if($data_api){
		$content=$this->final_json_data($data_api);
		wp_send_json_success($content);
		}
		else{
		wp_send_json_error($data_api);
		}
		}
		die;
		}

		function sort_ticket_via_api(){

			if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

			$field = sanitize_text_field($_POST['field']);

			$link_page = $_POST['page_link'];

			$is_admin=$_POST['is_admin'];

			if ( isset( $_POST['order'] ) && ! empty( $_POST['order'] ) ){

				$order = $_POST['order'];

			} else {

				$order = 'asc';

			}

			$current_user=wp_get_current_user();

			$c_email=$current_user->user_email;

			if ( $field ) {

				if ($is_admin=='true') {

					$data_customerapi = UVDESK_API::get_customer_data_api( 'tickets.json', array( 'sort' => $field, 'direction' => $order ) );

				} else {

					$data_customerapi = UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>$field,'direction'=>$order,'search'=>$c_email));

				}
				if ( ! empty( $data_customerapi ) ) {

						$content = $this->final_json_data_customer( $data_customerapi, $link_page );

					wp_send_json_success($content);
				}
				else{
					wp_send_json_error($data_assign_api);
				}
			}
			else{
				echo "Please enter a valid filter";
			}

			die;
			}
		}

		function update_priority_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$field=sanitize_text_field($_POST['field']);
		$is_admin=intval($_POST['is_admin']);
		if($field){
		$data_customerapi=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>$field,'direction'=>'desc'));
		if(!empty($data_customerapi)){
			if ($is_admin==1) {
				$content=$this->final_json_data($data_customerapi);
			}
			else{
				$content=$this->final_json_data_customer($data_customerapi);
			}

			wp_send_json_success($content);
		}
		else{

			wp_send_json_error($data_assign_api);
		}
		}
		else{
		echo "Please enter a valid filter";
		}

		die;
		}
		}
		function change_ticket_priority(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$ticket_ids=sanitize_text_field($_POST['ticket_id']);
		$ticket_ids=explode(",", $ticket_ids);
		$priority=intval($_POST['priority']);
		$json_data=array('ids'=>$ticket_ids,'priorityId'=>$priority);

		if($json_data){

		$data_customerapi=UVDESK_API::update_ticket('tickets/priority.json',$json_data);

		if(!empty($data_customerapi)){

			wp_send_json_success($data_customerapi);
		}
		else{

			wp_send_json_error($data_customerapi);
		}
		}
		else{
		echo "Please enter a valid filter";
		}

		die;
		}
		}

		function change_ticket_agent(){

			if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

			$ticket_ids=sanitize_text_field($_POST['ticket_id']);

			$ticket_ids=explode(",", $ticket_ids);

			$agent=intval($_POST['agent']);

			$json_data=array('ids'=>$ticket_ids,'agentId'=>$agent);

			if($json_data){

				$data_customerapi = self::update_ticket('tickets/agent.json',$json_data);

				if ( ! empty( $data_customerapi ) ) {

					wp_send_json_success( $data_customerapi );

				} else {

					wp_send_json_error($data_customerapi);
				}
			}
			else{
				echo "Please enter a valid filter";
			}

			die;
			}
		}

		function change_ticket_label(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$ticket_ids=sanitize_text_field($_POST['ticket_id']);
		$ticket_ids=explode(",", $ticket_ids);
		$label=intval($_POST['label']);
		$json_data=array('ids'=>$ticket_ids,'labelId'=>$label);

		if($json_data){

		$data_customerapi=UVDESK_API::update_ticket('tickets/label.json',$json_data);

		if(!empty($data_customerapi)){

			wp_send_json_success($data_customerapi);
		}
		else{

			wp_send_json_error($data_customerapi);
		}
		}
		else{
		echo "Please enter a valid filter";
		}

		die;
		}
		}


		function change_ticket_status(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$ticket_ids=sanitize_text_field($_POST['ticket_id']);
		$ticket_ids=explode(",", $ticket_ids);
		$status=intval($_POST['status']);
		$json_data=array('ids'=>$ticket_ids,'statusId'=>$status);

		if($json_data){

		$data_customerapi=UVDESK_API::update_ticket('tickets/status.json',$json_data);

		if(!empty($data_customerapi)){

			wp_send_json_success($data_customerapi);
		}
		else{

			wp_send_json_error($data_customerapi);
		}
		}
		else{
		echo "Please enter a valid filter";
		}

		die;
		}

		}

		function sort_customer_ticket_via_status(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$field=intval($_POST['field']);
		$current_user=wp_get_current_user();

		$c_email=$current_user->user_email;
		if($field){

		$arr_sum = array(
			'status'     => $field,
			'direction'  => 'desc',
			'actAsEmail' => $c_email,
			'actAsType'  => 'customer',
		);
		$data_customerapi=UVDESK_API::get_customer_data_api( 'tickets.json',$arr_sum );
		if(!empty($data_customerapi)){
			$content=$this->final_json_data_customer($data_customerapi);

			wp_send_json_success($content);
		}
		else{

			wp_send_json_error($data_assign_api);
		}
		}
		else{
		echo "Please enter a valid filter";
		}

		die;
		}
		}

		function get_data_via_type(){

		$type_filter = sanitize_text_field($_POST['type_filter']);

		if($type_filter){
		$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('type'=>intval($type_filter)));
		if($data_api){
		$content=$this->final_json_data($data_api);
		wp_send_json_success($content);
		}
		else{
		wp_send_json_error($data_api);
		}
		}
		die;
		}

		function get_data_via_tag(){
		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$tag_filter = sanitize_text_field($_POST['tag_filter']);
		$tag_filter= sanitize_text_field($tag_filter);
		if($tag_filter){
		$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('tags'=>intval($tag_filter)));
		if($data_api){
			$content=$this->final_json_data($data_api);
			wp_send_json_success($content);
		}
		else{
			wp_send_json_error($data_api);
		}
		}
		die;
		}
		}

		//delete thread

		function delete_thread_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$thread_id=sanitize_text_field($_POST['thread-id']);

		$tid=intval($_POST['ticket_id']);

		if(intval($thread_id)){

		$data_thread=UVDESK_API::delete_tag_ticket('ticket/'.$tid.'/thread/'.$thread_id.'.json',array('id'=>$thread_id));

		if($data_thread->message){

				echo json_encode($data_thread);
			}
			else{

				wp_send_json_error("There is an error in deleting thread1");
			}
		die;

		}
		else{
		wp_send_json_error("There is an error in deleting thread2");
		die;
		}
		}

		}


		// Delete Ticket

		function delete_ticket_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
		$t_id=intval($_POST['ticket_id']);
		$json_id=array('ids'=>$t_id);
		if($t_id){
		$data_deleted_ticket=UVDESK_API::update_ticket('tickets/trash.json',$json_id);
		if($data_deleted_ticket){
			wp_send_json_success($data_deleted_ticket);
		}
		else{

			wp_send_json_error($data_deleted_ticket);
		}
		}
		else{
		echo "Please enter valid Ticket Id";
		}

		die;
		}
		}


		//change Starred

		function toggle_the_starred(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$t_id=intval($_POST['ticket_id']);

		$ticket_stared = $_POST['stared_no'];

		$json_data = array("editType"=> "star", "value"=> $ticket_stared);

		if( $t_id ) {

		$data_starred_ticket = UVDESK_API::get_patch_data_api('ticket/'.$t_id.'.json',$json_data);

		if($data_starred_ticket){

		wp_send_json_success($data_starred_ticket);

		}
		else{

		wp_send_json_error($data_starred_ticket);
		}
		}
		else{

		echo "Please enter valid Ticket Id";

		}

		die;
		}

		}

		//changing the field with patch

		function get_patch_data_api( $patch_url_param = '', $ticket_prm ) {

		$uv = new UvdeskProtected();

		$domain = $uv->get_company_domain();

		$str = '';

		$url = 'https://'.$domain.'.uvdesk.com/en/api/'.$patch_url_param;

		$ch = curl_init($url);
		$headers = array(
		'Authorization: Bearer '.$uv->get_access_token(),
		);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH"); // note Assignement here
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ticket_prm));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($output, 0, $header_size);
		$response = substr($output, $header_size);
		$response=json_decode($response);

		if($info['http_code'] == 200) {
		return $response;
		} else if($info['http_code'] == 404) {
		return "Error, resource not found (http-code: 404) \n";
		} else {
		// echo "Headers are ".$headers;
		echo "Response are ".$response;
		}
		curl_close($ch);
		}


		function final_json_data_customer( $data_api='', $link_page = '' ) {

		ob_start();

		if( empty( $data_api->tickets ) ) {

		$con = "<div class='table-container' id='ticket-table'>
							<div class='tabs-table'>
								<table class='table'>
									<tr >
										<td class='record-no'><span>No Record Found</span></td>
									</tr>
								</table>
							</div>
						</div>";
		return $con ;

		}
		?>

		<div class="table-container" id="ticket-table">
		<div class="tabs-table">
				<table class="table">
										<tr>
									<td class="check-col"></td>
									<td class="id-col">Id</td>
									<td class="reply-col">Reply</td>
									<td class="date-col">Date</td>
									<td class="subject-col">Subject</td>
									<td class="agent-name-col">Agent Name</td>
								</tr>
								<?php
								$count = 1;
									if(!empty($data_api->tickets) && isset($data_api->tickets)){

										 foreach ($data_api->tickets as $ticket_key => $ticket_value) { ?>

											<tr data-toggle="tooltip" data-placement="left" title="" class="Open 1 unread" data-original-title="Open" >
													<td class="check-col">
														<span class="priority-check" style="background-color:<?php echo $ticket_value->priority->color; ?>"></span>
													</td>
													<td class="id-col" >
															<a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
																<?php echo '#'.$ticket_value->id; ?>
															</a>
													</td>
													<td class="reply-col">
															<a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
																	<span class="badge badge-lg"><?php echo $ticket_value->totalThreads; ?></span>
															</a>
													</td>
													<td class="date-col">
														<a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
															<span class="date"><?php echo $ticket_value->formatedCreatedAt; ?></span>
														</a>
													</td>
													<td class="subject-col">
															<a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>" class="subject">
																<?php echo $ticket_value->subject; ?>
															</a>
															<span class="fade-subject"></span>
													</td>

													<td class="agent-name-col">

															<a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">

															<?php
															if(!empty($ticket_value->agent->name)){
																echo $ticket_value->agent->name;
															}
															else {
																echo 'Not Assigned';
															} ?>

															</a>
													</td>

											</tr>

								<?php $count++;  }

									}


								?>

				</table>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="navigation">

		<?php
		$tot_post = $data_api->pagination->totalCount;

		$per_page = $data_api->pagination->numItemsPerPage;

		$last_page = $data_api->pagination->pageCount;

		function uv_pagination( $tot_post, $per_page, $last_page, $paged ,$link_page) {

		$prev_arrow = 'Next&nbsp;&raquo;';

		$next_arrow = '&laquo;&nbsp;Previous';

		global $wp_query;

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

				echo paginate_links ( array(

				'base'      => str_replace( $big, '%#%', $link_page ),

				'format'    => $format,

				'current'   => max( 1, $paged ),

				'total'     => ceil( $total ),

				'mid_size'  => 3,

				'type'      => 'list',

				'prev_text' => $next_arrow,

				'next_text' => $prev_arrow,

			) );

		}

		}
		echo "<nav class='uv-pagination'>";

		uv_pagination( $tot_post, $per_page, $last_page, $paged ,$link_page );

		echo '</div>';
		?>

		</div>

		<?php
		return ob_get_clean();
		}

		function get_thread_data_customer(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

		$page = $_POST['page_no'];

		$page = explode( '-',$page );

		$tid = $page[0];

		$page_no = $page[1] + 1 ;

		if ( 0 != $tid ) {

		$data_assign_api = $ticket_thread = UVDESK_API::get_customer_data_api( 'ticket/' . $tid . '/threads.json', array( 'page' => $page_no ) );

		if ( $data_assign_api ) {

			$content = $this->final_thread_json_data( $data_assign_api, $tid );

			wp_send_json_success($content);

		}
		else{

			wp_send_json_error($data_assign_api);

		}
		}
		else{
		echo "Please enter a valid ticket Id";
		}

		die;
		}
		}

		function final_thread_json_data( $data_api = '', $tid  = '') {

		ob_start();

		if ( empty( $data_api ) && empty( $tid ) ) {

			echo '' ;

		} else {

			$tot_post=$data_api->pagination->totalCount;
			$per_page=$data_api->pagination->numItemsPerPage;
			$last_page=$data_api->pagination->pageCount;
			$last_count=$data_api->pagination->lastItemNumber;

			if ( $tot_post - $last_count > 0 && $last_count > 0 ) {

				?>

				<div style="position:relative;" id="ajax-load-page">
					<span class="pagination-space" data-page="<?php echo ( $tid.'-'.$data_api->pagination->current ); ?>"><?php echo ( $tot_post - $last_count ); ?></span>
				</div>
				<hr>

				<?php
			}

			for ( $i = count( $data_api->threads ) - 1; $i >= 0; $i-- ) :

				if ( get_current_user_id() != 1 ) {

				$thread_value = $data_api->threads[ $i ];
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
												$aid = $attchment_value->id;

												$anamea = $attchment_value->name;

												$tmp = ( explode( '.', $anamea ) );

												$aname = end( $tmp );

												$img_ar = array( 'png', 'jpg', 'jpeg' );

												if ( in_array( $aname, $img_ar, true ) ) {
										?>
										<a href="<?php echo esc_url( $attchment_value->attachmentOrginal ); ?>" title="<?php echo $anamea;?>" target="_blank">

												<img src="<?php echo esc_html( $attchment_value->attachmentThumb );?>" class="fa fa-file zip" title="<?php echo esc_html( $anamea );?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>">

										</a>

										<?php
												} elseif ( $aname === 'zip' ) {
													$uv     = new UvdeskProtected();
													$domain = $uv->get_company_domain();

													$access_token=get_option('uvdesk_access_token');
			 										$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
										?>
										<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo $anamea;?>" target="_blank">
												<i class="wk-file-zip" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo esc_html( $attchment_value->name ); ?>">

												</i>
										</a>
										<?php
												} else {
													$uv     = new UvdeskProtected();
													$domain = $uv->get_company_domain();

													$access_token=get_option('uvdesk_access_token');
			 										$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;

									?>

								<a href="<?php echo esc_url( $attach_url ); ?>" title="<?php echo $anamea;?>" target="_blank">
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
				<?php if ( $i != 0 ) { ?>
				<hr>
				<?php }
				} else {
					$thread_value=$data_api->threads[ $i ];

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

													 $anamea = $attchment_value->name;

													 $tmp = ( explode( '.', $anamea ) );
													 $aname = end( $tmp );
													 $img_ar = array( 'png', 'jpg', 'jpeg' );
										 if ( in_array( $aname, $img_ar ) ) {
											 ?>
											 <a href="<?php echo $attchment_value->attachmentOrginal; ?>" title="<?php echo $anamea;?>" target="_blank">
													 <img src="<?php echo $attchment_value->attachmentThumb;?>" class="fa fa-file zip" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

											 </a>

											 <?php
										 }elseif($aname == 'zip'){
											 $uv=new UvdeskProtected();
											 $domain = $uv->get_company_domain();
											 $access_token=get_option('uvdesk_access_token');
 	 										$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;
											 ?>
											 <a href="<?php echo $attach_url; ?>" title="<?php echo $anamea;?>" target="_blank">
													 <i class="wk-file-zip" title="<?php echo $anamea;?>" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">

													 </i>
											 </a>
											 <?php
										 }
										 else{
											 $uv = new UvdeskProtected();

	 										$domain = $uv->get_company_domain();
											$access_token=get_option('uvdesk_access_token');
	 										$attach_url = 'https://'.$domain.'.uvdesk.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;

										?>

										<a href="<?php echo $attach_url; ?>" title="<?php echo $anamea;?>" target="_blank">
												<i class="wk-file"  data-toggle="tooltip" title="<?php echo $anamea;?>" data-original-title="<?php echo $attchment_value->name; ?>">

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
				endfor;

			return ob_get_clean();

		}

	}

 }

$uv_obj=new UVDESK_API();

add_action( 'wp_ajax_nopriv_change_ticket_label',array($uv_obj,'change_ticket_label' ));
add_action( 'wp_ajax_change_ticket_label',array($uv_obj,'change_ticket_label'));

add_action( 'wp_ajax_nopriv_change_ticket_agent',array($uv_obj,'change_ticket_agent' ));
add_action( 'wp_ajax_change_ticket_agent',array($uv_obj,'change_ticket_agent'));

add_action( 'wp_ajax_nopriv_change_ticket_priority',array($uv_obj,'change_ticket_priority' ));
add_action( 'wp_ajax_change_ticket_priority',array($uv_obj,'change_ticket_priority'));

add_action( 'wp_ajax_nopriv_get_data_via_tag',array($uv_obj,'get_data_via_tag' ));
add_action( 'wp_ajax_get_data_via_tag',array($uv_obj,'get_data_via_tag'));

add_action( 'wp_ajax_nopriv_change_ticket_status',array($uv_obj,'change_ticket_status' ));
add_action( 'wp_ajax_change_ticket_status',array($uv_obj,'change_ticket_status'));

add_action( 'wp_ajax_nopriv_sort_customer_ticket_via_status',array($uv_obj,'sort_customer_ticket_via_status' ));
add_action( 'wp_ajax_sort_customer_ticket_via_status',array($uv_obj,'sort_customer_ticket_via_status'));

add_action( 'wp_ajax_nopriv_sort_ticket_via_api',array($uv_obj,'sort_ticket_via_api' ));
add_action( 'wp_ajax_sort_ticket_via_api',array($uv_obj,'sort_ticket_via_api'));

add_action( 'wp_ajax_nopriv_delete_ticket_via_api',array($uv_obj,'delete_ticket_via_api' ));
add_action( 'wp_ajax_delete_ticket_via_api',array($uv_obj,'delete_ticket_via_api'));

add_action( 'wp_ajax_nopriv_filter_members_via_api',array($uv_obj,'filter_members_via_api' ));
add_action( 'wp_ajax_filter_members_via_api',array($uv_obj,'filter_members_via_api'));

add_action( 'wp_ajax_nopriv_get_data_via_priority',array($uv_obj,'get_data_via_priority'));
add_action( 'wp_ajax_get_data_via_priority',array($uv_obj,'get_data_via_priority'));

add_action( 'wp_ajax_nopriv_get_data_via_type',array($uv_obj,'get_data_via_type'));
add_action( 'wp_ajax_get_data_via_type',array($uv_obj,'get_data_via_type'));

add_action( 'wp_ajax_nopriv_list_members_via_api',array($uv_obj,'list_members_via_api' ));
add_action( 'wp_ajax_list_members_via_api',array($uv_obj,'list_members_via_api'));

add_action( 'wp_ajax_nopriv_get_data_hash_via_api',array($uv_obj,'get_data_hash_via_api' ));
add_action( 'wp_ajax_get_data_hash_via_api',array($uv_obj,'get_data_hash_via_api'));

add_action( 'wp_ajax_nopriv_delete_tag_via_api',array($uv_obj,'delete_tag_via_api' ));
add_action( 'wp_ajax_delete_tag_via_api',array($uv_obj,'delete_tag_via_api'));

add_action( 'wp_ajax_nopriv_get_data_via_api',array($uv_obj,'get_data_via_api' ));
add_action( 'wp_ajax_get_data_via_api',array($uv_obj,'get_data_via_api'));


add_action( 'wp_ajax_nopriv_update_tag_via_api',array($uv_obj,'update_tag_via_api' ));
add_action( 'wp_ajax_update_tag_via_api',array($uv_obj,'update_tag_via_api'));

add_action( 'wp_ajax_nopriv_get_tag_data',array($uv_obj,'get_tag_data' ));
add_action( 'wp_ajax_get_tag_data',array($uv_obj,'get_tag_data'));

add_action( 'wp_ajax_nopriv_get_data_via_team',array($uv_obj,'get_data_via_team' ));
add_action( 'wp_ajax_get_data_via_team',array($uv_obj,'get_data_via_team'));

add_action( 'wp_ajax_nopriv_get_customer_data',array($uv_obj,'get_customer_data' ));
add_action( 'wp_ajax_get_customer_data',array($uv_obj,'get_customer_data'));

add_action( 'wp_ajax_nopriv_get_thread_data_customer',array($uv_obj,'get_thread_data_customer' ));
add_action( 'wp_ajax_get_thread_data_customer',array($uv_obj,'get_thread_data_customer'));

add_action( 'wp_ajax_nopriv_get_data_via_customer',array($uv_obj,'get_data_via_customer' ));
add_action( 'wp_ajax_get_data_via_customer',array($uv_obj,'get_data_via_customer'));

add_action( 'wp_ajax_nopriv_get_data_via_group',array($uv_obj,'get_data_via_group' ));
add_action( 'wp_ajax_get_data_via_group',array($uv_obj,'get_data_via_group'));

add_action( 'wp_ajax_nopriv_get_group_data',array($uv_obj,'get_group_data' ));
add_action( 'wp_ajax_get_group_data',array($uv_obj,'get_group_data'));

add_action( 'wp_ajax_nopriv_get_default_data',array($uv_obj,'get_default_data' ));
add_action( 'wp_ajax_get_default_data',array($uv_obj,'get_default_data'));

add_action( 'wp_ajax_nopriv_delete_thread_via_api',array($uv_obj,'delete_thread_via_api' ));
add_action( 'wp_ajax_delete_thread_via_api',array($uv_obj,'delete_thread_via_api'));

add_action( 'wp_ajax_nopriv_toggle_the_starred',array($uv_obj,'toggle_the_starred' ));
add_action( 'wp_ajax_toggle_the_starred',array($uv_obj,'toggle_the_starred'));

endif;
