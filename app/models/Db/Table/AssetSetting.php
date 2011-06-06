<?php

/**
 * Description of AssetSetting
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_AssetSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuAssetSetting';
    protected function  _setupDatabaseAdapter()
    {
        $zl = Zend_Registry::get('Zend_Locale');
        if ($zl->getLanguage() == 'id')
            $this->_db = Zend_Registry::get('db1');
        else
            $this->_db = Zend_Registry::get('db3');

        parent::_setupDatabaseAdapter();
    }
}
