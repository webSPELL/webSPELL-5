<?php
class Module_Example extends Module{
	private $box = true;
	private $http = false;
	
	public function section_default($params = array()){
		return "Sie befinden sich auf der Startseite des Example-Moduls";
	}
	
	public function section_details($params = array()){
		return "Hier könnte man Details finden";
	}

	public function box_infobox(){
	    return "Dies wäre ein Box Content";
	}
	
}