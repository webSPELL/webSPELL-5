<?php
abstract class Captcha_Interface {
    private $mysqli;
    private $registry;

    public function __construct() {
    }

    abstract function create();
    abstract function validate();
}
?>
