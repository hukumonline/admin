<?php

/**
 * Description of Remote
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Lib_Remote
{
    /**
     *
     */
    static function serverCmd($cmd, $vars=null)
    {
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
        $url = $config->getOption('service');

        $curl = curl_init($url['config']['account']['url'] . '?cmd=' . urlencode($cmd));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_COOKIE, "PHPSESSID=" . $this->getSessionId());

        if (isset($vars)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $vars);
        }

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $body = curl_exec($curl);
        $ret = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl) != 0) throw new Exception("Service failure: HTTP request to server failed. " . curl_error($curl));

        return array($ret, $body);
    }
}
