<?php
class userPropertiesMapper extends \Spot\Entity {
     
    protected $_datasource = "userProperties";

    public static function fields()
    {
        return array(
           'userPropertyId'     => array('type' => 'int', 'primary' => true, 'serial' => true),
           'name'               => array('type' => 'string', 'required' => true),
           'required'           => array('type' => 'boolean', 'default' => false),
           'privacy'            => array('type' => 'int', 'default' => 0) //0 = guests, 1 = registered, 2 = myself 
        );
    }
}

?>