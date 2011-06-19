<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
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
        $this->uid = UserUtil::getVar('uid');
    }

    private $uid;

    /**
     * Update the users status.
     */
    public function update_status() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));

        //Get params from front end (ajax)
        $args['status'] = $this->request->getPost()->get('status');
        $args['uid'] = $this->uid;

        //call api function to update the status
        try {
            $me = ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
            return new Zikula_Response_Ajax($me);
        } catch (Zim_Exception_ContactNotFound $e) {
            return new Zim_Response_Ajax_Exception($e);
        } catch (Zim_Exception_UIDNotSet $e) {
            return new Zim_Response_Ajax_Exception($e);
        } catch (Zim_Exception_StatusNotSet $e) {
            return new Zim_Response_Ajax_Exception($e);
        }
    }

    /**
     * Get all of the online contacts.
     *
     */
    public function get_online_contacts() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));

        //keep the user from timing out
        try {
            ModUtil::apiFunc('Zim', 'contact', 'keep_alive', $this->uid);
        } catch (Zim_Exception_ContactNotFound $e) {
            return new Zim_Response_Ajax_Exception($e);
        } catch (Zim_Exception_UIDNotSet $e) {
            return new Zim_Response_Ajax_Exception($e);
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
            if ($contact['status'] == 3 || $contact['timedout'] == 1) {
                $contact[$key]['status'] = 0;
            }
            unset($contacts[$key]['timedout']);
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
        $uid = $this->request->getPost()->get('uid');

        //call api to get contact
        try {
            $user = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        } catch (Zim_Exception_ContactNotFound $e) {
            return new Zim_Response_Ajax_Exception($e);
        } catch (Zim_Exception_UIDNotSet $e) {
            return new Zim_Response_Ajax_Exception($e);
        }

        //rewrite invisible to offline.
        if ($user['status'] == 3 || $contact['timedout'] == 1) {
            $user['status'] = 0;
        }
        unset($user['timedout']);
        
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
        $args['uid'] = $this->uid;
        $args['uname'] = $this->request->getPost()->get('uname');

        //api function to change the username.
        try {
            $output = ModUtil::apiFunc('Zim', 'contact', 'update_username', $args);
        } catch (Zim_Exception_UsernameCouldNotBeUpdated $e) {
            return new Zim_Response_Ajax_Exception($e);
        } catch (UIDNotSet $e) {
            return new Zim_Response_Ajax_Exception($e);
        }

        //return JSON response
        return new Zikula_Response_Ajax($output);
    }
}