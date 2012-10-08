<?php

/**
 * Description of UserLog
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_UserLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserAccessLog';
    protected $_schema = 'hid';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('user_id'),
            'refTableClass' => array('App_Model_Db_Table_User'),
            'refColumns'    => array('kopel')
        )
    );
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }

    /**
     * Adds an user log account for the specified user with the specified data.
     *
     * @param integer $userId The userid this account will belong to
     * @param array $data an assoc array with keys as fields and values as data
     * to add
     *
     * @return integer 0 if the data wasn't added, otherwise the id of the newly added
     * row
     */
    public function addUserLog(Array $data)
    {
        $whiteList = array(
            'user_id',
            'user_ip',
            'login'
        );

        $addData = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $whiteList)) {
                $addData[$key] = $value;
            }
        }

        if (empty($addData)) {
            return 0;
        }

        $id = $this->insert($addData);

        if ((int)$id == 0) {
            return $id;
        }

        return $id;
    }
    
    /**
     * Updates an user log with the specified data.
     *
     * @param integer $userId The $userId of the user log account to update
     * @param array $data an assoc array with keys as fields and values as data
     * to update
     *
     * @return integer The number of rows updated. If any input error happened, -1
     * will be returned. 0 will be returned, if the data was not changed, i.e. if
     * the row equaled to teh data tried to update. 1 will be returned if any changes to
     * the data has been made.
     */
    public function updateUserLog($userId, Array $data)
    {
        $updateData = array();

        $whiteList = array(
            'lastlogin'
        );

        foreach ($data as $key => $value) {
            if (in_array($key, $whiteList)) {
                $updateData[$key] = $value;
            }
        }

        if (empty($updateData)) {
            return -1;
        }

        if ($where = $this->fetchRow("user_id='".$userId."' AND (lastlogin='0000-00-00 00:00:00' or isnull(lastlogin))","user_access_log_id DESC")) {
	        $where->lastlogin = new Zend_Db_Expr('NOW()');
	        return $where->save();        
        }
    }
}
