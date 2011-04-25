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
    	$q = Doctrine_Query::create()
    		->from('Zim_Model_User user')
    		->where('user.uid = ?', $uid);
    	$contact = $q->fetchOne();
    	if (empty($contact)) {
    		throw new Zim_Exception_ContactNotFound();
    	}
    	
    	$contact = $contact->toArray();
        
        //If the contacts username has never been set then get their username from zikula
        if (!isset($contact['uname']) || empty($contact['uname'])) {
            $contact['uname'] = UserUtil::getVar('uname', $uid);
            $contact->save();
        }
        
        //return the contact
        return $contact;
    }

    /**
     * Gets all the contacts. 
     */
    function get_all_contacts() {
    	$this->timeout();
    	
        //get the table and select everything.
        $task = Doctrine_Query::create()
    		->from('Zim_Model_User');
    	$exec = $task->execute();
    	$contacts = $exec->toArray();

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
    	
    	$this->timeout();
    	
        //get the table and select everything.
        $task = Doctrine_Query::create()
    		->from('Zim_Model_User user')
    		//->where('user.status !== 0')
    		->where('user.status != 0')
    		->andWhere('user.status != 3');
    	$exec = $task->execute();
    	$contacts = $exec->toArray();

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
     * Updates the status of a contact.
     *
     * @param $args['uid']    Intiger user id to update status for.
     * @param $args['status'] Intiger Status int, 0 offline, 1 online, 2 away, 3 invis.
     *
     * @return Intiger the status or boolean false if fail.
     */
    function update_contact_status($args) {
        //check the params to make sure everything is set
        if (!isset($args['uid']) || !$args['uid']) throw new Zim_Exception_UIDNotSet();
        if (!isset($args['status'])) throw new Zim_Exception_StatusNotSet();
        
        $q = Doctrine_Query::create()
    		->from('Zim_Model_User user')
    		->where('user.uid = ?', $args['uid']);
    	$contact = $q->fetchOne();
    	if (empty($contact)) throw new Zim_Exception_ContactNotFound();
        $contact['status'] = $args['status'];
        $contact->save();    
        
        //return contact
        return $contact->toArray();
    }
    
    function first_time_init() {
    	$q = Doctrine_Query::create()
    		->from('Zim_Model_User user')
    		->where('user.uid = ?', $this->uid);
    	$me = $q->fetchOne();
    	if (empty($me)) {
    		$me = new Zim_Model_User();
    		$me['status'] = 1;
    		$me->save();
    	}
        //return the status of the user
        return $me;
    }

    /**
     * Timeout function goes through all the users who are online and checks to
     * see if they have been inactive for too long, if so then it sets them offline.
     *
     */
    function timeout() {
    	//TODO this time comparison doesnt really work 
    	$q = Doctrine_Query::create()
    		->update('Zim_Model_User')
    		->set('status', 0)
    		->where('(NOW() - updated_at) > 30');
    	$q->execute();

        return;
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

    	$q = Doctrine_Query::create()
    		->from('Zim_Model_User user')
    		->where('user.uid = ?', $args['uid']);
    	$contact = $q->fetchOne();
    	if (empty($contact)) throw new Zim_Exception_ContactNotFound();
    	    
        $contact['uname'] = $args['uname'];
        $contact->save();
        //return the users uname
        return $contact->get('uname');
    }
    
    function keep_alive($uid) {
    	
    	$q = Doctrine_Query::create()
    		->from('Zim_Model_User user')
    		->where('user.uid = ?', $uid);
    	$me = $q->fetchOne();
        $me->keepAlive();
        $me->save();
        
        //return users status
        return $me->toArray();
    }
}
