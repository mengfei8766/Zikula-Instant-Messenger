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
        $state_window_add = array();
        $state_window_del = array();
        $state_windows = array();
        $state_window_add = $this->request->getPost()->get('state_add', array());
        $state_window_del = $this->request->getPost()->get('state_del', array());
        //$state_windows = $this->request->getPost()->get('state_windows', array());

        ModUtil::apiFunc('Zim', 'state', 'window_add',
        array(	'state_windows_add'	 => $state_window_add,
                'uid'        => $this->uid));
        ModUtil::apiFunc('Zim', 'state', 'window_del',
        array(	'state_windows_del'	 => $state_window_del,
                'uid'        => $this->uid));


        //get all new messages from database
        $args = array(  'to'    =>  $this->uid);
        $messages = ModUtil::apiFunc('Zim', 'message', 'getall', $args);

        ModUtil::apiFunc('Zim', 'state', 'message_set',
        array(	'state_messages_set' => $messages,
                'uid'                => $this->uid));

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
        $message['msg_to'] = $this->request->getPost()->get('to');
        if (!isset($message['msg_to']) || !$message['msg_to']) {
            throw new Zikula_Exception_BadData($this->__('Error! No recipient set.'));
        }

        //set from address to current users uid
        $message['msg_from'] = $this->uid;

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
            $contact = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $message['msg_to']);
            if ($contact['status'] != 0 && $contact['status'] != 3) {
                $message = ModUtil::apiFunc('Zim', 'message', 'send', $message);
                $output['status'] = 'ok';
            } else {
                return new Zikula_Response_Ajax_BadData($this->__('Error! Contact is offline. Offline messaging is disabled'));
            }
        }

        //add the message to the current state
        ModUtil::apiFunc('Zim', 'state', 'message_set',
        array('state_messages_set' => array($message)));

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
