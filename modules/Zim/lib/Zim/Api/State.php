<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */
//TODO: why aren't messages and windows in seperate session variables.
class Zim_Api_State extends Zikula_AbstractApi {

    /**
     * Make changes to the current state of a user, this keeps information such
     * as what message windows and messages are open.
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

            //check session params to insure they are all ok
            if (!isset($arg['data']) || empty($arg['data'])
            || !isset($arg['type']) || empty($arg['type'])
            || !isset($arg['method']) || empty($arg['method'])) {
                continue;
            }

            //set session information depending on type
            if ($arg['type'] == 'message') {

                //check that uid is set for messages
                if (!isset($arg['uid']) || empty($arg['uid'])) {
                    continue;
                }

                //make the session line for messages
                $line = array('type' => $arg['type'], 'data' => $arg['data'], 'uid' => $arg['uid']);
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
                        unset($sess[$key]);
                    }
                    if ($s['type'] == $arg['type'] && $s['data'] == $arg['data']) {
                        unset($sess[$key]);
                    }
                }
            }
        }
        //save the session
        SessionUtil::setVar('zim-sess', $sess, '/', true, true);
        return true;
    }

    /**
     * Gets the current state of the user, what windows/messages are open.
     */
    function get($uid) {
        $this->sanitize();
        //get the session
        $sess = SessionUtil::getVar('zim-sess', array());
        $messages = array();
        $windows = array();

        //break the session into windows or messages
        foreach ($sess as $s) {
            if ($s['type'] == 'message') {
                array_push($messages, $s['data']);
            } else if ($s['type'] == 'window') {
                array_push($windows, $s['data']);
            }
        }

        //mid array of messages
        $args['mid'] = $messages;

        //get the users id.
        $args['uid'] = $uid;

        //get the messages
        $s = ModUtil::apiFunc('Zim', 'message', 'getSelectedMessages', $args);

        //get each contact for open windows.
        $w = array();
        foreach ($windows as $window) {
            $wd = ModUtil::apiFunc('Zim', 'contact', 'get_contact', $window);
            array_push($w, $wd);
        }

        //return the messages and windows
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
