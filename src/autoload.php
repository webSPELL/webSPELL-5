<?php
class Autoload {
	static $knownPath = array();
	static $classes = array();
	public static function addExternalPath($path){
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	}
	public static function fixPackageNames($s) {
        return preg_replace('/(?<!^)([A-Z])([a-z])/', '_\\1\\2', $s);
	}
	public static function load($class){
		$correctedName = str_replace(array("_","\\"), DIRECTORY_SEPARATOR, $class);
		if(stristr($class, 'Module') && strlen($class)>6){
			$modul = str_replace("Module", "", $class);
			$path = 'modules/'.$modul.'/index.php';
		}
		elseif(strlen($class) > 6 && stristr(substr($class, -6), 'Mapper') && !stristr(substr($class, 0, 5), 'Spot\\')){
			$path = 'core/models/'.$correctedName.'.php';
		}
		else{
			$path = 'core/classes/'.$correctedName.'.php';
		}

		if(file_exists(WEBSPELL_ROOT.$path)){
			require_once WEBSPELL_ROOT.$path;
			return;
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