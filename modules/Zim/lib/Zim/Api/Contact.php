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
		->where('user.uid = ?', $uid)
		->limit(1);
		$contact = $q->fetchOne();
		if (empty($contact)) {
			throw new Zim_Exception_ContactNotFound();
		}
		$contact = $contact->toArray();
		if (!isset($contact['uname']) || trim($contact['uname']) == ''){
			$contact['uname'] = $this->update_username(array('uid' => $contact['uid']));
		}

		//return the contact
		return $contact;
	}

	/**
	 * Gets all the contacts.
	 *
	 * @return Array Every contact in Zim's user table (every user who has ever used zim).
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
	 * Get all the contacts having history with some user.
	 * This is primarily used to determine a list of contacts for which a history lookup will have messages.
	 *
	 * @param $uid Integer The user to find contacts for.
	 *
	 * @return Array All the contacts for which the user has history.
	 */
	function get_all_contacts_having_history($uid) {
		if (!isset($uid) || $uid == '') {
			throw new Zim_Exception_UIDNotSet();
		}
		$task = Doctrine_Query::create()
		->from('Zim_Model_User user')
		->where('(SELECT COUNT(m1.mid) FROM Zim_Model_Message m1 WHERE m1.msg_from = user.uid AND m1.msg_to = ? AND m1.msg_to_deleted != 1 GROUP BY m1.msg_from) > 0',$uid)
		->orWhere(' (SELECT COUNT(m2.mid) FROM Zim_Model_Message m2 WHERE m2.msg_to = user.uid AND m2.msg_from = ? AND m2.msg_from_deleted != 1 GROUP BY m2.msg_to)   > 0', $uid)
		->orWhere('(SELECT COUNT(m3.mid) FROM Zim_Model_HistoricalMessage m3 WHERE m3.msg_from = user.uid AND m3.msg_to = ? AND m3.msg_to_deleted != 1 GROUP BY m3.msg_from) > 0',$uid)
		->orWhere(' (SELECT COUNT(m4.mid) FROM Zim_Model_HistoricalMessage m4 WHERE m4.msg_to = user.uid AND m4.msg_from = ? AND m4.msg_from_deleted != 1 GROUP BY m4.msg_to)   > 0', $uid);
		$results = $task->execute();
		$results = $results->toArray();
		return $results;
	}

	/**
	 * Get all of the online contacts only
	 */
	function get_all_online_contacts() {
		 
		$this->timeout();
		 
		//get the table and select everything.
		$task = Doctrine_Query::create()
		->from('Zim_Model_User user')
		->where('user.status != 0')
		->andWhere('user.status != 3')
		->andWhere('user.timedout != 1');
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
		//TODO: check to make sure valid status.

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
	 *
	 * @return Array newly created user.
	 */
	function first_time_init($uid) {
		$q = Doctrine_Query::create()
		->from('Zim_Model_User user')
		->where('user.uid = ?', $uid)
		->limit(1);
		$me = $q->fetchOne();
		if (empty($me)) {
			$me = new Zim_Model_User();
			$me['status'] = 1;
			$me->save();
		}
		//return the user
		return $me;
	}

	/**
	 * Timeout function goes through all the users who are online and checks to
	 * see if they have been inactive for too long, if so then it sets them offline.
	 */
	function timeout() {
		$q = Doctrine_Query::create()
		->update('Zim_Model_User u')
		->set('u.timedout', '?', '1')
		->where("u.updated_at <= ?", date('Y-m-d H:i:s', time()- $this->getVar('timeout_period', 30)));
		$q->execute();
		return;
	}

	/**
	 * Sets or updates a username.
	 *
	 * @param $args['uid']   Intiger User id of the user for whom to change the username.
	 * @param $args['uname'] String  Nickname to change to.
	 *
	 * @return The updated contact.
	 */
	function update_username($args) {
		//check input to make sure everything is set.
		if (!isset($args['uid']) || !$args['uid'])
		throw new Zim_Exception_UIDNotSet();
		if (!isset($args['uname']) || empty($args['uname']) || trim($args['uname']) == '')
		$args['uname'] = UserUtil::getVar('uname', $args['uid']);
		$args['uname'] = trim($args['uname']);
		$q = Doctrine_Query::create()
		->update('Zim_Model_User')
		->set('uname', "?", $args['uname'])
		->where('uid = ?', $args['uid']);
		$result = $q->execute();
		if (!isset($result) || $result == 0) {
			throw new Zim_Exception_UsernameCouldNotBeUpdated();
		}

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
		if(!isset($uid) || empty($uid)) {
			throw new Zim_Exception_UIDNotSet();
		}
		$q = Doctrine_Query::create()
		->from('Zim_Model_User user')
		->where('user.uid = ?', $uid)
		->limit(1);
		$me = $q->fetchOne();
		if (sizeof($me) == 0) {
			throw new Zim_Exception_ContactNotFound();
		}
		$me->keepAlive();
		$me->save();

		//return users status
		return $me->toArray();
	}
}
