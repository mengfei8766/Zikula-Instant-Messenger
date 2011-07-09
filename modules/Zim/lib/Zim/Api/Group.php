<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Api_Group extends Zikula_AbstractApi
{


    public function get_all($args){
        if (!isset($args['uid']) || $args['uid'] == '') {
            throw new Zim_Exception_UIDNotSet();
        }
        $q = Doctrine_Query::create()
        ->from('Zim_Model_Group g')
        ->where('uid = ?', $args['uid'])
        ->leftJoin('g.members members');
        $result = $q->execute();
        return $result->toArray();
    }
    /**
     * Create a new group.
     */
    public function create_group($args) {
        if (!isset($args['uid']) || $args['uid'] == '') {
            throw new Zim_Exception_UIDNotSet();
        }
        if (!isset($args['groupname']) || $args['groupname'] == '') {
            throw new Zim_Exception_GnameNotSet();
        }

        $group = new Zim_Model_Group();
        $group->fromArray($args);
        $group->save();
        return $group->toArray();
    }

    /**
     * Delete a group.
     *
     */
    public function delete_group($args) {
        if (!isset($args['uid']) || $args['uid'] == '') {
            throw new Zim_Exception_UIDNotSet();
        }
        if (!isset($args['gid']) || $args['gid'] == '') {
            throw new Zim_Exception_GIDNotSet();
        }

        //TODO: delete group

        $output = array();
        return $output;
    }

    /**
     * Edit the name of a group.
     *
     */
    public function edit_groupname($args) {
        if (!isset($args['groupname']) || $args['groupname'] == '' || trim($args['groupname'] == '')) {
            throw new Zim_Exception_GnameNotSet();
        }
        if (!isset($args['gid']) || $args['gid'] == '' ) {
            throw new Zim_Exception_GIDNotSet();
        }
        if (!isset($args['uid']) || $args['uid'] == '' ) {
            throw new Zim_Exception_UIDNotSet();
        }
        $args['groupname'] = trim($args['groupname']);

        $q = Doctrine_Query::create()
        ->update('Zim_Model_Group')
        ->set('groupname', "?", $args['groupname'])
        ->where('gid = ?', $args['gid'])
        ->andWhere('uid = ?', $args['uid']);

        $result = $q->execute();
        if (!isset($result) || $result == 0) {
            //TODO failed
        }
    }

    /**
     * Add a user to a group.
     */
    public function add_to_group($args) {
        if (!isset($args['uid']) || $args['uid'] == '') {
            throw new Zim_Exception_UIDNotSet();
        }
        if (!isset($args['gid']) || $args['gid'] == '') {
            throw new Zim_Exception_GIDNotSet();
        }
        if (!isset($args['user']) || $args['user'] == '') {
            throw new Zim_Exception_GIDNotSet();
        }

        //check to make sure that user owns the group
        $q = Doctrine_Query::create()
        ->from("Zim_Model_Group g")
        ->where('g.gid = ?', $args['gid'])
        ->andWhere('g.uid = ?', $args['uid'])
        ->limit('1');
        $group = $q->fetchOne();
        if (empty($group)) {
            return;
            //TODO: group not found
        }

        $args['uid'] = $args['user'];
        unset($args['user']);
        $groupuser = new Zim_Model_GroupUser();
        $group->fromArray($args);
        $group->save();
    }

	/**
     * Delete a user from a group.
     */
    public function del_from_group($args) {
        if (!isset($args['uid']) || $args['uid'] == '') {
            throw new Zim_Exception_UIDNotSet();
        }
        if (!isset($args['gid']) || $args['gid'] == '') {
            throw new Zim_Exception_GIDNotSet();
        }
        if (!isset($args['user']) || $args['user'] == '') {
            throw new Zim_Exception_GIDNotSet();
        }

        //check to make sure that user owns the group
        $q = Doctrine_Query::create()
        ->from("Zim_Model_Group g")
        ->where('g.gid = ?', $args['gid'])
        ->andWhere('g.uid = ?', $args['uid'])
        ->limit('1');
        $group = $q->fetchOne();
        if (empty($group)) {
            return;
            //TODO: group not found
        }

        $q = Doctrine_Query::create()
        ->delete('Zim_Model_GroupUser g')
        ->addWhere('g.uid = ?', $args['user'])
        ->andWhere('g.gid', $args['gid']);
        $result = $q->execute();
        if (!isset($result) || $result == 0) {
            //TODO failed
        }
    }
}