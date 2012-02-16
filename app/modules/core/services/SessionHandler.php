<?php
/**
 * @author	2011-2012 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: SessionHandler.php 1 2012-02-07 15:48:23Z $
 */


class Core_Services_SessionHandler implements Zend_Session_SaveHandler_Interface 
{
	/**
	 * @var Core_Services_SessionHandler
	 */
	private static $_instance;
	
	/**
	 * @var Core_Models_Interface_Session
	 */
	private $_sessionDb;
	
    /**
     * constatns
     */
    // primary key column name
    const COLUMN_PRIMARY_KEY = 'sessionId';
    // lifetime column name
    const COLUMN_LIFETIME    = 'sessionExpiration';
    // data column name
    const COLUMN_DATA        = 'sessionData';

    /**
     * primary key
     * @var string
     */
    protected $_primary = self::COLUMN_PRIMARY_KEY;

    /**
     * table columns.
     * @var array
     */
    protected $_columnMap = array(
        self::COLUMN_PRIMARY_KEY => self::COLUMN_PRIMARY_KEY,
        self::COLUMN_LIFETIME    => self::COLUMN_LIFETIME,
        self::COLUMN_DATA        => self::COLUMN_DATA
    );

    /**
     * session maxlifetime
     * @var null|intger
     */
    protected $_lifetime = null;

    /**
     * constructor
     * @param string $table      Session table name
     * @param array  $columnMap  Session table column names
     */
    public function __construct()
    {
    	$this->_sessionDb = new App_Model_Db_Table_Session();
    	
    }

    /**
     * Set session max lifetime.
     * @param $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->_lifetime = $lifetime;
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name)
    {
        return true;
    }

	/**
	 * @return Core_Services_SessionHandler
	 */
	public static function getInstance() 
	{
		if (null == self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $return = '';
        $where = $this->_sessionDb->getAdapter()->quoteInto($this->_columnMap[self::COLUMN_PRIMARY_KEY] . "=?", $id);
        if ($row = $this->_sessionDb->fetchRow($where)) {
            $return = $row->{$this->_columnMap[self::COLUMN_DATA]};
        }
        return $return;
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     */
    public function write($id, $data)
    {
        $return = false;
        $dataSet = array(
            $this->_columnMap[self::COLUMN_PRIMARY_KEY] => $id,
            $this->_columnMap[self::COLUMN_LIFETIME]    => date("Y-m-d H:i:s", mktime()),
            $this->_columnMap[self::COLUMN_DATA]        => $data
        );
        $where = $this->_sessionDb->getAdapter()->quoteInto($this->_columnMap[self::COLUMN_PRIMARY_KEY] . "=?", $id);

        if ($this->_sessionDb->fetchRow($where)) {
            $return = ($this->_sessionDb->update($dataSet, $where)) ? true : false;
        } else {
            $return = ($this->_sessionDb->insert($dataSet)) ? true: false;
        }

        return $return;
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $where = $this->_sessionDb->getAdapter()->quoteInto($this->_columnMap[self::COLUMN_PRIMARY_KEY] . "=?", $id);
        return ($this->_sessionDb->delete($where)) ? true : false;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        $lifetime = ($this->_lifetime) ? $this->_lifetime : $maxlifetime;
        $expiry = date("Y-m-d H:i:s", mktime() - $lifetime);
        $where = $this->_sessionDb->getAdapter()->quoteInto($this->_columnMap[self::COLUMN_LIFETIME] . "<=?", $expiry);
        return ($this->_sessionDb->delete($where)) ? true : false;
    }
}
