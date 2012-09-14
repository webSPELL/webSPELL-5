<?php
/**
 * URL class for URL parsing and link generation
 */
class Url {

	private $modRewrite = true;
	private $defaultModule = "news";

	private $module;
	private $moduleParamId = 0;
	private $moduleParamSection;
	private $moduleParams = Array();

	public function __construct(){
		
		$this->parseQueryString();

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
    		throw new CoreException('missing_module');
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
/*    
        if($this->registry->get('url_withindex')) {
            $querystring = 'index.php?';
        }
        else {
            $querystring = '?';
        }
        $querystring .= '/'.$urldata['module'];
        $module = true;
        foreach($urldata AS $key => $value) {
            if($module) {
                $module = false;
                continue;
            }
            else {
                $querystring .= '/'.$key.'/'.$value;
            }
        }
*/
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
					array_shift($this->moduleParams);
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
			return null;
		} else {
			return $this->moduleParamSection;
		}
	}

	/**
	 * get module for core use
	 * @return string
	 */
	public function getModule() {
		return $this->module;
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

$input = Array(
			Array('module'=>'user'),
			Array('module'=>'user', 'params'=> Array('desc', '5')),
			Array('module'=>'user', 'id'=>'1234', 'title'=>'bluetiger', 'params'=> Array('desc', 'contact')),
			Array('module'=>'user', 'section'=>'edit', 'id'=>'200', 'title'=>'editieren'),
			Array('module'=>'forum', 'section'=>'board', 'id'=>'31', 'title'=>'allgemeiner support'),
			Array('module'=>'forum', 'section'=>'topic', 'id'=>'1233', 'title'=>'ich brauche hilfe!'),
			Array('module'=>'forum', 'section'=>'topic', 'id'=>'1234', 'title'=>'Dies ist der Topictitel! -#äö+ü', 'params'=> Array('desc', '5'))
		);

$output = Array();
$input_original = Array();

foreach($input as $value) {

	array_push($input_original, Array('module'=>$value['module'], 'section'=>(isset($value['section']) ? $value['section'] : ""), (isset($value['id']) ? $value['id'] : ""), (isset($value['params']) ? $value['params'] : Array())));
	$c = new Url();
	$url = $c->generateUrl($value);
	echo $url."\n";
	$c->parseQueryString($url);
	print_r($c);
	array_push($output, Array('module'=>$c->getModule(), 'section'=>$c->getSection(), 'id'=>$c->getId(), 'params'=>$c->getModuleParams()));

}

print_r($input_original);
print_r($output);



/*
$test = Array(
"user.html",
"user,save.html",
"user/200-bluetiger,31,asc.html",
"user/edit/200-editieremich.html",
"user,31,asc.html"
"forum/board/31-name.html",
"forum/topic/2183-ich_bin_ein_topic.htm"
		);

// http://www.webspell.org/user.html
// http://www.webspell.org/user,save.html
// http://www.webspell.org/user/200-bluetiger,31,asc.html
// http://www.webspell.org/user/bluetiger,31,asc.html
// http://www.webspell.org/forum/board/31-name.html
// http://www.webspell.org/forum/topic/2183-ich_bin_ein_topic.htm

foreach($test as $k=>$v) {

	$_SERVER['QUERY_STRING'] = $v;
	echo $_SERVER['QUERY_STRING']."\n";
	$c = new url();
	print_r($c);

}
*/
?>