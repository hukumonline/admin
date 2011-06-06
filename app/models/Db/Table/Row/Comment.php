<?php

/**
 * Description of Comment
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Row_Comment extends Zend_Db_Table_Row_Abstract
{
    protected function _delete()
    {
        $modelComment = new App_Model_Db_Table_Comment();
        $modelComment->delete("parent=".$this->id);
    }
}
