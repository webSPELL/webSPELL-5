<?php
class SingletonTest extends PHPUnit_Framework_TestCase
{

    public function testClone()
    {
      $notCloneable = new ReflectionClass('Registry');
      $this->assertEquals(false, $notCloneable->isCloneable());
    }
}
?>