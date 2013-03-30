<?php
namespace Captcha;
abstract class Base {
    private $mysqli;
    private $registry;

    public function __construct() {
    }

    abstract function create();
    abstract function validate();
}
?>
