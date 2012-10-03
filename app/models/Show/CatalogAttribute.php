<?php

/**
 * Description of CatalogAttribute
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_CatalogAttribute extends App_Model_Db_DefaultAdapter
{
    /**
     * class instance object
     */
    private static $_instance;

    private static $_db;

    /**
     * de-activate constructor
     */
    final private function  __construct()
    {
        $zl = Zend_Registry::get("Zend_Locale");
        if ($zl->getLanguage() == 'id')
            self::$_db = Zend_Registry::get('db1');
        else
            self::$_db = Zend_Registry::get('db3');
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
    
    public function getCatalogAttributeValue($catalogGuid, $attributeGuid)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuCatalogAttribute',array(
                            'value'
                      ))
                      ->where('catalogGuid=?',$catalogGuid)
                      ->where('attributeGuid=?',$attributeGuid);

        $conn = self::$_db;

        $row = $conn->fetchRow($select);

        return ($row !== null) ? $row['value'] : '';
    }

	public function getFormatedURLForDetalPeraturan($title, $subtitle)
	{
		// try to fetch from the cache first
		//$cache = Zend_Registry::get('cache');
		//$cacheKey = "gcav_cg_".$catalogGuid."_ag_".$attributeGuid;
		//$row = $cache->load($cacheKey);
		//if (!$row) {
		$title = strip_tags($title);
		$title = strtolower($title);
		$title = str_replace("\"", "", $title);
		$title = str_replace('/', '_', $title);
		$title = str_replace('\'', '', $title);
		$title = str_replace('nomor', 'no', $title);
		$title = str_replace('undang-undang', 'uu', $title);
		$title = str_replace('uu darurat', 'undang-undang darurat', $title);
		$title = str_replace('uu dasar', 'uud', $title);
		$title = str_replace('peraturan pemerintah', 'pp', $title);
		$title = str_replace('peraturan daerah', 'perda', $title);
		$title = str_replace('keputusan gubernur', 'pergub', $title);
		$title = str_replace('majelis permusyawaratan rakyat', 'mpr', $title);
		$title = str_replace('pp pengganti uu', 'perpu', $title);
		$title = str_replace('mahkamah konstitusi', 'mk', $title);
		$title = str_replace('mahkamah agung', 'ma', $title);
		$title = str_replace('surat edaran', 'se', $title);
		$title = str_replace('instruksi presiden', 'inpres', $title);
		$title = str_replace('keputusan presiden', 'keppres', $title);
		$title = str_replace('penetapan presiden', 'penpres', $title);
		$title = str_replace('peraturan presiden', 'perpres', $title);
		$title = str_replace('ketetapan mpr', 'tap mpr', $title);
		$title = str_replace(' - ', '-', $title);
		$title = str_replace(' ', '-', $title);
		
		$subtitle = strip_tags($subtitle);
		$subtitle = strtolower($subtitle);
		$subtitle = preg_replace('/\%/','persen', $subtitle);
		$subtitle = str_replace("\"", "", $subtitle);
		$subtitle = str_replace('/', '_', $subtitle);
		$subtitle = str_replace('\'', '', $subtitle);
		$subtitle = str_replace(' - ', '-', $subtitle);
		$subtitle = str_replace(' ', '-', $subtitle);
		
		$x = $title."-".$subtitle;
		
		$x = str_replace("--", "-", $x);
			//$cache->save($row);
		//}
		return $x;
	}
	
	public function getFormatedURLForKlinikTitle($title)
	{
		// try to fetch from the cache first
		//$cache = Zend_Registry::get('cache');
		//$cacheKey = "gcav_cg_".$catalogGuid."_ag_".$attributeGuid;
		//$row = $cache->load($cacheKey);
		//if (!$row) {
		$title = strip_tags($title);
		$title = strtolower($title);
		$title = str_replace("\"", "", $title);
		$title = str_replace('/', '_', $title);
		$title = str_replace('\'', '', $title);
		$title = str_replace(' - ', '-', $title);
		$title = str_replace(' ', '-', $title);
		$title = str_replace("--", "-", $title);
			//$cache->save($row);
		//}
		return $title;
	}
}
