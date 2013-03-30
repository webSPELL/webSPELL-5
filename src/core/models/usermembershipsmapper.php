<?php
class UserMembershipsMapper extends \Spot\Entity {

    protected static $_datasource = 'userMemberships';

    public static function fields() {
        return array(
            'userMembershipId'        => array('type' => 'int', 'primary' => true, 'serial' => true),
            'userId'                  => array('type' => 'int', 'index' => true, 'required' => true),
            'groupId'                 => array('type' => 'int', 'index' => true, 'required' => true)
        );
    }

    public static function relations() {
        return array(
            'group' => array(
                 'type'        => 'HasOne',
                 'entity'      => 'userGroupMapper',
                 'where'       => array('userGroupId' => ':entity.groupId')
            )
        );
    }
}
?>
