<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */
 //TODO: all instances of rewriting status 3 to status 0 (invis show as offline) should be taken out and moved to controllers.
class Zim_Api_Contact extends Zikula_AbstractApi {

    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return   array   indexed array of items.
     */
    function get_contact($uid) {
        //make sure the uid is set
        if (!isset($uid)) {
            return false;
        }
        //get the table and select contact where uid is the same as supplied.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $uid;
        $contact = DBUtil::selectObject('zim_users', $where);
        
        //if no contact found return false
        //TODO: maybe this should return empty as false is for an error
        if (!$contact) {
            return false;
        }
        //TODO: whats the point of this? does an empty array eval to false above?
        if (sizeof($contact) == 0)
            return $contact;
        
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
        return $contacts;
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
        //perform timeout checks/updates
        $this->timeout();
        
        //check the params to make sure everything is set
        if (!isset($args))
            return false;
        if (!isset($args['uid']) || !$args['uid'])
            return false;
        if (!isset($args['status']))
            return false;
        
        //get the tables and prepare the where statment.
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $args['uid'];
        
        //get the user matching $args['uid']
        $me = $this->get_contact($args['uid']);
        
        //get current date and time and set the update_on.
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $args['update_on'] = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
        
        //if me doesnt exist then create me and get their uname from zikula
        if (!isset($me) || sizeof($me) == 0 || empty($me) || $me === false) {
            if ((!isset($args['uname']) || empty($args['uname']))) {
                $me['uname'] = UserUtil::getVar('uname', $args['uid']);
            } else {
                $me['uname'] = $args['uname'];
            }
            if ((!isset($me['uid']) || empty($me['uid']))) {
                $me['uid'] = $args['uid'];
            }
            $me['status'] = $args['status'];
            $me['update_on'] = $args['update_on'];
            
            //insert the new user
            if (!DBUtil::insertObject($me, 'zim_users')) {
                return false;
            }
            
            //return the status of the user
            return $me;
        }
        
        $me['update_on'] = $args['update_on'];
        $me['status'] = $args['status'];
        //user does exist so we update that user
        if (!DBUtil::updateObject($me, 'zim_users', $where)) {
            return false;
        }
        
        //return users status
        return $me;
    }

    /**
     * Timeout function goes through all the users who are online and checks to
     * see if they have been inactive for too long, if so then it sets them offline.
     *
     */
    function timeout() {
        //get current datetime and format it.
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $now = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
        
        //get the table
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        
        //get all online contacts
        $contacts = $this->get_all_online_contacts();
        
        //TODO: this can be optimized to compile a list of objects to update and  only execute one DB call.
        //go through removing each contact thats timed out.
        foreach ($contacts as $contact) {
            $datediff = DateUtil::GetDatetimeDiff($contact['update_on'], $now);
            if ($datediff['d'] > 0 || $datediff['h'] > 0 || $datediff['m'] > 0 || $datediff['s'] > 30) {
                $contact['status'] = 0;
                $where = "$column[uid] = $contact[uid]";
                DBUtil::updateObject($contact, 'zim_users', $where);
            }
        }
    }

    /**
     * Sets or updates a username.
     *
     * @param $args['uid']   Intiger User id of the user for whom to change the username.
     * @param $args['uname'] String  Nickname to change to.
     */
    function update_username($args) {
        //check input to make sure everything is set.
        if (!isset($args['uid']) || empty($args['uid']))
            return false;
        
        //if uname not set then go to default from zikula.
        if (!isset($args['uname']) || empty($args['uname'])) {
            $args['uname'] = UserUtil::getVar('uname', $args['uid']);
        }
        
        //get the tables and construct the where argument
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $args['uid'];
        
        //TODO is this unset needed?
        unset($args['uid']);
        
        //update the object
        if (!DBUtil::updateObject($args, 'zim_users', $where)) {
            return false;
        }
        
        //return the users uname
        return $args['uname'];
    }

}
