<?php

/**
 * Description of Order
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Order extends App_Model_Db_DefaultAdapter
{
    /**
     * class instance object
     */
    private static $_instance;

    private static $_schema;

    /**
     * de-activate constructor
     */
    final private function  __construct()
    {
    	$config = Pandamp_Config::getConfig();
        $zl = Zend_Registry::get('Zend_Locale');
        if ($zl->getLanguage() == "id")
        {
            self::$_schema = $config->web->db->ina;
        }
        else {
            self::$_schema = $config->web->db->en;
        }
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

    public function getOrderSummaryAdmin($where,$limit,$offset)
    {
        $db = parent::_getDefaultAdapter();
        $query = $db->query("SELECT KO.*,KOS.ordersStatus, COUNT(itemId) AS countTotal,KU.* FROM ((".self::$_schema.".KutuOrder AS KO Left join ".self::$_schema.".KutuOrderDetail AS KOD ON KOD.orderId=KO.orderId)
                                LEFT JOIN hid.KutuUser AS KU ON KU.kopel = KO.userId)
                                LEFT JOIN ".self::$_schema.".KutuOrderStatus AS KOS ON KOS.orderStatusId = KO.orderStatus
                                WHERE $where GROUP BY(KO.orderId) DESC
                                LIMIT $offset, $limit");

        $result = $query->fetchAll(Zend_Db::FETCH_OBJ);

        return $result;
    }
    public function countOrdersAdmin($where)
    {
        $db = parent::_getDefaultAdapter();
        $query = $db->query("Select count(orderId) AS count
                                FROM
                                    ".self::$_schema.".KutuOrder AS KO,
                                    ".self::$_schema.".KutuOrderStatus AS KOS,
                                    hid.KutuUser as KU
                                WHERE
                                    KOS.orderStatusId = KO.orderStatus
                                AND
                                    KU.kopel = KO.userId
                                AND
                                     ".$where);

        $result = $query->fetchAll(Zend_Db::FETCH_OBJ);

        return $result[0]->count;
    }
    public function getOrderAndStatus($orderId)
    {
        $db = parent::_getDefaultAdapter();
        $query = $db->query("SELECT KO.*, KOS.* FROM KutuOrder AS KO,KutuOrderStatus AS KOS WHERE KO.orderStatus = KOS.orderStatusId AND KO.orderId = $orderId");

        $result = $query->fetchAll(Zend_Db::FETCH_ASSOC);

        return $result;
    }
    public function getOrder($idOrder)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuOrder')
                    ->where("orderId = ". $idOrder);

        $result = parent::_getDefaultAdapter()->fetchAll($select);

        return $result;
    }
}
