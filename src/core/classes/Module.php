<?php
abstract class Module {
	private $box = false;
	private $content = false;

	public function canHandle($type) {
		switch($type){
			case 'box':
				return self::$box;
			case 'content':
				return self::$content;
			default:
				return false;
		}
	}	
}
?>