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
    /**
     * Create a new group.
     */
    public function create_group($args) {
        if (!isset($args['uid']) || $args['uid'] == '') {
            //TODO throw bad data
        }
        if (!isset($args['groupname']) || $args['groupname'] == '') {
            //TODO throw bad data
        }

        $group = new Zim_Model_Group();
        $group->fromArray($args);
        $group->save();
    }

    /**
     * Delete a group.
     *
     */
    public function delete_group($args) {

    }

    /**
     * Edit the name of a group.
     *
     */
    public function edit_groupname($args) {
        if (!isset($args['groupname']) || $args['groupname'] == '' || trim($args['groupname'] == '')) {
            //TODO throw bad data
        }
        if (!isset($args['gid']) || $args['gid'] == '' ) {
            //TODO throw bad data
        }
        if (!isset($args['uid']) || $args['uid'] == '' ) {
            //TODO throw bad data
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
            //TODO throw bad data
        }
        if (!isset($args['gid']) || $args['gid'] == '') {
            //TODO throw bad data
        }

        $groupuser = new Zim_Model_GroupUser();
        $group->fromArray($args);
        $group->save();
    }

	/**
     * Delete a user from a group.
     */
    public function del_from_group($args) {
        if (!isset($args['uid']) || $args['uid'] == '') {
            //TODO throw bad data
        }
        if (!isset($args['gid']) || $args['gid'] == '') {
            //TODO throw bad data
        }
        $q = Doctrine_Query::create()
        ->delete('Zim_Model_GroupUser g')
        ->addWhere('g.uid = ?', $args['uid'])
        ->andWhere('g.gid', $args['gid']);
        $result = $q->execute();
        if (!isset($result) || $result == 0) {
            //TODO failed
        }
    }
}