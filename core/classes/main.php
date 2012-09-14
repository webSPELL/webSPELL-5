<?php
class Main {
	public function __construct(){
		
		// Load everthing
		$registry = Registry::getInstance();
		$registry->set('debugLevel',3);
		$registry->set('pathToLogFile','./log.txt');
		$registry->set('modRewrite',true);
		$registry->set('session',new Session());
		$registry->set('url', new Url());
		
		
		// Render design
		
		//$this->render();
		
	}
}// Load everthing