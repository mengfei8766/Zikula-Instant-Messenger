<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */

class Zim_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
        // In this controller we never want caching.
        Zikula_AbstractController::configureView();
        $this->view->setCaching(false);
    	$this->uid = UserUtil::getVar('uid');
    }
    
    private $uid;
    
    /**
     * The init function is called via an ajax call from the browser, it performs
     * all startup functions such as getting contact lists and messages/state.
     *
     */
    public function init() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        //get users status
        $me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $this->uid);

        //see if the JS side requested a certain status, if not then get it from the DB
        $status = $this->request->getPost()->get('status', $me['status']);

    	//if status from the database and JS aren't set then make it 1 (probably first time)
        if (!isset($status) || !is_numeric($status) || (empty($status) && $status !== '0')) {
        	$status = '1';
        	//save the status update so it filters to all users
        	ModUtil::apiFunc('Zim', 'contact', 'update_contact_status',
         	Array(	'status'=> $status,
         			'uid'	=> $this->uid));
        } elseif ($status !== $me['status']) {
        	//the user requested a new status in the init and its different from the DB
        	//save the status update so it filters to all users
        	ModUtil::apiFunc('Zim', 'contact', 'update_contact_status',
         	Array(	'status'=> $status,
         			'uid'	=> $this->uid));
        }
		
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
        $output['my_uid'] = $this->uid;
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
    
    /**
     * Get the current state for the current user.
     * TODO: is this even used?
     */
    public function get_state(){
    	//security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        $output = ModUtil::apiFunc('Zim', 'state', 'get', $args);
        return new Zikula_Response_Ajax($output);
    }
}
