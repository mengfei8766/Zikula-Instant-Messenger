<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Model_Message extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('zim_message');
        $this->hasColumn('mid', 'integer', 16, array(
            'unique'  => true,
            'primary' => true,
            'notnull' => true,
            'autoincrement' => true
        ));
        
        $this->hasColumn('msg_to', 'integer', 16, array(
            'unique'  => false,
            'primary' => false,
            'notnull' => true
        ));
        
        $this->hasColumn('msg_from', 'integer', 16, array(
            'unique'  => false,
            'primary' => false,
            'notnull' => true
        ));
        
        $this->hasColumn('message', 'clob', array(
            'unique' => false,
            'primary'=> false,
            'notnull' => true,
            'default' => ''
        ));
        
        $this->hasColumn('recd', 'integer',2 , array(
            'unique' => false,
            'primary'=> false,
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('msg_to_deleted', 'integer',2 , array(
            'unique' => false,
            'primary'=> false,
            'notnull' => true,
            'default' => 0
        ));
        
        $this->hasColumn('msg_from_deleted', 'integer',2 , array(
            'unique' => false,
            'primary'=> false,
            'notnull' => true,
            'default' => 0
        ));
    }

    public function setUp()
    {
        $this->actAs('Timestampable');
        $this->hasOne('Zim_Model_User as to', array(
                'local' => 'msg_to',
                'foreign' => 'uid',
        ));
        
        $this->hasOne('Zim_Model_User as from', array(
                'local' => 'msg_from',
                'foreign' => 'uid',
        ));
    }
}