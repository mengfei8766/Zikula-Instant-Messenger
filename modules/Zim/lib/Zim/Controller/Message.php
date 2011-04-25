<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 * 
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Controller_Message extends Zikula_Controller_AbstractAjax
{
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
    	$this->uid = UserUtil::getVar('uid');
    }
    
    private $uid;
    
    /**
     * Get all of the messages for a user.
     *
     * @return JSON Ajax response, array of messages.
     */
    public function get_all_messages() {
       //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
       //get all messages from database
       $args = array('to' =>  $this->uid, 'recd' => true);
       $messages = ModUtil::apiFunc('Zim', 'message', 'getAll', $args);
       $output['messages'] = $messages;
       return new Zikula_Response_Ajax($output);
    }
    
    /**
     * Get all new messages for the user.
     *
     * @return JSON Ajax array of all new messages.
     */
    public function get_new_messages() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //confirm old messages
        $mid = $this->request->getPost()->get('confirm');
        ModUtil::apiFunc('Zim', 'message', 'confirm', array('id' => $mid, 'to' => $this->uid));
        
        //check for state updates (windows opened or closed)
        $state_add = $this->request->getPost()->get('state_add');
        $state_del = $this->request->getPost()->get('state_del');
        $state = array();
        if (isset($state_add) && !empty($state_add) && is_array($state_add)) {
            foreach ($state_add as $add) { 
                $s = array('method' => 'add', 'type' => 'window', 'data' => $add);
                array_push($state, $s);
            }
        }
        if (isset($state_del) && !empty($state_del) && is_array($state_del)) {
            foreach ($state_del as $del) { 
                $s = array('method' => 'del', 'type' => 'window', 'data' => $del);
                array_push($state, $s);
            }
        }
        
        //get all new messages from database
        $args = array(  'to'    =>  $this->uid);
        $messages = ModUtil::apiFunc('Zim', 'message', 'getAll', $args);
        
        //add state information to messages
        foreach ($messages as $message) {
            $s = array('method' => 'add', 'type' => 'message', 'data' => $message['mid'], 'uid' => $message['from']);
            array_push($state, $s);
        }
        
        //update the state in the session
        if (!empty($state)) {
            ModUtil::apiFunc('Zim', 'state', 'update', $state);
        }
        
        //return the new messages
        $output['messages'] = $messages;
        return new Zikula_Response_Ajax($output);
    }
    
    /**
     * Send a new message to user.
     *
     * @return output with a status message or zikula ajax exception.
     */
    public function send_new_message($args) {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //make sure the 'to' user id is set
        $message['to'] = $this->request->getPost()->get('to');
        if (!isset($message['to']) || !$message['to']) {
            throw new Zikula_Exception_BadData($this->__('Error! No recipient set.'));
        }
        
        //set from address to current users uid
        $message['from'] = $this->uid;
        
        //get and make sure the message is set
        $message['message'] = $this->request->getPost()->get('message');
        if (!isset($message['message']) || !is_string($message['message'])) {
            throw new Zikula_Exception_BadData($this->__('Error! Malformed Message'));
        }
        $message['message'] = strip_tags($message['message'], $this->getVar('allowed_msg_tags'));
        
        //send the message
        $allow_offline_msg = $this->getVar('allow_offline_msg');
        if ($allow_offline_msg) {
            $message = ModUtil::apiFunc('Zim', 'message', 'send', $message);
            $output['status'] = 'ok';
        } else {
            $contact = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $message['to']);
            if ($contact['status'] != 0 && $contact['status'] != 3) {
                $message = ModUtil::apiFunc('Zim', 'message', 'send', $message);
                $output['status'] = 'ok';
            } else {
                return new Zikula_Response_Ajax_BadData($this->__('Error! Contact is offline. Offline messaging is disabled'));
            }
        }
        
        //add the message to the current state
        $state = array(array(
            'method' => 'add', 
            'type' => 'message',
            'data' => $message['mid'],
            'uid' => $message['to']));  
        ModUtil::apiFunc('Zim', 'state', 'update', $state);
        
        //return the JSON output.
        return new Zikula_Response_Ajax($output);
    }
    
    /**
     * Confirm reciept of message so that Zim can stop sending it.
     * 
     */
    public function confirm_message($args) {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //message id to confirm
        $mid = $this->request->getPost()->get('mid');
        
        //current user - this is used in the api to make sure current user
        //is allowed to confirm the message
        
        //confirm the message(s) as recd.
        if (is_array($mid)) {
            foreach($mid as $id) {
                ModUtil::apiFunc('Zim', 'message', 'confirm', array('id' => $id, 'to' => $this->uid));
            }
            return new Zikula_Response_Ajax(array());
        }
        ModUtil::apiFunc('Zim', 'message', 'confirm', array('id' => $mid, 'to' => $this->uid));
        return new Zikula_Response_Ajax(array());
    }
}
