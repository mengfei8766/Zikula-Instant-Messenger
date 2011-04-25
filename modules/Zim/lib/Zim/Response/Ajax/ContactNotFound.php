<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 * 
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
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