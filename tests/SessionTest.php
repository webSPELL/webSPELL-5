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
	
	public function testShouldReturnValueWhichIsStoredBefore(){
		$key = 'foo';
		$value = 'bar';
		$this->session->set($key, $value);
		$this->assertEquals($value, $this->session->get($key));
	}
	
	public function testShouldNotReturnValueWhenSessionIsDestroyed(){
		$key = 'foo';
		$value = 'bar';
		$this->session->set($key, $value);
		$this->session->destroy();
		$this->assertEquals(null, $this->session->get($key));
	}
}

?>