<?php
class Module_News extends Module{
	private $box = false;
	private $http = true;
	
	public function section_default($params = array()){
	    Render::addStylesheet('css/myfile.css');
		return "Sie befinden sich auf der Startseite des CMS";
	}
	
	public function section_details($params = array()){
		return "Hier könnte man Details finden";
	}
	
}