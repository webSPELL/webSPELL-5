<?php
class url {
	
	private $options = array('mod_rewrite'=>true,'querystring_seperator'=>'?','default_querystring'=>'/news.html');
	public function __construct(){
		
	}
	
	public function parseQueryString(){
		if(empty($_SERVER['QUERY_STRING']) == false){
			$queryString = $_SERVER['QUERY_STRING'];
		}
		else{
			$queryString = $this->options['default_querystring'];
		}
		
		$data = explode("/",$queryString);
		if(count($data)>1){
			$modul = $data[0];
			
		}
		else{
			$modul = substr($data[0],0,strpos($data[0], "."));
		}
		
	}
	
}