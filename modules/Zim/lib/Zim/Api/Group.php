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
        if (!isset($args['show_members']) || $args['show_members'] == '') {
            $args['show_members'] = true;
        }
        if (!isset($args['offline_members']) || $args['offline_members'] == '') {
            $args['offline_members'] = false;
        }
        if (!isset($args['clean']) || $args['clean'] == '') {
            $args['clean'] = true;
        }


        $q = Doctrine_Query::create()
        ->from('Zim_Model_Group g')
        ->where('g.uid = ?', $args['uid'])
        ->orderBy('g.groupname');
        if ($args['show_members']) {
            if (!$args['offline_members']) {
                $q->leftJoin('g.members members WITH members.timedout != 1 AND members.status != 0 AND members.status != 3');
            } else {
                $q->leftJoin('g.members members');
            }
        }
        $grouped_results = $q->execute();
        $grouped_results = $grouped_results->toArray();

        if ($args['show_members']) {
            $contacts_found = array();
            foreach ($grouped_results as $key => $group) {
                    foreach ($group['members'] as $key2 => $contact) {
                        $contacts_found[] = $contact['uid'];
                    }
            }

            $q = Doctrine_Query::create()
            ->from('Zim_Model_User user');
            if (!$args['offline_members']) {
                $q->where('user.status != 0')
                ->andWhere('user.status != 3')
                ->andWhere('user.timedout != 1');
                if (sizeof($contacts_found) > 0) {
                    $q->andWhereNotIn('user.uid', $contacts_found);
                }
            } elseif (sizeof($contacts_found) > 0) {
                $q->WhereNotIn('user.uid', $contacts_found);
            }
            $ungrouped_results = $q->execute();
            $ungrouped_results = $ungrouped_results->toArray();
        }

        if ($args['clean'] && $args['show_members']) {
            foreach ($grouped_results as $key => $group) {
                foreach ($group['members'] as $key2 => $contact) {
                    if ($contact['status'] == 3 || $contact['timedout'] == 1) {
                        $grouped_results[$key]['members'][$key2]['status'] = 0;
                    }
                    unset($grouped_results[$key]['members'][$key2]['created_at']);
                    unset($grouped_results[$key]['members'][$key2]['updated_at']);
                    unset($grouped_results[$key]['members'][$key2]['timedout']);
                }
            }
            if (isset($ungrouped_results)) {
                foreach ($ungrouped_results as $key=>$contact) {
                    if ($contact['status'] == 3 || $contact['timedout'] == 1) {
                        $ungrouped_results[$key]['status'] = 0;
                    }
                    unset($ungrouped_results[$key]['created_at']);
                    unset($ungrouped_results[$key]['updated_at']);
                    unset($ungrouped_results[$key]['timedout']);
                }
            }
        }
        if (isset($ungrouped_results) && sizeof($ungrouped_results) > 0) {
            $results = array_merge($grouped_results, $ungrouped_results);
        } else {
            $results = $grouped_results;
        }
        return $results;
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