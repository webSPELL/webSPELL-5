<?php
error_reporting(E_ALL);
$GLOBALS['starttime'] = microtime(true);
define("WEBSPELL_ROOT", __DIR__.DIRECTORY_SEPARATOR);

include(WEBSPELL_ROOT."autoload.php");
spl_autoload_register(array("Autoload", "load"));
Autoload::addExternalPath(WEBSPELL_ROOT."core/lib");

try {
	new Main();
}
catch(Exception $e){
	echo $e;
}

echo microtime(true)-$GLOBALS['starttime'];
?>
