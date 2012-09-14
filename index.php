<?php 
include "core/config/config.php";

spl_autoload_register(function ($class) {
	if(stristr($class, 'Module_')){
		$modul = str_replace("Module_", "", $class);
		$path = 'modules/'.$modul.'/index.php';
	}
	else{
		$path = 'core/classes/'.str_replace("_", DIRECTORY_SEPARATOR, $class).'php';
	}
	if(file_exists(WEBSPELL_ROOT.$path)){
		include WEBSPELL_ROOT.$path;
	}
	else{
		throw new WebspellException("Autoload failed for ".$class." [".$path."]");
	}
});

new Main();
?>