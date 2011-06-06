<?php

/**
 * Description of Detail
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_Catalog_Detail
{
    public $view;
    public $catalogGuid;
    public $folderGuid;

    public function __construct($catalogGuid, $folderGuid)
    {
        $this->view = new Zend_View();
        $this->view->setScriptPath(dirname(__FILE__));

        $this->catalogGuid = $catalogGuid;
        $this->folderGuid = $folderGuid;

        $this->view();
    }
    public function view()
    {
        $this->view->addHelperPath(ROOT_DIR.'/library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');

        $catalogGuid =($this->catalogGuid)? $this->catalogGuid : '';
        $node =($this->folderGuid)? $this->folderGuid : 'root';

        $tblCatalog = new App_Model_Db_Table_Catalog();

        if(!empty($catalogGuid))
        {
            $rowCatalog = $tblCatalog->find($catalogGuid)->current();
            $rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();

            $tableProfileAttribute = new App_Model_Db_Table_ProfileAttribute();
            $profileGuid = $rowCatalog->profileGuid;
            $where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
            $rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');

            $aAttribute = array();
            $i = 0;
            $tblAttribute = new App_Model_Db_Table_Attribute();
            foreach ($rowsetProfileAttribute as $rowProfileAttribute)
            {
                if($rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid))
                {
                    $rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid);

                    $rowsetAttribute = $tblAttribute->find($rowCatalogAttribute->attributeGuid);
                    if(count($rowsetAttribute))
                    {
                        $rowAttribute = $rowsetAttribute->current();
                        $aAttribute[$i]['name'] =  $rowAttribute->name;
                    }
                    else
                    {
                        $aAttribute[$i]['name'] =  '';
                    }
                    
                    $aAttribute[$i]['value'] = $rowCatalogAttribute->value;

                }
                else
                {

                }
                $i++;
            }
        }
        $this->view->aAttribute = $aAttribute;
        $this->view->rowCatalog = $rowCatalog;
        $this->view->rowsetCatalogAttribute = $rowsetCatalogAttribute;
        $this->view->node = $node;
        $this->view->catalogGuid = $catalogGuid;

        $rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedExpired');
        if(!empty($rowCatalogAttribute->value))
        {
            $tDate = $rowCatalogAttribute->value;
            $aDate = explode('-', $tDate);
            $year=$aDate[0];
            $month=$aDate[1];
            $day=$aDate[2];
            $hour="00";
            $minute="00";
            $second="00";

            $event="My birthday";

            $time=mktime($hour, $minute, $second, $month, $day, $year);

            $timecurrent=date('U');
            $cuntdowntime=$time-$timecurrent;
            $cuntdownminutes=$cuntdowntime/60;
            $cuntdownhours=$cuntdowntime/3600;
            $cuntdowndays=$cuntdownhours/24;
            $cuntdownmonths=$cuntdowndays/30;
            $cuntdownyears=$cuntdowndays/365;

            if($cuntdowndays < 0)
            {
                echo "<script>alert('Dokumen perjanjian ini telah berakhir masa berlakunya.');</script>";
                echo "<br><strong>Dokumen perjanjian ini telah berakhir masa berlakunya.</strong>";
            }
            else
            {
                echo "<br><strong>Dokumen perjanjian ini akan berakhir masa berlakunya dalam ".round($cuntdowndays)." hari.</strong>";
            }
        }

    }
    public function render()
    {
        $aName = explode('_', basename(__FILE__));

        return $this->view->render(str_replace('.php','.phtml',strtolower($aName[count($aName)-1])));
    }
}
