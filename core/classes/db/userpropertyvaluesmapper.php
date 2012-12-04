<?php
class userPropertyValuesMapper extends \Spot\Entity {
     
    protected $_datasource = "userPropertyValues";

    public static function fields()
    {
        return array(
            'userPropertyValueId'    => array('type' => 'int', 'primary' => true, 'serial' => true),
            'userId'                 => array('type' => 'int', 'index' => true, 'required' => true),
            'propertyId'             => array('type' => 'int', 'index' => true, 'required' => true),
            'value'                  => array('type' => 'string'),
            'privacy'                => array('type' => 'int', 'default' => 0)
       );
   }
   public static function relations() {
        return array(
           'property' => array(
               'type'       => 'HasOne',
               'entity'     => 'userPropertiesMapper',
               'where'      => array('propertyId' => ':entity.userPropertyId')
            )
        );
    }
}
?>