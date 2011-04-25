<?php


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
        
        $this->hasColumn('status', 'integer',  2, array(
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
        	'local'		=>	'uid',
        	'foreign'	=>	'msg_from',
        	)
        );
        $this->hasMany('Zim_Model_Message as RecdMessages', array(
        	'local'		=>	'uid',
        	'foreign'	=>	'msg_to',
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
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $this->updated_at = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
    }
}