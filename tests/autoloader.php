<?php
//TODO: find way to integrate libs
foreach (glob("src/core/lib/Spot/*.php") as $filename)
{
	include_once $filename;
}

spl_autoload_register(function ($class) {
	$class = strtolower($class);
	if(stristr($class, 'module_')){
		$modul = str_replace("module_", "", $class);
		$path = 'src/modules/'.$modul.'/index.php';
	}
	elseif(strstr($class, 'mapper')){
		$path = 'src/core/classes/db/'.str_replace("_", DIRECTORY_SEPARATOR, $class).'.php';
	}
	else{
		$path = 'src/core/classes/'.str_replace("_", DIRECTORY_SEPARATOR, $class).'.php';
	}
	if(file_exists($path)){
		include $path;
	}
	
});
?>