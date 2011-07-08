<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Block_Zim extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
    }

    /**
     * get information on block
     */
    public function info()
    {
        // block values
        return array('module'         => 'Zim',
                     'text_type'      => $this->__('Zikula Instant Messanger'),
                     'text_type_long' => $this->__('Display the Zikula Instant Messenger.'),
                     'allow_multiple' => false,
                     'form_content'   => false,
                     'form_refresh'   => false,
                     'show_preview'   => true);
    }

    public function modify($blockinfo)
    {
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        if (empty($vars['unloggedin_block'])) {
            $vars['unloggedin_block'] = 0;
        }
        $this->view->assign($vars);
        return $this->view->fetch('zim_block_modify.tpl');
    }

    public function update($blockinfo)
    {
        $vars['unloggedin_block'] = (int)$this->request->getPost()->get('unloggedin_block');
        if (empty($vars['unloggedin_block'])) {
            $vars['unloggedin_block'] = 0;
        }

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('zim_block_modify.tpl');
        Zikula_View_Theme::getInstance()->clear_cache();
        return($blockinfo);
    }

    /**
     * display block
     */
    public function display($blockinfo)
    {
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        //disable cache
        $this->view->setCaching(false);
        //if users not logged in we cant show the block at all
        if (!UserUtil::isLoggedIn()) {
            if ($vars['unloggedin_block']) {
                PageUtil::addVar('stylesheet', 'modules/Zim/style/Zim.css');
                $blockinfo['content'] = $this->view->fetch('zim_block_notloggedin.tpl');
                //return the block
                return BlockUtil::themeBlock($blockinfo);
            } else {
                return false;
            }
        }

        if (!SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT)) {
            return false;
        }
         
        //load all the JS and CSS that ZIM needs
        //TODO: is this the best way of doing this?
        PageUtil::addVar('javascript', 'javascript/helpers/Zikula.js');
        PageUtil::addVar('javascript', 'javascript/ajax/original_uncompressed/scriptaculous.js');
        PageUtil::addVar('javascript', 'javascript/livepipe/livepipe.js');
        PageUtil::addVar('javascript', 'javascript/livepipe/contextmenu.js');
        PageUtil::addVar('javascript', 'modules/Zim/javascript/Emoticon.js');
        if ($this->getVar('use_minjs')) {
            PageUtil::addVar('javascript', 'modules/Zim/javascript/Zim_min.js');
            PageUtil::addVar('javascript', 'modules/Zim/javascript/tooltips_min.js');
        } else {
            PageUtil::addVar('javascript', 'modules/Zim/javascript/Zim.js');
            PageUtil::addVar('javascript', 'modules/Zim/javascript/tooltips.js');
        }
        PageUtil::addVar('stylesheet', 'modules/Zim/style/Zim.css');
        PageUtil::addVar('stylesheet', 'modules/Zim/style/tooltips.css');

        //get users information
        $uid = UserUtil::getVar('uid');
        $me = array();
        try {
            $me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        } catch (Zim_Exception_ContactNotFound $e) {
            $me = ModUtil::apiFunc('Zim', 'contact', 'first_time_init', $uid);
        }

        //get the block
        $this->view->assign('uname', $me['uname']);
        $blockinfo['content'] = $this->view->fetch('zim_block_zim.tpl');

        //return the block
        return BlockUtil::themeBlock($blockinfo);
    }
}
