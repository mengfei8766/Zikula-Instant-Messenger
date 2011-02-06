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
class Zim_Api_State extends Zikula_Api {

    /**
     *
     */
    function update($args) {
        //skip if the args are not set
        if (!isset($args) || !is_array($args)) {
            return false;
        }

        //sanitize the state, make sure there are no duplicates
        $this->sanitize();

        //get the current session
        $sess = SessionUtil::getVar('zim-sess', array());
        if (!is_array($sess)) {
            return false;
        }

        //go through all the args and perform the updates
        foreach ($args as $arg) {
            //TODO the count doesnt need to be used, it was here to define how
            //long to keep a message in the session
            $arg['count'] = 0;

            //check session params to insure they are all ok
            if (!isset($arg['data']) || empty($arg['data'])
                    || !isset($arg['type']) || empty($arg['type'])
                    || !isset($arg['method']) || empty($arg['method'])) {
                continue;
            }

            //set session information depending on type
            if ($arg['type'] == 'message') {
                //if (!isset($arg['count']) || empty($arg['count']) || !is_numeric($arg['count'])) {
                //    $arg['count'] = 0;
                //}
                //check that uid is set for messages
                if (!isset($arg['uid']) || empty($arg['uid'])) {
                    continue;
                }

                //make the session line for messages
                $line = array('type' => $arg['type'], 'data' => $arg['data'], 'uid' => $arg['uid'], 'count' => $arg['count']);
            } else if ($arg['type'] == 'window') {
                //make the session line for windows
                $line = array('type' => $arg['type'], 'data' => $arg['data']);
            }

            //if we are adding to the session
            if ($arg['method'] == 'add') {
                //check if is in session already
                foreach ($sess as $key => $s) {
                    if ($s['type'] == $line['type'] && $s['data'] == $line['data']) {
                        $line = array();
                        break;
                    }
                }
                //add to session if not in session already
                if (!empty($line)) {
                    array_push($sess, $line);
                }
            }

            //if we are deleting from the session
            if ($arg['method'] == 'del') {
                //find session entries to remove
                foreach ($sess as $key => $s) {
                    if ($s['type'] == 'message' && $s['uid'] == $arg['data']) {
                        //if (isset($s['count']) && is_numeric($s['count'])) {
                        //    $sess[$key]['count'] = $sess[$key]['count'] + 1;
                        //} else {
                        //    $sess[$key]['count'] = 1;
                        //}
                        //if ($sess[$key]['count'] > 1) {
                        unset($sess[$key]);
                        //}
                    }
                    if ($s['type'] == $arg['type'] && $s['data'] == $arg['data']) {
                        unset($sess[$key]);
                    }
                }
            }
        }
        SessionUtil::setVar('zim-sess', $sess, '/', true, true);
        return true;
    }

    function get() {
        $this->sanitize();
        $sess = SessionUtil::getVar('zim-sess', array());
        $messages = array();
        $windows = array();
        foreach ($sess as $s) {
            if ($s['type'] == 'message') {
                array_push($messages, $s['data']);
            } else if ($s['type'] == 'window') {
                array_push($windows, $s['data']);
            }
        }
        $args['mid'] = $messages;
        $args['to'] = UserUtil::getVar('uid');
        $s = ModUtil::apiFunc('Zim', 'message', 'getSelectedMessages', $args);
        $w = array();
        foreach ($windows as $window) {
            $wd = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $window);
            array_push($w, $wd);
        }
        $return['messages'] = $s;
        $return['windows'] = $w;
        return $return;
    }

    function sanitize() {
        $sess = SessionUtil::getVar('zim-sess', array());

        foreach ($sess as $key => $line) {
            if (!isset($line['data']) || empty($line['data'])
                    || !isset($line['type']) || empty($line['type'])) {
                unset($sess[$key]);
                continue;
            }
            if ($line['type'] == 'message' && (!isset($line['uid']) || empty($line['uid']))) {
                unset($sess[$key]);
                continue;
            }
        }
        SessionUtil::setVar('zim-sess', $sess, '/', true, true);
    }

}
