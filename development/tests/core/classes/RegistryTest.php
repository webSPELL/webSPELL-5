<?php
include("../../../../core/classes/pattern/singleton.php");
include("../../../../core/classes/webspellexception.php");
include("../../../../core/classes/registry.php");
class RegistryTest extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        $this->registry = Registry::getInstance();
    }

    public function testSetEmptyKey()
    {
       $this->setExpectedException('WebspellException');
       $this->registry->set('','Value');
    }
 
    public function testGetUnknownKey()
    {
       $this->setExpectedException('WebspellException');
       $this->registry->get(null);
    }

    public function testGetEmptyKey()
    {
       $this->setExpectedException('WebspellException');
       $this->registry->get('');
    }
    public function testSetAndGetKey(){
        $this->registry->set("myKey","MyValue");
        $this->assertEquals("MyValue",$this->registry->get("myKey"));

        $object = new stdClass();
        $object->key = "Test";
        $this->registry->set("object",$object);
        $this->assertEquals("Test",$this->registry->get("object")->key);
    }
}
?>