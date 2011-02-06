<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Zim_Controller_Message extends Zikula_Controller
{
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
        // In this controller we never want caching.
        $this->view->setCaching(false);
    }
    
    /**
     * Get all of the messages for a user.
     *
     * @return JSON Ajax response, array of messages.
     */
    public function get_all_messages() {
       //security checks
       if (!SecurityUtil::confirmAuthKey()) {
           LogUtil::registerAuthidError();
           throw new Zikula_Exception_Fatal();
       }
       if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
           LogUtil::registerPermissionError(null,true);
           throw new Zikula_Exception_Forbidden();
       }
       
       //check and update status
       $status = FormUtil::getPassedValue('status');
       $uid = UserUtil::getVar('uid');
       if (isset($status) && is_int($status)) {
           $args2['status'] = $status;
           $args2['uid'] = $uid;
           ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args2);
       }
       
       //get all messages from database
       $args = array('to' =>  $uid, 'recd' => true);
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
        //perform security checks
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        
        //Check and update status
        $status = FormUtil::getPassedValue('status');
        $uid = UserUtil::getVar('uid');
        if (isset($status) && is_int($status)) {
            $args2['status'] = $status;
            $args2['uid'] = $uid;
            ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args2);
        }
        
        //confirm old messages
        $mid = FormUtil::getPassedValue('confirm');
        if (isset($mid) && is_array($mid)) {
            foreach($mid as $id) {
                ModUtil::apiFunc('Zim', 'message', 'confirm', array('id' => $id, 'to' => $uid));
            }
        }
        
        //check for state updates (windows opened or closed)
        $state_add = FormUtil::getPassedValue('state_add');
        $state_del = FormUtil::getPassedValue('state_del');
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
        $args = array(  'to'    =>  $uid);
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
    
    public function send_new_message($args) {
        
        //security check
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }
        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        
        //check and update user status
        $status = FormUtil::getPassedValue('status');
        $uid = UserUtil::getVar('uid');
        if (isset($status) && is_int($status)) {
            $args['status'] = $status;
            $args['uid'] = $uid;
            ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
        }
        
        //make sure the 'to' user id is set
        $message['to'] = FormUtil::getPassedValue('to');
        if (!isset($message['to']) || !$message['to']) {
            throw new Zikula_Exception_Fatal($this->__('Error! No recipient set.'));
        }
        
        //set from address to current users uid
        $message['from'] = $uid;
        
        //get and make sure the message is set
        $message['message'] = FormUtil::getPassedValue('message');
        if (!isset($message['message']) || !is_string($message['message'])) {
            throw new Zikula_Exception_Fatal($this->__('Error! Malformed Message'));
        }
        $message['message'] = strip_tags($message['message'], '<b><u>');
        
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
                //TODO what kind of exception?
                throw new Zikula_Exception_Fatal($this->__('Error! Contact is offline. Offline messaging is disabled'));
            }
        }
        
        //add the message to the current state
        $state = array(array(
            'method' => 'add', 
            'type' => 'message',
            'data' => $message['mid'],
            'uid' => $message['to']));  
        ModUtil::apiFunc('Zim', 'state', 'update', $state);
        
        return new Zikula_Response_Ajax($output);
        
    }
    
    public function confirm_message($args) {
        
        //security check
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }
        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        
        //message id to confirm
        $mid = FormUtil::getPassedValue('mid');
        
        //current user - this is used in the api to make sure current user
        //is allowed to confirm the message
        $uid = UserUtil::getVar('uid');
        
        //confirm the message(s) as recd.
        if (is_array($mid)) {
            foreach($mid as $id) {
                ModUtil::apiFunc('Zim', 'message', 'confirm', array('id' => $id, 'to' => $uid));
            }
            return new Zikula_Response_Ajax(array());
        }
        ModUtil::apiFunc('Zim', 'message', 'confirm', array('id' => $mid, 'to' => $uid));
        return new Zikula_Response_Ajax(array());
    }
}
