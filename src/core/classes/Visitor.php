<?php
class Visitor {

    private static $ip = null;
    private static $browser = null;

    static public function getIp() {
        if(self::$ip == null) {
            if(isset($_SERVER["HTTP_CLIENT_IP"]) && Validate::Ip($_SERVER["HTTP_CLIENT_IP"])) {
                self::$ip = $_SERVER["HTTP_CLIENT_IP"];
                return self::$ip;
            }
            if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                foreach(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
                    if(Validate::Ip($ip)) {
                        self::$ip = $ip;
                        return self::$ip;
                    }
                }
            }
            if(isset($_SERVER["HTTP_X_FORWARDED"]) && Validate::Ip($_SERVER["HTTP_X_FORWARDED"])) {
                self::$ip = $_SERVER["HTTP_X_FORWARDED"];
                return self::$ip;
            }
            if(isset($_SERVER["HTTP_FORWARDED_FOR"]) && Validate::Ip($_SERVER["HTTP_FORWARDED_FOR"])) {
                self::$ip = $_SERVER["HTTP_FORWARDED_FOR"];
                return self::$ip;
            }
            self::$ip = $_SERVER["REMOTE_ADDR"];
            return self::$ip;
        }
        return self::$ip;
    }
}
?>
