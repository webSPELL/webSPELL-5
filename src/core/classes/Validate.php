<?php
/**
 * Validation class for various inputs
 */
class Validate {
    public static $precUTF8 = null;
    /**
     * Check wheater utf8 support is enabled in prec
     * @see http://www.regular-expressions.info/unicode.html
     * @return int
     */
    static function checkUTF8() {
        if(self::$precUTF8 == null){
            self::$precUTF8 = (@preg_match("/\p{L}/u", "и") == 1);
        }
        return self::$precUTF8;
    }
    /**
     * Validate email address
     * @param string $email
     * @return boolean
     */
    static function email($email) {
        if(self::checkUTF8()) {
            $regex = "/^(?!\.)(\.?[\p{L}0-9!#\$%&'\*\+\/=\?^_`\{\|}~-]+)+@";	#local-part
            $regex .= "(?!\.)(\.?(?!-)[0-9\p{L}-]+(?<!-))+\.[\p{L}0-9]{2,}";	#hostname
            $regex .= "$/sui";
        }
        else {
            $regex = "/^(?!\.)(\.?[\w0-9!#\$%&'\*\+\/=\?^_`\{\|}~-]+)+@";	#local-part
            $regex .= "(?!\.)(\.?(?!-)[0-9\w-]+(?<!-))+\.[\w0-9]{2,}";	#hostname
            $regex .= "$/sui";
        }
        return preg_match($regex, $email);
    }
    /**
     * Validate url
     * @param string $url
     * @return boolean
     */
    static function url($url) {
        $regex = "/^(ht|f)tps?:\/\/"; 						#protocol
        $regex .= "([^:@]+:[^:@]+@)?";							#auth
        $regex .= "(?!\.)(\.?(?!-)[0-9\p{L}-]+(?<!-))+";	#hostname
        $regex .= "(:[0-9]{2,5})?";							#port
        $regex .= "(\/[^#\?]*";								#path
        $regex .= "(\?[^#\?]*)?";							# - query string
        $regex .= "(#.*)?)?$/sui";							# - fragment identifier
        return preg_match($regex, $url);
    }

    /**
     * Validate an IP
     * @param String $ip
     */
    static function ip($ip) {
        // IPv6
        if(strpos($ip, ".") === false && strpos($ip, ":")) {
            /**
             * @todo Add Validation vor IPv6
             */
            throw Exception("IPv6 validation: todo");
        }
        // IPv4
        else {
            return preg_match("/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/", $ip);
        }
    }
}
?>
