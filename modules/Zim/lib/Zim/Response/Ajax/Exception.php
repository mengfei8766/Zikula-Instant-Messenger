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