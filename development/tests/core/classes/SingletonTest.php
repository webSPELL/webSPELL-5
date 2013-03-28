<?php
require_once("../../../../core/classes/pattern/singleton.php");
require_once("../../../../core/classes/webspellexception.php");
require_once("../../../../core/classes/registry.php");
class SingletonTest extends PHPUnit_Framework_TestCase
{

    public function testClone()
    {
      $notCloneable = new ReflectionClass('Registry');
      $this->assertEquals(false, $notCloneable->isCloneable());
    }
}
?>