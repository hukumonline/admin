<?php

/**
 * Description of GetSolrDir
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_GetSolrDir
{
    public function getSolrDir()
    {
        $indexingEngine = Pandamp_Search::manager();

        return $indexingEngine->getSolrDir();
    }
}
