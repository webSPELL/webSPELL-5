<?php
class userAccessRightsMapper extends \Spot\Entity
{
    protected static $_datasource = 'userAccessRights';

    public static function fields() {
        return array(
            'userAccessRightsId'        => array('type' => 'int', 'primary' => true, 'serial' => true),
            'module'                    => array('type' => 'string', 'required' => true),
            'name'                      => array('type' => 'string', 'required' => true)
        );
    }
}

?>