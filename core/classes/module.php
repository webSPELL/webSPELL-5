<?php
abstract class Module {
	private $box = false;
	private $content = false;
	
	public function canHandle($type) {
		switch($type){
			case 'box':
				return $box;
			case 'content':
				return $content;
			default:
				return false;
		}
	}	
}
?>