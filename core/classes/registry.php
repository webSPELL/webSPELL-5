<?php
class Registry extends Singleton {
	
	
	private $array = array();
	
	public function getValue($key){
		if(empty($array[$key])) {
			return false;
		}
		return $array[$key];
	}
}

?>