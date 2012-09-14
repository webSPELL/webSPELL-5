<?php
class Registry extends Singleton {
	
	private $array = array();
	
	/**
	 * 
	 * gets the value of the given key
	 * @param string|int $key
	 */
	public function get($key) {
		if(empty($key) || !isset($this->array[$key]) || (!is_string($key) && !is_int($key) )) {
			throw new WebspellException('invalid or not existing key '.$key);
		}
		return $this->array[$key];
	}
	
	/**
	 * 
	 * sets the value for the given key
	 * @param string|int $key
	 * @param mixed $value
	 */
	public function set($key, $value) {
		if(empty($key) || (!is_string($key) && !is_int($key) )) {
			throw new WebspellException('invalid key '.$key);
		}
		$this->array[$key] = $value;
	}
}

?>