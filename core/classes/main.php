<?php
class Main {
	public function __construct(){
		
		// Load everything
		$registry = Registry::getInstance();
		$registry->set('pathToLogFile','./log.txt');
		$registry->set('theme','default');
		$registry->set('modRewrite',true);
		$registry->set('session',new Session());
		$registry->set('url', new Url());
		$registry->get('url')->parseQueryString();
        $registry->set('template_modul',new Template('mod'));
        $registry->set('db', new Database());
        
        // load config file
        include('config.php');

        // initialize Spot
        $cfg = new \Spot\Config();
        $cfg->addConnection("default_connection", $database['type']."://".$database['user'].":".$database['password']."@".$database['host']."/".$database['dbname']);
        $registry->set('db', new \Spot\Mapper($cfg));
        unset($database);
        
		$render = new Render();
		
		$render->loadElements();
		
		$render->display();
		
		
		// Render design
		
		//$this->render();
		
	}
}// Load everthing
