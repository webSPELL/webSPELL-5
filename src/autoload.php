<?php
class Autoload {
	static $knownPath = array();
	static $classes = array();
	public static function addExternalPath($path){
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	}
	public static function load($class){
		$correctedName = str_replace(array("_","\\"), DIRECTORY_SEPARATOR, $class);
		if(stristr($class, 'Module_')){
			$modul = str_replace("Module_", "", $class);
			$path = 'modules/'.$modul.'/index.php';
		}
		elseif(strstr($class, 'Mapper')){
			$path = 'core/classes/db/'.$correctedName.'.php';
		}
		else{
			$path = 'core/classes/'.$correctedName.'.php';
		}
		if(file_exists(WEBSPELL_ROOT.$path)){
			include WEBSPELL_ROOT.$path;
		}
		else{
			foreach(explode(PATH_SEPARATOR,get_include_path()) as $path){
				$file = $path.DIRECTORY_SEPARATOR.$correctedName.".php";
				if(file_exists($file)){
					require_once $file;
					return;
				}
			}
		}
	}
}
?>