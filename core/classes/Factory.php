<?php
class Factory {
    static public function getMail(){
        $registry = Regstry::single();
        $mysqli = $registry->get('db');
        $type = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='mail.type'");
        if(class_exists($this->type)){
            return new $type();
        }
        else {
            throw new Exception("Unknown mail config");
        }
    }

    static public function getHttpRequest(){
        $registry = Regstry::single();
        $mysqli = $registry->get('db');
        $type = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='httpRequest.type'");
        if(class_exists($this->type)){
            return new $type();
        }
        else {
            throw new Exception("Unknown httprequest config");
        }
    }

    static public function getCaptcha(){
        $registry = Regstry::single();
        $mysqli = $registry->get('db');
        $type = $this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='captcha.type'");
        if(class_exists($this->type)){
            return new $type();
        }
        else {
            throw new Exception("Unknown captcha config");
        }
    }

}