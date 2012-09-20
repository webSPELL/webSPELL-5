<?php
class Render {
    static $scripts = array();
    static $styles = array();
    static $outputs = array();
    
    private $theme_dir = 'themes';
    private $registry;
    
    static function addStylesheet($path){
        self::$styles[] = $path;
    }
    
    static function addScript($path, $defer){
        self::$scripts[] = array($path, $defer);
    }
    
    static function addOutput($key, $output, $append=false){
        if(array_key_exists($key, self::$outputs)){
            if($append == false){
                throw new WebspellException('Output key exists');
            }
            else{
                self::$outputs[$key] .= $output;
            }
        }
        else{
    	    self::$outputs[$key] = $output;
        }
    }
    
    public function __construct(){
        $this->registry = Registry::getInstance();
        $this->theme = $this->registry->get('theme');
        $this->themepath  = WEBSPELL_ROOT.DIRECTORY_SEPARATOR;
        $this->themepath .= $this->theme_dir.DIRECTORY_SEPARATOR;
        $this->themepath .= $this->theme;
        if(is_dir($this->themepath) == false){
            throw new WebspellException('Unknown_theme');
        }
    }
    
    public function display(){
        $theme = self::$outputs;
        include($this->themepath.DIRECTORY_SEPARATOR.'theme.php');
    }
    
    public function execute(){
        $url = $this->registry->get('url');
        $contentmodul = $url->getModule();
        $modulpath = WEBSPELL_ROOT.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$contentmodul;
        if(is_dir($modulpath)){
            $modulname = "Module_".ucfirst($contentmodul);
            $modul =  new $modulname();
            $section = 'section_'.$url->getSection();
            self::addOutput('content', $modul->$section());
            
        }  
        else{
            echo $modulpath;
            throw new WebspellException('Unknown_modul');
        }    
    }
    
}