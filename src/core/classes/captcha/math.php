<?php
class Captcha_Math extends Captcha_Interface {

    public function __construct() {
        parent::__construct();
    }

    protected function createString() {
        $first = rand(1, pow(10, ($this->length-2) / 2)-1);
        $captchastring = $first;
        $captchastring_show = (string) $first;
        if(rand(0, 1)) {
            $captchastring_show .= "+";
            $next = rand(1, pow(10, ($this->length-2) / 2)-1);
            $captchastring += $next;
            $captchastring_show .= $next;
        }
        else {
            $captchastring_show .= "-";
            $next = rand(1, $first);
            $captchastring -= $next;
            $captchastring_show .= $next;
        }
        $captchastring_show .= "=";
        $this->captchaString = $captchastring_show;
        $this->captchaResult = $captchastring;
    }
}
?>
