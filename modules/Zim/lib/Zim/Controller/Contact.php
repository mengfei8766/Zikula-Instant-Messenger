<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */

class Zim_Controller_Contact extends Zikula_Controller_AbstractAjax
{ 
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
    }
    
    /**
     * Update the users status.
     */
    public function update_status() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //Get params from front end (ajax)
        $args['status'] = FormUtil::getPassedValue('status', 1);
        $args['uid'] = UserUtil::getVar('uid');
        
        //call api function to update the status
        $me = ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
        if(!isset($me) || !$me || empty($me)) {
        	throw new Zikula_Exception_Fatal();
        }
        return new Zikula_Response_Ajax($me);
    }
    
    /**
     * Get all of the online contacts.
     *
     */
    public function get_online_contacts() {
		//security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //perform status update if supplied
        $status = FormUtil::getPassedValue('status');
        if (isset($status) && is_numeric($status)) {
            $args['status'] = $status;
            $args['uid'] = UserUtil::getVar('uid');
            ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
        } else {
        	ModUtil::apiFunc('Zim', 'contact', 'keep_alive', UserUtil::getVar('uid'));
        	//if status update not performed we still need to update when we last heard from this user.
        }
        
        //get the contact list
        $show_offline = $this->getVar('show_offline');
        if ($show_offline) {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_contacts');
        } else {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_online_contacts');
        }
        
        //go through each contact making sure that any invisible contact is shown as offline
        foreach ($contacts as $key => $contact) {
            if ($contact['status'] == 3) {
                $contact[$key]['status'] = 0;
            }
        }
        
        $output['contacts'] = $contacts;
        
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }
    
    /**
     * Get a specific contact.
     *
     */
    public function get_contact() {
		//security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //get the uid to pull up contact information for
        $uid = FormUtil::getPassedValue('uid');
        if (!isset($uid) || empty($uid)) {
             throw new Zikula_Exception_Fatal();
        }
        
        //call api to get contact
        $user = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        
        //rewrite invisible to offline.
        if ($user['status'] == 3) {
            $user['status'] = 0;
        }
        
        //return json response.
        return new Zikula_Response_Ajax($user);
        
    }
    
    /**
     * Update users username.
     */
    public function update_username() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //get required information.
        $args['uid'] = UserUtil::getVar('uid');
        $args['uname'] = FormUtil::getPassedValue('uname');
        
        //api function to change the user name.
        $output = ModUtil::apiFunc('Zim', 'contact', 'update_username', $args);
        if (!$output) {
            throw new Zikula_Exception_Fatal();
        }
        
        //return JSON response
        return new Zikula_Response_Ajax($output);
    }
     
    
}
