<?php

class Dev_ApiController extends Zend_Controller_Action 
{
	function shortenerAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$text = "http://pmg.hukumonline.n1/berita/baca/lt4df84dc5d6c93/referensi-hasil-pertelaan-kembali";
		
		$http = new Zend_Http_Client();		
		$http->setUri("http://hukum.nl/api?url=".$text);
		$response = $http->request();
		if ($response->isSuccessful())
		{
//			$result = Zend_Json::decode($response->getBody());
			$result = $response->getBody();
//			if (isset($result["results"][$longUrl]["shortUrl"])) {
//				$shortUrl =  $result["results"][$longUrl]["shortUrl"];
//			}
			$data = simplexml_load_string($response->getBody());
			$t_url = $data->holurl;
			echo (string) $t_url;
//			print_r($result["results"]);
//			echo "<pre>";print_r($result);echo "</pre>";
//			echo $shortUrl;
		
		}
	}
}