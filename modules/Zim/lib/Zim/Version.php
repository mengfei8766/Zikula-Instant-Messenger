<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */

class Zim_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name']           = 'Zim';
        $meta['displayname']    = $this->__('Zikula Instant Messanger');
        $meta['description']    = $this->__("An instant messenger module for Zikula.");
        //! module name that appears in URL
        $meta['url']            = $this->__('zim');
        $meta['version']        = '0.0.4';
        return $meta;
    }
}
