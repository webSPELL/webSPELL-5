<?php
/**
 * Option Object for internal use
 */
class HttpRequest_Options{
    /**
     * Host of the HTTP Request
     * @var string
     */
    public $host;
    /**
     * Port of the HTTP Request
     * @var int
     */
    public $port = 80;
    /**
     * Path
     * @var string
     */
    public $path;
    /**
     * Post fields
     * @var string
     */
    public $data;
    /**
     * Used useragend
     * @var string
     */
    public $useragent = "webspell/php";
    /**
     * Connection timeout in seconds
     * @var int
     */
    public $timeout = 5;
    /**
     * Used Protocol
     * http or https
     * @var string
     */
    public $protocol = "http";
    /**
     * Additional headers to be send
     * @var array
     */
    public $headers = array();
}
class HttpRequest_Interface{
    /**
     * The Options of the connection
     * @var HttpRequest_Options
     */
    protected $options = null;

    /**
     *
     * @var mixed
     */
    protected $connection = null;
    /**
     * Class constructor method
     */
    public function __construct(){
    }
    /**
     * Static method to make a string from
     * an array of post fields
     * @param array $array
     * @static
     * @return string
     */
    static function makePostData($array){
        $string = '';
        foreach($array as $key => $value){
            $string .= $key."=".urlencode($value)."&";
        }
        return mb_substr($string,0,-1);
    }
    /**
     * Set host and port of the connection
     * @param string $host
     * @param int $port [optional]
     * @return
     */
    public function setHost($host, $port = 80){
        $this->options->host = $host;
        $this->options->port = $port;
    }
    /**
     * Set the path
     * @param string $path
     * @return
     */
    public function setPath($path){
        $this->options->path = $path;
    }
    /**
     * Set the post fields
     * @param array/string $data
     * @return
     */
    public function setData($data){
        if(is_array($data)){
            $data = HttpRequest::makePostData($data);
        }
        $this->options->data = $data;
    }
    /**
     * Set the user agent for the connection
     * @param string $string
     * @return
     */
    public function setUserAgent($string){
        $this->options->useragent= $string;
    }
    /**
     * Set the timeout of the connection
     * in seconds
     * @param int $int
     * @return
     */
    public function setTimeout($int){
        $this->options->timeout = $int;
    }
    /**
     * Set the protocol of the connection
     * just http or https
     * @param string $proto
     * @return
     */
    public function setProtocol($proto){
        if(in_array($proto,array("http","https"))){
            $this->options->protocol = $proto;
        }
    }
    /**
     * Add an additional header for the connection
     * @param string $header
     * @return
     */
    public function addHeader($header){
        $this->options->headers[] = $header;
    }
    /**
     * Method to check, wheater all settings
     * are ok, to use it
     * @abstract
     */
    public abstract function init();
    /**
     * Method is called, when a GET-Request is needed
     * @see HttpRequest::get()
     * @return mixed
     */
    public abstract function get();
    /**
     * Method is called, when a POST-Request is needed
     * @see HttpRequest::post()
     * @return mixed
     */
    public abstract function post();
}
?>