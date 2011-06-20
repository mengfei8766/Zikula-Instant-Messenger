<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Model_State extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('zim_state');
        $this->hasColumn('uid', 'integer', 16, array(
            'notnull' => true
        ));
        $this->hasColumn('user', 'integer', 16, array(
            'notnull' => true
        ));
        $this->hasColumn('start_msg', 'integer', 16, array(
            'notnull' => false
        ));
    }

    public function setUp()
    {
        $this->hasOne('Zim_Model_User as to', array(
                'local' => 'user',
                'foreign' => 'uid',
        )
        );
    }

    public function preDelete($event)
    {
        $task = Doctrine_Query::create()
        ->from('Zim_Model_Message m')
        ->where('(m.msg_to = ? AND m.msg_from = ?) OR (m.msg_to = ? AND m.msg_from = ?)',array(
        $this->user, UserUtil::getVar('uid'),UserUtil::getVar('uid'), $this->user))
        ->andWhere('m.recd = ?', '1')
        ->andWhere('(m.mid < ( SELECT s.start_msg FROM Zim_Model_State s WHERE s.uid = ? LIMIT 1)) OR (SELECT COUNT(*) FROM Zim_Model_State s2 WHERE s2.uid = ?)= 0 ', array($this->user, $this->user));
        $messages = $task->execute();
        foreach ($messages as $message) {
            if (ModUtil::getVar('Zim', 'keep_history', '0') == '1') {
                $msg = new Zim_Model_HistoricalMessage();
                $msg->fromArray($message->toArray());
                $msg->save();
            } 
            $message->delete();
        }
    }
     
    public function preInsert($ecent)
    {

        return $this->preSave($event);
    }
    public function preSave($event)
    {
        $isUnique = false;
        $task = Doctrine_Query::create()
        ->from('Zim_Model_State window')
        ->where('window.uid = ?', $this->uid)
        ->andWhere('window.user = ?', $this->user)
        ->limit(1);
        $statewindow = $task->fetchOne();

        if (!isset($statewindow) || empty($statewindow)) {
            $isUnique = true;
        } else {
            $statewindow = $statewindow->toArray();
            if (empty($statewindow['start_msg']) || $statewindow['start_msg'] == ''){
                $statewindow['start_msg'] = $this->start_msg;
            }
        }

        if (!$isUnique) {
            $q = Doctrine_Query::create()
            ->update('Zim_Model_State')
            ->set('start_msg',"?", min($this->start_msg, $statewindow['start_msg']))
            ->where('uid = ?', $this->uid)
            ->andWhere('user = ?', $this->user);
            $q->execute();
            $event->skipOperation();
        }

    }

    private function isUnique()
    {
        $task = Doctrine_Query::create()
        ->from('Zim_Model_State window')
        ->where('window.uid = ?', $this->uid)
        ->andWhere('window.user = ?', $this->user);
        $statewindow = $task->fetchOne();
        $statewindow = $statewindow->toArray();
        if (empty($statewindow) || sizeof($statewindow) == 0) {
            return true;
        }
        return false;
    }
}