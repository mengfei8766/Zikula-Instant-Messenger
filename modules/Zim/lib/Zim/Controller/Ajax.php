<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
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
        $this->me = array();
        //get users status
        try {
            $this->me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $this->uid);
        } catch (Zim_Exception_ContactNotFound $e) {
            $this->me = ModUtil::apiFunc('Zim', 'contact', 'first_time_init', $this->uid);
        }
    }

    private $uid;
    private $me;

    /**
     * The init function is called via an ajax call from the browser, it performs
     * all startup functions such as getting contact lists and messages/state.
     *
     */
    public function init() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));


        //see if the JS side requested a certain status, if not then get it from the DB
        $status = (int)$this->request->getPost()->get('status');
        if (!isset($status) || $status == '') {
            $status = (int)$this->me['status'];
        }
        //the user requested a new status in the init and its different from the DB
        //save the status update so it filters to all users
        if ((int)$status !== (int)$this->me['status']) {
            ModUtil::apiFunc('Zim', 'contact', 'update_contact_status',
            Array(    'status'=> $status,
                     'uid'    => $this->uid));
        }

        if ($this->me['timedout'] == '1') {
            try {
                ModUtil::apiFunc('Zim', 'contact', 'keep_alive', $this->uid);
            } catch (Zim_Exception_ContactNotFound $e) {
                return new Zim_Response_Ajax_Exception($e);
            } catch (Zim_Exception_UIDNotSet $e) {
                return new Zim_Response_Ajax_Exception($e);
            }
        }

        //get all contacts
        $show_offline = $this->getVar('show_offline');
        if ($show_offline) {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_contacts');
            foreach ($contacts as $key => $contact) {
                if ($contact['status'] == 3 || $contact['timedout'] == 1) {
                    $contacts[$key]['status'] = 0;
                }
            }
        } else {
            $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_online_contacts');
        }
        foreach ($contacts as $key => $contact) {
            unset($contacts[$key]['timedout']);
            unset($contacts[$key]['created_at']);
            unset($contacts[$key]['updated_at']);
        }

        //get templates for javascript
        $contact_template = $this->view->fetch('zim_block_contact.tpl');
        $message_template = $this->view->fetch('zim_block_message.tpl');
        $settingsmenu_template = $this->view->fetch('zim_block_settingsmenu.tpl');
        $sentmessage_template = $this->view->fetch('zim_block_sentmessage.tpl');
        $groupadd_template = $this->view->fetch('zim_block_groupadd.tpl');
        $group_template = $this->view->fetch('zim_block_group.tpl');

        //prepare output
        $output['status'] = $status;
        $output['my_uid'] = $this->uid;
        $output['my_uname'] = $this->me['uname'];
        $output['contacts'] = $contacts;
        $output['contact_template'] = $contact_template;
        $output['message_template'] = $message_template;
        $output['sentmessage_template'] = $sentmessage_template;
        $output['settingsmenu_template'] = $settingsmenu_template;
        if ($this->getVar('contact_groups')) {
            $output['groupadd_template'] = $groupadd_template;
            $output['group_template'] = $group_template;
        }

        //get global settings
        $output['settings']['execute_period'] = $this->getVar('message_check_period');
        $output['settings']['contact_update_freq'] = $this->getVar('contact_update_freq');
        $output['settings']['allow_offline_msg'] = $this->getVar('allow_offline_msg');

        //retreive state
        $state = ModUtil::apiFunc('Zim', 'state', 'get', $this->uid);
        foreach ($state['windows'] as $key => $window) {
            if ($window['status'] == 3 || $window['timedout'] == 1) {
                $state['windows'][$key]['status'] = 0;
            }
            unset($state['windows'][$key]['timedout']);
        }
        foreach ($state['messages'] as $key => $message) {
            unset($state['messages'][$key]['recd']);
            unset($state['messages'][$key]['msg_to_deleted']);
            unset($state['messages'][$key]['msg_from_deleted']);
            unset($state['messages'][$key]['from']['created_at']);
            unset($state['messages'][$key]['from']['updated_at']);
            unset($state['messages'][$key]['from']['timedout']);
            if ($state['messages'][$key]['from']['status'] == 3 || $state['messages'][$key]['from']['status'] == 1) {
                $state['messages'][$key]['from']['status'] = 0;
            }
        }

        if (isset($state)) {
            $output['state'] = $state;
        }

        $groups = ModUtil::apiFunc('Zim', 'group', 'get_all', array('uid' => $this->uid));
        $output['groups'] = $groups;

        //return the JSON output
        return new Zikula_Response_Ajax($output);
    }
}