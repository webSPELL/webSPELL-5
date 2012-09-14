<?php
class Module_Example extends Module{
	private $box = false;
	private $http = true;
	
	public function section_default($params = array()){
		return "Sie befinden sich auf der Startseite des Example-Moduls";
	}
	
	public function section_details($params = array()){
		return "Hier könnte man Details finden";
	}
	
}