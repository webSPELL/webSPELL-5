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

	class Template {
		
		/**
		 * Holds the registry
		 * @var array
		 */
		private $registry = false;
		
		/**
		 * Holds the general template type (either 'admin', 'mod' or 'core')
		 * @var string
		 */
		private $templatetype = false;
		
		/**
		 * Holds the template base path depending on the template type
		 * @var string
		 */
		private $template_basepath = false;
		
		/**
		 * Holds the relative path and file name of the template
		 * @var string
		 */
		private $template_path = false;
		
		/**
		 * Holds the parsed template file contents in the form:
		 * [modulename_templatename1]=>
		 * 		[subtemplatename1]=>subtemplatecontent1
		 * 		[subtemplatename2]=>subtemplatecontent2
		 * 		...
		 * [modulename_templatename2]=>
		 * 		[subtemplatename1]=>subtemplatecontent1
		 * 		[subtemplatename2]=>subtemplatecontent2
		 * 		...
		 * ...
		 * @var array
		 */
		private $templates = array();
		
		/**
		 * Class constructor method
		 * @return boolean
		 * @param string $templatetype[optional] Selects either mod or core template type
		 */				
		function __construct($templatetype='mod') {
			$this->registry = Registry::getInstance();
			$this->templatetype = $templatetype;
			$this->init();
		}
		
		/**
		 * Sets the basepath for the templates
		 * @return 
		 */
		private function init() {
			if($this->templatetype == 'mod') {
				$this->template_basepath = './modules/';
			}
			elseif($this->templatetype == 'core') {
				$this->template_basepath = './core/';
			}
			elseif($this->templatetype == 'admin') {
				$this->template_basepath = './';
			}
			else {
				throw new WebspellException("Template Error: Invalid template type specified.", 2);
			}
		}
		
		/**
		 * Loads and splits up a template file
		 * @return boolean
		 * @param string $modulename
		 * @param string $templatename
		 */
		private function loadTemplate($modulename, $templatename) {
			if($this->templatetype == 'mod') {
				$this->template_path = $this->template_basepath.$modulename.'/templates/'.$templatename.'.html';
			}
			elseif($this->templatetype == 'core') {
				$this->template_path = $this->template_basepath.'templates/'.$modulename.'/'.$templatename.'.html';
			}
			elseif($this->templatetype == 'admin') {
				$this->template_path = $this->template_basepath.'templates/'.$modulename.'/'.$templatename.'.html';
			}
			else {
				throw new WebspellException("Template Error: Invalid template type specified.", 3);
			}
			if($template = @file_get_contents($this->template_path)) {
				$this->templates[$modulename.'_'.$templatename] = array(); 
				$pattern = '/{§[a-zA-Z0-9]+?§}/i';
				$content = preg_split($pattern, $template);
				preg_match_all($pattern, $template, $keys);
				$count = count($keys[0]);
				for($i=0; $i<$count; $i++) {
					$this->templates[$modulename.'_'.$templatename][str_replace(array('{§', '§}'), '', $keys[0][$i])] = $content[$i+1];
				}
				return true;
			}
			else {
				throw new WebspellException("Template Error: Requested template file not available.", 4);
			}
		}
		
		/**
		 * Parses a subtemplate and fills it based on the given arrays keys and values
		 * @return array
		 * @param string $modulename
		 * @param string $templatename
		 * @param string $subtemplatename
		 * @param array $values
		 */
		private function parseTemplate($modulename, $templatename, $subtemplatename, $values) {
			$invalide = array('\\', '/', '/\/', ':', '.');
			$modulename = str_replace($invalide, '', $modulename);
			$templatename = str_replace($invalide, '', $templatename);
			$subtemplatename = str_replace($invalide, '', $subtemplatename);
			if(!isset($this->templates[$modulename.'_'.$templatename])) $this->loadTemplate($modulename, $templatename);
			if(isset($this->templates[$modulename.'_'.$templatename][$subtemplatename])) {
				$search = $replace = array();
				foreach($values AS $keyword => $value) {
					$search[] = '{#'.$keyword.'#}';
					$replace[] = $value;
				}
				return str_replace($search, $replace, $this->templates[$modulename.'_'.$templatename][$subtemplatename]);
			}
			else {
				throw new WebspellException("Template Error: Requested subtemplate not available.", 5);
			}
		}
		
		/**
		 * Wrapper for single run and return a parsed subtemplate
		 * @return string
		 * @param string $modulename
		 * @param string $templatename
		 * @param string $subtemplatename
		 * @param array $values
		 */
		public function returnTemplate($modulename, $templatename, $subtemplatename, $values = array()) {
			return $this->parseTemplate($modulename, $templatename, $subtemplatename, $values);
		}
		
		/**
		 * Wrapper for single run and echoing a parsed subtemplate
		 * @return 
		 * @param string $modulename
		 * @param string $templatename
		 * @param string $subtemplatename
		 * @param array $values
		 */
		public function echoTemplate($modulename, $templatename, $subtemplatename, $values = array()) {
			echo $this->parseTemplate($modulename, $templatename, $subtemplatename, $values);
		}
		
		/**
		 * Wrapper for multiple runs and return a multiple parsed subtemplate
		 * @return string
		 * @param string $modulename
		 * @param string $templatename
		 * @param string $subtemplatename
		 * @param array $values
		 */
		public function returnMultiTemplate($modulename, $templatename, $subtemplatename, $values = array()) {
			$result = '';
			foreach($values AS $subvalues) {
				$result .= $this->returnTemplate($modulename, $templatename, $subtemplatename, $subvalues);
			}
			return $result;
		}
		
		/**
		 * Wrapper for multiple runs and echoing a multiple parsed subtemplate
		 * @return 
		 * @param string $modulename
		 * @param string $templatename
		 * @param string $subtemplatename
		 * @param array $values
		 */
		public function echoMultiTemplate($modulename, $templatename, $subtemplatename, $values = array()) {
			foreach($values AS $subvalues) {
				$this->echoTemplate($modulename, $templatename, $subtemplatename, $subvalues);
			}
		}
	}

?>