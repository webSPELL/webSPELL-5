<?php
class UserGroupsMapper extends \Spot\Entity {

    protected static $_datasource = 'userGroups';
    
    public static function fields() {
        return array(
                'userGroupId'       => array('type' => 'int', 'primary' => true, 'serial' => true),
                'name'              => array('type' => 'string')
        );
    }
    
    public static function relations() {
        return array(
                'groupAccessRights' => array(
                     'type'        => 'HasOne',
                     'entity'      => 'userGroupsAccessRightsMapper',
                     'where'       => array('userGroupId' => ':entity.userGroupId')
        )
        );
    }
}
?>
