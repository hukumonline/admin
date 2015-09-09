<?php
class Pandamp_Controller_Action_Helper_GetNumber extends Zend_Controller_Action_Helper_Abstract
{
	public function generate($field)
	{
		$rowset = App_Model_Show_Number::show()->getNumber();
		$num = $rowset[$field];
		$num = strval($num);
		$jumdigit = strlen($num);
		
		$kod = str_pad($num, $jumdigit, '0', STR_PAD_LEFT);
		
		return $kod;
	}
	public function counter($field)
	{
		try {
			$modelNumber = new App_Model_Db_Table_Number();
			$modelNumber->update(array($field=>new Zend_Db_Expr($field.' + 1')), 'num=1');
		}
		catch (Exception $e)
		{
		}
	}
}