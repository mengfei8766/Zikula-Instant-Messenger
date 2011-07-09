<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Model_UserGroup extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('zim_usergroups');

        $this->hasColumn('uid',   'integer', 16, array(
            'primary' => true,
        ));
        $this->hasColumn('gid',   'integer', 16, array(
            'primary' => true,
        ));
    }
}