<?php

/**
 * Description of Folder
 *
 * @author user
 */
class Pandamp_Core_Hol_Folder
{
    public function delete($folderGuid)
    {
        $tbl = new App_Model_Db_Table_Folder();
        $rowset = $tbl->find($folderGuid);
        if(count($rowset))
        {
            $row = $rowset->current();
            $row->delete();
        }
    }
    public function forceDelete($folderGuid)
    {
        $tblFolder = new App_Model_Db_Table_Folder();
        $rowSet = $tblFolder->fetchChildren($folderGuid);
        $row1 = $tblFolder->find($folderGuid)->current();

        foreach($rowSet as $row)
        {
            $this->forceDelete($row->guid);
        }

        $rowsetCatalogFolder = $row1->findDependentRowsetCatalogFolder();

        $tblCatalog = new App_Model_Db_Table_Catalog();
        $bpmCatalog = new Pandamp_Core_Hol_Catalog();
        if(count($rowsetCatalogFolder))
        {
            foreach($rowsetCatalogFolder as $rowCatalogFolder)
            {
                $rowCatalog = $tblCatalog->find($rowCatalogFolder->catalogGuid)->current();
                if ($rowCatalog) $bpmCatalog->delete($rowCatalog->guid);
            }

            $this->delete($row1->guid);
        }
        else
        {
            $this->delete($row1->guid);
        }
    }
}
