<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Api_ZikulaGroup extends Zikula_AbstractApi
{


    public function get_all($args){
        //verify arguments
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

        //get all of the users group memberships
        $groups = UserUtil::getGroupsForUser($args['uid']);
        $grouped_results = array();
        //go through each group the user belongs to and construct the contact list
        foreach ($groups as $group) {
            //get the group information from the groups module
            $item = ModUtil::apiFunc('Groups', 'user', 'get',
            array('gid'      => $group));
            $members = array();
            foreach ($item['members'] as $member) {
                array_push($members, $member['uid']);
            }
            //if we want to show the group members in the listing
            $contacts = array();
            if ($args['show_members']) {
                //query for zim users in this group
                $task = Doctrine_Query::create()
                ->from('Zim_Model_User user')
                ->whereIn('user.uid', $members);
                //if we dont want offline users then don't fetch them
                if (!$args['offline_members']) {
                    $task->andwhere('user.status != 0')
                    ->andWhere('user.status != 3')
                    ->andWhere('user.timedout != 1');
                }
                $exec = $task->execute();
                $contacts = $exec->toArray();
            }

            $line = array( 'gid'       => $item['gid'],
                     'groupname' => $item['name'],
                     'members'  => $contacts
            );
            //print_r($line);
            array_push($grouped_results, $line);
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
        }
        return $grouped_results;
    }

}