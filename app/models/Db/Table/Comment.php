<?php

/**
 * Description of Comment
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Comment extends Zend_Db_Table_Abstract
{
    protected $_name = 'comments';
    protected $_rowClass = 'App_Model_Db_Table_Row_Comment';
}
