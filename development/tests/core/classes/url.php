<?php
include('../../../../core/classes/pattern/singleton.php');
include('../../../../core/classes/registry.php');
Registry::getInstance()->set('modRewrite',true);
include('../../../../core/classes/webspellexception.php');
include('../../../../core/classes/url.php');

class UrlTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider testData
     */

    public function testAdd($input, $expectedUrl) {

        // check url generation
        $c = new Url();
        $url = $c->generateUrl($input);

        $this->assertEquals($url, $expectedUrl);

        // check url parsing
        $_SERVER['QUERY_STRING'] = substr($expectedUrl, 1);
        $c->parseQueryString();

        $inputParam = Array();
        $outputParam = Array();
        array_push($inputParam, Array('module'=>$input['module'], 'section'=>(isset($input['section']) ? $input['section'] : "default"), 'id'=>(isset($input['id']) ? $input['id'] : null), 'params'=>(isset($input['params']) ? $input['params'] : Array())));
        array_push($outputParam, Array('module'=>$c->getModule(), 'section'=>$c->getSection(), 'id'=>$c->getId(), 'params'=>$c->getModuleParams()));

        $this->assertEquals($inputParam, $outputParam);

    }

    public function testData() {

        return array(
            Array(Array('module'=>'user'), '/user.html'),
            Array(Array('module'=>'user', 'params'=> Array('desc', '5')), '/user,desc,5.html'),
            Array(Array('module'=>'user', 'id'=>'1234', 'title'=>'bluetiger', 'params'=> Array('desc', 'contact')), '/user/1234-bluetiger,desc,contact.html'),
            Array(Array('module'=>'user', 'section'=>'edit', 'id'=>'200', 'title'=>'editieren'), '/user/edit/200-editieren.html'),
            Array(Array('module'=>'user', 'section'=>'edit'), '/user/edit.html'),
            Array(Array('module'=>'user', 'section'=>'edit', 'params'=> Array('desc', '5')), '/user/edit,desc,5.html'),
            Array(Array('module'=>'forum', 'section'=>'board', 'id'=>'31', 'title'=>'allgemeiner support'), '/forum/board/31-allgemeiner_support.html'),
            Array(Array('module'=>'forum', 'section'=>'topic', 'id'=>'1233', 'title'=>'ich brauche hilfe!'), '/forum/topic/1233-ich_brauche_hilfe.html'),
            Array(Array('module'=>'forum', 'section'=>'topic', 'id'=>'1234', 'title'=>'Dies ist der Topictitel! -#äö+ü', 'params'=> Array('desc', '5')), '/forum/topic/1234-Dies_ist_der_Topictitel_aeoe_ue,desc,5.html')
        );

    }

}

?>
