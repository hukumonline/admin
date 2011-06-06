<?php

/**
 * Description of Country
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Country extends Zend_View_Helper_FormSelect
{
    public function country($elementName="countryId", $selectedValue)
    {
        $config = new Zend_Config_Xml(CONFIG_PATH.'/countries.xml','countries');
        $aCountries = array();
        foreach($config->get('country') as $country)
        {
            $aCountries[$country->alpha2] = $country->name;
        }

        return $this->formSelect($elementName, $selectedValue, null , $aCountries);

    }
}
