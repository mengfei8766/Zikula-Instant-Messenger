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
    }

    private $uid;

    /**
     * The init function is called via an ajax call from the browser, it performs
     * all startup functions such as getting contact lists and messages/state.
     *
     */
    public function get_template() {
        //security checks
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT));
        
        $contacts = ModUtil::apiFunc('Zim', 'contact', 'get_all_contacts');
        foreach ($contacts as $key => $contact) {
            if ($contact['uid'] == $this->uid) unset($contacts[$key]);
        }
        
        $obj = array('contacts' => $contacts);
        $this->view->assign($obj);
        $output['template'] = $this->view->fetch('zim_block_history.tpl');
        return new Zikula_Response_Ajax($output);
    }
}