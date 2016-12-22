<?php
/**
 * Plugin Name: Uvdesk Ticket system in wordpress
 * Plugin URI: https://store.webkul.com/Wordpress-Woocommerce-Marketplace.html
 * Description: WordPress Uvdesk ticket system will integrate symphony based ticket in wordpress framework using symfony api.
 * Version: 1.0
 * Author: Webkul
 * Author URI: http://webkul.com
 * Domain Path: plugins/marketplace
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
**/
//BACKEND
/*---------------------------------------------------------------------------------------------*/
if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

} 

define( 'UVDESK_PLUGIN_FILE', __FILE__ );

define( 'UVDESK_VERSION', '1.0' ); 

define( 'UVDESK_API', plugin_dir_url(__FILE__)); 
 


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
		if ( function_exists( "__autoload" ) ) {

			spl_autoload_register( "__autoload" );

		}
		
		 
		// Include required files
		add_filter('rewrite_rules_array',array($this,'wp_insertcustom_rules'));

        add_filter('query_vars',array($this,'wp_insertcustom_vars'));

		$this->includes(); 

		// add_action( 'widgets_init', array( $this, 'include_widgets' ) );
   
		add_action('admin_init',array($this,'default_settings'));

		add_action('wp_enqueue_scripts',array($this,'front_enqueue_script'));
		
		// Loaded action
   

        add_action('admin_menu', array($this, 'webkul_uvdesk_admin_menu'));


	}
 
 	function default_settings(){
		
		register_setting('webkul-uvdesk-settings-group','uvdesk_access_token');
		register_setting('webkul-uvdesk-settings-group','uvdesk_company_domain');
		register_setting('webkul-uvdesk-settings-group','uvdesk_client_key');
		register_setting('webkul-uvdesk-settings-group','uvdesk_secret_key');
	
	}

	// Admin settings page

	function webkul_uvdesk_admin_menu() {

		add_options_page('UVDesk Setting','UVDesk Setting','administrator','uvdesk_setting', array($this, 'uvdesk_settings_page'));

	}

	function uvdesk_settings_page() {

		?>

		<style>
			.wrap {
				background-color: #fff;
				padding: 20px;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}

			.wrap label {
				display: block;
				margin: 30px 0px 10px 0px;
				font-weight: bold;
			}
			.wrap input[type="text"] {
				padding: 8px;
				width: 50%;
			}
		</style>

		<div class="wrap">

			<h1>UVDesk Settings</h1>
			
			<form method="post" action="options.php"> 
				
				<?php settings_fields('webkul-uvdesk-settings-group'); ?>

				<div class="inner-wrap">
	                
	                <label for="uvdesk_access_token">Access Token</label>
	           		
	                <input id="uvdesk_access_token" type="text" name="uvdesk_access_token"  value="<?php echo get_option('uvdesk_access_token');?>"/>

	                <p class="description">You need to create access token from <a href="https://www.uvdesk.com">Uvdesk Site</a></p>
	                
	                <br /> 

	                
	                <h1><strong>Company Domain</strong></h1>

	                <input id="uvdesk_company_domain" type="text" name="uvdesk_company_domain"  value="<?php echo get_option('uvdesk_company_domain');?>"/>

	                <p class="description">This field is the domain of your organization.</p>

	                <br />
	                
	                <h1><strong>Setup Recaptcha</strong></h1>
	                
	                <div class="form-group">
		                
		                <label for="uvdesk_client_key" > Client Key OR Site Key</label>	
		                
		                <input type="text" id="uvdesk_client_key" name="uvdesk_client_key" value="<?php echo get_option('uvdesk_client_key');?>" placeholder="eg :-12***********12">
	                	<p class="description">You can create recaptcha keys from <a href="https://www.google.com/recaptcha/intro/index.html">Google RECAPTCHA</a> Site</p>

		                <label for="uvdesk_secret_key" > Secret Key</label>	

		                <input type="text" id= "uvdesk_secret_key" name="uvdesk_secret_key" value="<?php echo get_option('uvdesk_secret_key');?>" placeholder="eg :-12***********34">

	            	</div>

	            </div>

                <?php  

			        submit_button();

			    ?>
                
			</form>

		</div>

		<?php
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

		wp_enqueue_script( 'uvdesk-script', plugin_dir_url(__FILE__). 'assets/js/plugin-uvdesk.js', array( 'jquery' ) );
		
		wp_enqueue_script( 'uvdesk-recaptcha-script','https://www.google.com/recaptcha/api.js'); 

		wp_enqueue_script('bootstrap-script','https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js');
    
		wp_enqueue_style( 'boostrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css');
		
		wp_enqueue_style( 'boostrap-select-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.min.css');

		wp_enqueue_script('boostrap-select-js','https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.js');
		 
		wp_enqueue_style('uvdesk-style', UVDESK_API. 'assets/css/style.css'); 
		
		wp_localize_script( 'uvdesk-script', 'api_script', array( 'api_admin_ajax' => admin_url( 'admin-ajax.php' ), 'api_nonce' => wp_create_nonce('api-ajaxnonce'),'uvdesk_member_url'=>site_url().'/uvdesk/customer','is_admin'=>$current_user));
 
	}
 

	public static function create_new_ticket_with_attachement($post_attahment=array(),$post_image){ 
 			
 			$uv=new UvdeskProtected(); 
			 
			$domain = $uv->get_company_domain(); 

			$url = 'http://'.$domain.'.webkul.com/en/api/tickets.json'; 
			
			$fileType = $post_image['type'][0];
			$fileName = $post_image['name'][0];
			$fileTmpName = $post_image['tmp_name'][0];
			 
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
			// attachement 1
			$data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . $fileName . '"' . $lineEnd;
			$data .= "Content-Type: $fileType" . $lineEnd . $lineEnd;
			$data .= file_get_contents($fileTmpName) . $lineEnd;
			$data .= '--' . $mime_boundary . $lineEnd;
 
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

		$url = 'http://'.$domain.'.webkul.com/en/api/tickets.json';  

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
			 
			$url = 'http://'.$domain.'.webkul.com/en/api/'.$thread_url_param;  
			
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
			  
			$url = 'http://'.$domain.'.webkul.com/en/api/'.$thread_url_param; 
			
			$fileType = $post_image['type'][0];
			$fileName = $post_image['name'][0];
			$fileTmpName = $post_image['tmp_name'][0]; 
			 
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
			// attachement 1
			$data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . $fileName . '"' . $lineEnd;
			$data .= "Content-Type: $fileType" . $lineEnd . $lineEnd;
			$data .= file_get_contents($fileTmpName) . $lineEnd;
			$data .= '--' . $mime_boundary . $lineEnd;

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
		
			$url = 'http://'.$domain.'.webkul.com/en/api/'.$url_param;  
		
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

			$url = 'http://'.$domain.'.webkul.com/en/api/'.$url_param.$str;

			$ch = curl_init($url);
			$headers = array(
			    'Authorization: Bearer '.$uv->get_access_token(),
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

			$url = 'http://'.$domain.'.webkul.com/en/api/'.$tag_url_param;  
			$ch = curl_init($url);
			$headers = array(
			    'Authorization: Bearer '.$uv->get_access_token(),
			);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // note the Delete here
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tag_prm));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

			$url = 'http://'.$domain.'.webkul.com/en/api/'.$ticket_url_param;  
			$ch = curl_init($url);
			$headers = array(
			    'Authorization: Bearer '.$uv->get_access_token(),
			);  
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // note Assignement here
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ticket_prm));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
 
	public static function get_attachment_data_api($attachment_url_param=''){
			$uv=new UvdeskProtected();   
			 
			$domain = $uv->get_company_domain();   

			$url = 'http://'.$domain.'.webkul.com/en/api/'.$attachment_url_param;
			$ch = curl_init($url);
			$headers = array(
			    'Authorization: Bearer '.$uv->get_access_token(),
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
			$tag=esc_attr($_POST['tag']);
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
			$tag_id=esc_attr($_POST['tag-id']);
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

			$hash=esc_attr($_POST['hash']);
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
           

            $data_customerapi=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','direction'=>'asc'));
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

            $data_customerapi=UVDESK_API::get_customer_data_api('tags.json',array('sort'=>'name','direction'=>'asc'));

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
			$tab_id=esc_attr($_POST['tab_id']);
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
				 var_dump($data_group_api);
				 die;
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

		$priority_filter = $_POST['priority_filter'];
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
			$field=esc_attr($_POST['field']); 
		 	$is_admin=intval($_POST['is_admin']);
		 	$current_user=wp_get_current_user();

			$c_email=$current_user->user_email;

			if($field){ 
				$data_customerapi=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>$field,'direction'=>'asc','search'=>$c_email)); 
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

	function update_priority_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
			$field=esc_attr($_POST['field']); 
		 	$is_admin=intval($_POST['is_admin']);
			if($field){ 
				$data_customerapi=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>$field,'direction'=>'asc')); 
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

			$ticket_ids=esc_attr($_POST['ticket_id']); 
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

			$ticket_ids=esc_attr($_POST['ticket_id']); 
			$ticket_ids=explode(",", $ticket_ids);
			$agent=intval($_POST['agent']); 
		 	$json_data=array('ids'=>$ticket_ids,'agentId'=>$agent); 
		  
			if($json_data){ 

				$data_customerapi=UVDESK_API::update_ticket('tickets/agent.json',$json_data); 

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

	function change_ticket_label(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){

			$ticket_ids=esc_attr($_POST['ticket_id']); 
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

			$ticket_ids=esc_attr($_POST['ticket_id']); 
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

				$data_customerapi=UVDESK_API::get_customer_data_api('tickets.json',array('status'=>$field,'direction'=>'asc','search'=>$c_email)); 
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

		$type_filter = $_POST['type_filter'];

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

			$tag_filter = $_POST['tag_filter'];
			$tag_filter= trim($tag_filter); 
			if($tag_filter){
				$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('tags'=>intval($tag_filter)));
				var_dump($data_api);
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
	
	// Delete Ticket 

	function delete_ticket_via_api(){

		if(check_ajax_referer( 'api-ajaxnonce', 'nonce',false)){
			$t_id=$_POST['ticket_id'];
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
	function final_json_data_customer($data_api=''){


		ob_start(); ?>
 
			    <div class="col-sm-12">
			        <div class="table-container clearfix" id="ticket-table">
			            <table class="table">
			                <colgroup>
			                    <col class="id">
			                    <col class="subject">
			                    <col class="details">
			                    <col class="agent-name">
			                </colgroup>
			                <tbody>

			                    <?php  
			                    	if(!empty($data_api->tickets) && isset($data_api->tickets)){
			                    		 
			                    		 foreach ($data_api->tickets as $ticket_key => $ticket_value) { ?>
												
		                    		 		<tr data-toggle="tooltip" data-placement="left" title="" class="Open 1 unread" data-original-title="Open">
						                        <td class="id" style="border-left: 3px solid #337ab7;">
						                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
											    		<?php echo $ticket_value->incrementId; ?>
											    	</a>
						                        </td>
						                        <td class="subject">
						                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>" class="subject">
											    		<?php echo $ticket_value->subject; ?>
											    	</a>
						                            <span class="fade-subject"></span>
						                        </td>
						                        <td class="details">
						                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
						                                <span class="date"><?php echo $ticket_value->formatedCreatedAt; ?></span>
						                                <span class="badge badge-lg">
															<?php echo $ticket_value->customer->name; ?>
														</span>
						                                <span class="badge badge-lg"><?php echo $ticket_value->totalThreads; ?></span>
						                            </a>
						                        </td>
						                        <td class="agent-name">
						                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
						        		
										        		<?php echo $ticket_value->agent->name; ?>
											        	
										        	</a>
						                        </td>
						                      
						                    </tr>

			                    <?php   }
			                    	
			                    	}

			                    ?>
			                    
			                </tbody>
			            </table>
			        </div>
			    </div>
			    <div class="col-sm-12">
			        <div class="navigation">

    				<?php	
    				$tot_post=$data_api->pagination->totalCount;
    				$per_page=$data_api->pagination->numItemsPerPage; 
    				$last_page=$data_api->pagination->pageCount;
    				
    				function uv_pagination($tot_post,$per_page,$last_page,$paged) { 

					        $prev_arrow = 'Next&nbsp;&raquo;';

					        $next_arrow = '&laquo;&nbsp;Previous';

					        global $wp_query;

					        if($tot_post>0)

					        {

					            $total=$tot_post/$per_page;
					        }

					        else

					        {

					            $total = $last_page;

					        }          
					        $big = 9999999999999; // need an unlikely integer

					        if( $total > 1 )  {

					             if( !$current_page = $paged )

					                 $current_page = 1;

					             if( get_option('permalink_structure') ) {

					                 $format = 'page/%#%/';

					             } else {

					                 $format = '&paged=%#%';

					             }
					            echo paginate_links(array(

					                'base'          => str_replace( $big, '%#%', esc_url( site_url( '/uvdesk/customer/page/'.$big ) ) ),

					                'format'        => $format,

					                'current'       => max( 1, $paged ),

					                'total'         => ceil($total),

					                'mid_size'      => 3,

					                'type'          => 'list',

					                'prev_text'     => $next_arrow,

					                'next_text'     => $prev_arrow,

					             ) );

					        }

					    }
					    echo "<nav class='uv-pagination'>";
					    	uv_pagination($tot_post,$per_page,$last_page,$paged);
					    echo "</div>";
					    ?>
					
					</div>

			    </div>

	<?php
			return ob_get_clean();
	}


	function final_json_data($data_api='',$tab=''){

		ob_start(); 

		?> 
			<div class="col-sm-12">
					 
					<?php 

						foreach ($data_api->tabs as $key => $value) {
							
							$tabs_arr[]=json_decode(json_encode($value),TRUE);
						}  
					?>
					<ul class="ticket-tabs-list">
						 
						<div>
					    	<li class="open <?php if($tab==1){echo 'active"'; echo 'style="border-top:3px solid #337ab7';} elseif(empty($tab)){ echo 'active"'; echo 'style="border-top:3px solid #337ab7';} ?>" data-id="1" >
					    		<i class="fa fa-inbox"></i>
								<span class="name">Open</span>
								<span class="badge">
									
									<?php echo $tabs_arr[0];?>
									
								</span>
							</li>
					    	<li class="open <?php if($tab==2){echo 'active"'; echo 'style="border-top:3px solid #d9534f';} ?>" data-id="2">
					    		<i class="fa fa-exclamation-triangle"></i>
								<span class="name">Pending</span>
								<span class="badge">
									
									<?php echo $tabs_arr[1];?>
									
								</span>
							</li>
					    	<li class="open <?php if($tab==6){echo 'active"'; echo 'style="border-top:3px solid #F1BB52';} ?>" data-id="6">
					    		<i class="fa fa-lightbulb-o"></i>
								<span class="name">Answered</span>
								<span class="badge">
									
									<?php echo $tabs_arr[5];?>
									
								</span>
							</li>
					    	<li class="open <?php if($tab==3){echo 'active"'; echo 'style="border-top:3px solid #5cb85c';} ?>" data-id="3">
					    		<i class="fa fa-check-circle"></i>
								<span class="name">Resolved</span>
								<span class="badge">
									
									<?php echo $tabs_arr[2];?> 

								</span>
							</li>
					    	<li class="open <?php if($tab==4){echo 'active"'; echo 'style="border-top:3px solid #767676';} ?>" data-id="4">
					    		<i class="fa fa-minus-circle"></i>
								<span class="name">Closed</span>
								<span class="badge">
									
									<?php echo $tabs_arr[3];?>
									
								</span>
							</li>
					    	<li class="open <?php if($tab==5){echo 'active"'; echo 'style="border-top:3px solid #00A1F2';} ?> " data-id="5">
					    		<i class="fa fa-ban"></i>
								<span class="name">Spam</span>
								<span class="badge">
									
									<?php echo $tabs_arr[4];?>
									
								</span>
							</li>
					    </div>
						 
					</ul>
					<div class="panel panel-default table-container" id="ticket-table">
						<table class="table">
							<colgroup>
								<col class="quick-link">
								<col class="id">
								<col class="customer-name">
								<col class="subject">
								<col class="details">
								<col class="agent-name">
							</colgroup>
		                	<tbody>
		                	<?php
		                		foreach ($data_api->tickets as $data_key => $data_value) : 
		                			if(!empty($data_value->group) && isset($data_value->group)){
		                				$group_name='<span class="badge badge-lg group">'.$data_value->group.'</span>';
		                			}else{
		                				$group_name='';
		                			}
		                			if(!empty($data_value->agent->name) && isset($data_value->agent->name)){

		                				$agent_data = '<i class="fa fa-user" aria-hidden=true></i><a class=semibold title='.$data_value->agent->name.'>'.$data_value->agent->name.'</a>';
		                			}
		                			
		                			else{
		                				$agent_data = '<button class="btn btn-md btn-info edit-ticket-agent"><i class="fa fa-plus-circle"></i>Agent</button>
		                				<select class="selectpicker agents" data-live-search="true" title="Assign to" tabindex="-98"><option class="bs-title-option" value="">Assign to</option>
					    				</select></button>';
					    			}
		                		?>

			                		<tr data-toggle=tooltip data-placement=left title class="Low 1" data-original-title=Low>
										    <td class=quick-link style="border-left: 3px solid #5cb85c;">
										        <div class=icheckbox_square-blue>
										            <input type=checkbox value="<?php echo $data_value->id; ?>" class=mass-action-checkbox style="position: absolute; top: -10%; left: -10%; display: block; width: 120%; height: 120%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);">
										            <ins class=iCheck-helper></ins>
										        </div><i class="fa fa-television source" aria-hidden=true></i><a href class=mark-star><i class="fa fa-star"></i></a><a class="bold quick-view" href data-id="<?php echo $data_value->id; ?>"><span class="badge badge-lg badge-primary"><i class="fa fa-bolt"></i></span></a></td>
										    <td class=id><a href="<?php echo site_url().'uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>">#<?php echo $data_value->incrementId; ?></a></td>
										    <td class=customer-name><a href="<?php echo site_url().'uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>" title="<?php echo $data_value->customer->name?>"><?php echo $data_value->customer->name; ?></a></td>
										    <td class=subject><a href="<?php echo site_url().'uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>"><?php echo $data_value->subject; ?></a><span class=fade-subject></span></td>
										    <td class=details><a href="<?php echo site_url().'uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>"><span class=date><?php echo $data_value->formatedCreatedAt;?></span><span class="badge badge-lg">1</span><?php echo $group_name; ?></a></td>
										    <td class=agent-name><?php echo $agent_data; ?></td>
										    <td class=responsive-data>
										        <ul class=list-block>
										            <li class=subject><a href="<?php echo site_url().'uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>" class=bold><?php echo $data_value->subject; ?></a> </li>
										            <li class=customer-info> <a class="bold ellipsis-name"><?php echo $data_value->customer->name;?></a> </li>
										            <li class="agent-info pull-left"> <span class="ticket-d pull-left">#<?php echo $data_value->incrementId; ?></span> <span class=agent> <i class="fa fa-user" aria-hidden=true></i> <span class=assign-text> Assigned To - &nbsp; </span> <a class=semibold> <?php echo $data_value->agent->name;?></a> </span>
										            </li>
										            <li class=info> <span class="badge badge-lg badge-default"><?php echo $data_value->group;?></span> <span class="badge badge-lg badge-default"><?php echo $data_value->formatedCreatedAt; ?></span> <span class="badge badge-lg badge-default"><?php echo $data_value->totalThreads;?> Replies</span> </li>
										            <li class=priority> <span class="label priority-label" style="background-color: #5cb85c; border: 1px solid #5cb85c"><?php echo $data_value->priority->name;?></span> </li>
										        </ul>
										    </td>
									</tr>
								
								<?php endforeach; ?>
		                		
		                		<tr style="text-align: center;" id="ticket-loader"><td colspan="6">No Record Found</td></tr>

		                	</tbody>
		              	</table>
		            </div>
				</div>
				<div class="col-sm-12">
					<div class="navigation">

    				<?php	
    				$tot_post=$data_api->pagination->totalCount;
    				$per_page=$data_api->pagination->numItemsPerPage; 
    				$last_page=$data_api->pagination->pageCount;
    				function mp_pagination($tot_post,$per_page,$last_page) { 
					        $prev_arrow = 'Next&nbsp;&raquo;';

					        $next_arrow = '&laquo;&nbsp;Previous';

					        global $wp_query;

					        if($tot_post>0)

					        {

					            $total=$tot_post/$per_page;
					        }

					        else

					        {

					            $total = $last_page;

					        }          
					        $big = 9999999999999; // need an unlikely integer

					        if( $total > 1 )  {

					             if( !$current_page = get_query_var('paged') )

					                 $current_page = 1;

					             if( get_option('permalink_structure') ) {

					                 $format = 'page/%#%/';

					             } else {

					                 $format = '&paged=%#%';

					             }

					            echo paginate_links(array(

					                'base'          => str_replace( $big, '%#%', esc_url( site_url( '/uvdesk/admin/page/'.$big ) ) ),

					                'format'        => $format,

					                'current'       => max( 1, get_query_var('paged') ),

					                'total'         => ceil($total),

					                'mid_size'      => 3,

					                'type'          => 'list',

					                'prev_text'     => $next_arrow,

					                'next_text'     => $prev_arrow,

					             ) );

					        }

					    }
					    echo "<nav class='uv-pagination'>";
					    	mp_pagination($tot_post,$per_page,$last_page);
					    echo "</div>";
					    ?>
					</div>
				</div> 

	<?php	 

		return ob_get_clean();

	}


	function add_seller_metabox() {

	 add_meta_box("seller-meta-box","Seller","seller_metabox","product","side","low",NULL);

}



function seller_metabox() {
	 
	 wp_nonce_field( 'blog_save_meta_box_data', 'blog_meta_box_nonce' );

	 global $wpdb;
      
        $sql =  "SELECT user_id from {$wpdb->prefix}mpsellerinfo where seller_value = 'seller'";
      
        $result = $wpdb->get_results($sql);


    ?>
    <div class="return-seller">
        <!--<input type="text" name="seller-name" id="search_seller_name" placeholder="Search Seller">--> 
        	   <div class="btn-group bootstrap-select">
		            <button type="button" class="btn dropdown-toggle btn-default caret" data-toggle="dropdown" role="button" aria-expanded="true">
		            		<span class="filter-option pull-left">Select Seller</span>&nbsp;<span class="bs-caret">
		            		<span class="caret"></span>
		            		</span>
		            </button>
		            <div class="dropdown-menu open" role="combobox">
		            	<div class="bs-searchbox">
		            		<input type="text" class="form-control" autocomplete="off" role="textbox" aria-label="Search" id="check-seller">
		            	</div>
		            	<ul class="dropdown-menu inner" role="listbox" aria-expanded="true">
		            		
		            		<li data-original-index="0" class="selected active">
		            	
		            				<?php foreach ($result as $ke) {
           								?>   
           								<a tabindex="0" data-seller-id="<?php echo $ke->user_id; ?>" role="option" aria-disabled="false" aria-selected="true">        								
            							<span class="text" ><?php echo get_user_meta($ke->user_id, 'first_name',true); ?></span>
            							</a>
            							<?php
        							}?>		            			
		            			
		            		</li>
		            		<li class="search-selected"></li>
		            	
		            	</ul>
		            </div>
		        <div class="checkbox-seller">
		       		<input type="hidden" name="seller_id">
		       		<input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
		       	</div>			    
  			</div>
    </div>
    <?php
} 

function save_version_meta( $post_id, $post, $update)
{
	global $wpdb;
  	if ( isset( $_POST['blog_meta_box_nonce'] ) ) {
  	// Verify that the nonce is valid.
  	if ( wp_verify_nonce( $_POST['blog_meta_box_nonce'], 'blog_save_meta_box_data' ) ) {
      
       	if(!empty($_REQUEST['seller_id']) && !empty($_REQUEST['post_id'])){
 
       		$table_name = "{$wpdb->prefix}posts";

       		$res = $wpdb->update($table_name,array('post_author'=> $_REQUEST['seller_id']), array('ID' => $_REQUEST['post_id']), array('%d'), array('%d'));    		

       	}
      
      }
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

add_action( 'wp_ajax_nopriv_get_data_via_customer',array($uv_obj,'get_data_via_customer' ));
add_action( 'wp_ajax_get_data_via_customer',array($uv_obj,'get_data_via_customer'));

add_action( 'wp_ajax_nopriv_get_data_via_group',array($uv_obj,'get_data_via_group' ));
add_action( 'wp_ajax_get_data_via_group',array($uv_obj,'get_data_via_group'));

add_action( 'wp_ajax_nopriv_get_group_data',array($uv_obj,'get_group_data' ));
add_action( 'wp_ajax_get_group_data',array($uv_obj,'get_group_data'));

add_action( 'wp_ajax_nopriv_get_default_data',array($uv_obj,'get_default_data' ));
add_action( 'wp_ajax_get_default_data',array($uv_obj,'get_default_data'));

endif;