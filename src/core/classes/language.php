<?php
/*
 #######################################################
#                                                     #
#    Version 5       /                        /   /   #
#   -----------__---/__---__------__----__---/---/-   #
#    | /| /  /___) /   ) (_ `   /   ) /___) /   /     #
#   _|/_|/__(___ _(___/_(__)___/___/_(___ _/___/___   #
        #                Free Content / Management System     #
        #                            /                        #
        #                                                     #
        #   Copyright 2005-2010 by webSPELL e.V.              #
        #                                                     #
        #   visit www.webspell.org to get webSPELL for free   #
        #                                                     #
        #   - License: GNU General Public License version 3   #
        #   - http://www.gnu.org/copyleft/gpl.html            #
        #   - It is NOT allowed to remove this copyright-tag  #
        #                                                     #
        #######################################################
        */

class Language {

    /**
     * Holds the general language type (either 'mod' or 'core')
     * @var string
     */
    private $languagetype = false;

    /**
     * Holds the user selected language
     * @var string
     */
    private $language = false;

    /**
     * Holds the page default languaeg
     * @var string
     */
    private $default_language = false;

    /**
     * Holds the fallback language for the case that neither the user selected nor the default language exists
     * @var String
     */
    private $fallback_language = 'uk';

    /**
     * Holds the language base path depending on the template type
     * @var string
     */
    private $language_basepath = false;

    /**
     * Holds the relative path to the languages folders depending on the language type
     * @var string
     */
    private $language_path = false;

    /**
     * Language cache
     * @var array
     */
    private $translations = array();

    /**
     * Holds the last loaded translations array key for further translations without the need to specify which language array to use
     * @var string
     */
    private $active_translation = false;

    /**
     * Class constructor method
     * @return
     * @param string $lang User selected language
     * @param string $default_lang Page default language
     * @param string $type[optional] Language type ('mod' or 'core')
     */
    function __construct($lang, $default_lang, $type = 'mod') {
        $this->language = $lang;
        $this->default_language = $default_lang;
        $this->languagetype = $type;
        $this->init();
    }

    /**
     * Sets the language base path depending on the selected language type on class construction
     * @return
     */
    private function init() {
        if($this->languagetype == 'mod') {
            $this->language_basepath = './modules/';
        }
        elseif($this->languagetype == 'core') {
            $this->language_basepath = './core/';
        }
        elseif($this->languagetype == 'admin') {
            $this->template_basepath = './';
        }
        else {
            throw new WebspellException("Language Error: Invalid language type specified.", 1);
        }
    }

    /**
     * Wrapper for loading a language file into the translations cache
     * @return
     * @param string $modulename Name of the module which language file should be loaded
     */
    public function loadTranslation($modulename) {
        $this->loader($modulename);
    }

    /**
     * Language file loader
     * @return
     * @param string $modulename
     */
    private function loader($modulename) {

        $invalide = array('\\', '/', '/\/', ':', '.');
        $modulename = str_replace($invalide, '', $modulename);

        if($this->languagetype == 'mod') {
            $this->language_path = $this->language_basepath.$modulename.'/languages/';
        }
        elseif($this->languagetype == 'core') {
            $this->language_path = $this->language_basepath.'languages/';
        }
        elseif($this->languagetype == 'admin') {
            $this->language_path = $this->language_basepath.'languages/';
        }
        else {
            throw new WebspellException("Language Error: Invalid language type specified.", 2);
        }
        	
        if(file_exists($this->language_path.$this->language.'/'.$modulename.'.php')) {
            $languagefile = $this->language_path.$this->language.'/'.$modulename.'.php';
        }
        elseif(file_exists($this->language_path.$this->default_language.'/'.$modulename.'.php')) {
            $languagefile = $this->language_path.$this->default_language.'/'.$modulename.'.php';
        }
        elseif(file_exists($this->language_path.$this->fallback_language.'/'.$modulename.'.php')) {
            $languagefile = $this->language_path.$this->fallback_language.'/'.$modulename.'.php';
        }
        else {
            throw new WebspellException("Language Error: Language file neither available in selected, nor in default nor in english language.", 3);
        }
        	
        require($languagefile);
        	
        $this->translations[$modulename] = $language_array;
        $this->active_translation = $modulename;
    }

    /**
     * Wrapper for translating a single language variable
     * @return string
     * @param string  $languagekey Language variable to translate
     * @param string $modulename[optional] Optional parameter for specifying which module's language file should be loaded. If supplied language cache will be checked for availability and language file will be autoloaded if needed. If ommitted active_translation will be used if available.
     */
    public function getTranslation($languagekey, $modulename = false) {
        return $this->translate($languagekey, $modulename);
    }

    /**
     * Single language variable translator
     * @return string boolean
     * @param string $languagekey
     * @param string $modulename[optional]
     */
    private function translate($languagekey, $modulename = false) {
        if(!$modulename) {
            if($this->active_translation) {
                $modulename = $this->active_translation;
            }
            else {
                throw new WebspellException("Language Error: No modulname specified in method call and no previous selected active modulname.", 4);
            }
        }
        $invalide = array('\\', '/', '/\/', ':', '.');
        $modulename = str_replace($invalide, '', $modulename);
        if(!isset($this->translations[$modulename])) {
             $this->loadTranslation($modulename);
        }
        if(isset($this->translations[$modulename][$languagekey])) {
            return $this->translations[$modulename][$languagekey];
        }
        else {
            return false;
        }
    }

    /**
     * Wrapper for translating all template language variables in a given string
     * @return string boolean
     * @param string $data Text containing template language variables to translate
     * @param string $modulename[optional] Optional parameter for specifying which module's language file should be loaded. If supplied language cache will be checked for availability and language file will be autoloaded if needed. If ommitted active_translation will be used if available.
     */
    public function translateData($data, $modulename = false) {
        return $this->translateAll($data, $modulename);
    }

    /**
     * Template variables translator
     * @return string
     * @param string $data
     * @param string $modulename[optional]
     */
    private function translateAll($data, $modulename = false) {
        if(!$modulename) {
            if($this->active_translation) {
                $modulename = $this->active_translation;
            }
            else {
                throw new WebspellException("Language Error: No modulname specified in method call and no previous selected active modulname.", 4);
            }
        }
        $invalide = array('\\', '/', '/\/', ':', '.');
        $modulename = str_replace($invalide, '', $modulename);
        if(!isset($this->translations[$modulename])) $this->loadTranslation($modulename);
        	
        $search = $replace = array();
        foreach($this->translations[$modulename] AS $keyword => $translation) {
            $search[] = '{%'.$keyword.'%}';
            $replace[] = $translation;
        }
        return str_replace($search, $replace, $data);
    }
}

?>
