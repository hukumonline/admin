<?php

/**
 * Description of PaymentConfirmation
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_PaymentConfirmation extends App_Model_Db_DefaultAdapter
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

    /**
     * @return obj
     */
    function unconfirmListCount($where='')
    {
        $db = parent::_getDefaultAdapter();
        $query = $db->query("Select count(paymentId) AS count
                                FROM
                                    ".self::$_schema.".KutuPaymentConfirmation AS KPC,
                                    ".self::$_schema.".KutuOrder AS KO,
                                    hid.KutuUser AS KU
                                WHERE
                                    KO.orderId = KPC.orderId
                                AND
                                    KU.kopel = KO.userId
                                AND
                                    KPC.confirmed = 0
                                $where");

        $result = $query->fetchAll(Zend_Db::FETCH_OBJ);

        return $result[0]->count;
    }
    public function unconfirmList($where='', $limit, $offset)
    {
        $db = parent::_getDefaultAdapter();
        $query = $db->query("SELECT
        						KU.username, KU.fullName, KO.invoiceNumber, KO.orderTotal, KO.paymentMethod,
        						KPC.senderAccount, KPC.senderAccountName, KPC.bankName, KPC.note, KPC.destinationAccount,
        						KPC.amount, KPC.paymentDate, KPC.orderId
                            FROM
                                ".self::$_schema.".KutuPaymentConfirmation AS KPC,
                                ".self::$_schema.".KutuOrder AS KO,
                                hid.KutuUser AS KU,
                                ".self::$_schema.".KutuOrderStatus AS KOS
                            WHERE
                                KO.orderId = KPC.orderId
                            AND
                                KU.kopel = KO.userId
                            AND
                                KPC.confirmed = 0
                            AND
                                KO.orderStatus = KOS.orderStatusId
                            $where
                                LIMIT $offset, $limit");

        $result = $query->fetchAll(Zend_Db::FETCH_ASSOC);

        return $result;
    }
}
?>
