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
class HttpRequest{
	/**
	 * Holds the used type of request
	 * @var HttpRequest_Core
	 */
	private $handle = null;
	/**
	 * All possible types (class names)
	 * @var array
	 */
	private $classes = array("HttpRequest_CURL","HttpRequest_FOPEN");
	/**
	 * The Options of the connection
	 * @var HttpRequest_Options
	 */
	private $options = null;
	/**
	 * Class constructor method
	 * @return boolean
	 */
	public function __construct(){
		$this->options = new HttpRequest_Options();
		for($i=0;$i<count($this->classes);$i++){
			try{
				$try = new $this->classes[$i]();
			}
			catch(Exception $error){
				unset($try);
				echo $error;
				continue;
			}
			$this->handle = $try;
			return true;
		}
		if($this->handle == null){
			return false;
		}
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
	 * Makes the GET-Request with the options
	 * returns page content on success
	 * returns false of fail
	 * @return string / boolean
	 */
	public function get(){
		$this->handle->setOptions($this->options);
		return $this->handle->get();
	}
	/**
	 * Makes the POST-Request with the options
	 * returns page content on success
	 * returns false of fail
	 * @return string / boolean
	 */
	public function post(){
		$this->handle->setOptions($this->options);
		return $this->handle->post();
	}
}
/**
 * Abstract class for all HttpRequest Types
 * gives all needed method names and options
 * @abstract
 */
abstract class HttpRequest_Core{
	/**
	 * @var HttpRequest_Options
	 */
	protected $options;
	
	protected $connection;
	/**
	 * Class constructor method
	 * @return 
	 */
	public function __construct(){
		$this->init();
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
	/**
	 * Sets the options for the request
	 * @param HttpRequest_Options $options
	 * @return 
	 */
	public function setOptions($options){
		$this->options = $options;
	}
}
/**
 * Http Request Class with usage of curl
 * 
 */
class HttpRequest_CURL extends HttpRequest_Core{
	/**
	 * @see HttpRequest_Core::init();
	 */
	public function init(){
		if(function_exists('curl_init') == false || extension_loaded('curl') == false){
			throw new Exception("HttpRequest Error: CURL Modul not loaded");
		}
		if(in_array(@ini_get('allow_url_fopen'),array("On",1)) == false){
			throw new Exception("HttpRequest Error: URL Open not allowed");
		}
	}
	/**
	 * prepare a curl connection with needed
	 * settings
	 */
	private function prepareConnection(){
		$this->connection = curl_init();
		curl_setopt($this->connection,	CURLOPT_HEADER, 				0);
		curl_setopt($this->connection,	CURLOPT_RETURNTRANSFER,	1);
		curl_setopt($this->connection,	CURLOPT_URL,						$this->options->protocol."://".$this->options->host.":".$this->options->port."/".$this->options->path);
		curl_setopt($this->connection,	CURLOPT_USERAGENT,			$this->options->useragent);
		curl_setopt($this->connection,	CURLOPT_FOLLOWLOCATION	,1);
		if(!empty($this->headers)){
			curl_setopt($this->connection,CURLOPT_HTTPHEADER,			$this->options->headers);
		}
	}
	/**
	 * @see HttpRequest::get();
	 * @return mixed
	 */
	public function get(){
		$this->prepareConnection();
		curl_setopt($this->connection,	CURLOPT_POST,						0);
		$return = curl_exec($this->connection);
		curl_close($this->connection);
		return $return;
	}
	/**
	 * @see HttpRequest::post();
	 * @return mixed
	 */
	public function post(){
		$this->prepareConnection();
		curl_setopt($this->connection,	CURLOPT_POST,						1);
		curl_setopt($this->connection,	CURLOPT_POSTFIELDS,			$this->options->data);
		$return = curl_exec($this->connection);
		curl_close($this->connection);
		return $return;
	}
}
/**
 * Http Request Class with usage of fsockopen
 */
class HttpRequest_FOPEN extends HttpRequest_Core{
	/**
	 * @see HttpRequest_Core::init();
	 */
	public function init(){
		if(in_array(@ini_get('allow_url_fopen'),array("On",1)) == false){
			throw new Exception("HttpRequest Error: URL Open not allowed");
		}
		if(in_array(@ini_get('disabled_functions'),array("fsockopen",1))){
			throw new Exception("HttpRequest Error: fsockopen disabled");
		}
	}
	/**
	 * Prepages a socket connection to the host
	 * with settings
	 */
	private function prepareConnection(){
		$this->connection = fsockopen($this->options->host,$this->options->port,$errno,$errstr,$this->options->timeout);
		if($this->connection){
			stream_set_blocking($this->connection, 0);
			stream_set_timeout($this->connection, $this->options->timeout);
		}
		else{
			throw new Exception("HttpRequest Error: Can't connect to server");
		}
	}
	/**
	 * @see HttpRequest::get();
	 * @return mixed
	 */
	public function get(){
		$this->prepareConnection();
		fwrite($this->connection, "GET ".$this->options->path." HTTP/1.0\r\n");
		fwrite($this->connection, "Host: ".$this->options->host."\r\n");
		fwrite($this->connection, "User-Agent: ".$this->options->useragent."\r\n");
		foreach($this->options->headers as $header){
			fwrite($this->connection, $header."\r\n");
		}
		fwrite($this->connection, "Connection: Close\r\n\r\n"); 
		$return = "";
		while(!feof($this->connection)){
			$return .= fgets($this->connection,1024);
		}
		fclose($this->connection);
		return $return;
	}
	/**
	 * @see HttpRequest::post();
	 * @return mixed
	 */
	public function post(){
		$this->prepareConnection();
		fwrite($this->connection, "POST ".$this->options->path." HTTP/1.0\r\n");
		fwrite($this->connection, "Host: ".$this->options->host."\r\n");
		fwrite($this->connection, "Content-Type: application/x-www-form-urlencoded;\r\n");
		fwrite($this->connection, "Content-Length: ".strlen($this->options->data)."\r\n");
		foreach($this->options->headers as $header){
			fwrite($this->connection, $header."\r\n");
		}
		fwrite($this->connection, "User-Agent: ".$this->options->useragent."\r\n\r\n");
		fwrite($this->connection, $this->options->data);
		$return = "";
		while(!feof($this->connection)){
			$return .= fgets($this->connection,1024);
		}
		fclose($this->connection);
		$return = explode("\r\n\r\n", $return, 2);
		return $return[1];
	}
}
?>