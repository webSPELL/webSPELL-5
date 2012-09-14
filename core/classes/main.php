<?php
class Main {
	public function __construct(){
		$registry = Registry::getInstance();
		new Session();
	}
}