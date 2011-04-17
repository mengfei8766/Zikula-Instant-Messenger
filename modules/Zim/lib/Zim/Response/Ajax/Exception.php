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
class Zim_Response_Ajax_Exception extends Zikula_Response_Ajax_Error
{
    /**
     * Response code.
     *
     * @var integer
     */
    protected $responseCode = 400;
    
	public function __construct($exception = null, $message=null, $code=null, $payload=array())
    {
    	$payload['ZimCode'] = $code;
    	if (isset($exception)) {
    		$message = $exception->getMessage();
    		$payload['ZimCode'] = $exception->getCode();
    	}
    	if (!isset($message) || empty($message)) {
    		$message = __('An unknown error occured.');
    	}
        parent::__construct($message, $payload);
    }
}