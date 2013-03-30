<?php

class logMapper extends \Spot\Entity
{
    protected static $_datasource = 'log';
    
    public static function fields() {
        return array(
                'logId'       => array('type' => 'int', 'primary' => true, 'serial' => true),
                'userId'      => array('type' => 'int', 'index' => true),
                'ipAddress'   => array('type' => 'string'),
                'query'       => array('type' => 'text'),
                'executed'    => array('type' => 'datetime')
        );
    }
}

?>