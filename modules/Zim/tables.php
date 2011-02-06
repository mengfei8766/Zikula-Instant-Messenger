<?php
/**
 * Copyright Kyle Giovannetti 2011
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */


/**
 * Create Zim tables.
 *
 * @return array Tables.
 */
function Zim_tables()
{
    $dbtable = array();

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $dbtable['zim_users'] = DBUtil::getLimitedTablename('zim_users');
    $dbtable['zim_users_column'] = 	array('uid'         =>  'z_uid',
                                          'status'      =>  'z_name',
                                          'update_on'   =>  'z_update_on',
                                          'status_msg'  =>  'z_status_msg',
                                          'uname'       =>  'z_uname');

    $dbtable['zim_users_column_def'] = array('uid'          => "I PRIMARY",
                                             'status'       => "I", //0 - offline, 1 - online
                                             'update_on'    => "T",
                                             'status_msg'   => "C(254) NOTNULL DEFAULT ''",
                                             'uname'        => "C(254) NOTNULL DEFAULT ''");


    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $admin_category = DBUtil::getLimitedTablename('zim_message');
    $dbtable['zim_message'] = $admin_category;
    $dbtable['zim_message_column'] = array( 'mid'       => 'z_mid',
                                            'from'      => 'z_from',
                                            'to'        => 'z_to',
                                            'message'   => 'z_message',
                                            'recd'      => 'z_recd',
                                            'sent_on'   => 'z_sent_on',
                                            'recd_on'   => 'z_recd_on');

    $dbtable['zim_message_column_def'] = array( 'mid'       => "I NOTNULL AUTO PRIMARY",
                                                'from'      => "I NOTNULL DEFAULT -1",
                                                'to'        => "I NOTNULL DEFAULT -1",
                                                'message'   => "XL",
                                                'recd'      => "I",
                                                'sent_on'   => 'T',
                                                'recd_on'   => 'T');

    $dbtable['zim_message_column_idx'] = array ('mtf' => array('mid', 'to', 'from'));

    // Return the table information
    return $dbtable;
}
