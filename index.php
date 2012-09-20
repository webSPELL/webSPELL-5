<?php 

error_reporting(E_ALL);
$GLOBALS['starttime'] = microtime(true);
define("WEBSPELL_ROOT",__DIR__.DIRECTORY_SEPARATOR);

spl_autoload_register(function ($class) {
	$class = strtolower($class);
	if(stristr($class, 'module_')){
		$modul = str_replace("module_", "", $class);
		$path = 'modules/'.$modul.'/index.php';
	}
	else{
		$path = 'core/classes/'.str_replace("_", DIRECTORY_SEPARATOR, $class).'.php';
	}
	if(file_exists(WEBSPELL_ROOT.$path)){
		include WEBSPELL_ROOT.$path;
	}
	else{
		throw new WebspellException("Autoload failed for ".$class." [".$path."]");
	}
});

try {
	new Main();
}
catch(WebspellException $e){
	echo $e;
}
echo microtime(true)-$GLOBALS['starttime'];
?>