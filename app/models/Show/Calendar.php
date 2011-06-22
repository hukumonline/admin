<?php

/**
 * Description of Catalog
 *
 * @author nihki <nihki@madaniyah.com>
 */

class App_Model_Show_Calendar extends App_Model_Db_DefaultAdapter
{
    /**
     * class instance object
     */
    private static $_instance;

    private static $_db;

    /**
     * de-activate constructor
     */
    final private function  __construct()
    {
        //$zl = Zend_Registry::get("Zend_Locale");
        //if ($zl->getLanguage() == 'id')
            self::$_db = Zend_Registry::get('db1');
        //else
            //self::$_db = Zend_Registry::get('db3');
    }

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
    public function EventDateCalendar($month, $year)
    {
        $db = parent::_getDefaultAdapter();

        $sql = "SELECT id,d,title,text,start_time,end_time, ";

        if (TIME_DISPLAY_FORMAT == "12hr") {
                $sql .= "TIME_FORMAT(start_time, '%l:%i%p') AS stime, ";
                $sql .= "TIME_FORMAT(end_time, '%l:%i%p') AS etime ";
        } elseif (TIME_DISPLAY_FORMAT == "24hr") {
                $sql .= "TIME_FORMAT(start_time, '%H:%i') AS stime, ";
                $sql .= "TIME_FORMAT(end_time, '%H:%i') AS etime ";
        } else {
                echo "Bad time display format, check your configuration file.";
        }

        $sql .= "FROM calendar_mssgs WHERE m = $month AND y = $year ";
        $sql .= "ORDER BY start_time";

        $conn = self::$_db;

        $statement = $conn->query($sql);

        $result = $statement->fetchAll(Zend_Db::FETCH_OBJ);

        return $result;
    }

    /*
    public function getCountCalendar()
    {
        $db = parent::_dbSelect();

        $statement = $db->from('calendar_mssgs',array('COUNT(*) as count'))
                ->joinLeft('KutuUser','calendar_mssgs.uid=KutuUser.guid',array());

        $conn = self::$_db;

        $row = $conn->fetchRow($statement);

        return ($row !== null) ? $row['count'] : 0;
    }
     *
     */
    
    public function openPosting( $pid )
    {
        $sql = "SELECT d, m, y FROM calendar_mssgs WHERE id=".$pid;

        $conn = self::$_db;

        $db = $conn->query($sql);
        $dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        return $dataFetch;
    }
    public function writePosting( $pid )
    {
        $sql = "SELECT cm.y, cm.m, cm.d, cm.title, cm.text, cm.start_time, cm.end_time, ";
        $sql .= "ku.kopel, ku.username, ";

        if (TIME_DISPLAY_FORMAT == "12hr") {
                $sql .= "TIME_FORMAT(cm.start_time, '%l:%i%p') AS stime, ";
                $sql .= "TIME_FORMAT(cm.end_time, '%l:%i%p') AS etime ";
        } elseif (TIME_DISPLAY_FORMAT == "24hr") {
                $sql .= "TIME_FORMAT(cm.start_time, '%H:%i') AS stime, ";
                $sql .= "TIME_FORMAT(cm.end_time, '%H:%i') AS etime ";
        } else {
                echo "Bad time display format, check your configuration file.";
        }

        $sql .= "FROM calendar_mssgs AS cm ";
        $sql .= "LEFT JOIN hid.KutuUser AS ku ";
        $sql .= "ON (cm.uid = ku.kopel) ";
        $sql .= "WHERE cm.id = " . $pid;

        $conn = self::$_db;

        $db = $conn->query($sql);
        $dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        return $dataFetch;
    }

}
