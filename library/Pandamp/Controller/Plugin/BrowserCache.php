<?php

/**
 * Description of BrowserCache
 *
 * @author nihki <nihki@hukumonline.com>
 */
class Pandamp_Controller_Plugin_BrowserCache
    extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $send_body = true;
        $etag = '"' . md5($this->getResponse()->getBody()) . '"';
        $inm = explode(',', getenv("HTTP_IF_NONE_MATCH"));
        $inm = str_replace('-gzip', '', $inm);
        $response_code = $this->getResponse()->getHttpResponseCode();
        if (($response_code > 200 && $response_code < 206) || ($response_code == 304)) {
            foreach ($inm as $i) {
            if (trim($i) == $etag) {
                    $this->getResponse()
                         ->clearAllHeaders()
                         ->setHttpResponseCode(304)
                         ->clearBody();
                    $send_body = false;
                    break;
                }
            }
        }
        $this->getResponse()
             ->setHeader('Cache-Control', 'max-age=7200, private, proxy-revalidate', true)
             ->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 2 * 3600) . ' GMT', true)
             ->clearRawHeaders();
        if ($send_body) {
            $this->getResponse()
                 ->setHeader('Content-Length', strlen($this->getResponse()->getBody()));
        }

        $this->getResponse()->setHeader('ETag', $etag, true);
        $this->getResponse()->setHeader('Pragma', '');
    }
}
?>
