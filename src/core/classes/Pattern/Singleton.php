<?php
namespace Pattern;
/**
 * Singleton design pattern for extension
 */
class Singleton {

private static $_instances = array();
    public static function getInstance() {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }
	

	private function __construct() {}
	private function __clone() {}

}
?>
