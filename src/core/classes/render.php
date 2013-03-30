<?php
class Render {
    static $scripts = array();
    static $styles = array();
    static $outputs = array();
    static $toRender = array();
    
    private $theme_dir = 'themes';
    private $registry;
    
    static function addStylesheet($path){
        self::$styles[] = $path;
    }
    
    static function addScript($path, $defer){
        self::$scripts[] = array($path, $defer);
    }
    
    static function addToRender($key,$callback,$params = array()){
        self::$toRender[$key] = array($callback, $params);
    }
    
    static function addOutput($key, $output, $append=false){
        if(array_key_exists($key, self::$outputs)){
            if($append == false){
                throw new WebspellException('Output key exists');
            }
            else{
                if(is_array(self::$outputs[$key])){
                    self::$outputs[$key] = array_merge(self::$outputs[$key],$output);
                }
                else{
                    self::$outputs[$key] .= $output;
                }
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
        self::$outputs['core'] = array();
        
        foreach(self::$toRender as $key => $array){
        	self::addOutput($key, call_user_func($array[0],$array[1]));
        }
        
        self::$outputs['core']['pagetitle'] = "SEO Page Title"; //@Todo 
        self::$outputs['core']['scripts'] = '';
        array_unique(self::$scripts);
        foreach(self::$scripts as $script){
            self::$outputs['core']['scripts'] .= '<script src="'.$script[0].'" type="text/javascript"></script>';
        }
        
        self::$outputs['core']['styles'] = '';
        array_unique(self::$styles);
        foreach(self::$styles as $style){
        	self::$outputs['core']['styles'] .= '<link rel="stylesheet" type="text/css" href="'.$style.'" />';
        }
        
        $theme = self::$outputs;
        include($this->themepath.DIRECTORY_SEPARATOR.'theme.php');
    }
    
    public function loadElements(){
        $url = $this->registry->get('url');
        $contentmodule = $url->getModule();
        $modulepath = WEBSPELL_ROOT.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$contentmodule;
        if(is_dir($modulepath)){
            $modulename = "Module_".ucfirst($contentmodule);
            $module =  new $modulename();
            $section = 'section_'.$url->getSection();
            $this->addToRender('content', array($module,$section));           
        }  
        else{
            throw new WebspellException('Unknown_module');
        }    
        
        $options = parse_ini_file($this->themepath.DIRECTORY_SEPARATOR.'options.ini',true);
        if(isset($options['boxes'])){
            foreach($options['boxes'] as $key => $function){
                if(strpos($function,"Module_")!==false){
                    $parts = explode("_",$function,3);
                    $modulename = "Module_".$parts[1];
                    $function = $parts[2];
                    $this->addToRender($key, array(new $modulename(),$function));
                }
            }
        }
    }
    
}