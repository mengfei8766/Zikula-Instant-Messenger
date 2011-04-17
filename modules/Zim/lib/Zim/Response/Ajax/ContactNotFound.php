<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Ajax class.
 */
class Zim_Response_Ajax_ContactNotFound extends Zikula_Response_Ajax_Error
{
    /**
     * Response code.
     *
     * @var integer
     */
    protected $responseCode = 400;
    
	public function __construct($message=null, $payload=array())
    {
    	$payload['ZimCode'] = '400';
    	if (!isset($message) || empty($message)) {
    		$message = __('The specified contact could not be found.');
    	}
        parent::__construct($message, $payload);
    }
}