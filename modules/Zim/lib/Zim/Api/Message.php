<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */
 
class Zim_Api_Message extends Zikula_Api {

    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return   array   indexed array of items.
     */
    function getall($args) {
        if (!isset($args['to']) || !$args['to']) {
            return false;
        }
        if (!isset($args['recd'])) {
            $args['recd'] = false;
        }
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE $column[to] =" . $args['to'];
        if (!$args['recd']) {
            $where .= " AND $column[recd] = 0";
        }

        $orderBy = "$column[sent_on]";
        $messages = DBUtil::selectObjectArray('zim_message', $where, $orderBy);
        foreach ($messages as $key => $message) {
            $messages[$key]['uname'] = UserUtil::getVar('uname', $message['from']);
        }

        // Return the items
        return $messages;
    }

    function get_message($mid) {
        if (!isset($mid)) {
            return false;
        }
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE $column[mid] =" . $mid;
        $contact = DBUtil::selectObject('zim_message', $where);
        if (sizeof($contact) == 0)
            return $contact;

        // Return the item
        return $contact;
    }

    function send($message) {
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
        if (!isset($message['sent_on'])) {
            $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
            $message['sent_on'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        }
        $return = DBUtil::insertObject($message, 'zim_message', 'mid');
        return $return;
    }

    function confirm($args) {
        if (!isset($args['id']) || $args['id'] == '')
            return false;
        if (!isset($args['to']) || $args['to'] == '')
            return false;

        $message['recd'] = 1;
        $message['mid'] = $args['id'];
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $message['recd_on'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE $column[mid] =" . $args['id'] . " AND $column[to] =" . $args['to'];
        return DBUtil::updateObject($message, 'zim_message', $where);
    }

    function getSelectedMessages($args) {
        if (!isset($args) || !is_array($args)) {
            return false;
        }
        if (!isset($args['to']) || !$args['to']) {
            return false;
        }
        if (!isset($args['mid'])) {
            return false;
        }

        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_message_column'];
        $where = "WHERE ($column[to] =" . $args['to'] . " OR $column[from] =" . $args['to'] . ") AND $column[mid] IN(";
        $in = '';
        foreach ($args['mid'] as $mid) {
            $in .= "$mid,";
        }
        if (strlen($in) > 0) {
            $in = substr($in, 0, -1);
            $where .= $in . ')';
        } else {
            return array();
        }



        $orderBy = "$column[sent_on]";
        $messages = DBUtil::selectObjectArray('zim_message', $where, $orderBy);
        foreach ($messages as $key => $message) {
            $messages[$key]['uname'] = UserUtil::getVar('uname', $message['from']);
        }

        // Return the items
        return $messages;
    }

}
