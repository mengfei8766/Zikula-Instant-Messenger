<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */
 
class Zim_Api_Message extends Zikula_AbstractApi {

    /**
     * Get all messages for a user.
     *
     * @param intiger $args['to']   user for whom to get messages.
     * @param boolean $args['recd'] get already recieved messages or not.
     *
     * @return array list of messages.
     */
    function getall($args) {
        //make sure everything is set.
        if (!isset($args['to']) || !$args['to']) {
            return false;
        }
        if (!isset($args['recd'])) {
            $args['recd'] = false;
        }
        //get the table and select everything.
        $task = Doctrine_Query::create()
    		->from('Zim_Model_Message message')
    		->where('message.msg_to = ?', $args['to'])
    		->andWhere('message.recd != true')
    		->leftJoin('message.from uname')
    		->orderBy('message.created_at');
    	$messages = $task->execute();
        // Return the messages
        return $messages->toArray();
    }

    /**
     * get a particular message
     *
     * @param Integer mid Message id to get.
     *
     * @return Array list of messages.
     */
    function get_message($mid) {
        //make sure mid is set
        if (!isset($mid)) {
            return false;
        }
        
        $task = Doctrine_Query::create()
    		->from('Zim_Model_Message message')
    		->where('message.mid = ?', $mid)
    		->leftJoin('message.from from');
    	$message = $task->fetchOne();	
        
        //message is not found
        if (empty($message)) {
        	throw new Zim_Exception_MessageNotFound();
        }

        // Return the item
        return $message->toArray();
    }

    /**
     * Send a new message.
     *
     */
    function send($message) {
        //Check that everythings set.
        if (!isset($message))
            return false;
        if (!isset($message['from']) || !$message['from'])
            return false;
        if (!isset($message['to']) || !$message['to'])
            return false;
        if (!isset($message['message']) || !$message['message'])
            return false;
        if (!isset($message['recd']))
            $message['recd'] = 0;
        
        $msg = new Zim_Model_Message();
    	$msg['msg_to'] = $message['to'];
    	$msg['msg_from'] = $message['from'];
    	$msg['message'] = $message['message'];
    	$msg->save();    
         
        return $msg->toArray();
    }

    
    /**
     * Confirm receipt of message.
     *
     * @param Integer $args['id'] Message id to confirm.
     * @param Integer $args['to'] User id of recipient of message.
     */
    function confirm($args) {
    	//Check params
        if (!isset($args['id']) || $args['id'] == '')
            return false;
        if (!isset($args['to']) || $args['to'] == '')
            return false;
    	$q = Doctrine_Query::create()
    		->update('Zim_Model_Message message')
    		->set('message.recd', 1)
    		->whereIn('message.mid', $args['id'])
    		->andwhere('message.msg_to = ?', $args['to']);
    	$q->execute();
    	return;
    }

    /**
     * get an array of messages. This function only gets messages that are either to or from
     * the user specified in $args['to']
     *
     * @param Integer $args['id'] Message id's to get.
     * @param Integer $args['to'] User id of sender or recipient of message.
     *
     * @return Array A list of messages.
     */
    function getSelectedMessages($args) {
        //check arguments.
        if (!isset($args['uid']) || !$args['uid']) {
        	throw new Zim_Exception_UIDNotSet();
        }
        if (!isset($args['mid'])) {
            return false;
        }
		$task = Doctrine_Query::create()
    		->from('Zim_Model_Message message')
    		->where('message.msg_to = ?', $args['uid'])
    		->andWhereIn('message.mid',$args['mid'])
    		->orWhere('message.msg_from = ?', $args['uid'])
    		->andWhereIn('message.mid',$args['mid'])
    		->leftJoin('message.from from')
    		->orderBy('message.created_at');
    	$exec = $task->execute();
    	$messages = $exec->toArray();

        // Return the items
        return $messages;
    }

}
