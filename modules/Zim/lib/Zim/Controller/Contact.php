<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */

class Zim_Controller_Contact extends Zikula_Controller
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
    
    public function update_status() {
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        
        $args['status'] = FormUtil::getPassedValue('status', 1);
        $args['uid'] = UserUtil::getVar('uid');
        ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
        return new Zikula_Response_Ajax(array());
    }
    
    public function get_online_contacts() {
        
        //security checks
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        
        //status update
        $args['status'] = FormUtil::getPassedValue('status', 1);
        $args['uid'] = UserUtil::getVar('uid');
        ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
        
        //get the contact list
        $show_offline = $this->getVar('show_offline');
        if ($show_offline) {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_contacts');
        } else {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_online_contacts');
        }
        
        foreach ($contacts as $key => $contact) {
            if ($contact['status'] == 3) {
                $contact[$key]['status'] = 0;
            }
        }
        
        $output['contacts'] = $contacts;
        
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }
    
    public function get_contact() {
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        $uid = FormUtil::getPassedValue('uid');
        if (!isset($uid) || empty($uid)) {
             throw new Zikula_Exception_Fatal();
        }
        $user = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        if ($user['status'] == 3) {
            $user['status'] = 0;
        }
        return new Zikula_Response_Ajax($user);
        
    }
    
    public function update_username() {
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        if (!SecurityUtil::checkPermission('Zim::', "::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null,true);
            throw new Zikula_Exception_Forbidden();
        }
        $args['uid'] = UserUtil::getVar('uid');
        $args['uname'] = FormUtil::getPassedValue('uname');
        $output = ModUtil::apiFunc('Zim', 'contact', 'update_username', $args);
        if (!$output)
        {
            throw new Zikula_Exception_Fatal();
        }
        return new Zikula_Response_Ajax($output);
    }
     
    
}
