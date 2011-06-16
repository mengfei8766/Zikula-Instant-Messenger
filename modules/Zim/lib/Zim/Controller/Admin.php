<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Controller_Admin extends Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
        // In this controller we never want caching.
        $this->view->setCaching(false);
    }

    /**
     * the main administration function
     * Just a stub for now
     * @return void
     */
    public function main()
    {
        // Security check will be done in view()
        if (!SecurityUtil::checkPermission('Zim::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        return $this->view->fetch('zim_admin_main.tpl');
    }
    
    public function settings_update()
    {
        if (!SecurityUtil::checkPermission('Zim::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $settings = $this->request->getPost()->get('settings');
        if (!isset($settings) || empty($settings)) {
            return false;
        }
        $vars = $this->getVars();
        foreach ($settings as $key => $setting) {
           if (array_key_exists($key, $vars)) {
               $this->setVar($key, $setting);
           }
        }
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
        return $this->view->fetch('zim_admin_main.tpl');
        
    }

}


