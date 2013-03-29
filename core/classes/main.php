<?php
class Main {
	public function __construct(){
		
		// Load everything
		$registry = Registry::getInstance();
		$registry->set('debugLevel',3);
		$registry->set('pathToLogFile','./log.txt');
		$registry->set('theme','default');
		$registry->set('modRewrite',true);
		$registry->set('session',new Session());
		$registry->set('url', new Url());
		$registry->get('url')->parseQueryString();
        $registry->set('template_modul',new Template('mod'));
        $registry->set('db', new Database());
        
        // initialize Spot
        $cfg = new \Spot\Config();
        $cfg->addConnection("mysql", "mysql://root:1234@localhost/ws_v5");
        $registry->set('db', new \Spot\Mapper($cfg));
        
		$render = new Render();
		
		$render->loadElements();
		
		
		
		$render->display();
		
		
		// Render design
		
		//$this->render();
		
	}
}// Load everthing
