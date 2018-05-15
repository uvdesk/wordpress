<?php 

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}


/**
* 
*/
class UvdeskProtected
{
	
	private $access_token;
	private $client_key;
	private $secret_key;
	function __construct()
	{
		// setup default access token secret key and client key

		$this->set_access_token();
		$this->set_secret_key();
		$this->set_client_key();
		$this->set_company_domain();
	}

	protected function get_access_token(){
		return $this->access_token;
	}
	public function get_secret_key(){
		return $this->secret_key;
	}
	public function get_client_key(){
		return $this->client_key;
	} 
	protected function get_company_domain(){
		return $this->company_domain;
	} 
	private function set_access_token(){
		$uvdesk_access_token=get_option('uvdesk_access_token');
		if(!empty($uvdesk_access_token))
			$this->access_token=$uvdesk_access_token;
		else
			$this->access_token='';
	}

	private function set_client_key(){
		$uvdesk_client_key=get_option('uvdesk_client_key');
		if(!empty($uvdesk_client_key))
			$this->client_key=$uvdesk_client_key;
		else
			$this->client_key='';
	}

	private function set_company_domain(){
		$uvdesk_company_domain=get_option('uvdesk_company_domain');
		if(!empty($uvdesk_company_domain))
			$this->company_domain=$uvdesk_company_domain;
		else
			$this->company_domain='';
	}

	private function set_secret_key(){
		$uvdesk_secret_key=get_option('uvdesk_secret_key');
		if(!empty($uvdesk_secret_key))
			$this->secret_key=$uvdesk_secret_key;
		else
			$this->secret_key='';
	}
	 
}