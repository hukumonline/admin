<?php

/**
 * Description of Log
 *
 * @author nihki <nihki@hukumonline.com>
 */
class Pandamp_Log extends Zend_Log
{
    public function __construct()
    {
        // set formatter, disini ditambahkan %class% untuk informasi
        // log message dihasilkan dari class mana
        $format = '%timestamp% (%priorityName%) %priority% %class%: %message%'.PHP_EOL;
        $this->_formatter = new Zend_Log_Formatter_Simple($format);
        parent::addWriter($this->_errorWriter());
        parent::addWriter($this->_allWriter());
        parent::__construct();
    }

    /**
     * Writer untuk log message error
     * Log message error akan dituliskan ke dalam file error.log
     */
    protected function _errorWriter()
    {
    	$log = ROOT_DIR.'/data/log/error.log';
		$stream = fopen($log, 'a', false);
		if (! $stream) {
		    throw new Exception('Failed to open _error stream');
		}
        $writer = new Zend_Log_Writer_Stream($stream);
        $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::ERR));
        $writer->setFormatter($this->_formatter);
        return $writer;
    }

    /**
     * Writer untuk log message yang lainnya
     * Log message yang lainnya akan dituliskan ke dalam file system.log
     */
    protected function _allWriter()
    {
    	$log = ROOT_DIR.'/data/log/system.log';
		$stream = fopen($log, 'a', false);
		if (! $stream) {
		    throw new Exception('Failed to open _writer stream');
		}
        $writer = new Zend_Log_Writer_Stream($stream);
        $writer->setFormatter($this->_formatter);
        return $writer;
    }
}
?>
