<?php 

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

/**
 * Handle frontend forms
 *
 * @class 		UVDESK_API_Form_Handler
 * @version		1.0.0
 * @package		uvdesk_app/include/
 * @category	Class
 * @author 		webkul
 */  
class UVDDDESK_API_Form_Handler {
	/**
	 * Constructor
	 */	 

	
	 public function __construct() 
	 {

	   	  	
 			add_action( 'wp_enqueue_scripts', array( $this, 'uvdesk_calling_pages' ) ); 			
	
			add_action( 'wp_logout', array($this,'wedge_redirect_after_logout') );		  

			add_action( 'template_redirect', array($this,'mw_template_redirect') );

			add_action( 'wp_login_failed', array($this,'uvdesk_front_end_login_fail') );   
	 }
  
 	function mw_template_redirect () {
 		$main_page=get_query_var('main_page');	
		
		if ( is_page( 'uvdesk' ) && $main_page == 'login' && is_user_logged_in() ) {     		
    		 if(get_current_user_id()==1){

    			wp_redirect( home_url( '/uvdesk/admin' ) );
    		 	
    		 }
    		 else{
    			
    			wp_redirect( home_url( '/uvdesk/customer' ) );

    		 }
    		
    		exit();
  		
  		}

  		if ( is_page( 'uvdesk' ) && $main_page == 'customer' &&  is_user_logged_in() ) {
  			if(get_current_user_id()==1){
    			wp_redirect( home_url( '/uvdesk/admin' ) );
    		 	
    		 }
  		}
  		else if ( is_page( 'uvdesk' ) && $main_page == 'admin' &&  is_user_logged_in() ) {
  			if(get_current_user_id() !=1){
    			
    			wp_redirect( home_url( '/uvdesk/customer' ) );
    		 	
    		 }
  		}

  		if ( is_page( 'uvdesk' ) && $main_page == 'customer' &&  !is_user_logged_in() ) {

    		wp_redirect( home_url( '/uvdesk/login' ) );
    		
    		exit();
  		
  		}
	
	}

	function uvdesk_front_end_login_fail( $username ) {
   		
   		$referrer = $_SERVER['HTTP_REFERER'];  

   		$referrer = explode('?', $referrer);
   		
   		if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
      
      		wp_redirect( $referrer[0] . '?login=failed' );  
      
      		exit;
   		
   		}
	
	}

	function wedge_redirect_after_logout() {
        
        if (!current_user_can('administrator')) { 
        
        	$url = 'uvdesk/login?loggedout=true';
        
        } else { 
        
        	$url = 'wp-login.php?loggedout=true'; 

        }
        
        $redirect_url = home_url( $url );
        
        wp_safe_redirect( $redirect_url );
        
        exit;
    
    }
	 public function uvdesk_calling_pages()
	{	 

		global $current_user,$wpdb; 
		
		$current_user=wp_get_current_user();  
	 	
	 	$pagename=sanitize_text_field(get_query_var('pagename'));
	 	$main_page=get_query_var('main_page');	
	 	$action=get_query_var('action');	
	 	$tid=get_query_var('tid');	
	 	$ticket_type=get_query_var('type');	 
	 	$pagination=get_query_var('pagination');	 
	 	$paged=get_query_var('paged');	
	 	$create_ticket=get_query_var('create');		  	 
	 	$aid=get_query_var('aid');
			if(!empty($pagename)){
				
				if(($main_page=="customer" && $action=="view" && $current_user->ID && $ticket_type="ticket" && !empty($tid)) || ($main_page=="customer" && $action=="view" && $current_user->ID && !empty($tid) & $pagination='page' && !empty($paged)))
				{ 
					require 'front/customer-ticket-view.php';
					add_shortcode('uvdesk','wk_customer_ticket_view');
				}
				else if( $main_page=="customer" && ($current_user->ID) && $pagination='page' && !empty($paged))
				{  
					require 'front/customer.php';
					add_shortcode('uvdesk','wk_customer_dashboard');
				}
				elseif($main_page=="customer" && $create_ticket=="create-ticket" && $current_user->ID ){
					require 'front/create-ticket.php';
					add_shortcode('uvdesk','wk_customer_create_ticket');
				}
				elseif( $main_page=="customer" && ($current_user->ID))
				{  
					require 'front/customer.php';
					add_shortcode('uvdesk','wk_customer_dashboard');
				}
				elseif($main_page=="admin" && $action=="view" && $current_user->ID && $ticket_type="ticket" && !empty($tid) )
				{  
					
					require 'front/admin-ticket-view.php';
					add_shortcode('uvdesk','wk_admin_ticket_view');
				}
				elseif($main_page=="download" && !empty($aid))
				{  
					require 'front/download.php'; 

				}
				elseif( $main_page=="admin" && ($current_user->ID))
				{ 
					// var_dump($main_page);
					require 'front/admin.php';
					add_shortcode('uvdesk','wk_admin_dashboard');
				}
				else if($main_page=='login'){ 

					add_shortcode('uvdesk','wk_customer_login');

				}
				else if($main_page=='register'){ 

					add_shortcode('uvdesk','display_uvdesk_form');

				}
				else{
					// call registration form from page
					add_shortcode( 'uvdesk', 'wk_customer_login' );
				}
			}
			else{
				// call registration form from page
				add_shortcode( 'uvdesk', 'wk_customer_login' );
			}

	}
}

new UVDDDESK_API_Form_Handler();

?>