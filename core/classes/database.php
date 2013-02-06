<?php

class Database {
	
	private $cfg;
	private $mapper;
	
	private $entities = array('logMapper', 'notificationMapper', 'settingsMapper', 'userAccessRightsMapper', 'userGroupAccessRightsMapper', 'userGroupsMapper', 'userMapper', 'userMembershipsMapper', 'userPropertiesMapper', 'userPropertiesValuesMapper');
	
	public function __construct() {
		$this->cfg = new \Spot\Config();
		$this->cfg->addConnection("mysql", "mysql://root:1234@localhost/ws_v5");
		$this->mapper = new \Spot\Mapper($this->cfg);
		
		$this->initEntities();
	}
	
	private function initEntities() {
		foreach($this->entities as $entity){
			$this->mapper->migrate($entity);
		}
		/*
		$old_dir = getcwd();
		chdir("./core/classes/db/");
		foreach (glob("*mapper.php") as $filename)
		{
			$entity = substr($filename, 0, -4);
			echo $entity;
			$mapper->migrate($entity);
		}
		chdir($old_dir);
		*/
		
		
	}
	
	public function getMapper() {
		return $this->mapper;
	}
	
}