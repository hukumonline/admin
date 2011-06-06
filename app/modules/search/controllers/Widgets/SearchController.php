<?php

/**
 * Description of SearchController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Search_Widgets_SearchController extends Zend_Controller_Action
{
    function fclinicAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query))
        {
            $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
        } else {

            if ($category)
            {
                //$querySolr = $query.' -profile:kutu_doc status:99 profile:'.$category.';publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc profile:'.$category;
            }
            else
            {
                //$querySolr = $query.' -profile:kutu_doc status:99;publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr,0, 1);
        }


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->kategoriklinik as $facet => $count)
        {
            if ($count == 0)
            {
                continue;
            }
            else
            {
                $data[$content][0] = $facet;
                $data[$content][1] = $count;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }
    function fprofileAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query))
        {
            $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
        } else {

            if ($category)
            {
                //$querySolr = $query.' -profile:kutu_doc status:99 profile:'.$category.';publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc profile:'.$category;
            }
            else
            {
                //$querySolr = $query.' -profile:kutu_doc status:99;publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr,0, 1);
        }


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->profile as $facet => $count)
        {
            if ($count == 0 || in_array($facet, array('comment','partner','kategoriklinik','kutu_signup')))
            {
                continue;
            }
            else
            {
                $f = str_replace(array('kutu_'), "", $facet);
                $data[$content][0] = $f;
                $data[$content][1] = $count;
                $data[$content][3] = $facet;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }
    function fholdAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query))
        {
            $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
        } else {

            if ($category)
            {
                //$querySolr = $query.' -profile:kutu_doc status:99 profile:'.$category.';publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc profile:'.$category;
            }
            else
            {
                //$querySolr = $query.' -profile:kutu_doc status:99;publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr,0, 1);
        }


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->regulationType as $facet => $count)
        {
            if ($count == 0)
            {
                continue;
            }
            else
            {
                $data[$content][0] = $facet;
                $data[$content][1] = $count;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }
    function fcreateAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query))
        {
            $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
        } else {

            if ($category)
            {
                //$querySolr = $query.' -profile:kutu_doc status:99 profile:'.$category.';publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc profile:'.$category;
            }
            else
            {
                //$querySolr = $query.' -profile:kutu_doc status:99;publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr,0, 1);
        }


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->createdBy as $facet => $count)
        {
            if ($count == 0 || in_array($facet, array('comment','partner','kategoriklinik','kutu_signup')))
            {
                continue;
            }
            else
            {
                $f = str_replace(array('kutu_'), "", $facet);
                $data[$content][0] = $f;
                $data[$content][1] = $count;
                $data[$content][3] = $facet;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }
}
