<?php

class Pandamp_Debug
{
	public static function manager($data)
	{
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		//die;
	}
}