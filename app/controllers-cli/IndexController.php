<?php

class IndexController extends Application_Controller_Cli
{


	/**
	 *	Just run
	 *  php cli.php
	 *  nohup /usr/local/zend/bin/php cli.php index < /dev/null &
	 */
	public function indexAction ()
	{
		
	}
	
	/**
	 * php cli.php info
	 */
	public function infoAction ()
	{
		echo <<<info
Usage:
	php cli.php index info
		This information.


info;

	}


	public function errorAction ()
	{
		throw new Exception ("Some error.");
	}
	
	protected function getCatalogAttribute($guid,$attributeGuid)
	{
		$db = $this->db;
	
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuCatalogAttribute', ['value']);
		$sql->where('catalogGuid=?',$guid);
		$sql->where('attributeGuid=?',$attributeGuid);
		$row = $db->fetchRow($sql);
	
		$sql = $sql->__toString();
	
		return ($row) ? $row->value : '';
	}
	
	protected function checkExist($guid)
	{
		$db = $this->db3;
		
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		
		$sql->from('pio_event_1', '*');
		$sql->where("properties LIKE '%$guid%'");
		$row = $db->fetchRow($sql);
	
	
		return $row;
	}	
	
	public function generate()
	{
		$db = $this->db2;
		
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$sql = $db->select();
		$sql->from('KutuNumber', ['pio']);
		$sql->where('num=?',1);
		$rowset = $db->fetchRow($sql);
		
		$num = $rowset->pio;
		$num = strval($num);
		$jumdigit = strlen($num);
	
		$kod = str_pad($num, $jumdigit, '0', STR_PAD_LEFT);
	
		return $kod;
	}
	public function counter()
	{
		try {
			$db = $this->db2;
			$data = ['pio' => new Zend_Db_Expr('pio + 1')];
			$db->update('KutuNumber', $data, 'num=1');
		}
		catch (Exception $e)
		{
		}
	}
	
}
