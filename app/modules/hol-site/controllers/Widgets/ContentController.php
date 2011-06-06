<?php

/**
 * Description of ContentController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class HolSite_Widgets_ContentController extends Zend_Controller_Action
{
    function pusatdataAction()
    {
        
    }
    function aktualAction()
    {
        $modelCatalog = App_Model_Show_Catalog::show()->fetchFromFolder('fb29',0,5);
        $this->view->rowset = $modelCatalog;
    }
    function klinikAction()
    {
        $rowset = App_Model_Show_Catalog::show()->fetchFromFolder('lt4a0a533e31979',0,3);

        $content = 0;
        $data = array();

        foreach ($rowset as $row)
        {
            $rowCatalogTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedCommentTitle');
            $rowCatalogQuestion = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedCommentQuestion');
            $rowCatalogSelectCat = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedKategoriKlinik');
            $author = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedSelectNama');
            $source = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedSelectMitra');

            /* Get Category from profile clinic_category */
            $findCategory = App_Model_Show_Catalog::show()->getCatalogByGuid($rowCatalogSelectCat);
            $category = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($findCategory['guid'],'fixedTitle');

            /* Get Author from profile author */
            $findAuthor = App_Model_Show_Catalog::show()->getCatalogByGuid($author);
            $author = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($findAuthor['guid'],'fixedTitle');

            /* Get Source from profile partner */
            $findSource = App_Model_Show_Catalog::show()->getCatalogByGuid($source);

            if ($findSource) {
                    $source = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($findSource['guid'],'fixedTitle');
            }

            $data[$content][0] = $rowCatalogTitle;
            $data[$content][1] = $rowCatalogQuestion;
            $data[$content][2] = $category;
            $data[$content][3] = $row['guid'];
            $data[$content][4] = $row['createdBy'];
            $data[$content][5] = (isset($author))? $author : '';
            $data[$content][6] = (isset($source))? $source : '';
            $content++;
        }

        $num_rows = count($rowset);

        $this->view->numberOfRows = $num_rows;
        $this->view->data = $data;
    }
    function utamaAction()
    {
        $rowset = App_Model_Show_Catalog::show()->fetchFromFolder('lt4aaa29322bdbb',0,4);

        $content = 0;
        $data = array();

        foreach ($rowset as $row)
        {
            $rowSumComment = App_Model_Show_Comment::show()->getParentCommentCount($row['guid']);

            $data[$content][0] = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedTitle');
            $data[$content][1] = strftime("%H:%M",strtotime($row['createdDate']));
            $data[$content][2] = $row['guid'];
            $data[$content][3] = $row['shortTitle'];
            $data[$content][4] = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedAuthor');
            $data[$content][5] = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedDescription');
            $data[$content][6] = $rowSumComment;
            $content++;
        }

        $num_rows = count($rowset);

        $this->view->numberOfRows = $num_rows;
        $this->view->data = $data;
    }
}
