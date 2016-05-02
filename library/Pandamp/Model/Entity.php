<?php

class Pandamp_Model_Entity 
{
	protected $_properties;
	
	public function __construct($data) 
	{
		if (is_object($data)) {
			$data = (array)$data;
		}
		if (!is_array($data)) {
			//throw new Exception('The data must be an array or object');
		}
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getProperties() 
	{
		return $this->_properties;
	}
	
	public function __set($name, $value)
	{
		$this->_properties[$name] = $value;
	}
	
	public function __get($name) 
	{
		if (array_key_exists($name, $this->_properties)) {
			return $this->_properties[$name];
		}
		return null;
	}
	
	public function __isset($name) 
	{
		return isset($this->_properties[$name]);
	}
	
	public function __unset($name) 
	{
		if (isset($this->$name)) {
			$this->_properties[$name] = null;
		}
	}
}
