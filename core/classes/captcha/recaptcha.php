<?php
class Captcha_ReCaptcha extends Captcha_Interface{
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