<?php
include('../../../../core/classes/pattern/singleton.php');
include('../../../../core/classes/registry.php');
Registry::getInstance()->set('modRewrite',true);
include('../../../../core/classes/webspellexception.php');
include('../../../../core/classes/ArrayHelper.php');
include('../../../../core/classes/template.class.php');

$templateEngine = new Template();
$templateEngine->setType(Template::Core);
$templateEngine->setBasePath('../../');
$templateEngine->loadTemplate('main','template');
echo $templateEngine->fillTemplate('main','template','news',array());

$templateEngine->loadTemplate('nested','template');
echo $templateEngine->fillTemplate('nested','template','list',array('title'=>'Überschrift',
                                                                    'item'=>array(
                                                                        array("href"=>"#","title"=>"Test"),
                                                                        array("href"=>"google.de","title"=>"Google")
                                                                    )
                                                            ));
print_r($templateEngine);
?>
