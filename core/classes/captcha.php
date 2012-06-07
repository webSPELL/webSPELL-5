<?php
class Captcha {
	/**
	 * The used Captcha Object
	 * @var Object Captcha_Core
	 */
	private $captcha = null;
	public function __construct(){
		/*switch(rand(0,1)){
			case 0:
				$this->captcha = new Captcha_Default();
				break;
			default:
				$this->captcha = new Captcha_ReCaptcha();
				break;
		}*/
		$this->captcha = new Captcha_ReCaptcha();
	}
	public function create(){
		return $this->captcha->create();
	}
	public function validate(){
		return $this->captcha->validate();
	}
}
abstract class Captcha_Core {
	private $mysqli;
	private $registry;
	public function __construct(){
		//$this->registry = Regstry::getInstance();
		//$this->mysqli = $this->registry->get('db');
	}
	abstract function create();
	abstract function validate();
}
class Captcha_Default extends Captcha_Core{
	/**
	 * Background Color of the Captcha
	 * @var array
	 */
	private $Color_Background = array("r"=>255,"g"=>255,"b"=>255);
	/**
	 * Text Color of the Captcha
	 * @var array
	 */
	private $Color_Text = array("r"=>0,"g"=>0,"b"=>0);
	/**
	 * TTL for a Captcha
	 * @var int
	 */
	private $validationTime = 60;
	/**
	 * Length of the Captcha
	 * @var int
	 */
	protected $length = 6;
	
	private $linenoise = 5;
	private $noise = 20;
	
	protected $captchaString;
	protected $captchaResult;
	
	public function __construct(){
		parent::__construct();
		/*$background_color = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha_default_background'");
		if($background_color){
			$this->Color_Background = $this->hex2rgb($background_color);
		}
		$text_color = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha_default_text'");
		if($text_color){
			$this->Color_Text = $this->hex2rgb($text_color);
		}*/
		foreach(glob("tmp/*.[jpg|dat]*") as $file){
			if(filemtime($file)  < time()-$this->validationTime){
				unlink($file);
			}
		}
	}
	private function hex2rgb($col){
		$col = str_replace("#","",$col);
		$int = hexdec($col);
		$return = array(
            "r" => 0xFF & $int >> 0x10,
            "g" => 0xFF & ($int >> 0x8),
            "b" => 0xFF & $int
            );
		return $return;
	}
	public function create(){
		$this->hash = md5(time().rand(0, 10000));
		$this->createString();
		return $this->returnHTML();
	}
	public function validate(){
		/**
		 * @todo Put hashs into the database
		 */
		$hash = $_POST['captcha_default_hash'];
		$userinput = $_POST['captcha_default_input'];
		$expexted = file_get_contents('tmp/'.$hash.'.dat');
		@unlink('tmp/'.$hash.'.dat');
		@unlink('tmp/'.$hash.'.jpg');
		if($expexted == $userinput){
			return true;
		}
		return false;
	}
	protected function createString(){
		$chars = range("A","Z") + range(1,9);
		$captchastring = '';
		for($i=0;$i<$this->length;$i++) {
			$captchastring .= $chars[array_rand($chars,1)];
		}
		$this->captchaString = $captchastring;
		$this->captchaResult = $captchastring;
	}
	private function returnHTML(){
		$w = ($this->length*15)+20;
		$h = 25;
		$img = imagecreatetruecolor($w, $h);
		$bgcolor = imagecolorallocate($img, $this->Color_Background['r'], $this->Color_Background['g'], $this->Color_Background['b']);
		$fontcolor = imagecolorallocate($img, $this->Color_Text['r'], $this->Color_Text['g'], $this->Color_Text['b']);
		imagefill($img,0,0,$bgcolor);
		
		for($i=0;$i<$this->linenoise;$i++) {
			imageline($img, rand(0,$w), rand(0,$h), rand(0,$w), rand(0,$h), imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255)));
		}

		for($i=0;$i<$this->noise;$i++) {
			imagesetpixel($img, rand(0,$w), rand(0,$h), $fontcolor);
		}
		for($i=0;$i<mb_strlen($this->captchaString);$i++){
			$char = mb_substr($this->captchaString,$i,1);
			$font = rand(2,5);
			imagestring($img, $font, $i*15+5, 5, $char, $fontcolor);
		}
		imagejpeg($img, 'tmp/'.$this->hash.'.jpg');
		/**
		 * @todo Put hashs into the database
		 */
		file_put_contents('tmp/'.$this->hash.'.dat',$this->captchaResult);
		@chmod('tmp/'.$this->hash.'.jpg', 0755);
		return '<img src="tmp/'.$this->hash.'.jpg" border="0" alt="" /><br/><input type="text" name="captcha_default_input"/><input type="hidden" name="captcha_default_hash" value="'.$this->hash.'"/>';
	}
}
class Captcha_Math extends Captcha_Default {
	public function __construct(){
		parent::__construct();
	}
	protected function createString(){
		$first = rand(1,pow(10,($this->length-2) / 2)-1);
		$captchastring = $first;
		$captchastring_show = (string) $first;
		if(rand(0,1)){
			$captchastring_show .= "+";
			$next = rand(1,pow(10,($this->length-2) / 2)-1);
			$captchastring += $next;
			$captchastring_show .= $next;
		}
		else{
			$captchastring_show .= "-";
			$next = rand(1,$first);
			$captchastring -= $next;
			$captchastring_show .= $next;
		}
		$captchastring_show .= "=";
		$this->captchaString = $captchastring_show;
		$this->captchaResult = $captchastring;
	}
}
class Captcha_ReCaptcha extends Captcha_Core{
	/**
	 * Api Key from Recaptcha
	 * http://recaptcha.net/api/getkey
	 * @var String
	 */
	private $publicKey = '';
	/**
	 * Api Key from Recaptcha
	 * http://recaptcha.net/api/getkey
	 * @var String
	 */
	private $privateKey = '';
	
