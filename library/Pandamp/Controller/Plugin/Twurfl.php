<?php

/**
 * Description of Twurfl
 *
 * @author nihki <nihki@hukumonline.com>
 */

require_once 'Tera-WURFL/TeraWurfl.php';

class Pandamp_Controller_Plugin_Twurfl extends Zend_Controller_Plugin_Abstract
{
    public function  dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $tw = new TeraWurfl();
        $tw->getDeviceCapabilitiesFromAgent($request->getHeader('User-Agent'));
        Zend_Registry::set('twurfl', $tw);
    }
}
?>
