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
    function window_add($args)
    {
        $windows = array();
        //throw new Zim_Exception_ContactNotFound(var_export($args['state_windows_add']));
        foreach ($args['state_windows_add'] as $window) {
            $w = new Zim_Model_State();
            $w['uid'] = $args['uid'];
            $w['user'] = $window;
            try {
                $w->save();
            } catch (Exception $e){

            }
        }
    }

    /**
     * Make changes to the current state of a user, this keeps information such
     * as what message windows and messages are open.
     */
    function window_del($args)
    {
        if (!empty($args['state_windows_del']) || sizeof($args['state_windows_del']) != 0)
        {
            $q = Doctrine_Query::create()
            ->from('Zim_Model_State window')
            ->whereIn('window.user', $args['state_windows_del'])
            ->andWhere('window.uid = ?', $args['uid']);
            $windows = $q->execute();

            foreach ($windows as $window) {
                $window->delete();
            }
        }
    }

    function message_set($args)
    {
        foreach ($args['state_messages_set'] as $message) {
            $m = new Zim_Model_State();
            if ((int)$message['msg_from'] == (int)UserUtil::getVar('uid')){
                $m['uid'] = $message['msg_from'];
                $m['user'] = $message['msg_to'];
                $m['start_msg'] = $message['mid'];
            } else {
                $m['uid'] = $message['msg_to'];
                $m['user'] = $message['msg_from'];
                $m['start_msg'] = $message['mid'];
            }
            try {
                $m->save();
            } catch (Exception $e){

            }
        }
    }

    /**
     * Gets the current state of the user, what windows/messages are open.
     */
    function get($uid) {
        $task = Doctrine_Query::create()
        ->select('state.user as uid, state.uid as my_uid, state.start_msg as start_msg, u.uname as uname, u.status as status, u.timedout as timedout')
        ->from('Zim_Model_State state')
        ->where('state.uid = ?', $uid)
        ->leftJoin('state.to u');
        $s = $task->execute();
        if ($s->count() > 0) {
            $state['windows'] = $s->toArray();
            $q = Doctrine_Query::create();
            $q->from("Zim_Model_Message m");
            $q->where("m.mid >= ? AND (m.msg_to = ? OR m.msg_from = ?)", array(
            $state['windows'][0]['start_msg'],
            $state['windows'][0]['my_uid'],
            $state['windows'][0]['my_uid']
            )
            );
            foreach ($state['windows'] as $key => $window) {
                $q->orWhere("m.mid >= ? AND (m.msg_to = ? OR m.msg_from = ?)", array(
                $window['start_msg'],
                $window['my_uid'],
                $window['my_uid']
                )
                );
                unset($state['windows'][$key]['user']);
                unset($state['windows'][$key]['my_uid']);
                unset($state['windows'][$key]['id']);
            }
            $q->leftJoin('m.from uname')
            ->orderBy('m.created_at');
            $messages = $q->execute();
            $state['messages'] = $messages->toArray();
        } else {
            $state['windows'] = array();
            $state['messages'] = array();
        }

        // Return the messages
        return $state;
    }
}
