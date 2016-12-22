<?php 
ob_start();
if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

} 
	$access_token=get_option('uvdesk_access_token');
	$company_domain=get_option('uvdesk_company_domain');
	$aid=get_query_var('aid');

	if(!empty(intval($aid)) && isset($aid)){
		
		$url = 'http://'.$company_domain.'.webkul.com/en/api/ticket/attachment/'.$aid.'.json?access_token='.$access_token;

	    $ch = curl_init(); 
	    curl_setopt($ch, CURLOPT_URL,$url);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); 
	    $result = curl_exec($ch); 
	    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	    $type=explode("/", $type);
    	$filename = $aid.".".$type[1];
	    if (curl_errno($ch)) { 
	        return null; 
	    } else { 
	        curl_close($ch); 
	        if ($result != ""){ 
	            file_put_contents($filename,$result); 
	    		header('Content-Type: '.$type);
				header("Content-Disposition: attachment; filename=".$filename);
				readfile($filename); 
	            unlink($filename); 
	      
	        }else{ 
	            // return null; 
	        } 
	    } 
	} 
 


?> 