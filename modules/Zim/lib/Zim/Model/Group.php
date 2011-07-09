<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Model_Group extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('zim_groups');

        $this->hasColumn('gid', 'integer', 16, array(
            'unique'  => true,
            'primary' => true,
            'notnull' => true,
            'autoincrement' => true,
        ));

        $this->hasColumn('uid',   'integer', 16, array(
            'notnull' => true,
        ));

        $this->hasColumn('groupname', 'string', 100, array(
           'notnull' => true,
           'default' => '',
        ));
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->hasOne('Zim_Model_User as owner', array(
            'local'      =>    'uid',
            'foreign'    =>    'uid',
        )
        );
        $this->hasMany('Zim_Model_user as members', array(
            'local'      =>    'gid',
            'foreign'    =>    'uid',
            'refclass'	 =>    'Zim_Model_UserGroup'
        )
        );
    }
}