<?php
namespace Captcha;
class Standard extends Captcha\Base {
    /**
     * Background Color of the Captcha
     * @var array
     */
    private $Color_Background = array("r"=>255, "g"=>255, "b"=>255);
    /**
     * Text Color of the Captcha
     * @var array
     */
    private $Color_Text = array("r"=>0, "g"=>0, "b"=>0);
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

    public function __construct() {
        parent::__construct();
        foreach(glob("tmp/*.[jpg|dat]*") as $file) {
            if(filemtime($file)  < time()-$this->validationTime) {
                unlink($file);
            }
        }
    }
    private function hex2rgb($col) {
        $col = str_replace("#", "", $col);
        $int = hexdec($col);
        $return = array(
                "r" => 0xFF & $int >> 0x10,
                "g" => 0xFF & ($int >> 0x8),
                "b" => 0xFF & $int
        );
        return $return;
    }
    public function create() {
        $this->hash = md5(time().rand(0, 10000));
        $this->createString();
        return $this->returnHTML();
    }
    public function validate() {
        /**
         * @todo Put hashs into the database
         */
        $hash = $_POST['captcha_default_hash'];
        $userinput = $_POST['captcha_default_input'];
        $expexted = file_get_contents('tmp/'.$hash.'.dat');
        @unlink('tmp/'.$hash.'.dat');
        @unlink('tmp/'.$hash.'.jpg');
        if($expexted == $userinput) {
            return true;
        }
        return false;
    }
    protected function createString() {
        $chars = range("A", "Z") + range(1, 9);
        $captchastring = '';
        for($i=0;$i<$this->length;$i++) {
            $captchastring .= $chars[array_rand($chars, 1)];
        }
        $this->captchaString = $captchastring;
        $this->captchaResult = $captchastring;
    }
    private function returnHTML() {
        $w = ($this->length*15)+20;
        $h = 25;
        $img = imagecreatetruecolor($w, $h);
        $bgcolor = imagecolorallocate($img, $this->Color_Background['r'], $this->Color_Background['g'], $this->Color_Background['b']);
        $fontcolor = imagecolorallocate($img, $this->Color_Text['r'], $this->Color_Text['g'], $this->Color_Text['b']);
        imagefill($img, 0, 0, $bgcolor);

        for($i=0;$i<$this->linenoise;$i++) {
            imageline($img, rand(0, $w), rand(0, $h), rand(0, $w), rand(0, $h), imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255)));
        }

        for($i=0;$i<$this->noise;$i++) {
            imagesetpixel($img, rand(0, $w), rand(0, $h), $fontcolor);
        }
        for($i=0;$i<mb_strlen($this->captchaString);$i++) {
            $char = mb_substr($this->captchaString, $i, 1);
            $font = rand(2, 5);
            imagestring($img, $font, $i*15+5, 5, $char, $fontcolor);
        }
        imagejpeg($img, 'tmp/'.$this->hash.'.jpg');
        /**
         * @todo Put hashs into the database
         */
        file_put_contents('tmp/'.$this->hash.'.dat', $this->captchaResult);
        @chmod('tmp/'.$this->hash.'.jpg', 0755);
        return '<img src="tmp/'.$this->hash.'.jpg" border="0" alt="" /><br/><input type="text" name="captcha_default_input"/><input type="hidden" name="captcha_default_hash" value="'.$this->hash.'"/>';
    }
}
?>
