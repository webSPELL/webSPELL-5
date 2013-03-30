<?php
require('./src/core/classes/Session.php');
class SessionTest extends PHPUnit_Framework_TestCase{
	
	private $session;
	
	public function setUp(){
		$this->session = new Session();
	}
	
	public function testShouldFailIfKeyParameterAtSetMethodIsNull(){
		$this->setExpectedException('WebspellException');
		$this->session->set(null);
	}
	
	public function testShouldReturnNullWithUnknownKey(){
		$value = $this->session->get("unknownKey");
		$this->assertEquals(null, $value);
	}
}

?>