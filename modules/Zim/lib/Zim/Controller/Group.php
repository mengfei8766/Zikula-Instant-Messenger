<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Controller_Group extends Zikula_Controller_AbstractAjax
{
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
        $this->uid = UserUtil::getVar('uid');
        try {
            $this->me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $this->uid);
        } catch (Zim_Exception_ContactNotFound $e) {
            return new Zim_Response_Ajax_Exception(null,'Error: You do not exist.');
        }
        $this->groups_allowed = ($this->getVar('user_groups') == '1' ? true : false);
    }

    private $uid;
    private $me;
    private $groups_allowed;

    /**
     * Create a new group.
     */
    public function create_group() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        $this->throwForbiddenUnless($this->groups_allowed);

        //Get params from front end (ajax)
        $args['groupname'] = $this->request->getPost()->get('groupname');
        $args['uid'] = $this->uid;

        $output = array();
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Delete a group.
     *
     */
    public function delete_group() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        $this->throwForbiddenUnless($this->groups_allowed);
        $output = array();
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Edit the name of a group.
     *
     */
    public function edit_groupname() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        $this->throwForbiddenUnless($this->groups_allowed);

        $args['gid'] = $this->request->getPost()->get('gid');
        $args['groupname'] = $this->request->getPost()->get('groupname');

        $output = array();
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Add a user to a group.
     */
    public function add_to_group() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        $this->throwForbiddenUnless($this->groups_allowed);

        //get required information.
        $args['uid'] = $this->uid;
        $args['user'] = $this->request->getPost()->get('uid');
        $args['gid'] = $this->request->getPost()->get('gid');

        $output = array();
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }

	/**
     * Delete a user from a group.
     */
    public function del_from_group() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        $this->throwForbiddenUnless($this->groups_allowed);

        //get required information.
        $args['uid'] = $this->uid;
        $args['gid'] = $this->request->getPost()->get('gid');
        $args['user'] = $this->request->getPost()->get('uid');

        $output = array();
        //return JSON response.
        return new Zikula_Response_Ajax($output);
    }

}