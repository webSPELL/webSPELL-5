<?php
/**
 * Singleton design pattern for extension
 */
class Pattern_Singleton {

	static private $instance = null;

	static public function getInstance() {

		if (null === self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;

	}

	private function __construct() {}
	private function __clone() {}

}
?>