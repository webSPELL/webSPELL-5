<?php
class Main {
	public function __construct(){
		// Load everthing
		$registry = Registry::getInstance();
		$registry->set('session',new Session());
		$registry->set('url', new URL());
		
		
		// Render design
		
		$this->render();
		
	}
}