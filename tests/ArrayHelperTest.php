<?php
class ArrayHelperTest extends PHPUnit_Framework_TestCase
{

    public function testAssocArray()
    {
      $array = array();
      $array["test"]="Test";
      $array["key"]="Value";
      $this->assertEquals(true, ArrayHelper::isAssoc($array));
    }

    public function testIndexedArray()
    {
      $array = array();
      $array[0]="Test";
      $array[1]="Value";
      $array[2]="Value";
      $this->assertEquals(false, ArrayHelper::isAssoc($array));
    }

    public function testEmptyArray(){
    	$array = array();
    	$this->assertEquals(true, ArrayHelper::isAssoc($array));
    }
}
?>
