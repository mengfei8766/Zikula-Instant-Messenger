<?php
/**
 * Copyright Kyle Giovannetti 2011
 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zim
 *
 */

class Zim_Installer extends Zikula_Installer
{
    /**
     * Initialise the Admin module.
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation succcesful, false otherwise.
     */
    public function install()
    {
        if (!DBUtil::createTable('zim_users')) {
            return false;
        }

        if (!DBUtil::createTable('zim_message')) {
            return false;
        }

        $this->setVar('message_check_period', 4);
        $this->setVar('contact_update_freq', 6);
        $this->setVar('show_offline', 0);
        $this->setVar('allow_offline_msg', 1);
        
        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion)
        {
        case '0.0.1':
            return $this->upgrade('0.0.2');
        case '0.0.2':    
            if (!DBUtil::changeTable('zim_users')) {
                return '0.0.2';
            }
            $this->setVar('message_check_period', 4);
            $this->setVar('contact_update_freq', 6);
            $this->setVar('show_offline', 0);
            return $this->upgrade('0.0.3');
        case '0.0.3':    
            if (!DBUtil::changeTable('zim_users')) {
                return '0.0.3';
            }
            $this->setVar('allow_offline_msg', 1);
            break;
        }

        // Update successful
        return true;
    }

    /**
     * delete the Zim module
     *
     * @return bool true if deletetion succcesful, false otherwise
     */
    public function uninstall()
    {
        if (!DBUtil::dropTable('zim_users')) {
            return false;
        }

        if (!DBUtil::dropTable('zim_message')) {
            return false;
        }

        $this->delVars();

        // Deletion successful
        return true;
    }
}
