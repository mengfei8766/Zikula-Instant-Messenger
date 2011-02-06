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

class Zim_Controller_Ajax extends Zikula_Controller
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
    
    public function init() {
        
        //security checks
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }
        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        
        //get users status
        $uid = UserUtil::getVar('uid');
        $me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        //TODO appear offline gets put to offline after this!
        //TODO Aslo does the js connect if status is offline?
        $status = $me['status'];
        $status = (!isset($status) || !is_numeric($status) || empty($status) || $status == '0') ? '1' : $status;
        
        //get all contacts
        $show_offline = $this->getVar('show_offline');
        if ($show_offline) {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_contacts');
        } else {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_online_contacts');
        }
        
        //get templates for javascript
        $contact_template = $this->view->fetch('zim_block_contact.tpl');
        $message_template = $this->view->fetch('zim_block_message.tpl');
        $sentmessage_template = $this->view->fetch('zim_block_sentmessage.tpl');
        
        //prepare output
        $output['status'] = $status;
        $output['my_uid'] = $uid;
        $output['my_uname'] = $me['uname'];
        $output['contacts'] = $contacts;
        $output['contact_template'] = $contact_template;
        $output['message_template'] = $message_template;
        $output['sentmessage_template'] = $sentmessage_template;
        
        //get global settings
        $output['settings']['show_offline']= $this->getVar('show_offline');
        $output['settings']['execute_period'] = $this->getVar('message_check_period');
        $output['settings']['contact_update_freq'] = $this->getVar('contact_update_freq');
        $output['settings']['allow_offline_msg'] = $this->getVar('allow_offline_msg');
        
        //retreive state
        $state = ModUtil::apiFunc('Zim', 'state', 'get');
        if (isset($state)) {
            $output['state'] = $state;   
        }
        
        //return the JSON output
        return new Zikula_Response_Ajax($output);
    }
    
    
    public function get_state(){
        $output = ModUtil::apiFunc('Zim', 'state', 'get', $args);
        return new Zikula_Response_Ajax($output);
    }
}