	/**
	 * Use SSL Connection
	 * @var boolean
	 */
	private $useSSL = false;
	/**
	 * The Servers of Recaptcha
	 * @var array
	 */
	private $servers = array(
	'verify'=>'api-verify.recaptcha.net',
	'script'=>'http://api.recaptcha.net',
	'script_ssl'=>'https://api-secure.recaptcha.net');
	/**
	 * The Theme which is used
	 * @var string
	 */
	private $theme = 'clean';
	
	public function __construct(){
		parent::__construct();
		$allow_url_fopen=@ini_get('allow_url_fopen');
		if($allow_url_fopen != 'On' && $allow_url_fopen =! 1) {
			throw new Exception("URL FOPEN WRAPPER: allow_url_fopen is deactivated in your php configuration");
		}
		$this->publicKey = '6LcOIQwAAAAAALGHVrUGCEc6lu2QDdwnw8SFL6jp';//$this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha_recaptcha_publicKey'");
		$this->privateKey = '6LcOIQwAAAAAAKAasQ8vrtIhhETEI2iLkrqOm-tX'; //$this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha_recaptcha_privateKey'");
		$this->useSSL = false;//(boolean)$this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha_recaptcha_useSSL'");
		//$this->theme = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha_recaptcha_theme'");
		if($this->publicKey == '' || $this->privateKey == ''){
			throw new Exception("RECAPTCHA ERROR: You need a Api Key to use. http://recaptcha.net/api/getkey");
		}
	}
	
	public function create(){
		if($this->useSSL){
			$server = $this->servers['script_ssl'];
		}
		else{
			$server = $this->servers['script'];
		}
		
		return '<script type="text/javascript">
var RecaptchaOptions = {
   theme : "'.$this->theme.'"
};
</script>
<script type="text/javascript" src="'.$server.'/challenge?k='.$this->publicKey.'"></script>
	<noscript>
  		<iframe src="'.$server.'/noscript?k='.$this->publicKey.'" height="300" width="500" frameborder="0"></iframe><br/>
  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
	</noscript>';
	}
	public function validate(){
		if(empty($_POST['recaptcha_challenge_field']) ||
			 empty($_POST['recaptcha_response_field'])){
			 	return false;
		}
		$http = new HttpRequest();
		if($http){
			$http->setHost($this->servers['verify']);
			$http->setPath("/verify");
			$http->setData(array ('privatekey' => $this->privateKey,
														'remoteip' => $_SERVER['REMOTE_ADDR'],
														'challenge' => $_POST['recaptcha_challenge_field'],
														'response' => $_POST['recaptcha_response_field']
														)
										);
			$http->setUserAgent("reCAPTCHA/PHP");
			$response = $http->post();
			$data = explode("\n",$response);
			return ($data[0] == "true");
		}
		else{
			return false;
		}
	}
}
?>