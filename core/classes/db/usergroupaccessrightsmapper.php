<?php
class userGroupAccessRightsMapper extends \Spot\Entity
{
    protected static $_datasource = 'userGroupAccessRights';

    public static function fields() {
        return array(
            'userGroupAccessRightsId'        => array('type' => 'int', 'primary' => true, 'serial' => true),
            'userGroupId'                    => array('type' => 'int', 'index' => true, 'required' => true),
            'userAccessRightsId'             => array('type' => 'int', 'index' => true, 'required' => true)
        );
    }

    public static function relations() {
        return array(
            'accessRights' => array(
                 'type'        => 'HasOne',
                 'entity'      => 'userAccessRightsMapper',
                 'where'       => array('userGroupId' => ':entity.userAccessRightsId')
            )
        );
    }
}

?>