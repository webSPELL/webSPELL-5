<?php

class URL {

    /**
     * Holds the parsed query string data
     * @var array
     */
    public $data = array();

    /**
     * Holds the registry
     * @var array
     */
    private $registry = false;

    /**
     * Class constructor method
     * Parses the query string into $this->data
     * @return
     */
    public function __construct() {
        $this->registry = Registry::getInstance();
        if(strlen($_SERVER['QUERY_STRING'])) {
            $querystring = urldecode($_SERVER['QUERY_STRING']);
        }
        else {
            $querystring = $this->registry->get('url_default_query_string');
        }
        $querydata = explode('/', $querystring);
        $amount = count($querydata);

        if(empty($querydata[$amount -1])) {
            unset($querydata[$amount -1]);
            $amount--;
        }

        if($amount%2 == 0) {
            throw new WebspellException("URL Parser Error: Invalid query string data amount.", 1);
        }
        if($querydata[0]=='') {
            $start = 2;
        }
        else {
            $start = 1;
        }
        $limit = count($querydata)-1;
        $urldata = array('module'=>$querydata[$start - 1]);
        for($i = $start; $i < $limit; $i=$i+2) {
            $urldata[$querydata[$i]] = $querydata[$i + 1];
        }
        $this->data = $urldata;
        $this->registry->get('Security')->security_slashes($this->data);
        $this->generateGET();
    }

    /**
     * Generates normal $_GET data
     */
    private function generateGET() {
        unset($_GET);
        $_GET = array();
        foreach($this->data AS $key => $value) {
            $_GET[$key] = $value;
        }
    }

    /**
     * Concatenates an given query string data array into a relative URL
     * First array content has to be in the form of 'module'=>'yourmodulenamehere'
     * @return string $querystring
     * @param array $urldata
     */
    public function generateURL($urldata) {
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
        return $querystring;
    }
}

?>