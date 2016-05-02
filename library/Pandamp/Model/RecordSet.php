<?php

class Pandamp_Model_RecordSet implements Countable, Iterator, ArrayAccess 
{
	/**
	 * @var int
	 */
	protected $_count = 0;
	
	private $_iteratorIndex = 0;
	
	/**
	 * @var Pandamp_Model_Gateway
	 */
	protected $_gateway;
	
	/**
	 * @var string
	 */
	protected $_entityClass;
	
	/**
	 * @var mixed
	 */
	protected $_results;
	
	public function __construct($results, $gateway, $entityClass = null) 
	{
		$this->_results 	= $results;
		$this->_gateway 	= $gateway;
		$this->_entityClass = $entityClass;
	}
	
	/*
	 * Implement Countable interface
	 */
	
	public function count() 
	{
		if (null == $this->_count) {
			$this->_count = count($this->_results);
		}
		return $this->_count;
	}
	
	/*
	 * Implement Iterator interface
	 */
	
	public function key() 
	{
		return key($this->_results);	
	}
	
	public function next() 
	{
		$this->_iteratorIndex++;
		return next($this->_results);
	}
	
	public function rewind() 
	{
		$this->_iteratorIndex = 0;
		return reset($this->_results);
	}
	
	public function valid() 
	{
		return $this->_iteratorIndex < $this->count();
//		return (bool)$this->current();
	}
	
	public function current() 
	{
		$key = ($this->_results instanceof Iterator)
				? $this->_results->key() 
				: key($this->_results);
		$result = $this->_results[$key];
		$result = $this->_gateway->convert($result);
			
		return $result;
	}
	
	/*
	 * Implement ArrayAccess interface
	 */
	
	public function offsetExists($key) 
	{
		return array_key_exists($key, $this->_results);
	}
	
	public function offsetGet($key) 
	{
		$result = $this->_gateway->convert($this->_results[$key]);
        return $result;
    }
    
	public function offsetSet($key, $element) 
	{
        $this->_results[$key] = $element;
        $this->_count = count($this->_results);
    }
    
	public function offsetUnset($key) 
	{
        unset($this->_results[$key]);
        $this->_count = count($this->_results);
    }
}