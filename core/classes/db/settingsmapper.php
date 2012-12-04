<?php

class settingsMapper extends \Spot\Entity
{
    protected static $_datasource = 'settings';
    
    public static function fields() {
        return array(
                'settingId'       => array('type' => 'int', 'primary' => true, 'serial' => true),
                'name'            => array('type' => 'string', 'index' => true, 'required' => true),
                'value'           => array('type' => 'string')
        );
    }
}

?>