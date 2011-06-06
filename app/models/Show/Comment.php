<?php

/**
 * Description of Comment
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Comment extends App_Model_Db_DefaultAdapter
{
    /**
     * class instance object
     */
    private static $_instance;

    /**
     * de-activate constructor
     */
    final private function  __construct() {}

     /**
      * de-activate object cloning
      */
    final private function  __clone() {}

    /**
     * @return obj
     */
    public function show()
    {
        if (!isset(self::$_instance)) {
                $show = __CLASS__;
                self::$_instance = new $show;
        }
        return self::$_instance;
    }

    /**
     * @return obj
     */
    public function getParentCommentCount($objectId)
    {
    	if (!isset($objectId)) {
    		return 0;
    	}

        $db = parent::_dbSelect();
    	$select = $db->from('comments', array(
                        'COUNT(id) as count_id'
                    ))
                    ->where('object_id=?',$objectId)
                    ->where('parent=?',0)
                    ->where('published=?',99);

    	$row = parent::_getDefaultAdapter()->fetchRow($select);

    	return ($row !== null) ? $row['count_id'] : 0;
    }
    public function getCommentByGuid($guid)
    {
        $rows = parent::_getDefaultAdapter()->fetchAll($this->select()->where('object_id=?', $guid));
        return $rows;
    }
    public function fetchComment($start = 0 , $end = 0)
    {
        $db = parent::_getDefaultAdapter();
        $query = $db->query("SELECT * FROM comments ORDER BY id desc LIMIT $start, $end");

        $result = $query->fetchAll(Zend_Db::FETCH_OBJ);

        return $result;
    }
    public function getNumOfComment()
    {
        $db = parent::_dbSelect();
    	$select = $db->from('comments', array(
                            'COUNT(id) as count_id'
                        ));

    	$row = parent::_getDefaultAdapter()->fetchRow($select);

    	return ($row !== null) ? $row['count_id'] : 0;
    }
}
