<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Model_User extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('zim_users');
        $this->hasColumn('uid',   'integer', 16, array(
            'unique'  => true,
            'primary' => true,
            'notnull' => true,
            'autoincrement' => false,
        ));

        $this->hasColumn('status', 'integer',  16, array(
           'notnull' => true,
           'default' => 0,
        ));
        $this->hasColumn('timedout', 'integer',  16, array(
           'notnull' => true,
           'default' => 0,
        ));

        $this->hasColumn('uname', 'string', 100, array(
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
        $this->actAs('Timestampable');
        $this->hasMany('Zim_Model_Message as SentMessages', array(
            'local'        =>    'uid',
            'foreign'    =>    'msg_from',
        )
        );
        $this->hasMany('Zim_Model_State as State', array(
            'local'        =>    'uid',
            'foreign'    =>    'uid',
        )
        );
        $this->hasMany('Zim_Model_Message as RecdMessages', array(
            'local'        =>    'uid',
            'foreign'    =>    'msg_to',
        )
        );
    }


    public function preInsert($event) {
        return $this->preSave($event);
    }

    public function preSave($event) {
        if (!isset($this['uid']) || empty($this['uid'])) {
            $uid = UserUtil::getVar('uid');
            if (isset($uid) && is_int((int)$uid)) {
                $q = Doctrine_Query::create()
                ->from('Zim_Model_User user')
                ->where('user.uid = ?', $uid);
                $user= $q->fetchOne();
                if (empty($user)) {
                    $this['uid'] = $uid;
                } else {
                    $event->skipOperation = true;
                }
            }
        }
        if (!isset($this['uname']) || $this['uname'] == '') {
            $this['uname'] = UserUtil::getVar('uname', $this['uid']);
        }
    }

    public function keepAlive()
    {
        // WILL be saved in the database
        $now = date('Y-m-d H:i:s', time());
        $this->updated_at = $now;
        $this->timedout = '0';
    }
}