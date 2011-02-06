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

class Zim_Version extends Zikula_Version
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
