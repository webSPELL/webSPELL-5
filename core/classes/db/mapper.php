<?php

class Mapper {
	protected $registry;
	protected $db;
	
	public function __construct() {
		$this->registry = Registry::getInstance();
		$this->db = $this->registry->get('db');
	}
}

?>