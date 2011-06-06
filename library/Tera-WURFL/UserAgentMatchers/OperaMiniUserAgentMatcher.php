<?php
/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurflUserAgentMatchers
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * Provides a specific user agent matching technique
 * @package TeraWurflUserAgentMatchers
 */
class OperaMiniUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		"browser_opera_mini_release1",
		"browser_opera_mini_release2",
		"browser_opera_mini_release3",
		"browser_opera_mini_release4",
		"browser_opera_mini_release4"
	);
	
	public function __construct(TeraWurfl $wurfl){
		parent::__construct($wurfl);
	}
	public function applyConclusiveMatch($ua) {
		$tolerance = UserAgentUtils::firstSlash($ua);
		$this->wurfl->toLog("Applying ".get_class($this)." Conclusive Match: RIS with threshold $tolerance",LOG_INFO);
		return $this->risMatch($ua, $tolerance);
	}
	public function recoveryMatch($ua){
       $this->wurfl->toLog("Applying ".get_class($this)." recovery match ($ua)",LOG_INFO);
    	if(self::contains($ua,"Opera Mini/1")){
    		return "browser_opera_mini_release1";
    	}
		if(self::contains($ua,"Opera Mini/2")){
    		return "browser_opera_mini_release2";
    	}
		if(self::contains($ua,"Opera Mini/3")){
    		return "browser_opera_mini_release3";
    	}
		if(self::contains($ua,"Opera Mini/4")){
    		return "browser_opera_mini_release4";
    	}
		if(self::contains($ua,"Opera Mini/5")){
    		return "browser_opera_mini_release5";
    	}
		if(self::contains($ua,"Opera Mobi")){
    		return "browser_opera_mini_release4";
    	}
		return "browser_opera_mini_release1";
	}
}
