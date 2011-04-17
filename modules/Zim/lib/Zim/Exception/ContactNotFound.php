<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Exception
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Exception class.
 */
class Zim_Exception_ContactNotFound extends Exception
{
    /**
     * Constructor.
     *
     * @param string  $message Default ''.
     * @param integer $code    Code.
     * @param mixed   $debug   Debug.
     */
	public function __construct($message=null, $code = 1, Exception $previous = null) {
		if (!isset($message) || empty($message)) {
    		$message = __('The specified contact could not be found.');
    	}
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}