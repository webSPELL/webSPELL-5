<?php
class ModuleNews extends Module {

	private $box = false;
	private $http = true;

	public function __construct() {
        $this->templateEngine = new Template();
        $this->templateEngine->setType(Template::Modul);
    }

	public function sectionDefault($params = array()) {
	    Render::addStylesheet('css/myfile.css');
        $url = URl::getInstance();
        $parameters = array('module'=>'News', 'section'=>'details');
        $href = $url->generateURL($parameters);
        $values = array('link'=>$href);
        return $this->templateEngine->fillTemplate('default','News', 'main', $values);
	}
	
	public function sectionDetails($params = array()) {
		 return $this->templateEngine->fillTemplate('default','News', 'details');
	}

}
?>
