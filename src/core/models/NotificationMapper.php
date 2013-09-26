<?php
class NotificationMapper extends \Spot\Entity {

    protected static $_datasource = 'notifications';
    
    public static function fields() {
        return array(
                'notificationId'       => array('type' => 'int', 'primary' => true, 'serial' => true),
                'content'              => array('type' => 'string'),
                'fromModule'           => array('type' => 'string'),
                'toUser'               => array('type' => 'int', 'index' => true),
                'read'                 => array('type' => 'boolean', 'default' => false),
                'date'                 => array('type' => 'datetime')
        );
    }
}
?>
