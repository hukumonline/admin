<?php
/**
 * @author	2011-2012 Nihki Prihadi
 * @version $Id: PaginatorAdapter.php 1 2012-01-05 13:24Z $
 */

class Pandamp_Utility_PaginatorAdapter extends Zend_Paginator_Adapter_Iterator
{
	public function __construct(Iterator $iterator, $count)
	{
		parent::__construct($iterator);
		$this->_count = $count;
	}
}
