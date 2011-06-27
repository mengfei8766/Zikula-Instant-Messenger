<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Api_History extends Zikula_AbstractApi {
    
    public function get_history($args) {
        if (!isset($args['user1']) || !isset($args['user2'])) {
            throw new Zim_Exception_UIDNotSet();
        }
        //get the table and select everything.
        $task = Doctrine_Query::create()
        ->from('Zim_Model_Message message')
        ->where('(message.msg_to = ?) AND (message.msg_from = ?) AND (message.msg_to_deleted != 1)', array($args['user1'], $args['user2']))
        ->orWhere('(message.msg_to = ?) AND (message.msg_from = ?) AND (message.msg_from_deleted != 1)', array($args['user2'], $args['user1']))
        ->leftJoin('message.from from')
        ->leftJoin('message.to to')
        ->orderBy('message.created_at');
        $messages_new = $task->execute();
        $messages_new = $messages_new->toArray();
        
        $task = Doctrine_Query::create()
        ->from('Zim_Model_HistoricalMessage message')
        ->where('(message.msg_to = ?) AND (message.msg_from = ?) ', array($args['user1'], $args['user2']))
        ->orWhere('(message.msg_to = ?) AND (message.msg_from = ?) ', array($args['user2'], $args['user1']))
        ->leftJoin('message.from from')
        ->leftJoin('message.to to')
        ->orderBy('message.created_at');
        $messages_old = $task->execute();
        $messages_old = $messages_old->toArray();
          
        //TODO: some sorting may be needed
        $messages = array_merge($messages_old, $messages_new);
        return $messages;
    }
    
    public function delete($args) {
        $q = Doctrine_Query::create()
        ->update('Zim_Model_Message m')
        ->set('m.msg_to_deleted', '?', 1)
        ->where('m.msg_to = ?', $args['uid'])
        ->andWhere('m.msg_from = ?', $args['user']);
        $q->execute();

        $q = Doctrine_Query::create()
        ->update('Zim_Model_Message m')
        ->set('m.msg_from_deleted', "?", 1)
        ->where('m.msg_from = ?', $args['uid'])
        ->andWhere('m.msg_to = ?', $args['user']);
        $q->execute();
        
        $q = Doctrine_Query::create()
        ->update('Zim_Model_HistoricalMessage m')
        ->set('m.msg_to_deleted', "?", 1)
        ->where('m.msg_to = ?', $args['uid'])
        ->andWhere('m.msg_from = ?', $args['user']);
        $q->execute();
        
        $q = Doctrine_Query::create()
        ->update('Zim_Model_HistoricalMessage m')
        ->set('m.msg_from_deleted', "?", 1)
        ->where('m.msg_from = ?', $args['uid'])
        ->andWhere('m.msg_to = ?', $args['user']);
        $q->execute();

        $q = Doctrine_Query::create()
        ->delete('Zim_Model_Message m')
        ->where('(m.msg_from_deleted = 1) AND (m.msg_to_deleted = 1)');
        $q->execute();
        
        $q = Doctrine_Query::create()
        ->delete('Zim_Model_HistoricalMessage m')
        ->where('(m.msg_from_deleted = 1) AND (m.msg_to_deleted = 1)');
        $q->execute();

        return;
    }
}
