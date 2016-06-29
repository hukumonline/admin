<?php
class Pandamp_Controller_Action_Helper_GetFb
{
	public function getFb($url)
	{
		try {
			$url="http://api.facebook.com/restserver.php?format=json&method=links.getStats&urls=".$url;
			$result["method"]="getRestServer - deprecaated sebenarnya , untuk internal aja atau admin";
			$result["fb-url"]=$url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); // set url
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$urlObject=curl_exec($ch);
			$urlObject=json_decode($urlObject);
			curl_close ($ch);
			$convert = (array)$urlObject[0];
			
			//$result=array_merge($result,$convert);
			//return $result;
			return $convert;
		}
		catch (Exception $e)
		{
			
		}
	}
}