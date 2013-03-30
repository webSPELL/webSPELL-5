<?php
class ArrayHelper {
	static function isAssoc($array) {
		if(empty($array)){
			return true;
		}
		return array_keys($array) !== range(0, count($array) - 1);
	}
}
?>
