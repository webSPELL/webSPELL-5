<?php
class ArrayHelper{
	static function is_assoc($array) {
		if(empty($array)){
			return true;
		}
		return array_keys($array) !== range(0, count($array) - 1);
	}
}
?>