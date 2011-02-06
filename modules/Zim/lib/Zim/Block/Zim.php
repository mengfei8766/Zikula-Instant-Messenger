<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Zim_Block_Zim extends Zikula_Block
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Admin:adminnavblock:', 'Block title::Block ID');
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

    /**
     * display block
     */
    public function display($blockinfo)
    {
        //disable cache
        $this->view->setCaching(false);
                
        //if users not logged in we cant show the block at all
        if (!UserUtil::isLoggedIn()) {
            return;
        }
           
        //load all the JS and CSS that ZIM needs
        PageUtil::addVar('javascript', 'javascript/helpers/Zikula.js');
        PageUtil::addVar('javascript', 'javascript/ajax/original_uncompressed/scriptaculous.js');
        PageUtil::addVar('javascript', 'javascript/livepipe/livepipe.js');
        PageUtil::addVar('javascript', 'javascript/livepipe/contextmenu.js');
        PageUtil::addVar('javascript', 'modules/Zim/javascript/Emoticon.js');
        PageUtil::addVar('javascript', 'modules/Zim/javascript/Zim.js');
        PageUtil::addVar('javascript', 'modules/Zim/javascript/tooltips.js');
        PageUtil::addVar('stylesheet', 'modules/Zim/style/Zim.css');
        PageUtil::addVar('stylesheet', 'modules/Zim/style/tooltips.css');
        //PageUtil::addVar('javascript', 'modules/Zim/soundmanager/script/soundmanager2.js');
        
        //get users information
        $uid = UserUtil::getVar('uid');
        $me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        
        //TODO this should handle if user doesnt exist in the records.
        if (!isset($me) || !$me || empty($me) || !isset($me['status'])) {
            $args['status'] = 1;
            $args['uid'] = $uid;
            ModUtil::apiFunc('Zim', 'contact', 'update_contact_status', $args);
            $me = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $uid);
        }
        $my_uname = $me['uname'];
        
        //get the block
        $this->view->assign('uname', $my_uname);
        $blockinfo['content'] = $this->view->fetch('zim_block_zim.tpl');
        
        //return the block
        return BlockUtil::themeBlock($blockinfo);
    }
}
