<?php
class ModuleExample extends Module {

    private $box = true;
	private $http = false;
	
	public function sectionDefault($params = array()) {
		return "Sie befinden sich auf der Startseite des Example-Moduls";
	}
	
	public function sectionDetails($params = array()) {
		return "Hier könnte man Details finden";
	}

	public function boxInfobox() {
	    return "Dies wäre ein Box Content";
	}
	
}
?>
