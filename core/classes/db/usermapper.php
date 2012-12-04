<?php
class userMapper extends \Spot\Entity
{
    protected static $_datasource = 'users';

    public static function fields() {
        return array(
            'userId'        => array('type' => 'int', 'primary' => true, 'serial' => true),
            'email'         => array('type' => 'string', 'required' => true),
            'password'      => array('type' => 'text', 'required' => true),
            'registered'    => array('type' => 'datetime')
        );
    }

    public static function relations() {
        return array(
            // Each user entity 'hasMany' userProperty entites
            'properties' => array(
                 'type'        => 'HasMany',
                 'entity'      => 'userPropertyValuesMapper',
                 'where'       => array('userId' => ':entity.userId')
             )
		);
	}
}

?>