<?php

/**
 * Description of Expense
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Expense
{
    public function expense($exp=NULL)
    {
        /*
        list($ret, $body) = Pandamp_Lib_Remote::serverCmd('selectUserExpense', array('exp'=>$exp));
        return $body;
         *
         */

        $tblExpense = new App_Model_Db_Table_Expense();
        $row = $tblExpense->fetchAll();

        $expense = "<select name=\"expense\" id=\"expense\">\n";
        if ($exp) {
            $rowExpense = $tblExpense->find($exp)->current();
            $expense .= "<option value='$rowExpense->expenseId' selected>$rowExpense->description</option>";
            $expense .= "<option value=''>Choose:</option>";
        } else {
            $expense .= "<option value='' selected>Choose:</option>";
        }
        foreach ($row as $rowset) {
            if (($exp) and ($rowset->expenseId == $rowExpense->expenseId)) {
                continue;
            } else {
                $expense .= "<option value='$rowset->expenseId'>$rowset->description</option>";
            }
        }
        $expense .= "</select>\n\n";
        return $expense;
    }
}
