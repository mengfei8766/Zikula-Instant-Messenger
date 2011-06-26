<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Installer extends Zikula_AbstractInstaller
{
    /**
     * Initialise the Zim module.
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation succcesful, false otherwise.
     */
    public function install()
    {
         
        try {
            DoctrineUtil::createTablesFromModels('Zim');
        } catch (Exception $e) {
            return LogUtil::registerError("<pre>".$e);
            return false;
        }

        $this->setVar('message_check_period', 4);
        $this->setVar('contact_update_freq', 3);
        $this->setVar('show_offline', 0);
        $this->setVar('allow_offline_msg', 1);
        $this->setVar('timeout_period', 30);
        $this->setVar('allowed_msg_tags', '<b><u>');
        $this->setVar('use_minjs', 0);
        $this->setVar('keep_history', 1);

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
        $tables = array(
            'zim_users',
            'zim_state',
            'zim_message',
            'zim_message_history'
            );

            foreach ($tables as $table) {
                $r = DoctrineUtil::dropTable($table);
            }
            $this->delVars();
            // Deletion successful
            return true;
    }
}
