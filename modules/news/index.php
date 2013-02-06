<?php
class Module_News extends Module{
	private $box = false;
	private $http = true;
	public function __construct(){
        $this->templateEngine = Registry::getInstance()->get('template_modul');
    }
	public function section_default($params = array()){
	    Render::addStylesheet('css/myfile.css');
        $url = URl::getInstance();
        $parameters = array('module'=>'news','section'=>'details');
        $href = $url->generateURL($parameters);
        $values = array('link'=>$href);
        return $this->templateEngine->returnTemplate('news','default','block',$values);
	}
	
	public function section_details($params = array()){
		 return $this->templateEngine->returnTemplate('news','default','details');
	}
	
}
