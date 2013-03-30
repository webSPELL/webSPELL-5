<?php
class SingletonTest extends PHPUnit_Framework_TestCase
{

    public function testClone()
    {
      $notCloneable = new ReflectionClass('Registry');
      if(method_exists($notCloneable, "isCloneable")){
      	$this->assertEquals(false, $notCloneable->isCloneable());
      }
      else{
      	$this->assertEquals(true, $notCloneable->getMethod("__clone")->isPrivate());
      }
    }
}