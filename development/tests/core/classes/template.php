<?php
include('../../../../core/classes/pattern/singleton.php');
include('../../../../core/classes/registry.php');
Registry::getInstance()->set('modRewrite',true);
include('../../../../core/classes/webspellexception.php');
include('../../../../core/classes/template.class.php');

$templateEngine = new Template();
$templateEngine->setType(Template::Core);
$templateEngine->setBasePath('../../');
$templateEngine->loadTemplate('main','template');
print_r($templateEngine);
echo $templateEngine->fillTemplate('main','template','news',array());
?>
