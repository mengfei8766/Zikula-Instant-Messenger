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
 * Zikula_Exception class.
 */
class Zim_Exception_UIDNotSet extends Exception
{
    /**
     * Constructor.
     *
     * @param string  $message Default ''.
     * @param integer $code    Code.
     * @param mixed   $debug   Debug.
     */
	public function __construct($message=null, $code = 2, Exception $previous = null) {
		if (!isset($message) || empty($message)) {
    		$message = __('No contact was specified.');
    	}
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}