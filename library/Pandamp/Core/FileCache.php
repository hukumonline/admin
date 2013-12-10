<?php 
class Pandamp_Core_FileCache
{
	/**
	 * Clear all caching file in given directory
	 *
	 * @param string $dir
	 */
	public static function clear($dir)
	{
		return Pandamp_Utility_File::deleteRescursiveDir($dir);
	}
}
