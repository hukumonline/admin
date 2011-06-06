<?php

/**
 * module Password Encryption/Decryption
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Pandamp_Crypt_Password
{
	function encryptPassword($password)
	{
		
		$obj = new Pandamp_Crypt_SecurePassword('kutu');
		
		return $obj->crypt($password);
		
	}
	function matchPassword($password, $hash)
	{ 
		$obj = new Pandamp_Crypt_SecurePassword('kutu');
		$e = $obj->decrypt($hash);
		
		if($e == $password) //match password by the hash
		{ 
		   return true;
		}
		else
		{
			//die($hash);
			//check if password is SHA
			if(strtoupper(sha1($password))==strtoupper($hash))
				return true;
			else
		   		return false;
		} 
	}
	function decryptPassword($hash)
	{
		$obj = new Pandamp_Crypt_SecurePassword('kutu');
		return $obj->decrypt($hash);
	}
}
