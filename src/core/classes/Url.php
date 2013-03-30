<?php
/**
 * URL class for URL parsing and link generation
 */
class Url extends Pattern_Singleton {

	private $registry;
	private $modRewrite = false;
	private $defaultModule = "News";
	private $defaultSection = "default";

	private $module;
	private $moduleParamId = 0;
	private $moduleParamSection;
	private $moduleParams = Array();

	public function __construct(){
	
		$this->registry = Registry::getInstance();
		$this->modRewrite = $this->registry->get('modRewrite');
	
	}

    /**
     * Concatenates an given query string data array into a URL
     * Array format: Array('module'=>'forum', 'section'=>'topic', 'id'=>'1234', 'title'=>'Dies ist der Topictitel!', 'params'=> Array('desc', '5')) // params: sort=desc, page=5
     * Obligatory: module; title (if id is set)
     * @return string $querystring
     * @param array $urldata
     */
    public function generateURL($urldata) {
    
    	if(!isset($urldata['module'])) {
    		throw new WebspellException('missing_module');
    	}

		// check additional module parameters
		if(isset($urldata['params']) && !empty($urldata['params'])) {
			$params = ",".implode(",", $this->filterUrl($urldata['params']));
		} else {
			$params = "";
		}

		if(!empty($urldata['id']) && !empty($urldata['title'])) {
	    	$urlString =	$urldata['module']."/".
					    	((!empty($urldata['section'])) ? $urldata['section']."/" : "").
					    	$urldata['id']."-".$this->filterUrl($urldata['title']).
					    	$params.
					    	".html";
		} else {
	    	$urlString =	$urldata['module'].
					    	((!empty($urldata['section'])) ? "/".$urldata['section'] : "").
					    	$params.
					    	".html";
		}

		if($this->modRewrite == false) {
			$urlString = "/?".$urlString;
		} else {
			$urlString = "/".$urlString;
		}

        return $urlString;
    }

	/**
	 * Parsing of global query string for parameter array generation
	 */
	public function parseQueryString() {

		$queryString = $_SERVER['QUERY_STRING'];
		$this->module = $this->defaultModule;

		if(empty($queryString) === false) {

			// check for aliases
			$queryString = $this->replaceAlias($queryString);

			// remove file ending
			$queryString = substr($queryString, 0, strpos($queryString, "."));

			$urlData = explode("/", $queryString);

			if(count($urlData) > 1) {

				// get module name
				$this->module = array_shift($urlData);
				
				// get section name if existing
				if(count($urlData) > 1) {
					$this->moduleParamSection = array_shift($urlData);
				}

				// extract parameters and id for module use if necessary
				$tmp = explode("-", $urlData[0], 2);
				if(is_numeric($tmp[0])) {
					$this->moduleParamId = $tmp[0];
					$this->moduleParams = explode(',', $tmp[1]);
					array_shift($this->moduleParams);
				}
				else {
					$this->moduleParams = explode(',', $urlData[0]);
					$this->moduleParamSection = array_shift($this->moduleParams);
				}

			}
			else {

				// get module name
				$this->moduleParams = explode(',', $urlData[0]);
				
				// extract parameters for module use
				$this->module = $this->moduleParams[0];
				array_shift($this->moduleParams);

			}
		}
	}

	/**
	 * get id for module use
	 * @return int
	 */
	public function getId() {
		if(empty($this->moduleParamId)) {
			return null;
		} else {
			return $this->moduleParamId;
		}
	}

	/**
	 * get array contains parameters for module use
	 * @return array
	 */
	public function getModuleParams() {
		return $this->moduleParams;
	}

	/**
	 * get section for module use
	 * @return string
	 */
	public function getSection() {
		if(empty($this->moduleParamSection)) {
			return $this->defaultSection;
		} else {
			return $this->moduleParamSection;
		}
	}

	/**
	 * get module for core use
	 * @return string
	 */
	public function getModule() {
	    if(empty($this->module)) {
	        return $this->defaultModule;
	    } else {
		    return $this->module;
	    }
	}

    /**
     * Striping non-alpahnumeric chars in an URL string
     * Array parameter is for recursive striping
     * @return string|array $querystring
     * @param string|array
     */
    private function filterUrl($urlPart) {
    	
    	if(is_array($urlPart)) {
    		foreach($urlPart as &$val) {
   				$this->filterUrl($val);
   			}
   		} else {

			// filtering special chars & umlauts
			$replacements = Array('ä'=>'ae', 'ö'=>'oe', 'ü'=>'ue', 'ß'=>'ss');
			$allowedRegex = "/[^0-9_a-z]/i";
			$urlPart = str_replace(array_keys($replacements), array_values($replacements), $urlPart);
			$urlPart = preg_replace($allowedRegex, '_', $urlPart);

			// strip multi-replacements
			$urlPart = preg_replace("/[_]+/", '_', $urlPart);
			$urlPart = trim($urlPart, '_');

    	}
    	
    	return $urlPart;
    }

	/**
	 * check the given query string against existing replacements
	 * @return string ready for parsing
	 */
	private function replaceAlias($queryString) {

		// todo: database alias replacement
	
		return $queryString;
	
	}

}

?>
