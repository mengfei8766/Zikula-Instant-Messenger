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
        return ;
    }

}


