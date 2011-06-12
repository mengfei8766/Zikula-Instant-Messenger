<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
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

        //return the contact
        return $contact;
    }

    /**
     * Gets all the contacts.
     */
    function get_all_contacts() {
        $this->timeout();
         
        //get all the users
        $task = Doctrine_Query::create()
        ->from('Zim_Model_User');
        $exec = $task->execute();
        $contacts = $exec->toArray();
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
        ->update('Zim_Model_User user')
        ->set('user.status',"?", $args['status'])
        ->where('user.uid = ?', $args['uid']);
        $q->execute();
        
        $q = Doctrine_Query::create()
        ->from('Zim_Model_User user')
        ->where('user.uid = ?', $args['uid'])
        ->limit(1);
        $contact = $q->fetchOne();
        
        //return contact
        return $contact->toArray();
    }

    /**
     * Initializes user for first time use.
     */
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
     */
    function timeout() {
        //TODO this time comparison doesnt really work
        $q = Doctrine_Query::create()
        ->update('Zim_Model_User')
        ->set('status',"?", 0)
        ->where('(NOW() - updated_at) > ?', $this->getVar('timeout_period', 30));
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
        ->update('Zim_Model_User')
        ->set('uname', "?", $args['uname'])
        ->where('uid = ?', $args['uid']);
        $result = $q->execute();
        
        $q = Doctrine_Query::create()
        ->from('Zim_Model_User user')
        ->where('user.uid = ?', $args['uid'])
        ->limit(1);
        $contact = $q->fetchOne();
        $contact = $contact->get('uname');
        
        //return the users uname
        return $contact;
    }

    /**
     * Keep a user from timing out.
     * 
     * @param $uid Intiger User ID to keep alive.
     */
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
