<?php

include_once("baseinit.php");

//error_reporting(E_ALL);

$application->bootstrap();

try {
	if (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
		ob_start();
		$application->run();
		$output = gzencode(ob_get_contents(), 9);
		ob_end_clean();
		header('Content-Encoding: gzip');
		echo $output;
	} else {
		$application->run();
	}
} catch (Exeption $e) {
	if (Zend_Registry::isRegistered('Zend_Log')) {
		Zend_Registry::get('Zend_Log')->err($e->getMessage());
	}
	$message = $e->getMessage() . "\n\n" . $e->getTraceAsString();
}