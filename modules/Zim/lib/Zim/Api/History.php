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

    /**
     * Get the history between two users.
     *
     * @param $args['user1'] First user.
     * @param $args['user2'] Second user.
     *
     * @return Array of all the messages in history between these users.
     */
    public function get_history($args) {
        if (!isset($args['user1']) || !isset($args['user2'])) {
            throw new Zim_Exception_UIDNotSet();
        }

        //Get history from messages still in an active state window.
        $task = Doctrine_Query::create()
        ->from('Zim_Model_Message message')
        ->where('(message.msg_to = ?) AND (message.msg_from = ?) AND (message.msg_to_deleted != 1)', array($args['user1'], $args['user2']))
        ->orWhere('(message.msg_to = ?) AND (message.msg_from = ?) AND (message.msg_from_deleted != 1)', array($args['user2'], $args['user1']))
        ->leftJoin('message.from from')
        ->leftJoin('message.to to')
        ->orderBy('message.created_at');
        $messages_new = $task->execute();
        $messages_new = $messages_new->toArray();

        //get old messages from history table.
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

    /**
     * Delete a users history of messages with one contact.
     *
     * @param Array $args['uid']  User id of the user deleting their history.
     * @param Array $args['user'] User id of the user they are deleting history of.
     */
    public function delete($args) {
        if (!isset($args['uid']) || !isset($args['user'])) {
            throw new Zim_Exception_UIDNotSet();
        }

        //set as deleted where they are the recipient and message is in an active session window.
        $q = Doctrine_Query::create()
        ->update('Zim_Model_Message m')
        ->set('m.msg_to_deleted', '?', 1)
        ->where('m.msg_to = ?', $args['uid'])
        ->andWhere('m.msg_from = ?', $args['user']);
        $q->execute();

        //set as deleted where they are the sender and message is in an active session window.
        $q = Doctrine_Query::create()
        ->update('Zim_Model_Message m')
        ->set('m.msg_from_deleted', "?", 1)
        ->where('m.msg_from = ?', $args['uid'])
        ->andWhere('m.msg_to = ?', $args['user']);
        $q->execute();

        //set as deleted where they are the recipient and message is now in an active session window.
        $q = Doctrine_Query::create()
        ->update('Zim_Model_HistoricalMessage m')
        ->set('m.msg_to_deleted', "?", 1)
        ->where('m.msg_to = ?', $args['uid'])
        ->andWhere('m.msg_from = ?', $args['user']);
        $q->execute();

        //set as deleted where they are the sender and message is not in an active session window.
        $q = Doctrine_Query::create()
        ->update('Zim_Model_HistoricalMessage m')
        ->set('m.msg_from_deleted', "?", 1)
        ->where('m.msg_from = ?', $args['uid'])
        ->andWhere('m.msg_to = ?', $args['user']);
        $q->execute();

        //cleanup the history removing any messages that have been deleted by both sender and recipient.
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
