<?php
abstract class CaptchaInterface {
    private $mysqli;
    private $registry;

    public function __construct() {
    }

    abstract function create();
    abstract function validate();
}
?>
