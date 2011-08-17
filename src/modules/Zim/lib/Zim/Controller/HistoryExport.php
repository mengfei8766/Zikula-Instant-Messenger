<?php
/**
 * Zikula-Instant-Messenger (ZIM)
 *
 * @Copyright Kyle Giovannetti 2011
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @author  Kyle Giovannetti
 * @package Zim
 */

class Zim_Controller_HistoryExport extends Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @retrun void
     */
    protected function postInitialize()
    {
        // In this controller we never want caching.
        $this->view->setCaching(false);
        $this->uid = UserUtil::getVar('uid');
    }

    /**
     * the main administration function
     * Just a stub for now
     * @return void
     */
    public function main()
    {
        //security checks
        if (!SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT)) {
            return LogUtil::registerPermissionError();
        }
        $this->checkCsrfToken();
        
        //get the contact the user wants the history for.
        $uid = (int)$this->request->getPost()->get('contact');

        //get the message history between the user and the contact.
        $messages = ModUtil::apiFunc('Zim', 'history', 'get_history', array('user1' => $this->uid, 'user2' => $uid));
        $myemail = UserUtil::getVar('email', $this->uid);
        $theiremail = UserUtil::getVar('email', $uid);
        $treated = array();
        foreach($messages as $message) {
            if ($message['to']['uid'] == $this->uid) {
                $to_email = $myemail;
                $from_email = $theiremail;
            } else {
                $to_email = $theiremail;
                $from_email = $myemail;
            }
            $m = array($message['to']['uname'] . "($to_email)",$message['from']['uname'] . "($from_email)",$message['created_at'],$message['message']);
            array_push($treated, $m);
        }
        $titlerow = array('To', 'From', 'Sent On', 'Message');
        //TODO: better name or allow users to enter the name?
        $exportFile = 'history.csv';
        //TODO: see https://github.com/zikula/core/blob/master/src/system/Users/lib/Users/Controller/Admin.php#L2061 for examples of this
        $delimiter = ',';
        return FileUtil::exportCSV($treated, $titlerow, $delimiter, '"', $exportFile);   
    }
    
    public function get_html() {
        //security checks
        if (!SecurityUtil::checkPermission('Zim::', '::', ACCESS_COMMENT)) {
            return LogUtil::registerPermissionError();
        }
        $this->checkCsrfToken();

        //get the contact the user wants the history for.
        $uid = (int)$this->request->getPost()->get('contact');

        //get the message history between the user and the contact.
        $messages = ModUtil::apiFunc('Zim', 'history', 'get_history', array('user1' => $this->uid, 'user2' => $uid));

        //get the template and return it to the user.
        $this->view->assign(array('messages' => $messages));
        $table = $this->view->fetch('zim_block_history_messages.tpl');
        $this->view->assign(array('uid1' => $this->uid,
                                  'uid2' => $uid,
                                  'table'=> $table));
        $output = $this->view->fetch('zim_history_html.tpl');
        
        return $this->output('history.htm', $output);
    }
    
    private function output($filename, $data) {
        $filename = DataUtil::formatForOS($filename);
        //disable compression and set headers
        ob_end_clean();
        ini_set('zlib.output_compression', 0);
        header('Cache-Control: no-store, no-cache');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Transfer-Encoding: binary');

        // open a file for csv writing
        $out = fopen("php://output", 'w');

        // write out data
        fwrite($out, $data);
         //close the out file
        fclose($out);
        exit;
    }
}