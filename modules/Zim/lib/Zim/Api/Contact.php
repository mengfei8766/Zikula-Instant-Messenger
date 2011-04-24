<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */
class Zim_Api_Contact extends Zikula_AbstractApi {

    /**
     * Get a single contact.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return   array   contact information.
     */
    function get_contact($uid) {
        //make sure the uid is set
        if (!isset($uid) || empty($uid)) {
            throw new Zim_Exception_UIDNotSet();
        }
        //get the table and select contact where uid is the same as supplied.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $uid;
        $contact = DBUtil::selectObject('zim_users', $where);
        
        //if no contact found return false
        if (!$contact || sizeof($contact) == 0) {
            throw new Zim_Exception_ContactNotFound();
        }
        
        //If the contacts username has never been set then get their username from zikula
        if (!isset($contact['uname']) || empty($contact['uname'])) {
            $contact['uname'] = UserUtil::getVar('uname', $uid);
        }
        
        //return the contact
        return $contact;
    }

    /**
     * Gets all the contacts. 
     */
    function get_all_contacts() {
        //get the table and select everything.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $contacts = DBUtil::selectObjectArray('zim_users');
        
        foreach ($contacts as $key => $contact) {
            //if the contacts username is not set then get it from Zikula
            if (!isset($contact['uname']) || empty($contact['uname'])) {
                $contacts[$key]['uname'] = UserUtil::getVar('uname', $contact['uid']);
            }
        }
        //return the array of contacts
        $this->timeout($contacts);
        //timeout the contacts but dont remove offline from returned array
        return $contacts;
    }

    /**
     * Get all of the online contacts only
     */
    function get_all_online_contacts() {
        //get the table and select all where status not offline or invis.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[status] !=0 && $column[status] !=3";
        $contacts = DBUtil::selectObjectArray('zim_users', $where);
        
        //go through each contact and check usernames, if not set then get from zikula.
        foreach ($contacts as $key => $contact) {
            if (!isset($contact['uname']) || empty($contact['uname'])) {
                $contacts[$key]['uname'] = UserUtil::getVar('uname', $contact['uid']);
            }
        }
        
        //return the array of contacts.
        return $this->timeout($contacts);
    }

    /**
     * Updates the status of a contact.
     *
     * @param $args['uid']    Intiger user id to update status for.
     * @param $args['status'] Intiger Status int, 0 offline, 1 online, 2 away, 3 invis.
     *
     * @return Intiger the status or boolean false if fail.
     */
    function update_contact_status($args) {
        //check the params to make sure everything is set
        if (!isset($args['uid']) || !$args['uid'])
            throw new Zim_Exception_UIDNotSet();
        if (!isset($args['status']))
            throw new Zim_Exception_StatusNotSet();
        
        //get the tables and prepare the where statment.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $args['uid'];
        
        //get the user matching $args['uid']
        $me = $this->get_contact($args['uid']);
        
        //get current date and time and set the update_on.
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $me['update_on'] = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
        $me['status'] = $args['status'];
        
        //user does exist so we update that user
        if (!DBUtil::updateObject($me, 'zim_users', $where)) {
            return false;
        }
        
        //return users status
        return $me;
    }
    
    function first_time_init($uid) {
    	try {
    		$me = $this->get_contact($uid);
    		return $me;
    	} catch (Zim_Exception_ContactNotFound $e) {
    		//if me doesnt exist then create me and get their uname from zikula
        	$me['uname'] = UserUtil::getVar('uname', $uid);
        	$me['uid'] = $uid;
        	$me['status'] = 1;
        	$nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        	$me['update_on'] = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
            
            //insert the new user
            if (!DBUtil::insertObject($me, 'zim_users')) {
                throw new Zim_Exception_CouldNotCreateUser;
            }
            
            //return the status of the user
            return $me;
        }
        throw new Zim_Exception_CouldNotCreateUser;
    }

    /**
     * Timeout function goes through all the users who are online and checks to
     * see if they have been inactive for too long, if so then it sets them offline.
     *
     */
    function timeout($contacts) {
        //get current datetime and format it.
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $now = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
        
        //go through removing each contact thats timed out.
        $contacts_to_timeout = Array();
        foreach ($contacts as $key => $contact) {
            $datediff = DateUtil::GetDatetimeDiff($contact['update_on'], $now);
            if ($datediff['d'] > 0 || $datediff['h'] > 0 || $datediff['m'] > 0 || $datediff['s'] > 30) {
                $contact['status]'] = 0;
                array_push($contacts_to_timeout, $contact);
                unset($contacts[$key]);
            }
        }
        DBUtil::updateObjectArray($contacts_to_timeout, 'zim_users', 'uid');
        return $contacts;
    }

    /**
     * Sets or updates a username.
     *
     * @param $args['uid']   Intiger User id of the user for whom to change the username.
     * @param $args['uname'] String  Nickname to change to.
     */
    function update_username($args) {
        //check input to make sure everything is set.
        if (!isset($args['uid']) || !$args['uid'])
            throw new Zim_Exception_UIDNotSet();
        
        //if uname not set then go to default from zikula.
        if (!isset($args['uname']) || empty($args['uname'])) {
            $args['uname'] = UserUtil::getVar('uname', $args['uid']);
        }
        
        //get the tables and construct the where argument
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $args['uid'];
        unset($args['uid']);
        
        //update the object
        if (!DBUtil::updateObject($args, 'zim_users', $where)) {
            throw new Zim_Exception_UsernameCouldNotBeUpdated();
        }
        
        //return the users uname
        return $args['uname'];
    }
    
    function keep_alive($uid) {
    	//get the tables and prepare the where statment.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $uid;
        
        //get the user matching $uid
        $me = $this->get_contact($uid);
        
        //get current date and time and set the update_on.
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $me['update_on'] = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
        
        //update the user
        if (!DBUtil::updateObject($me, 'zim_users', $where)) {
            return false;
        }
        //return users status
        return $me;
    }
}
