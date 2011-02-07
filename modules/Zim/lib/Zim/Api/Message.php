<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */
 
class Zim_Api_Message extends Zikula_Api {

    /**
     * Get all messages for a user.
     *
     * @param intiger $args['to']   user for whom to get messages.
     * @param boolean $args['recd'] get already recieved messages or not.
     *
     * @return array list of messages.
     */
    function getall($args) {
        //make sure everything is set.
        if (!isset($args['to']) || !$args['to']) {
            return false;
        }
        if (!isset($args['recd'])) {
            $args['recd'] = false;
        }
        
        //get tables and construct where statment
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE $column[to] =" . $args['to'];
        if (!$args['recd']) {
            $where .= " AND $column[recd] = 0";
        }
        $orderBy = "$column[sent_on]";
        
        //get the messages
        $messages = DBUtil::selectObjectArray('zim_message', $where, $orderBy);
        foreach ($messages as $key => $message) {
            //get the username for each message 
            //TODO: this creates a lot of database calls, there should be a better way to do this.
            $messages[$key]['uname'] = UserUtil::getVar('uname', $message['from']);
        }

        // Return the messages
        return $messages;
    }

    /**
     * get a particular message
     *
     * @param Integer mid Message id to get.
     *
     * @return Array list of messages.
     */
    function get_message($mid) {
        //make sure mid is set
        if (!isset($mid)) {
            return false;
        }
        
        //get tables and construct where argument
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE $column[mid] =" . $mid;
        
        //get the message
        $message = DBUtil::selectObject('zim_message', $where);
        
        //message is not found
        //TODO: handle message not found
        if (sizeof($message) == 0) {
            return $message;
        }

        // Return the item
        return $message;
    }

    /**
     * Send a new message.
     *
     */
    function send($message) {
        //Check that everythings set.
        if (!isset($message))
            return false;
        if (!isset($message['from']) || !$message['from'])
            return false;
        if (!isset($message['to']) || !$message['to'])
            return false;
        if (!isset($message['message']) || !$message['message'])
            return false;
        if (!isset($message['recd']))
            $message['recd'] = 0;
        
        //Set the sent date/time.
        if (!isset($message['sent_on'])) {
            $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
            $message['sent_on'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        }
        
        //insert the message.
        $return = DBUtil::insertObject($message, 'zim_message', 'mid');
        return $return;
    }

    /**
     * Confirm receipt of message.
     *
     * @param Integer $args['id'] Message id to confirm.
     * @param Integer $args['to'] User id of recipient of message.
     */
    function confirm($args) {
        //Check params
        if (!isset($args['id']) || $args['id'] == '')
            return false;
        if (!isset($args['to']) || $args['to'] == '')
            return false;

        //setup the message object
        $message['recd'] = 1;
        $message['mid'] = $args['id'];
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $message['recd_on'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        
        //get the table and update the object
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE $column[mid] =" . $args['id'] . " AND $column[to] =" . $args['to'];
        return DBUtil::updateObject($message, 'zim_message', $where);
    }

    /**
     * get an array of messages. This function only gets messages that are either to or from
     * the user specified in $args['to']
     *
     * @param Integer $args['id'] Message id's to get.
     * @param Integer $args['to'] User id of sender or recipient of message.
     *
     * @return Array A list of messages.
     */
    function getSelectedMessages($args) {
        //check arguments.
        if (!isset($args) || !is_array($args)) {
            return false;
        }
        if (!isset($args['to']) || !$args['to']) {
            return false;
        }
        if (!isset($args['mid'])) {
            return false;
        }

        //get table
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        
        //construct where argument.
        $where = "WHERE ($column[to] =" . $args['to'] . " OR $column[from] =" . $args['to'] . ") AND $column[mid] IN(";
        $in = '';
        foreach ($args['mid'] as $mid) {
            $in .= "$mid,";
        }
        if (strlen($in) > 0) {
            $in = substr($in, 0, -1);
            $where .= $in . ')';
        //if there are no mid's then just return here.
        } else {
            return array();
        }

        //order the messages by when they were sent
        $orderBy = "$column[sent_on]";
        
        //query the database for the messages
        $messages = DBUtil::selectObjectArray('zim_message', $where, $orderBy);
        
        //get the username for each message
        //TODO: this is expensive on the database, should be a better way of doing this.
        foreach ($messages as $key => $message) {
            $messages[$key]['uname'] = UserUtil::getVar('uname', $message['from']);
        }

        // Return the items
        return $messages;
    }

}
