<?php
class Pandamp_Controller_Action_Helper_IsMobile
{
	public function isMobile($string)
	{
		return preg_match('/(iPhone|iPod|iPad|Android|BlackBerry|J2ME)/i', $string);
	}
}