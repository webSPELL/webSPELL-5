<?php
define("WEBSPELL_ROOT",__DIR__."/../src/");
include(WEBSPELL_ROOT."autoload.php");
spl_autoload_register(array("Autoload","load"));
Autoload::addExternalPath(WEBSPELL_ROOT."core/lib");
?>