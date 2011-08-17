<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Controller_History extends Zikula_Controller_AbstractAjax
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
        try {
            $this->me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $this->uid);
        } catch (Zim_Exception_ContactNotFound $e) {
            return new Zim_Response_Ajax_Exception(null,'Error: You do not exist.');
        }
    }

    private $uid;
    private $me;

    /**
     * Gets the history window template, including a list of contacts with a history.
     *
     */
    public function get_template() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));

        //get list of contacts for this user with whom the user has a history.
        $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_contacts_having_history', $this->uid);

        //remove myself if i am in the list.
        //TODO: there should be checks to make sure self messaging can't happen.
        foreach ($contacts as $key => $contact) {
            if ($contact['uid'] == $this->uid) unset($contacts[$key]);
        }

        //fetch the template and return it.
        $obj = array('contacts' => $contacts);
        $this->view->assign($obj);
        $output['template'] = $this->view->fetch('zim_block_history.tpl');
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Get the history with a particular contact.
     */
    public function get_history() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));

        //get the contact the user wants the history for.
        $uid = (int)$this->request->getPost()->get('contact');

        //get the message history between the user and the contact.
        $messages = ModUtil::apiFunc('Zim', 'history', 'get_history', array('user1' => $this->uid, 'user2' => $uid));

        //get the template and return it to the user.
        $this->view->assign(array('contact'  => $uid));
        $download = $this->view->fetch('zim_block_history_download.tpl');
        $this->view->assign(array('messages' => $messages,
                                  'download'  => $download));
        $output['template'] = $this->view->fetch('zim_block_history_messages.tpl');
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Delete the message history between the user and a particular contact.
     */
    public function delete() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));

        //get the contact which the user wishes to delete history of.
        $user = (int)$this->request->getPost()->get('uid');

        //delete the history, note that this only deletes the history from the users point of view,
        //the other user will still see the history until they delete it.
        $delete = ModUtil::apiFunc('Zim', 'history', 'delete', array('user' => $user, 'uid' => $this->uid));

        //return a blank response
        $output = array();
        return new Zikula_Response_Ajax($output);
    }
}