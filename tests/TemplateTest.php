<?php
class TemplateTest extends PHPUnit_Framework_TestCase
{

    public function testSetType()
    {
     $template = new Template();
     $this->setExpectedException('WebspellException');
     $template->setType(2);
    }

    public function testSetUnknownPath()
    {
     $template = new Template();
     $this->setExpectedException('WebspellException');
     $template->setBasePath('/asd/');
    }

}
?>
