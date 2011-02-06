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
class Zim_Api_Contact extends Zikula_Api {

    /**
     * Return an array of items to show in the your account panel.
     *
     * @param array $array The arguments to pass to the function.
     *
     * @return   array   indexed array of items.
     */
    function get_contact($uid) {
        if (!isset($uid)) {
            return false;
        }
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $uid;
        $contact = DBUtil::selectObject('zim_users', $where);
        if (!$contact) {
            return false;
        }
        if (sizeof($contact) == 0)
            return $contact;
        if (!isset($contact['uname']) || empty($contact['uname'])) {
            $contact['uname'] = UserUtil::getVar('uname', $uid);
        }
        return $contact;
    }

    function get_all_contacts() {
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $contacts = DBUtil::selectObjectArray('zim_users');
        foreach ($contacts as $key => $contact) {
            if ($contact['status'] == 3) {
                $contacts[$key]['status'] = 0;
            }
            if (!isset($contact['uname']) || empty($contact['uname'])) {
                $contacts[$key]['uname'] = UserUtil::getVar('uname', $contact['uid']);
            }
        }
        return $contacts;
    }

    function get_all_online_contacts() {
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[status] !=0 && $column[status] !=3";
        $contacts = DBUtil::selectObjectArray('zim_users', $where);
        foreach ($contacts as $key => $contact) {
            if (!isset($contact['uname']) || empty($contact['uname'])) {
                $contacts[$key]['uname'] = UserUtil::getVar('uname', $contact['uid']);
            }
        }
        return $contacts;
    }

    function update_contact_status($args) {
        $this->timeout();
        if (!isset($args))
            return false;
        if (!isset($args['uid']) || !$args['uid'])
            return false;
        if (!isset($args['status']))
            return false;
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $args['update_on'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $args['uid'];
        $me = $this->get_contact($args['uid']);
        if (!isset($me) || sizeof($me) == 0 || empty($me) || $me === false) {
            if ((!isset($me['uname']) || empty($me['uname'])) && (!isset($args['uname']) || empty($args['uname'])))
            {
                $args['uname'] = UserUtil::getVar('uname', $args['uid']);
            }
            if (!DBUtil::insertObject($args, 'zim_users')) {
                return false;
            }
            return $args['status'];
        }
        if (!DBUtil::updateObject($args, 'zim_users', $where)) {
            return false;
        }
        return $args['status'];
    }

    function timeout() {
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $now = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $contacts = $this->get_all_online_contacts();
        $delwhere = '';
        foreach ($contacts as $contact) {
            $datediff = DateUtil::GetDatetimeDiff($contact['update_on'], $now);
            if ($datediff['d'] > 0 || $datediff['h'] > 0 || $datediff['m'] > 0 || $datediff['s'] > 30) {
                $contact['status'] = 0;
                $where = "$column[uid] = $contact[uid]";
                DBUtil::updateObject($contact, 'zim_users', $where);
            }
        }
    }

    function update_username($args) {
        if (!isset($args['uid']) || empty($args['uid']))
            return false;
        if (!isset($args['uname']) || empty($args['uname'])) {
            $args['uname'] = UserUtil::getVar('uname', $args['uid']);
        }
        $dbtable = DBUtil::getTables();
        $column = $dbtable['zim_users_column'];
        $where = "WHERE $column[uid] =" . $args['uid'];
        unset($args['uid']);
        if (!DBUtil::updateObject($args, 'zim_users', $where)) {
            return false;
        }
        return $args['uname'];
    }

}
